<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    /**
     * The global panel: every company, with counts read from each tenant database in turn.
     */
    public function index(): View
    {
        $companies = Company::with('plan')->orderBy('name')->get();

        // Each of these switches the tenant connection to another SQLite file and back.
        $usage = $companies->mapWithKeys(fn (Company $company): array => [
            $company->id => [
                'employees' => $company->employeesCount(),
                'categories' => $company->categoriesCount(),
                'products' => $company->productsCount(),
            ],
        ]);

        return view('admin.dashboard', [
            'companies' => $companies,
            'usage' => $usage,
        ]);
    }
}
