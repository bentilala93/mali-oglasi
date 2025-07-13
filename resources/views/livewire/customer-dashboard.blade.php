<?php

use App\Models\Ad;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;
use function Livewire\Volt\{state, layout, computed, usesFileUploads};

layout('layouts.app');
usesFileUploads();

state([
    'naslov' => '',
    'opis' => '',
    'cena' => '',
    'stanje' => 'novo',
    'slika' => null,
    'kontakt_telefon' => '',
    'lokacija' => '',
    'kategorija_id' => '',
    'editAdId' => null,
    'sortBy' => 'latest',
]);

$ads = computed(function () {
    $query = Ad::where('user_id', Auth::id());
    
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
    
    return $query->paginate(5);
});

$categories = computed(function () {
    return Category::whereNull('parent_id')->with(['children.children.children.children.children'])->get();
});

$formatCategoryName = function ($category, $level = 0) {
    $prefix = str_repeat("&nbsp;", $level*3);
    return $prefix . $category->name;
};

$getAllCategories = function ($categories = null, $level = 0) use (&$getAllCategories, &$formatCategoryName) {
    if ($categories === null) {
        $categories = Category::whereNull('parent_id')->with(['children.children.children.children.children'])->get();
    }
    
    $result = [];
    foreach ($categories as $category) {
        $result[] = [
            'id' => $category->id,
            'name' => $this->formatCategoryName($category, $level)
        ];
        
        if ($category->children->count() > 0) {
            $result = array_merge($result, $this->getAllCategories($category->children, $level + 1));
        }
    }
    
    return $result;
};

$resetForm = function () {
    $this->naslov = '';
    $this->opis = '';
    $this->cena = '';
    $this->stanje = 'novo';
    $this->slika = null;
    $this->kontakt_telefon = '';
    $this->lokacija = '';
    $this->kategorija_id = '';
    $this->editAdId = null;
};

$saveAd = function () {
    $data = $this->validate([
        'naslov' => 'required|string|max:255',
        'opis' => 'required|string',
        'cena' => 'required|numeric|min:0',
        'stanje' => 'required|in:novo,polovno',
        'slika' => 'nullable|image|max:2048',
        'kontakt_telefon' => 'required|string|max:20',
        'lokacija' => 'required|string|max:255',
        'kategorija_id' => 'required|exists:categories,id',
    ]);

    if ($this->slika) {
        $data['slika'] = $this->slika->store('ads', 'public');
    }

    $data['user_id'] = Auth::id();
    
    if ($this->editAdId) {
        $ad = Ad::where('user_id', Auth::id())->findOrFail($this->editAdId);
        $ad->update($data);
        session()->flash('success', 'Oglas je izmenjen.');
    } else {
        Ad::create($data);
        session()->flash('success', 'Oglas je dodat.');
    }

    $this->resetForm();
};

$editAd = function ($id) {
    $ad = Ad::where('user_id', Auth::id())->findOrFail($id);
    $this->naslov = $ad->naslov;
    $this->opis = $ad->opis;
    $this->cena = $ad->cena;
    $this->stanje = $ad->stanje;
    $this->kontakt_telefon = $ad->kontakt_telefon;
    $this->lokacija = $ad->lokacija;
    $this->kategorija_id = $ad->kategorija_id;
    $this->editAdId = $ad->id;
};

$deleteAd = function ($id) {
    $ad = Ad::where('user_id', Auth::id())->findOrFail($id);
    $ad->delete();
    session()->flash('success', 'Oglas je obrisan.');
};
?>

