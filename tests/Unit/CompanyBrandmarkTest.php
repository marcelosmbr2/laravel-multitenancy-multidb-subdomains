<?php

use App\Models\Company;

/**
 * The brandmark is pure derivation from the name and the slug, so none of this needs a database.
 */
function unsavedCompany(string $name, string $slug = 'acme'): Company
{
    return new Company(['name' => $name, 'slug' => $slug]);
}

test('a one word name gives its first two letters', function (string $name, string $initials) {
    expect(unsavedCompany($name)->initials())->toBe($initials);
})->with([
    ['Acme', 'AC'],
    ['Globex', 'GL'],
    ['initech', 'IN'],
]);

test('a longer name gives the first letter of its first two words', function (string $name, string $initials) {
    expect(unsavedCompany($name)->initials())->toBe($initials);
})->with([
    ['Initech Ltda', 'IL'],
    ['Acme Corp Ltda', 'AC'],
    ['  Acme   Corp  ', 'AC'],
]);

test('the hue is a stable degree of the colour wheel', function () {
    $hue = unsavedCompany('Acme', 'acme')->brandmarkHue();

    expect($hue)->toBe(unsavedCompany('Acme Renamed', 'acme')->brandmarkHue())
        ->toBeGreaterThanOrEqual(0)
        ->toBeLessThan(360);
});

test('two companies do not share a hue', function () {
    expect(unsavedCompany('Acme', 'acme')->brandmarkHue())
        ->not->toBe(unsavedCompany('Globex', 'globex')->brandmarkHue());
});
