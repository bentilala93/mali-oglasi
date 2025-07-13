<?php

use App\Models\User;
use App\Models\Ad;
use Illuminate\Support\Facades\Hash;
use function Livewire\Volt\{state, layout, computed, usesPagination};

layout('layouts.app');
usesPagination();

state([
    'name' => '',
    'email' => '',
    'password' => '',
    'password_confirmation' => '',
    'editUserId' => null,
    'search' => '',
    'role' => 'customer',
]);

$customers = computed(function () {
    $query = User::with(['ads'])->withTrashed()->whereHas('roles', function($q) {
        $q->where('name', 'customer');
    });
    
    if ($this->search) {
        $query->where(function($q) {
            $q->where('name', 'like', '%' . $this->search . '%')
              ->orWhere('email', 'like', '%' . $this->search . '%');
        });
    }
    
    return $query->latest()->paginate(10);
});

$resetForm = function () {
    $this->name = '';
    $this->email = '';
    $this->password = '';
    $this->password_confirmation = '';
    $this->editUserId = null;
    $this->role = 'customer';
};

$saveCustomer = function () {
            if ($this->editUserId) {
        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $this->editUserId,
            'password' => 'nullable|string|min:8|confirmed',
        ]);
        
        $user = User::findOrFail($this->editUserId);
        $user->update([
            'name' => $this->name,
            'email' => $this->email,
        ]);
        
        if ($this->password) {
            $user->update(['password' => Hash::make($this->password)]);
        }
        
        session()->flash('success', 'Korisnik je izmenjen.');
            } else {
        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);
        
        $user = User::create([
            'name' => $this->name,
            'email' => $this->email,
            'password' => Hash::make($this->password),
        ]);
        
        $user->assignRole('customer');
        
        session()->flash('success', 'Korisnik je dodat.');
    }
    
    $this->resetForm();
};

$editCustomer = function ($id) {
    $user = User::findOrFail($id);
    $this->name = $user->name;
    $this->email = $user->email;
    $this->editUserId = $user->id;
    $this->password = '';
    $this->password_confirmation = '';
};

$deleteCustomer = function ($id) {
    $user = User::findOrFail($id);
    
    $user->delete();
    session()->flash('success', 'Korisnik je obrisan.');
};

$restoreCustomer = function ($id) {
    $user = User::withTrashed()->findOrFail($id);
    
    $user->restore();
    session()->flash('success', 'Korisnik je vraćen.');
};

$forceDeleteCustomer = function ($id) {
    $user = User::withTrashed()->findOrFail($id);
    
    $user->forceDelete();
    session()->flash('success', 'Korisnik je trajno obrisan.');
};

$confirmDelete = function ($id) {
    $user = User::findOrFail($id);
    
    if ($user->ads->count() > 0) {
        $message = "Da li ste sigurni da želite da obrišete korisnika '{$user->name}'? Ova akcija će takođe obrisati {$user->ads->count()} oglasa koji pripadaju ovom korisniku. Ova akcija se ne može poništiti.";
    } else {
        $message = "Da li ste sigurni da želite da obrišete korisnika '{$user->name}'? Ova akcija se ne može poništiti.";
    }
    
    $this->dispatch('confirm-delete', message: $message, userId: $id);
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
        <h1 class="text-3xl font-bold text-gray-900">Upravljanje korisnicima</h1>
        <a href="{{ route('dashboard') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 transition duration-200">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Nazad na Dashboard
        </a>
    </div>

    <!-- Forma za dodavanje/izmenu - na vrhu -->
    <div class="bg-white rounded-lg shadow-sm border p-6 mb-8">
        <h2 class="text-xl font-semibold mb-4">{{ $editUserId ? 'Izmeni korisnika' : 'Dodaj korisnika' }}</h2>
        
        <form wire:submit.prevent="saveCustomer" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Ime i prezime</label>
                <input type="text" wire:model.defer="name" id="name" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                       placeholder="Unesite ime i prezime" required>
                @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email adresa</label>
                <input type="email" wire:model.defer="email" id="email" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                       placeholder="Unesite email adresu" required>
                @error('email') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                    {{ $editUserId ? 'Nova lozinka (opciono)' : 'Lozinka' }}
                </label>
                <input type="password" wire:model.defer="password" id="password" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                       placeholder="{{ $editUserId ? 'Ostavite prazno za nepromenjenu lozinku' : 'Unesite lozinku' }}"
                       {{ $editUserId ? '' : 'required' }}>
                @error('password') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <div class="flex items-end gap-2">
                @if(!$editUserId || $password)
                <div class="flex-1">
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Potvrda lozinke</label>
                    <input type="password" wire:model.defer="password_confirmation" id="password_confirmation" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                           placeholder="Potvrdite lozinku"
                           {{ $editUserId ? '' : 'required' }}>
                    @error('password_confirmation') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
                @endif
                
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 whitespace-nowrap">
                    {{ $editUserId ? 'Sačuvaj' : 'Dodaj' }}
                </button>
                @if($editUserId)
                    <button type="button" wire:click="resetForm" class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500 whitespace-nowrap">
                        Otkaži
                    </button>
                @endif
            </div>
        </form>
    </div>

    <!-- Lista korisnika - puna širina -->
    <div class="bg-white rounded-lg shadow-sm border">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold">Lista korisnika</h2>
                <div class="w-64">
                    <input type="text" wire:model.live.debounce.300ms="search" 
                           placeholder="Pretraži korisnike..." 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
        </div>
        
        <div class="w-full">
            <table class="w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Korisnik</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Oglasi</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Datum registracije</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Akcije</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($this->customers as $customer)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                            <span class="text-sm font-medium text-blue-600">
                                                {{ strtoupper(substr($customer->name, 0, 2)) }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $customer->name }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $customer->email }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    {{ $customer->ads->count() }} oglasa
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($customer->trashed())
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        Obrisan
                                    </span>
                                @elseif($customer->email_verified_at)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Verifikovan
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        Nije verifikovan
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $customer->created_at->format('d.m.Y H:i') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                @if($customer->trashed())
                                    <button wire:click="restoreCustomer({{ $customer->id }})" class="text-green-600 hover:text-green-900 bg-green-100 px-3 py-1 rounded-md text-xs">
                                        Vrati
                                    </button>
                                    <button wire:click="forceDeleteCustomer({{ $customer->id }})" 
                                            onclick="return confirm('Da li ste sigurni da želite da trajno obrišete korisnika? Ova akcija se ne može poništiti.')"
                                            class="text-red-600 hover:text-red-900 bg-red-100 px-3 py-1 rounded-md text-xs">
                                        Trajno obriši
                                    </button>
                                @else
                                    <button wire:click="editCustomer({{ $customer->id }})" class="text-blue-600 hover:text-blue-900 bg-blue-100 px-3 py-1 rounded-md text-xs">
                                        Izmeni
                                    </button>
                                    <button wire:click="confirmDelete({{ $customer->id }})" 
                                            class="text-red-600 hover:text-red-900 bg-red-100 px-3 py-1 rounded-md text-xs">
                                        Obriši
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="px-6 py-4 border-t border-gray-200">
                    {{ $this->customers->links() }}
    </div>
</div>

<script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('confirm-delete', (event) => {
            if (confirm(event.message)) {
                @this.deleteCustomer(event.userId);
            }
        });
    });
</script>