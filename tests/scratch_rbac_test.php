<?php

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use App\Models\Role;
use App\Models\Permission;
use App\Models\User;
use App\Http\Middleware\EnsureUserHasPermission;
use Illuminate\Http\Request;

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

echo "=========================================\n";
echo "RBAC SECURITY TEST RUNNER (Standalone)\n";
echo "=========================================\n";

DB::beginTransaction();

try {
    // 1. Fetch system roles
    $adminRole = Role::where('slug', 'admin')->firstOrFail();
    $agentRole = Role::where('slug', 'agent')->firstOrFail();
    $clientRole = Role::where('slug', 'client')->firstOrFail();

    // 2. Find or create test users
    $admin = User::firstOrCreate(['email' => 'admin.test@bucavoyages.test'], [
        'name' => 'Admin Test',
        'password' => bcrypt('Password@123'),
        'role_id' => $adminRole->id,
        'status' => 'active',
    ]);

    $agent = User::firstOrCreate(['email' => 'agent.test@bucavoyages.test'], [
        'name' => 'Agent Test',
        'password' => bcrypt('Password@123'),
        'role_id' => $agentRole->id,
        'status' => 'active',
    ]);

    $client = User::firstOrCreate(['email' => 'client.test@bucavoyages.test'], [
        'name' => 'Client Test',
        'password' => bcrypt('Password@123'),
        'role_id' => $clientRole->id,
        'status' => 'active',
    ]);

    echo "[+] Setup completed. Users initialized.\n";

    // 3. Test hasPermission and hasModule helper methods on User model
    echo "\n--- Testing User Model Helpers ---\n";
    
    // Admin checks
    assert($admin->hasPermission('users.manage') === true, "Admin should have users.manage permission");
    assert($admin->hasPermission('parcels.manage') === true, "Admin should have parcels.manage permission");
    assert($admin->hasModule('Securite') === true, "Admin should have Securite module access");
    echo "[+] Admin User Helper Tests - PASS\n";

    // Agent checks
    assert($agent->hasPermission('vip_clients.manage') === true, "Agent should have vip_clients.manage permission");
    assert($agent->hasPermission('parcels.manage') === true, "Agent should have parcels.manage permission");
    assert($agent->hasPermission('users.manage') === false, "Agent should NOT have users.manage permission");
    assert($agent->hasModule('Securite') === false, "Agent should NOT have Securite module access");
    assert($agent->hasModule('Clients VIP') === true, "Agent should have Clients VIP module access");
    echo "[+] Agent User Helper Tests - PASS\n";

    // Client checks
    assert($client->hasPermission('dashboard.view') === true, "Client should have dashboard.view permission");
    assert($client->hasPermission('vip_clients.manage') === false, "Client should NOT have vip_clients.manage permission");
    assert($client->hasModule('Clients VIP') === false, "Client should NOT have Clients VIP module access");
    echo "[+] Client User Helper Tests - PASS\n";

    // 4. Test Laravel Gates
    echo "\n--- Testing Laravel Gates ---\n";
    
    assert(Gate::forUser($admin)->allows('users.manage') === true, "Gate: Admin should be allowed users.manage");
    assert(Gate::forUser($agent)->allows('vip_clients.manage') === true, "Gate: Agent should be allowed vip_clients.manage");
    assert(Gate::forUser($agent)->allows('users.manage') === false, "Gate: Agent should NOT be allowed users.manage");
    assert(Gate::forUser($client)->allows('dashboard.view') === true, "Gate: Client should be allowed dashboard.view");
    assert(Gate::forUser($client)->allows('parcels.manage') === false, "Gate: Client should NOT be allowed parcels.manage");
    
    echo "[+] Laravel Gates Integration Tests - PASS\n";

    // 5. Test Middleware: EnsureUserHasPermission
    echo "\n--- Testing EnsureUserHasPermission Middleware ---\n";
    
    $middleware = new EnsureUserHasPermission();
    
    // Simulating requests
    $request = new Request();

    // 5.1 Request for Admin on users.manage (should pass)
    $request->setUserResolver(fn () => $admin);
    $response = $middleware->handle($request, function ($req) {
        return new \Symfony\Component\HttpFoundation\Response("next_called");
    }, 'users.manage');
    assert($response->getContent() === "next_called", "Middleware should allow admin for users.manage");

    // 5.2 Request for Agent on parcels.manage (should pass)
    $request->setUserResolver(fn () => $agent);
    $response = $middleware->handle($request, function ($req) {
        return new \Symfony\Component\HttpFoundation\Response("next_called");
    }, 'parcels.manage');
    assert($response->getContent() === "next_called", "Middleware should allow agent for parcels.manage");

    // 5.3 Request for Agent on users.manage (should fail with 403)
    try {
        $middleware->handle($request, function ($req) {
            return new \Symfony\Component\HttpFoundation\Response("next_called");
        }, 'users.manage');
        throw new Exception("Middleware should have blocked agent on users.manage");
    } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
        assert($e->getStatusCode() === 403, "Middleware should return 403 for unauthorized access");
        echo "[+] Middleware block check - PASS\n";
    }

    // 5.4 Request with multiple permissions (should pass if user has any of them)
    // Agent has 'parcels.manage' but not 'users.manage'
    $response = $middleware->handle($request, function ($req) {
        return new \Symfony\Component\HttpFoundation\Response("next_called");
    }, 'users.manage', 'parcels.manage');
    assert($response->getContent() === "next_called", "Middleware should pass if user has at least one permission");
    echo "[+] Middleware multi-permission check - PASS\n";

    echo "\n=========================================\n";
    echo "ALL RBAC SECURITY TESTS PASSED SUCCESSFULLY!\n";
    echo "=========================================\n";

} catch (Exception $e) {
    echo "\n[-] TEST FAILURE: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
} finally {
    DB::rollBack();
    echo "[+] Database transactions rolled back. Database is clean.\n";
}
