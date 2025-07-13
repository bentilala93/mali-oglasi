<?php

use App\Models\Ad;
use App\Models\User;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;
use function Livewire\Volt\{state, layout, computed};

layout('layouts.app');

state([
    'selectedStatus' => 'pending',
    'sortBy' => 'latest',
]);

$applySorting = function ($query) {
    switch ($this->sortBy) {
        case 'price_low':
            return $query->orderBy('cena', 'asc');
        case 'price_high':
            return $query->orderBy('cena', 'desc');
        case 'oldest':
            return $query->orderBy('created_at', 'asc');
        case 'latest':
        default:
            return $query->orderBy('created_at', 'desc');
    }
};

$pendingAds = computed(function () {
    $query = Ad::with(['user', 'kategorija'])->pending();
    return $this->applySorting($query)->paginate(10);
});

$approvedAds = computed(function () {
    $query = Ad::with(['user', 'kategorija'])->approved();
    return $this->applySorting($query)->paginate(10);
});

$rejectedAds = computed(function () {
    $query = Ad::with(['user', 'kategorija'])->rejected();
    return $this->applySorting($query)->paginate(10);
});

$approveAd = function ($adId) {
    $ad = Ad::findOrFail($adId);
    $ad->update(['status' => 'approved']);
    session()->flash('success', 'Oglas je odobren.');
};

$rejectAd = function ($adId) {
    $ad = Ad::findOrFail($adId);
    $ad->update(['status' => 'rejected']);
    session()->flash('success', 'Oglas je odbijen.');
};

$deleteAd = function ($adId) {
    $ad = Ad::findOrFail($adId);
    $ad->delete();
    session()->flash('success', 'Oglas je obrisan.');
};

$restoreAd = function ($adId) {
    $ad = Ad::withTrashed()->findOrFail($adId);
    $ad->restore();
    session()->flash('success', 'Oglas je vraćen.');
};

$forceDeleteAd = function ($adId) {
    $ad = Ad::withTrashed()->findOrFail($adId);
    $ad->forceDelete();
    session()->flash('success', 'Oglas je trajno obrisan.');
};

?>

