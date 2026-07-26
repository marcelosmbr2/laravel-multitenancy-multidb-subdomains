<x-layouts.app title="Dashboard">
    <div class="mb-6">
        <h1 class="text-2xl font-semibold">Dashboard</h1>

        <p class="text-base-content/60">
            Your data lives in <code>{{ $company->database }}.sqlite</code>, separate from every
            other company.
        </p>
    </div>

    <div class="stats stats-vertical bg-base-100 mb-6 w-full shadow-sm sm:stats-horizontal">
        <div class="stat">
            <div class="stat-title">Plan</div>
            <div class="stat-value text-2xl">{{ $company->plan->name }}</div>
            <div class="stat-desc">
                Up to {{ $company->plan->max_employees }} employees and
                {{ $company->plan->max_products }} products
            </div>
        </div>

        <div class="stat">
            <div class="stat-title">Employees</div>
            <div class="stat-value">{{ $employees->count() }}<span class="text-base-content/40 text-xl">/{{ $company->plan->max_employees }}</span></div>
            <div class="stat-desc">
                @if ($company->canAddEmployee())
                    {{ $company->remainingEmployees() }} remaining
                @else
                    <span class="text-error">Plan limit reached</span>
                @endif
            </div>
        </div>

        <div class="stat">
            <div class="stat-title">Products</div>
            <div class="stat-value">{{ $products->count() }}<span class="text-base-content/40 text-xl">/{{ $company->plan->max_products }}</span></div>
            <div class="stat-desc">
                @if ($company->canAddProduct())
                    {{ $company->remainingProducts() }} remaining
                @else
                    <span class="text-error">Plan limit reached</span>
                @endif
            </div>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <h2 class="card-title">Employees</h2>

                <div class="overflow-x-auto">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($employees as $employee)
                                <tr>
                                    <td>{{ $employee->name }}</td>
                                    <td>{{ $employee->email }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-base-content/60 text-center">No employees yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <h2 class="card-title">Categories</h2>

                <ul class="list">
                    @forelse ($categories as $category)
                        <li class="list-row">
                            <div>
                                <div class="font-medium">{{ $category->name }}</div>
                                <div class="text-base-content/60 text-sm">{{ $category->description }}</div>
                            </div>
                        </li>
                    @empty
                        <li class="text-base-content/60 text-sm">No categories yet.</li>
                    @endforelse
                </ul>
            </div>
        </div>

        <div class="card bg-base-100 shadow-sm lg:col-span-2">
            <div class="card-body">
                <h2 class="card-title">Products</h2>

                <div class="overflow-x-auto">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>SKU</th>
                                <th>Name</th>
                                <th>Category</th>
                                <th class="text-right">Price</th>
                                <th class="text-right">Qty</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($products as $product)
                                <tr>
                                    <td><code class="text-sm">{{ $product->sku }}</code></td>
                                    <td>{{ $product->name }}</td>
                                    <td>{{ $product->category->name }}</td>
                                    <td class="text-right">{{ number_format($product->price, 2) }}</td>
                                    <td class="text-right">{{ $product->quantity }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-base-content/60 text-center">No products yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
