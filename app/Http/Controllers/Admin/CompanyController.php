<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Company;
use App\Models\Product;
use Illuminate\Contracts\View\View;

class CompanyController extends Controller
{
    /**
     * Read the tenant data of an arbitrary company.
     *
     * Everything tenant-scoped is read inside `Company::execute()`, which makes the company current
     * for the duration of the closure and restores the previous state afterwards. Touching a
     * tenant model outside of it would hit a `tenant` connection whose database is null.
     */
    public function show(Company $company): View
    {
        $company->load('plan', 'users');

        [$categories, $products] = $company->execute(fn (): array => [
            Category::orderBy('name')->get(),
            Product::with('category')->orderBy('name')->get(),
        ]);

        return view('admin.companies.show', [
            'company' => $company,
            'categories' => $categories,
            'products' => $products,
        ]);
    }
}
