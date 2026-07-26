<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

/**
 * The home page of a company subdomain, for the company itself and for its employees alike.
 *
 * The two roles share one route because the subdomain already says which company this is, leaving
 * nothing to put in a path prefix. Laravel matches the first route registered for a path, so two
 * routes on `/` guarded by different `role:` middleware would 403 rather than fall through to each
 * other — hence one action serving two views.
 *
 * The tenant is already current here: `IdentifyTenantBySubdomain` made it so from the host, and the
 * `tenant` middleware group asserted it.
 */
class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $company = $request->user()->company;

        $catalogue = [
            'company' => $company,
            'categories' => Category::orderBy('name')->get(),
            'products' => Product::with('category')->orderBy('name')->get(),
        ];

        if ($request->user()->isEmployee()) {
            return view('employee.dashboard', $catalogue);
        }

        return view('company.dashboard', [
            ...$catalogue,
            'company' => $company->load('plan'),
            'employees' => $company->employees()->orderBy('name')->get(),
        ]);
    }
}
