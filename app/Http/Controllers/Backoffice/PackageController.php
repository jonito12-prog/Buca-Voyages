<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\TravelRoute;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PackageController extends Controller
{
    public function index(): View
    {
        $packages = Package::query()
            ->with('travelRoute')
            ->orderBy('status')
            ->orderBy('price')
            ->get();

        return view('backoffice.packages.index', compact('packages'));
    }

    public function create(): View
    {
        return view('backoffice.packages.create', [
            'package' => new Package(['status' => 'active', 'class_type' => 'vip']),
            'routes' => TravelRoute::where('status', 'active')->orderBy('departure_city')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $package = Package::create($this->validatedData($request));

        return redirect()->route('packages.index')
            ->with('status', 'Formule "' . $package->name . '" creee avec succes.');
    }

    public function edit(Package $package): View
    {
        return view('backoffice.packages.edit', [
            'package' => $package,
            'routes' => TravelRoute::where('status', 'active')->orderBy('departure_city')->get(),
        ]);
    }

    public function update(Request $request, Package $package): RedirectResponse
    {
        $package->update($this->validatedData($request));

        return redirect()->route('packages.index')
            ->with('status', 'Formule mise a jour avec succes.');
    }

    public function toggleStatus(Package $package): RedirectResponse
    {
        $package->update([
            'status' => $package->status === 'active' ? 'inactive' : 'active',
        ]);

        return redirect()->route('packages.index')
            ->with('status', $package->status === 'active' ? 'Formule activee.' : 'Formule desactivee.');
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'period_type' => ['required', Rule::in(['weekly', 'monthly', 'annual', 'custom'])],
            'trip_count' => ['required', 'integer', 'min:1', 'max:500'],
            'validity_days' => ['required', 'integer', 'min:1', 'max:730'],
            'price' => ['required', 'numeric', 'min:0'],
            'travel_route_id' => ['nullable', 'integer', Rule::exists('travel_routes', 'id')],
            'class_type' => ['required', Rule::in(['vip', 'classique', 'mixed'])],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);
    }
}