<div class="max-w-4xl mx-auto py-8">
    @if (session()->has('success'))
        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
            {{ session('success') }}
        </div>
    @endif
    
    <h2 class="text-xl font-semibold mb-2">{{ $editAdId ? 'Izmeni oglas' : 'Dodaj oglas' }}</h2>
    <form wire:submit.prevent="saveAd" class="mb-8 space-y-2">
        <input type="text" wire:model.defer="naslov" placeholder="Naslov" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
        <textarea wire:model.defer="opis" placeholder="Opis" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required></textarea>
        <input type="number" wire:model.defer="cena" placeholder="Cena" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
        <select wire:model.defer="stanje" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            <option value="novo">Novo</option>
            <option value="polovno">Polovno</option>
        </select>
        <input type="file" wire:model="slika" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
        <input type="text" wire:model.defer="kontakt_telefon" placeholder="Telefon" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
        <input type="text" wire:model.defer="lokacija" placeholder="Lokacija" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
        <select wire:model.defer="kategorija_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            <option value="">Kategorija</option>
            @foreach($this->getAllCategories() as $cat)
                <option value="{{ $cat['id'] }}">{!! $cat['name'] !!}</option>
            @endforeach
        </select>
        <div class="flex gap-2">
            <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500">{{ $editAdId ? 'Sačuvaj izmene' : 'Dodaj oglas' }}</button>
            @if($editAdId)
                <button type="button" wire:click="resetForm" class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500">Otkaži</button>
            @endif
        </div>
    </form>

    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-4">
        <h2 class="text-xl font-semibold">Moji oglasi</h2>
        
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
    @if($this->ads->count())
        <table class="w-full border-collapse border border-gray-300 mb-4">
            <thead>
                <tr class="bg-gray-100">
                    <th class="border border-gray-300 px-4 py-2 text-left">Slika</th>
                    <th class="border border-gray-300 px-4 py-2 text-left">Naslov</th>
                    <th class="border border-gray-300 px-4 py-2 text-left">Cena</th>
                    <th class="border border-gray-300 px-4 py-2 text-left">Kategorija</th>
                    <th class="border border-gray-300 px-4 py-2 text-left">Status</th>
                    <th class="border border-gray-300 px-4 py-2 text-left">Stanje</th>
                    <th class="border border-gray-300 px-4 py-2 text-left">Akcije</th>
                </tr>
            </thead>
            <tbody>
                @foreach($this->ads as $ad)
                    <tr class="hover:bg-gray-50">
                        <td class="border border-gray-300 px-4 py-2">
                            @if($ad->slika)
                                <img src="{{ asset('storage/' . $ad->slika) }}" 
                                        alt="{{ $ad->naslov }}"     
                                        class="h-20 object-cover rounded-t-lg">  
                            @else
                                <div class="h-20 bg-gray-200 rounded-t-lg flex items-center justify-center">
                                    <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                            @endif
                        </td>
                        <td class="border border-gray-300 px-4 py-2">{{ $ad->naslov }}</td>
                        <td class="border border-gray-300 px-4 py-2">{{ $ad->cena }} RSD</td>
                        <td class="border border-gray-300 px-4 py-2">{{ $ad->kategorija->name ?? '-' }}</td>
                        <td class="border border-gray-300 px-4 py-2">
                            @if($ad->status === 'pending')
                                <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs">Na čekanju</span>
                            @elseif($ad->status === 'approved')
                                <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs">Odobreno</span>
                            @else
                                <span class="px-2 py-1 bg-red-100 text-red-800 rounded-full text-xs">Odbijeno</span>
                            @endif
                        </td>
                        <td class="border border-gray-300 px-4 py-2">{{ ucfirst($ad->stanje) }}</td>
                        <td class="border border-gray-300 px-4 py-2">
                            <div class="flex flex-col sm:flex-row gap-2">
                                <button wire:click="editAd({{ $ad->id }})" class="px-2 py-1 bg-yellow-500 text-white rounded text-sm hover:bg-yellow-600 whitespace-nowrap">Izmeni</button>
                                <button wire:click="deleteAd({{ $ad->id }})" class="px-2 py-1 bg-red-500 text-white rounded text-sm hover:bg-red-600 whitespace-nowrap" onclick="return confirm('Obrisati oglas?')">Obriši</button>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        {{ $this->ads->links() }}
    @else
        <div>Nema oglasa.</div>
    @endif
</div>
