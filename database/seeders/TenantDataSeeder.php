<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Company;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Seeds the categories and products of the current tenant.
 *
 * Must run with a current company — either inside `$company->execute(...)` or through
 * `php artisan tenants:artisan "db:seed --class=TenantDataSeeder --database=tenant"`.
 */
class TenantDataSeeder extends Seeder
{
    /**
     * A different catalogue per company, so tenant isolation is visible at a glance.
     *
     * @var array<string, array<string, list<array{name: string, price: float, quantity: int}>>>
     */
    private const CATALOGUES = [
        'acme' => [
            'Explosives' => [
                ['name' => 'Dehydrated Boulder', 'price' => 149.90, 'quantity' => 12],
                ['name' => 'Giant Rubber Band', 'price' => 39.50, 'quantity' => 40],
            ],
            'Rocket Gear' => [
                ['name' => 'Strap-On Rocket', 'price' => 899.00, 'quantity' => 5],
                ['name' => 'Jet Propelled Skates', 'price' => 459.00, 'quantity' => 8],
            ],
        ],
        'globex' => [
            'Automation' => [
                ['name' => 'Hover Conveyor', 'price' => 2450.00, 'quantity' => 3],
                ['name' => 'Robotic Arm Mk II', 'price' => 5100.00, 'quantity' => 2],
            ],
            'Energy' => [
                ['name' => 'Fusion Cell', 'price' => 780.25, 'quantity' => 60],
                ['name' => 'Solar Array Panel', 'price' => 320.00, 'quantity' => 95],
            ],
        ],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $company = Company::current();

        $catalogue = self::CATALOGUES[$company->slug] ?? $this->genericCatalogue();
        $prefix = Str::upper(Str::substr($company->slug, 0, 3));
        $sequence = 1;

        foreach ($catalogue as $categoryName => $products) {
            $category = Category::create([
                'name' => $categoryName,
                'description' => "{$categoryName} sold by {$company->name}.",
            ]);

            foreach ($products as $product) {
                Product::create([
                    'category_id' => $category->id,
                    'sku' => sprintf('%s-%04d', $prefix, $sequence++),
                    'name' => $product['name'],
                    'price' => $product['price'],
                    'quantity' => $product['quantity'],
                ]);
            }
        }
    }

    /**
     * Fallback catalogue for companies registered through the sign-up form.
     *
     * @return array<string, list<array{name: string, price: float, quantity: int}>>
     */
    private function genericCatalogue(): array
    {
        return [
            'General' => [
                ['name' => 'Sample Product A', 'price' => 19.90, 'quantity' => 10],
                ['name' => 'Sample Product B', 'price' => 49.90, 'quantity' => 25],
            ],
        ];
    }
}
