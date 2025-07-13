@foreach($categories as $category)
    @php
        $indentClass = $level > 0 ? 'ml-' . ($level * 6) : '';
        $bgClass = $level === 0 ? 'bg-white' : ($level === 1 ? 'bg-gray-50' : 'bg-gray-100');
        $textSize = $level === 0 ? 'text-lg' : ($level === 1 ? 'text-sm' : 'text-xs');
    @endphp
    
    <div class="px-6 py-4 {{ $indentClass }}">
        <div class="flex items-center justify-between {{ $bgClass }} p-3 rounded-md">
            <div>
                <h3 class="{{ $textSize }} font-medium text-gray-900">{{ $category->name }}</h3>
                <p class="text-xs text-gray-500">Slug: {{ $category->slug }}</p>
                @if($level > 0)
                    <p class="text-xs text-blue-600">Nivo {{ $level }}</p>
                @endif
            </div>
            <div class="flex items-center space-x-2">
                <button wire:click="editCategory({{ $category->id }})" class="text-blue-600 hover:text-blue-900 bg-blue-100 px-2 py-1 rounded text-xs">
                    Izmeni
                </button>
                <button wire:click="deleteCategory({{ $category->id }})" onclick="return confirm('Obrisati kategoriju?')" class="text-red-600 hover:text-red-900 bg-red-100 px-2 py-1 rounded text-xs">
                    Obriši
                </button>
            </div>
        </div>
        
        @if($category->children->count() > 0)
            <div class="mt-3 space-y-2">
                @include('livewire.partials.admin-category-tree', [
                    'categories' => $category->children,
                    'level' => $level + 1
                ])
            </div>
        @endif
    </div>
@endforeach 