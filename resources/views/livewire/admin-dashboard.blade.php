<?php

use App\Models\Ad;
use App\Models\User;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;
use function Livewire\Volt\{layout, computed};

layout('layouts.app');


$totalAds = computed(function () {
    return Ad::count();
});

$pendingAdsCount = computed(function () {
    return Ad::pending()->count();
});

$approvedAdsCount = computed(function () {
    return Ad::approved()->count();
});

$rejectedAdsCount = computed(function () {
    return Ad::rejected()->count();
});

$totalUsers = computed(function () {
    return User::role('customer')->count();
});

$totalCategories = computed(function () {
    return Category::count();
});

$recentAds = computed(function () {
    return Ad::with(['user', 'kategorija'])->latest()->take(5)->get();
});

?>

<div class="max-w-7xl mx-auto py-8 px-4">
    <h1 class="text-3xl font-bold text-gray-900 mb-8">Admin Dashboard</h1>

    <!-- Statistika kartice -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Ukupno korisnika -->
        <div class="bg-white rounded-lg shadow-sm border p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 text-green-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Korisnika</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $this->totalUsers }}</p>
                </div>
            </div>
        </div>
        
        <!-- Ukupno oglasa -->
        <div class="bg-white rounded-lg shadow-sm border p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Ukupno oglasa</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $this->totalAds }}</p>
                </div>
            </div>
        </div>

        <!-- Oglasi na čekanju -->
        <div class="bg-white rounded-lg shadow-sm border p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Na čekanju</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $this->pendingAdsCount }}</p>
                </div>
            </div>
        </div>

        <!-- Ukupno kategorija -->
        <div class="bg-white rounded-lg shadow-sm border p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Kategorija</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $this->totalCategories }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <!-- Upravljanje korisnicima -->
        <div class="bg-white rounded-lg shadow-sm border p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Upravljanje korisnicima</h3>
            <p class="text-gray-600 mb-4">Upravljanje korisnicima i njihovim oglasima.</p>
            <a href="{{ route('admin.korisnici') }}" class="inline-flex items-center px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 transition duration-200">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
                Upravljaj korisnicima
            </a>
        </div>

        <!-- Upravljanje oglasima -->
        <div class="bg-white rounded-lg shadow-sm border p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Upravljanje oglasima</h3>
            <p class="text-gray-600 mb-4">Odobravanje, odbijanje i upravljanje svim oglasima na sajtu.</p>
            <a href="{{ route('admin.oglasi') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition duration-200">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
                Upravljaj oglasima
            </a>
        </div>

        <!-- Upravljanje kategorijama -->
        <div class="bg-white rounded-lg shadow-sm border p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Upravljanje kategorijama</h3>
            <p class="text-gray-600 mb-4">Dodavanje, izmena i brisanje kategorija i podkategorija.</p>
            <a href="{{ route('admin.kategorije') }}" class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition duration-200">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
                Upravljaj kategorijama
            </a>
        </div>
    </div>

    <!-- Najnoviji oglasi -->
    <div class="bg-white rounded-lg shadow-sm border">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Najnoviji oglasi</h3>
        </div>
        <div class="divide-y divide-gray-200">
            @foreach($this->recentAds as $ad)
                <div class="px-6 py-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-10 w-10">
                                @if($ad->slika)
                                    <img class="h-10 w-10 rounded-lg object-cover" src="{{ asset('storage/' . $ad->slika) }}" alt="{{ $ad->naslov }}">
                                @else
                                    <div class="h-10 w-10 rounded-lg bg-gray-200 flex items-center justify-center">
                                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                    </div>
                                @endif
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-900">{{ $ad->naslov }}</p>
                                <p class="text-sm text-gray-500">{{ $ad->user->name }} • {{ $ad->kategorija->name }}</p>
                            </div>
                        </div>
                        <div class="flex items-center space-x-2">
                            <span class="text-sm text-gray-900">{{ number_format($ad->cena) }} RSD</span>
                            @if($ad->status === 'pending')
                                <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs">Na čekanju</span>
                            @elseif($ad->status === 'approved')
                                <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs">Odobreno</span>
                            @else
                                <span class="px-2 py-1 bg-red-100 text-red-800 rounded-full text-xs">Odbijeno</span>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div> 