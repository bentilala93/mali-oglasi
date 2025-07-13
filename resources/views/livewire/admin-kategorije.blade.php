<?php

use App\Models\Category;
use Illuminate\Support\Str;
use function Livewire\Volt\{state, layout, computed};

layout('layouts.app');

state([
    'name' => '',
    'parent_id' => '',
    'editCategoryId' => null,
]);

$categories = computed(function () {
    return Category::with(['children.children.children.children.children'])->whereNull('parent_id')->get();
});

$allCategories = computed(function () {
    return Category::with(['children.children.children.children.children'])->whereNull('parent_id')->get();
});

$formatCategoryName = function ($category, $level = 0) {
    $prefix = str_repeat('&nbsp;', $level*3);
    return $prefix . $category->name;
};

$renderCategoryTree = function ($categories, $level = 0) {
    return view('livewire.partials.admin-category-tree', [
        'categories' => $categories,
        'level' => $level
    ])->render();
};

$getAllCategoriesFlat = function ($categories = null, $level = 0) use (&$getAllCategoriesFlat, &$formatCategoryName) {
    if ($categories === null) {
        $categories = Category::with(['children.children.children.children.children'])->whereNull('parent_id')->get();
    }
    
    $result = [];
    foreach ($categories as $category) {
        $result[] = [
            'id' => $category->id,
            'name' => $this->formatCategoryName($category, $level)
        ];
        
        if ($category->children->count() > 0) {
            $result = array_merge($result, $this->getAllCategoriesFlat($category->children, $level + 1));
        }
    }
    
    return $result;
};

$resetForm = function () {
    $this->name = '';
    $this->parent_id = '';
    $this->editCategoryId = null;
};

$saveCategory = function () {
    $this->validate([
        'name' => 'required|string|max:255',
        'parent_id' => 'nullable|exists:categories,id',
    ]);

    if ($this->editCategoryId && $this->parent_id == $this->editCategoryId) {
        session()->flash('error', 'Kategorija ne može biti roditelj samoj sebi.');
        return;
    }
    if ($this->editCategoryId && $this->parent_id) {
        $category = Category::findOrFail($this->editCategoryId);
        $children = $category->children;
        foreach ($children as $child) {
            if ($child->id == $this->parent_id) {
                session()->flash('error', 'Ne možete postaviti podkategoriju kao roditelja.');
                return;
            }
        }
    }



    $data = [
        'name' => $this->name,
        'slug' => Str::slug($this->name),
        'parent_id' => $this->parent_id ?: null,
    ];

    if ($this->editCategoryId) {
        $category = Category::findOrFail($this->editCategoryId);
        $category->update($data);
        session()->flash('success', 'Kategorija je izmenjena.');
    } else {
        Category::create($data);
        session()->flash('success', 'Kategorija je dodana.');
    }

    $this->resetForm();
};

$editCategory = function ($id) {
    $category = Category::findOrFail($id);
    $this->name = $category->name;
    $this->parent_id = $category->parent_id;
    $this->editCategoryId = $category->id;
};

$deleteCategory = function ($id) {
    $category = Category::findOrFail($id);
    
    if ($category->ads->count() > 0) {
        session()->flash('error', 'Ne možete obrisati kategoriju koja ima oglase.');
        return;
    }
    $this->deleteCategoryRecursive($category);
    
    session()->flash('success', 'Kategorija i sve njene podkategorije su obrisane.');
};

$deleteCategoryRecursive = function ($category) use (&$deleteCategoryRecursive) {
    foreach ($category->children as $child) {
        $this->deleteCategoryRecursive($child);
    }
    
    $category->delete();
};

?>

<div class="max-w-7xl mx-auto py-8 px-4">
    @if (session()->has('success'))
        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
            {{ session('success') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
            {{ session('error') }}
        </div>
    @endif

    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Upravljanje kategorijama</h1>
        <a href="{{ route('dashboard') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 transition duration-200">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Nazad na Dashboard
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Forma za dodavanje/izmenu -->
        <div class="bg-white rounded-lg shadow-sm border p-6">
            <h2 class="text-xl font-semibold mb-4">{{ $editCategoryId ? 'Izmeni kategoriju' : 'Dodaj kategoriju' }}</h2>
            
            <form wire:submit.prevent="saveCategory" class="space-y-4">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Naziv kategorije</label>
                    <input type="text" wire:model.defer="name" id="name" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                           placeholder="Unesite naziv kategorije" required>
                    @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="parent_id" class="block text-sm font-medium text-gray-700 mb-1">Roditeljska kategorija (opciono)</label>
                    <select wire:model.defer="parent_id" id="parent_id" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Glavna kategorija</option>
                        @foreach($this->getAllCategoriesFlat() as $category)
                            @if($category['id'] !== $editCategoryId)
                                <option value="{{ $category['id'] }}">{!! $category['name'] !!}</option>
                            @endif
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-500 mt-1">Možete dodati kategorije na bilo koji nivo hijerarhije</p>
                    @error('parent_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div class="flex gap-2">
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        {{ $editCategoryId ? 'Sačuvaj izmene' : 'Dodaj kategoriju' }}
                    </button>
                    @if($editCategoryId)
                        <button type="button" wire:click="resetForm" class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500">
                            Otkaži
                        </button>
                    @endif
                </div>
            </form>
        </div>

        <!-- Lista kategorija -->
        <div class="bg-white rounded-lg shadow-sm border">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-xl font-semibold">Lista kategorija</h2>
            </div>
            <div class="divide-y divide-gray-200">
                {!! $this->renderCategoryTree($this->categories) !!}
            </div>
        </div>
    </div>
</div> 