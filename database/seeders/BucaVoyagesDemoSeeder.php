<?php

namespace Database\Seeders;

use App\Models\Agency;
use App\Models\Package as VipPackage;
use App\Models\Permission;
use App\Models\Role;
use App\Models\TravelRoute;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class BucaVoyagesDemoSeeder extends Seeder
{
    public function run(): void
    {
        $roles = $this->seedRolesAndPermissions();
        $agencies = $this->seedAgencies();
        $routes = $this->seedRoutes();
        $this->seedUsers($roles, $agencies);
        $this->seedPackages($routes);
    }

    private function seedRolesAndPermissions(): array
    {
        $permissionData = [
            ['name' => 'Voir le tableau de bord', 'slug' => 'dashboard.view', 'module' => 'Tableau de bord'],
            ['name' => 'Gerer les utilisateurs', 'slug' => 'users.manage', 'module' => 'Securite'],
            ['name' => 'Gerer les roles', 'slug' => 'roles.manage', 'module' => 'Securite'],
            ['name' => 'Gerer les clients VIP', 'slug' => 'vip_clients.manage', 'module' => 'Clients VIP'],
            ['name' => 'Gerer les cartes VIP', 'slug' => 'vip_cards.manage', 'module' => 'Cartes VIP'],
            ['name' => 'Suspendre une carte VIP', 'slug' => 'vip_cards.suspend', 'module' => 'Cartes VIP'],
            ['name' => 'Gerer les forfaits', 'slug' => 'packages.manage', 'module' => 'Forfaits'],
            ['name' => 'Consommer un voyage', 'slug' => 'trips.consume', 'module' => 'Voyages'],
            ['name' => 'Voir les paiements', 'slug' => 'payments.view', 'module' => 'Paiements'],
            ['name' => 'Gerer les paiements', 'slug' => 'payments.manage', 'module' => 'Paiements'],
            ['name' => 'Voir les audits', 'slug' => 'audit.view', 'module' => 'Audit'],
            ['name' => 'Voir les colis', 'slug' => 'parcels.view', 'module' => 'Colis'],
            ['name' => 'Gerer les colis', 'slug' => 'parcels.manage', 'module' => 'Colis'],
        ];

        $permissions = collect($permissionData)->mapWithKeys(function (array $data) {
            $permission = Permission::updateOrCreate(
                ['slug' => $data['slug']],
                $data
            );

            return [$permission->slug => $permission];
        });

        $roleRules = [
            'admin' => [
                'name' => 'Administrateur general',
                'description' => 'Acces complet a la plateforme Buca Voyages VIP.',
                'permissions' => $permissions->keys()->all(),
            ],
            'agent' => [
                'name' => 'Agent agence',
                'description' => 'Gere les clients, cartes, forfaits, voyages et paiements en agence.',
                'permissions' => [
                    'dashboard.view',
                    'vip_clients.manage',
                    'vip_cards.manage',
                    'vip_cards.suspend',
                    'packages.manage',
                    'trips.consume',
                    'payments.view',
                    'payments.manage',
                    'parcels.view',
                    'parcels.manage',
                ],
            ],
            'client' => [
                'name' => 'Client VIP',
                'description' => 'Consulte son solde et son historique.',
                'permissions' => ['dashboard.view'],
            ],
        ];

        $roles = [];

        foreach ($roleRules as $slug => $data) {
            $role = Role::updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $data['name'],
                    'description' => $data['description'],
                ]
            );

            $role->permissions()->sync(
                $permissions->only($data['permissions'])->pluck('id')->all()
            );

            $roles[$slug] = $role;
        }

        return $roles;
    }

    private function seedAgencies(): array
    {
        $data = [
            'mvan' => ['name' => 'Agence Mvan', 'city' => 'Yaounde', 'district' => 'Mvan', 'address' => 'Mvan, Yaounde', 'phone' => '+237 690 000 001'],
            'mboppi' => ['name' => 'Agence Mboppi', 'city' => 'Douala', 'district' => 'Mboppi', 'address' => 'Mboppi, Douala', 'phone' => '+237 690 000 002'],
            'yassa' => ['name' => 'Agence Yassa', 'city' => 'Douala', 'district' => 'Yassa', 'address' => 'Yassa, Douala', 'phone' => '+237 690 000 003'],
        ];

        return collect($data)->mapWithKeys(fn (array $agency, string $key) => [
            $key => Agency::updateOrCreate(['name' => $agency['name']], $agency + ['status' => 'active']),
        ])->all();
    }

    private function seedRoutes(): array
    {
        $data = [
            'yde-dla' => ['departure_city' => 'Yaounde', 'arrival_city' => 'Douala', 'distance_km' => 245, 'estimated_duration' => '4h30'],
            'dla-yde' => ['departure_city' => 'Douala', 'arrival_city' => 'Yaounde', 'distance_km' => 245, 'estimated_duration' => '4h30'],
            'yde-ebolowa' => ['departure_city' => 'Yaounde', 'arrival_city' => 'Ebolowa', 'distance_km' => 168, 'estimated_duration' => '3h00'],
            'yde-sangmelima' => ['departure_city' => 'Yaounde', 'arrival_city' => 'Sangmelima', 'distance_km' => 172, 'estimated_duration' => '3h15'],
            'dla-pointe-noire' => ['departure_city' => 'Douala', 'arrival_city' => 'Pointe-Noire', 'distance_km' => 760, 'estimated_duration' => '12h00'],
        ];

        return collect($data)->mapWithKeys(fn (array $route, string $key) => [
            $key => TravelRoute::updateOrCreate(
                ['departure_city' => $route['departure_city'], 'arrival_city' => $route['arrival_city']],
                $route + ['status' => 'active']
            ),
        ])->all();
    }

    private function seedUsers(array $roles, array $agencies): array
    {
        $password = Hash::make('Password@123');

        $data = [
            'admin' => ['name' => 'Admin Buca VIP', 'email' => 'admin@bucavoyages.test', 'phone' => '+237 699 100 000', 'role_id' => $roles['admin']->id, 'agency_id' => $agencies['mvan']->id],
            'agent' => ['name' => 'Agent Mboppi', 'email' => 'agent@bucavoyages.test', 'phone' => '+237 699 100 002', 'role_id' => $roles['agent']->id, 'agency_id' => $agencies['mboppi']->id],
        ];

        return collect($data)->mapWithKeys(fn (array $user, string $key) => [
            $key => User::updateOrCreate(
                ['email' => $user['email']],
                $user + ['password' => $password, 'status' => 'active', 'email_verified_at' => now()]
            ),
        ])->all();
    }

    private function seedPackages(array $routes): array
    {
        $data = [
            'weekly-classic' => ['name' => 'Hebdomadaire Classique', 'period_type' => 'weekly', 'trip_count' => 4, 'validity_days' => 7, 'price' => 28000, 'travel_route_id' => null, 'class_type' => 'classique'],
            'monthly-vip' => ['name' => 'Mensuel VIP', 'period_type' => 'monthly', 'trip_count' => 10, 'validity_days' => 30, 'price' => 90000, 'travel_route_id' => $routes['yde-dla']->id, 'class_type' => 'vip'],
            'monthly-vip-plus' => ['name' => 'Mensuel VIP Plus', 'period_type' => 'monthly', 'trip_count' => 20, 'validity_days' => 30, 'price' => 170000, 'travel_route_id' => null, 'class_type' => 'vip'],
            'annual-vip' => ['name' => 'Annuel VIP', 'period_type' => 'annual', 'trip_count' => 120, 'validity_days' => 365, 'price' => 950000, 'travel_route_id' => null, 'class_type' => 'vip'],
            'custom-business' => ['name' => 'Personnalise Entreprise', 'period_type' => 'custom', 'trip_count' => 50, 'validity_days' => 90, 'price' => 420000, 'travel_route_id' => null, 'class_type' => 'mixed'],
        ];

        return collect($data)->mapWithKeys(fn (array $package, string $key) => [
            $key => VipPackage::updateOrCreate(['name' => $package['name']], $package + ['status' => 'active']),
        ])->all();
    }
}
