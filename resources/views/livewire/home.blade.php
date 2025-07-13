<?php

use App\Models\Ad;
use App\Models\Category;
use function Livewire\Volt\{state, layout, computed, usesPagination, url};

layout('layouts.app');

usesPagination();

state([
    'search' => '',
    'minPrice' => '',
    'maxPrice' => '',
    'location' => '',
    'sortBy' => 'latest',
    'expandedCategories' => [],
    'category' => '',
]);

state(['category' => null])->url('category');
$currentCategory = computed(function () {
    if ($this->category) {
        return Category::where('slug', $this->category)->first();
    }
    return null;
});

$oglasi = computed(function () {
    $query = Ad::with(['kategorija.parent', 'user'])->approved();
    
    $query->whereHas('kategorija');
    
    if ($this->search) {
        $query->where(function($q) {
            $q->where('naslov', 'like', '%' . $this->search . '%')
              ->orWhere('opis', 'like', '%' . $this->search . '%');
        });
    }
    if ($this->category) {
        $category = Category::where('slug', $this->category)->first();
        if ($category) {
            $categoryIds = $this->getAllDescendantIds($category->id);
            $categoryIds[] = $category->id;
            
            $query->whereIn('kategorija_id', $categoryIds);
        }
    }
    if ($this->minPrice) {
        $query->where('cena', '>=', $this->minPrice);
    }
    if ($this->maxPrice) {
        $query->where('cena', '<=', $this->maxPrice);
    }
    if ($this->location) {
        $query->where('lokacija', 'like', '%' . $this->location . '%');
    }
    
    switch ($this->sortBy) {
        case 'price_low':
            $query->orderBy('cena', 'asc');
            break;
        case 'price_high':
            $query->orderBy('cena', 'desc');
            break;
        case 'oldest':
            $query->orderBy('created_at', 'asc');
            break;
        case 'latest':
        default:
            $query->orderBy('created_at', 'desc');
            break;
    }
    
    return $query->paginate(12);
});

$kategorije = computed(fn () => Category::with(['children.children.children.children.children'])->whereNull('parent_id')->get());

$clearFilters = fn () => [
    $this->search = '',
    $this->category = null,
    $this->minPrice = '',
    $this->maxPrice = '',
    $this->location = '',
    $this->sortBy = 'latest',
];

$toggleCategory = function ($categoryId) {
    if (in_array($categoryId, $this->expandedCategories)) {
        $this->expandedCategories = array_diff($this->expandedCategories, [$categoryId]);
    } else {
        $this->expandedCategories[] = $categoryId;
    }
};

$renderCategoryTree = function ($categories, $level = 0) {
    return view('livewire.partials.category-tree', [
        'categories' => $categories,
        'level' => $level,
        'expandedCategories' => $this->expandedCategories,
        'selectedCategory' => $this->category
    ])->render();
};

$getAllCategoriesForDropdown = function ($categories = null, $level = 0) {
    if ($categories === null) {
        $categories = Category::with(['children.children.children.children.children'])->whereNull('parent_id')->get();
    }
    
    $result = [];
    foreach ($categories as $category) {
        $indent = str_repeat('&nbsp;&nbsp;&nbsp;', $level);
        $result[] = [
            'id' => $category->id,
            'slug' => $category->slug,
            'name' => $indent . $category->name
        ];
        
        if ($category->children->count() > 0) {
            $result = array_merge($result, $this->getAllCategoriesForDropdown($category->children, $level + 1));
        }
    }
    
    return $result;
};

$getAllDescendantIds = function ($categoryId) {
    $descendantIds = [];
    $children = Category::where('parent_id', $categoryId)->get();
    
    foreach ($children as $child) {
        $descendantIds[] = $child->id;
        $descendantIds = array_merge($descendantIds, $this->getAllDescendantIds($child->id));
    }
    
    return $descendantIds;
};

$buildAdUrl = function ($ad) {
    if (!$ad->kategorija) {
        return '#';
    }
    
    $category = $ad->kategorija;
    $segments = [];
    
    while ($category) {
        array_unshift($segments, $category->slug);
        $category = $category->parent;
    }
    
    $categoriesPath = implode('/', $segments);
    
    return route('oglas.show.dynamic', ['categories' => $categoriesPath, 'adSlug' => $ad->slug]);
};



?>

