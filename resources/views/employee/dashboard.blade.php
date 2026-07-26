<x-layouts.app title="Dashboard">
    <div class="mb-6">
        <h1 class="text-2xl font-semibold">Dashboard</h1>

        <p class="text-base-content/60">
            You are an employee of <span class="font-medium">{{ $company->name }}</span> and see only
            its catalogue.
        </p>
    </div>

    <div class="stats stats-vertical bg-base-100 mb-6 w-full shadow-sm sm:stats-horizontal">
        <div class="stat">
            <div class="stat-title">Company</div>
            <div class="stat-value text-2xl">{{ $company->name }}</div>
            <div class="stat-desc"><code>{{ $company->database }}.sqlite</code></div>
        </div>

        <div class="stat">
            <div class="stat-title">Categories</div>
            <div class="stat-value">{{ $categories->count() }}</div>
        </div>

        <div class="stat">
            <div class="stat-title">Products</div>
            <div class="stat-value">{{ $products->count() }}</div>
        </div>
    </div>

    <div class="card bg-base-100 shadow-sm">
        <div class="card-body">
            <h2 class="card-title">Catalogue</h2>

            <div class="overflow-x-auto">
                <table class="table">
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
</x-layouts.app>
