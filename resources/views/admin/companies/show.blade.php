<x-layouts.app :title="$company->name">
    <div class="mb-6">
        <a href="{{ route('admin.dashboard') }}" class="link link-hover text-sm">&larr; Back to administration</a>

        <div class="mt-2 flex items-center gap-3">
            <x-brandmark :company="$company" size="lg" />

            <h1 class="text-2xl font-semibold">{{ $company->name }}</h1>
        </div>

        <p class="text-base-content/60">
            Read from <code>{{ $company->database }}.sqlite</code> through
            <code>Company::execute()</code>.
        </p>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <h2 class="card-title">Details</h2>

                <dl class="grid grid-cols-2 gap-y-2 text-sm">
                    <dt class="text-base-content/60">Subdomain</dt>
                    <dd>
                        <a href="{{ $company->url() }}" class="link link-hover">{{ $company->domain() }}</a>
                    </dd>

                    <dt class="text-base-content/60">Plan</dt>
                    <dd><span class="badge badge-soft">{{ $company->plan->name }}</span></dd>

                    <dt class="text-base-content/60">Document</dt>
                    <dd>{{ $company->document ?? '—' }}</dd>

                    <dt class="text-base-content/60">Phone</dt>
                    <dd>{{ $company->phone ?? '—' }}</dd>

                    <dt class="text-base-content/60">Registered</dt>
                    <dd>{{ $company->created_at->format('d/m/Y') }}</dd>
                </dl>
            </div>
        </div>

        <div class="card bg-base-100 shadow-sm lg:col-span-2">
            <div class="card-body">
                <h2 class="card-title">Users <span class="text-base-content/60 text-sm font-normal">(landlord)</span></h2>

                <div class="overflow-x-auto">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($company->users as $user)
                                <tr>
                                    <td>{{ $user->name }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td><span class="badge badge-soft badge-sm">{{ $user->role->label() }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <h2 class="card-title">Categories <span class="text-base-content/60 text-sm font-normal">(tenant)</span></h2>

                <ul class="list">
                    @forelse ($categories as $category)
                        <li class="list-row">
                            <div>
                                <div class="font-medium">{{ $category->name }}</div>
                                <div class="text-base-content/60 text-sm">{{ $category->description }}</div>
                            </div>
                        </li>
                    @empty
                        <li class="text-base-content/60 text-sm">No categories.</li>
                    @endforelse
                </ul>
            </div>
        </div>

        <div class="card bg-base-100 shadow-sm lg:col-span-2">
            <div class="card-body">
                <h2 class="card-title">Products <span class="text-base-content/60 text-sm font-normal">(tenant)</span></h2>

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
                                    <td colspan="5" class="text-base-content/60 text-center">No products.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