<div class="max-w-7xl mx-auto py-8 px-4">
    @if (session()->has('success'))
        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
            {{ session('success') }}
        </div>
    @endif

    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Upravljanje oglasima</h1>
        <a href="{{ route('dashboard') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 transition duration-200">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Nazad na Dashboard
        </a>
    </div>

    <!-- Status Tabs and Sorting -->
    <div class="mb-6">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <nav class="flex space-x-8">
                <button wire:click="$set('selectedStatus', 'pending')" 
                        class="px-3 py-2 text-sm font-medium rounded-md {{ $selectedStatus === 'pending' ? 'bg-blue-100 text-blue-700' : 'text-gray-500 hover:text-gray-700' }}">
                    Na čekanju ({{ $this->pendingAds->total() }})
                </button>
                <button wire:click="$set('selectedStatus', 'approved')" 
                        class="px-3 py-2 text-sm font-medium rounded-md {{ $selectedStatus === 'approved' ? 'bg-green-100 text-green-700' : 'text-gray-500 hover:text-gray-700' }}">
                    Odobreno ({{ $this->approvedAds->total() }})
                </button>
                <button wire:click="$set('selectedStatus', 'rejected')" 
                        class="px-3 py-2 text-sm font-medium rounded-md {{ $selectedStatus === 'rejected' ? 'bg-red-100 text-red-700' : 'text-gray-500 hover:text-gray-700' }}">
                    Odbijeno ({{ $this->rejectedAds->total() }})
                </button>
            </nav>
            
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
    </div>

    <!-- Ads Table -->
    <div class="bg-white shadow-sm rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Oglas</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Korisnik</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kategorija</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cena</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Datum</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Akcije</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @if($selectedStatus === 'pending')
                    @foreach($this->pendingAds as $ad)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-12 w-12">
                                        @if($ad->slika)
                                            <img class="h-12 w-12 rounded-lg object-cover" src="{{ asset('storage/' . $ad->slika) }}" alt="{{ $ad->naslov }}">
                                        @else
                                            <div class="h-12 w-12 rounded-lg bg-gray-200 flex items-center justify-center">
                                                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                </svg>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $ad->naslov }}</div>
                                        <div class="text-sm text-gray-500">{{ Str::limit($ad->opis, 50) }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $ad->user->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $ad->kategorija->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($ad->cena) }} RSD</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $ad->created_at->format('d.m.Y H:i') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                <button wire:click="approveAd({{ $ad->id }})" class="text-green-600 hover:text-green-900 bg-green-100 px-3 py-1 rounded-md text-xs">
                                    Odobri
                                </button>
                                <button wire:click="rejectAd({{ $ad->id }})" class="text-red-600 hover:text-red-900 bg-red-100 px-3 py-1 rounded-md text-xs">
                                    Odbij
                                </button>
                                <button wire:click="deleteAd({{ $ad->id }})" onclick="return confirm('Obrisati oglas?')" class="text-gray-600 hover:text-gray-900 bg-gray-100 px-3 py-1 rounded-md text-xs">
                                    Obriši
                                </button>
                            </td>
                        </tr>
                    @endforeach
                @elseif($selectedStatus === 'approved')
                    @foreach($this->approvedAds as $ad)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-12 w-12">
                                        @if($ad->slika)
                                            <img class="h-12 w-12 rounded-lg object-cover" src="{{ asset('storage/' . $ad->slika) }}" alt="{{ $ad->naslov }}">
                                        @else
                                            <div class="h-12 w-12 rounded-lg bg-gray-200 flex items-center justify-center">
                                                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                </svg>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $ad->naslov }}</div>
                                        <div class="text-sm text-gray-500">{{ Str::limit($ad->opis, 50) }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $ad->user->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $ad->kategorija->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($ad->cena) }} RSD</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $ad->created_at->format('d.m.Y H:i') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                <button wire:click="rejectAd({{ $ad->id }})" class="text-red-600 hover:text-red-900 bg-red-100 px-3 py-1 rounded-md text-xs">
                                    Odbij
                                </button>
                                <button wire:click="deleteAd({{ $ad->id }})" onclick="return confirm('Obrisati oglas?')" class="text-gray-600 hover:text-gray-900 bg-gray-100 px-3 py-1 rounded-md text-xs">
                                    Obriši
                                </button>
                            </td>
                        </tr>
                    @endforeach
                @else
                    @foreach($this->rejectedAds as $ad)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-12 w-12">
                                        @if($ad->slika)
                                            <img class="h-12 w-12 rounded-lg object-cover" src="{{ asset('storage/' . $ad->slika) }}" alt="{{ $ad->naslov }}">
                                        @else
                                            <div class="h-12 w-12 rounded-lg bg-gray-200 flex items-center justify-center">
                                                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                </svg>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $ad->naslov }}</div>
                                        <div class="text-sm text-gray-500">{{ Str::limit($ad->opis, 50) }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $ad->user->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $ad->kategorija->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($ad->cena) }} RSD</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $ad->created_at->format('d.m.Y H:i') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                <button wire:click="approveAd({{ $ad->id }})" class="text-green-600 hover:text-green-900 bg-green-100 px-3 py-1 rounded-md text-xs">
                                    Odobri
                                </button>
                                <button wire:click="deleteAd({{ $ad->id }})" onclick="return confirm('Obrisati oglas?')" class="text-gray-600 hover:text-gray-900 bg-gray-100 px-3 py-1 rounded-md text-xs">
                                    Obriši
                                </button>
                            </td>
                        </tr>
                    @endforeach
                @endif
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        @if($selectedStatus === 'pending')
            {{ $this->pendingAds->links() }}
        @elseif($selectedStatus === 'approved')
            {{ $this->approvedAds->links() }}
        @else
            {{ $this->rejectedAds->links() }}
        @endif
    </div>
</div> 