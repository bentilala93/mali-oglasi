<?php

namespace Database\Seeders;

use App\Models\Ad;
use App\Models\User;
use App\Models\Category;
use Illuminate\Database\Seeder;

class AdSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::role('customer')->get();
        $categories = Category::all();

        if ($users->isEmpty() || $categories->isEmpty()) {
            return;
        }

        $oglasi = [
            [
                'naslov' => 'iPhone 13 Pro - Odlično stanje',
                'opis' => 'iPhone 13 Pro 128GB, plava boja, kupljen pre 6 meseci. Odlično očuvan, bez ogrebotina.',
                'cena' => 850.00,
                'stanje' => 'polovno',
                'kontakt_telefon' => '0601234567',
                'lokacija' => 'Beograd',
                'kategorija_id' => $categories->where('name', 'iPhone')->first()->id,
            ],
            [
                'naslov' => 'Gaming laptop ASUS ROG',
                'opis' => 'ASUS ROG Strix G15, RTX 3060, 16GB RAM, 512GB SSD. Idealno za gaming i rad.',
                'cena' => 1200.00,
                'stanje' => 'novo',
                'kontakt_telefon' => '0612345678',
                'lokacija' => 'Novi Sad',
                'kategorija_id' => $categories->where('name', 'Laptopovi')->first()->id,
            ],
            [
                'naslov' => 'Stan u centru grada',
                'opis' => 'Prelep stan 65m2 u centru, 2 sobe, renoviran, parking mesto uključen.',
                'cena' => 150000.00,
                'stanje' => 'novo',
                'kontakt_telefon' => '0623456789',
                'lokacija' => 'Beograd',
                'kategorija_id' => $categories->where('name', 'Stanovi')->first()->id,
            ],
            [
                'naslov' => 'VW Golf 7 - Prvi vlasnik',
                'opis' => 'VW Golf 7, 1.6 TDI, 2018. godina, prvi vlasnik, servisna knjiga, 85000km.',
                'cena' => 18500.00,
                'stanje' => 'polovno',
                'kontakt_telefon' => '0634567890',
                'lokacija' => 'Niš',
                'kategorija_id' => $categories->where('name', 'Putnički automobili')->first()->id,
            ],
            [
                'naslov' => 'RTX 4070 Ti - Najnovija generacija',
                'opis' => 'NVIDIA RTX 4070 Ti, 12GB GDDR6X, kupljena pre 2 meseca, garancija 2 godine.',
                'cena' => 750.00,
                'stanje' => 'novo',
                'kontakt_telefon' => '0645678901',
                'lokacija' => 'Beograd',
                'kategorija_id' => $categories->where('name', 'Grafičke kartice')->first()->id,
            ],
        ];

        foreach ($oglasi as $oglasData) {
            $slug = \Illuminate\Support\Str::slug($oglasData['naslov']);
            $counter = 1;
            $originalSlug = $slug;
            
            while (Ad::where('slug', $slug)->exists()) {
                $slug = $originalSlug . '-' . $counter;
                $counter++;
            }
            
            Ad::create(array_merge($oglasData, [ 
                'user_id' => $users->random()->id,
                'slug' => $slug,
                'status' => 'approved'
            ]));
        }
    }
}
