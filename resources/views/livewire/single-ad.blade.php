<?php
use App\Models\Ad;
use App\Models\Category;
use function Livewire\Volt\{state, layout, computed, with};

layout('layouts.app');

state([
    'categories' => null,
    'adSlug' => null,
]);

$with = function($categories, $adSlug) {
    return [
        'categories' => $categories,
        'adSlug' => $adSlug
    ];
};

$ad = computed(function() {
    if (!$this->categories || !$this->adSlug) {
        abort(404, 'Invalid URL parameters');
    }
    
    $categorySlugs = explode('/', $this->categories);
    $lastCategorySlug = end($categorySlugs);
    
    $category = Category::where('slug', $lastCategorySlug)->firstOrFail();
    
    $currentCategory = $category;
    $pathIndex = count($categorySlugs) - 1;
    
    while ($currentCategory && $pathIndex >= 0) {
        if ($currentCategory->slug !== $categorySlugs[$pathIndex]) {
            abort(404, 'Category path mismatch');
        }
        $currentCategory = $currentCategory->parent;
        $pathIndex--;
    }
    
    return Ad::with(['kategorija.parent', 'user'])
        ->where('slug', $this->adSlug)
        ->where('kategorija_id', $category->id)
        ->firstOrFail();
});
?>

<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Breadcrumb Navigation -->
        <nav class="mb-6">
            <ol class="flex items-center space-x-2 text-sm text-gray-500">
                <li>
                    <a href="{{ route('home') }}" class="text-blue-600 hover:text-blue-800 transition duration-200">
                        Početna
                    </a>
                </li>
                <li>
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                    </svg>
                </li>
                @if($this->ad->kategorija)
                    @php
                        $category = $this->ad->kategorija;
                        $breadcrumbs = [];
                        
                        while ($category) {
                            array_unshift($breadcrumbs, $category);
                            $category = $category->parent;
                        }
                    @endphp
                    
                    @foreach($breadcrumbs as $breadcrumb)
                        <li>
                            <a href="{{ route('home', ['category' => $breadcrumb->slug]) }}" class="text-blue-600 hover:text-blue-800 transition duration-200">
                                {{ $breadcrumb->name }}
                            </a>
                        </li>
                        @if(!$loop->last)
                            <li>
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                </svg>
                            </li>
                        @endif
                    @endforeach
                @endif
                <li>
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                    </svg>
                </li>
                <li class="text-gray-900 font-medium truncate">
                    {{ $this->ad->naslov }}
                </li>
            </ol>
        </nav>

        <!-- Main Content -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left Column - Image and Details -->
            <div class="lg:col-span-2">
                <!-- Image Section -->
                <div class="bg-white rounded-lg shadow-sm border overflow-hidden mb-6">
                    @if($this->ad->slika)
                        <img src="{{ asset('storage/' . $this->ad->slika) }}" 
                             alt="{{ $this->ad->naslov }}" 
                             class="w-full h-96 object-cover">
                    @else
                        <div class="w-full h-96 bg-gray-200 flex items-center justify-center">
                            <svg class="w-24 h-24 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                    @endif
                </div>

                <!-- Details Section -->
                <div class="bg-white rounded-lg shadow-sm border p-6">
                    <div class="flex justify-between items-start mb-4">
                        <h1 class="text-3xl font-bold text-gray-900">{{ $this->ad->naslov }}</h1>
                        <div class="text-right">
                            <div class="text-3xl font-bold text-green-600">{{ number_format($this->ad->cena) }} RSD</div>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800">
                                {{ $this->ad->stanje }}
                            </span>
                        </div>
                    </div>

                    <!-- Category and Location -->
                    <div class="flex items-center space-x-4 mb-6 text-sm text-gray-600">
                        @if($this->ad->kategorija)
                            <span class="flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                </svg>
                                {{ $this->ad->kategorija->name }}
                            </span>
                        @endif
                        <span class="flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            {{ $this->ad->lokacija }}
                        </span>
                        <span class="flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            {{ $this->ad->created_at->diffForHumans() }}
                        </span>
                    </div>

                    <!-- Description -->
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-3">Opis</h3>
                        <div class="prose max-w-none text-gray-700 leading-relaxed">
                            {!! nl2br(e($this->ad->opis)) !!}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column - Contact and Seller Info -->
            <div class="lg:col-span-1">
                <!-- Contact Card -->
                <div class="bg-white rounded-lg shadow-sm border p-6 mb-6 sticky top-4">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Kontakt informacije</h3>
                    
                    <!-- Phone -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Telefon</label>
                        <div class="flex items-center">
                            <svg class="w-4 h-4 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                            </svg>
                            <a href="tel:{{ $this->ad->kontakt_telefon }}" class="text-blue-600 hover:text-blue-800 font-medium">
                                {{ $this->ad->kontakt_telefon }}
                            </a>
                        </div>
                    </div>

                    <!-- Seller Info -->
                    @if($this->ad->user)
                        <div class="border-t pt-4">
                            <h4 class="text-sm font-medium text-gray-900 mb-2">Prodavac</h4>
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center mr-3">
                                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <div class="text-sm font-medium text-gray-900">{{ $this->ad->user->name }}</div>
                                    <div class="text-xs text-gray-500">Član od {{ $this->ad->user->created_at->format('M Y') }}</div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Action Buttons -->
                    <div class="mt-6 space-y-3">
                        <button class="w-full bg-blue-600 text-white py-3 px-4 rounded-md hover:bg-blue-700 transition duration-200 font-medium">
                            Pozovi odmah
                        </button>
                        <button class="w-full border border-gray-300 text-gray-700 py-3 px-4 rounded-md hover:bg-gray-50 transition duration-200 font-medium">
                            Pošalji poruku
                        </button>
                    </div>
                </div>

                <!-- Similar Ads (Placeholder) -->
                <div class="bg-white rounded-lg shadow-sm border p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Slični oglasi</h3>
                    <div class="text-center py-8 text-gray-500">
                        <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <p class="text-sm">Slični oglasi će biti prikazani ovde</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
