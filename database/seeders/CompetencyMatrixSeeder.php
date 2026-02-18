<?php

namespace Database\Seeders;

use App\Models\CompetencyCategory;
use App\Models\CompetencyItem;
use Illuminate\Database\Seeder;

class CompetencyMatrixSeeder extends Seeder
{
    public function run(): void
    {
        if (\App\Models\CompetencyCategory::count() > 0) {
            return;
        }

        $categories = [
            [
                'name' => 'IZDELAVA OBROČEV',
                'sort_order' => 1,
                'items' => [
                    'Priprava smole, vlaken in komponent',
                    'Sušenje',
                    'Brušenje in kontrola',
                    'Rezanje cevi',
                    'Staranje',
                    'Končna kontrola',
                    'Pakiranje',
                ],
            ],
            [
                'name' => 'BRIZGANJE PLASTIKE',
                'sort_order' => 2,
                'items' => [
                    'Menjava orodja in zagon procesa',
                    'Zagon procesa in brizganje',
                    'Priprava materiala za brizganje',
                    'Kontrola izdelkov',
                    'Pakiranje',
                ],
            ],
            [
                'name' => 'SPLOŠNA ZNANJA',
                'sort_order' => 3,
                'items' => [
                    'Naročanje materiala',
                    'Priprava ponudbe',
                    'Priprava plana proizvodnje',
                    'Priprava dokumentov za odpremo',
                    'Odprema izdelkov',
                    'Priprava PPAP dokumentacije',
                    'Reševanje reklamacij',
                    'Prevzem materiala',
                    'Reklamacija dobaviteljem',
                ],
            ],
            [
                'name' => 'MERILNICA',
                'sort_order' => 4,
                'items' => [
                    'Merjenje na 3D stroju in ostalih merilnih napravah',
                    'Preizkusi obročev',
                    ['name' => 'Funkcionalni preizkusi', 'is_hidden' => true],
                ],
            ],
            [
                'name' => 'ZAKONSKO PREDPISANA USPOSOBLJENOST',
                'sort_order' => 5,
                'items' => [
                    ['name' => 'Varstvo pri delu in požarna varnost (na 2 leti)', 'validity_years' => 2, 'allow_unlimited' => false, 'is_hidden' => true],
                    ['name' => 'Evakuacija in prvo posredovanje (na 3 let)', 'validity_years' => 3, 'allow_unlimited' => false, 'is_hidden' => true],
                    ['name' => 'Izpit prve pomoči', 'validity_years' => 3, 'allow_unlimited' => false, 'is_hidden' => true],
                    ['name' => 'Izpit za vožnjo viličarja', 'validity_years' => null, 'allow_unlimited' => true, 'is_hidden' => true],
                    ['name' => 'Varno delo z mostnim dvigalom (na 2 leti)', 'validity_years' => 2, 'allow_unlimited' => false, 'is_hidden' => true],
                    ['name' => 'Zdravniški pregled (na 3 leta)', 'validity_years' => 3, 'allow_unlimited' => false, 'is_hidden' => true],
                ],
            ],
        ];

        foreach ($categories as $catData) {
            $category = CompetencyCategory::create([
                'name' => $catData['name'],
                'sort_order' => $catData['sort_order'],
            ]);

            $sortOrder = 0;
            foreach ($catData['items'] as $item) {
                if (is_array($item)) {
                    CompetencyItem::create([
                        'competency_category_id' => $category->id,
                        'name' => $item['name'],
                        'requires_validity' => true,
                        'validity_years' => $item['validity_years'] ?? null,
                        'allow_unlimited' => $item['allow_unlimited'] ?? false,
                        'is_hidden' => $item['is_hidden'] ?? false,
                        'sort_order' => $sortOrder++,
                    ]);
                } else {
                    CompetencyItem::create([
                        'competency_category_id' => $category->id,
                        'name' => $item,
                        'requires_validity' => false,
                        'validity_years' => null,
                        'is_hidden' => false,
                        'sort_order' => $sortOrder++,
                    ]);
                }
            }
        }
    }
}
