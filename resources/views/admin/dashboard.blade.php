<x-layouts.app title="Admin">
    <div class="mb-6">
        <h1 class="text-2xl font-semibold">Administration</h1>

        <p class="text-base-content/60">
            Every company in the landlord database. The counts below are read from each tenant
            database in turn.
        </p>
    </div>

    <div class="stats stats-vertical bg-base-100 mb-6 w-full shadow-sm sm:stats-horizontal">
        <div class="stat">
            <div class="stat-title">Companies</div>
            <div class="stat-value">{{ $companies->count() }}</div>
            <div class="stat-desc">One SQLite database each</div>
        </div>

        <div class="stat">
            <div class="stat-title">Employees</div>
            <div class="stat-value">{{ $usage->sum('employees') }}</div>
            <div class="stat-desc">Landlord scope</div>
        </div>

        <div class="stat">
            <div class="stat-title">Products</div>
            <div class="stat-value">{{ $usage->sum('products') }}</div>
            <div class="stat-desc">Tenant scope</div>
        </div>
    </div>

    <div class="card bg-base-100 shadow-sm">
        <div class="card-body">
            <h2 class="card-title">Companies</h2>

            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Company</th>
                            <th>Subdomain</th>
                            <th>Database</th>
                            <th>Plan</th>
                            <th>Employees</th>
                            <th>Categories</th>
                            <th>Products</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($companies as $company)
                            <tr>
                                <td>
                                    <div class="flex items-center gap-2 font-medium">
                                        <x-brandmark :company="$company" size="sm" />
                                        {{ $company->name }}
                                    </div>
                                </td>
                                <td>
                                    <a href="{{ $company->url() }}" class="link link-hover text-sm">
                                        {{ $company->domain() }}
                                    </a>
                                </td>
                                <td><code class="text-sm">{{ $company->database }}.sqlite</code></td>
                                <td><span class="badge badge-soft">{{ $company->plan->name }}</span></td>
                                <td>
                                    {{ $usage[$company->id]['employees'] }}
                                    <span class="text-base-content/60">/ {{ $company->plan->max_employees }}</span>
                                </td>
                                <td>{{ $usage[$company->id]['categories'] }}</td>
                                <td>
                                    {{ $usage[$company->id]['products'] }}
                                    <span class="text-base-content/60">/ {{ $company->plan->max_products }}</span>
                                </td>
                                <td class="text-right">
                                    <a href="{{ route('admin.companies.show', $company) }}" class="btn btn-sm">
                                        View
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-base-content/60 text-center">
                                    No companies registered yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts.app>
