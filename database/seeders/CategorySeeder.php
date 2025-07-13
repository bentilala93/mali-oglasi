<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $racunari = Category::create(['name' => 'Računari', 'slug' => 'racunari']);
        $telefoni = Category::create(['name' => 'Telefoni', 'slug' => 'telefoni']);
        $automobili = Category::create(['name' => 'Automobili', 'slug' => 'automobili']);
        $nekretnine = Category::create(['name' => 'Nekretnine', 'slug' => 'nekretnine']);

        Category::create(['name' => 'Laptopovi', 'slug' => 'laptopovi', 'parent_id' => $racunari->id]);
        Category::create(['name' => 'Desktop računari', 'slug' => 'desktop-racunari', 'parent_id' => $racunari->id]);
        
        $komponente = Category::create(['name' => 'Komponente', 'slug' => 'komponente', 'parent_id' => $racunari->id]);
        
        Category::create(['name' => 'Grafičke kartice', 'slug' => 'graficke-kartice', 'parent_id' => $komponente->id]);
        Category::create(['name' => 'Procesori', 'slug' => 'procesori', 'parent_id' => $komponente->id]);
        Category::create(['name' => 'RAM memorija', 'slug' => 'ram-memorija', 'parent_id' => $komponente->id]);

        Category::create(['name' => 'iPhone', 'slug' => 'iphone', 'parent_id' => $telefoni->id]);
        Category::create(['name' => 'Samsung', 'slug' => 'samsung', 'parent_id' => $telefoni->id]);
        Category::create(['name' => 'Xiaomi', 'slug' => 'xiaomi', 'parent_id' => $telefoni->id]);

        Category::create(['name' => 'Putnički automobili', 'slug' => 'putnicki-automobili', 'parent_id' => $automobili->id]);
        Category::create(['name' => 'Teretna vozila', 'slug' => 'teretna-vozila', 'parent_id' => $automobili->id]);
        Category::create(['name' => 'Motocikli', 'slug' => 'motocikli', 'parent_id' => $automobili->id]);

        Category::create(['name' => 'Stanovi', 'slug' => 'stanovi', 'parent_id' => $nekretnine->id]);
        Category::create(['name' => 'Kuće', 'slug' => 'kuce', 'parent_id' => $nekretnine->id]);
        Category::create(['name' => 'Poslovni prostori', 'slug' => 'poslovni-prostori', 'parent_id' => $nekretnine->id]);
    }
}