<div class="min-h-screen bg-gray-50">
    <!-- Search Section -->
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                <!-- Search Input -->
                <div class="lg:col-span-2">
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Pretraga</label>
                    <input wire:model.live.debounce.300ms="search" type="text" id="search" 
                           placeholder="Pretraži oglase..." 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <!-- Category Filter -->
                <div>
                    <label for="category" class="block text-sm font-medium text-gray-700 mb-1">Kategorija</label>
                                         <select wire:model.live="category" id="category" 
                             class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                         <option value="">Sve kategorije</option>
                          @foreach($this->getAllCategoriesForDropdown() as $kategorija)
                             <option value="{{ $kategorija['slug'] }}">{!! $kategorija['name'] !!}</option>
                          @endforeach
                     </select>
                </div>

                <!-- Location Filter -->
                <div>
                    <label for="location" class="block text-sm font-medium text-gray-700 mb-1">Lokacija</label>
                    <input wire:model.live.debounce.300ms="location" type="text" id="location" 
                           placeholder="Lokacija..." 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <!-- Clear Filters -->
                <div class="flex items-end">
                    <button wire:click="clearFilters" 
                            class="w-full px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 transition duration-200">
                        Očisti filtere
                    </button>
                </div>
            </div>

            <!-- Price Range -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                <div>
                    <label for="minPrice" class="block text-sm font-medium text-gray-700 mb-1">Minimalna cena</label>
                    <input wire:model.live.debounce.300ms="minPrice" type="number" id="minPrice" 
                           placeholder="0" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label for="maxPrice" class="block text-sm font-medium text-gray-700 mb-1">Maksimalna cena</label>
                    <input wire:model.live.debounce.300ms="maxPrice" type="number" id="maxPrice" 
                           placeholder="999999" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex flex-col lg:flex-row gap-8">
            <!-- Sidebar -->
            <aside class="lg:w-1/4">
                <div class="bg-white rounded-lg shadow-sm border p-6 sticky top-4">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Kategorije</h2>
                    <nav class="space-y-1">
                        {!! $this->renderCategoryTree($this->kategorije, 0) !!}
                    </nav>
                </div>
            </aside>

            <!-- Main Content -->
            <main class="lg:w-3/4">
                <!-- Results Header -->
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
                    <h1 class="text-2xl font-bold text-gray-900">
                        Oglasi 
                        @if($this->oglasi->total() > 0)
                            <span class="text-gray-500 text-lg">({{ $this->oglasi->total() }})</span>
                        @endif
                    </h1>
                    
                    <!-- Sortiranje -->
                    <div class="flex items-center space-x-2">
                        <label for="sortBy" class="text-sm font-medium text-gray-700">Sortiraj po:</label>
                        <select wire:model.live="sortBy" id="sortBy" 
                                class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                            <option value="latest">Najnoviji</option>
                            <option value="oldest">Najstariji</option>
                            <option value="price_low">Cena: od najniže</option>
                            <option value="price_high">Cena: od najviše</option>
                        </select>
                    </div>
                </div>
                


                <!-- Oglasi Grid -->
                @if($this->oglasi->count() > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($this->oglasi as $oglas)
                            <div class="bg-white rounded-lg shadow-sm border hover:shadow-md transition duration-200">
                                <!-- Oglas Image -->
                                <a href="{{ $this->buildAdUrl($oglas) }}">
                                    <div class="aspect-w-16 aspect-h-9 bg-gray-200 rounded-t-lg">
                                        @if($oglas->slika)
                                            <img src="{{ asset('storage/' . $oglas->slika) }}" 
                                                alt="{{ $oglas->naslov }}" 
                                                class="w-full h-48 object-cover rounded-t-lg">
                                        @else
                                            <div class="w-full h-48 bg-gray-200 rounded-t-lg flex items-center justify-center">
                                                <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                </svg>
                                            </div>
                                        @endif
                                    </div>
                                </a>

                                <!-- Oglas Content -->
                                <div class="p-4">
                                    <div class="flex justify-between items-start mb-2">
                                        <h3 class="text-lg font-semibold text-gray-900 line-clamp-2">
                                            <a href="{{ $this->buildAdUrl($oglas) }}">{{ $oglas->naslov }}</a>
                                        </h3>
                                        <span class="text-lg font-bold text-green-600">
                                            {{ number_format($oglas->cena) }} RSD
                                        </span>
                                    </div>

                                    <p class="text-gray-600 text-sm mb-3 line-clamp-2">
                                        {{ \Illuminate\Support\Str::limit($oglas->opis, 100) }}
                                    </p>

                                    <div class="flex items-center justify-between text-sm text-gray-500 mb-3">
                                        <span class="flex items-center">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.172a4 4 0 015.656 0M9 12h6m-6-4h6m2 5.291A7.962 7.962 0 0112 15c-2.34 0-4.47-.881-6.08-2.33"></path>
                                            </svg>
                                            {{ $oglas->lokacija }}
                                        </span>
                                        <span class="px-2 py-1 bg-gray-100 rounded-full text-xs">
                                            {{ $oglas->stanje }}
                                        </span>
                                    </div>

                                    <div class="flex items-center justify-between">
                                        <span class="text-xs text-gray-500">
                                            {{ $oglas->kategorija->name ?? 'Bez kategorije' }}
                                        </span>
                                        <a href="{{ $this->buildAdUrl($oglas) }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                            Pogledaj detalje →
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Pagination -->
                    <div class="mt-8">
                        {{ $this->oglasi->links() }}
                    </div>
                @else
                    <!-- No Results -->
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 12h6m-6-4h6m2 5.291A7.962 7.962 0 0112 15c-2.34 0-4.47-.881-6.08-2.33"></path>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">Nema oglasa</h3>
                        <p class="mt-1 text-sm text-gray-500">
                            Pokušajte da promenite filtere.
                        </p>
                        @auth
                            @if(auth()->user()->hasRole('customer'))
                                <p class="mt-1 text-sm text-gray-500">
                                    Ili dodajte novi oglas.
                                </p>
                                <div class="mt-6">
                                    <a href="{{ route('dashboard') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                                        Dodaj oglas
                                    </a>
                                </div>
                            @endif
                        @endauth
                    </div>
                @endif
            </main>
        </div>
    </div>
    <style>
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
    </style>
</div>