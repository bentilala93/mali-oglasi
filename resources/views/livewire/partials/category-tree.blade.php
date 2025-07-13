@foreach($categories as $kategorija)
    @php
        $indentClass = $level > 0 ? 'ml-' . ($level * 2) : '';
        $textSize = $level === 0 ? 'text-sm' : 'text-xs';
        $textColor = $level === 0 ? 'text-gray-700' : 'text-gray-600';
    @endphp
    
    <div class="category-item {{ $indentClass }}">
        @if($kategorija->children->count() > 0)
            <div class="flex items-center">
                <button wire:click="toggleCategory({{ $kategorija->id }})" class="mr-2 text-gray-400 hover:text-gray-600 transition-colors">
                    @if(in_array($kategorija->id, $expandedCategories))
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    @else
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    @endif
                </button>
                <a href="#" wire:click.prevent="$set('category', '{{ $kategorija->slug }}')" 
                   class="flex-1 px-3 py-2 {{ $textSize }} {{ $textColor }} hover:bg-gray-100 rounded-md {{ $selectedCategory === $kategorija->slug ? 'bg-blue-50 text-blue-700' : '' }}">
                    {{ $kategorija->name }}
                </a>
            </div>
            
            @if(in_array($kategorija->id, $expandedCategories))
                <div class="ml-2 mt-1 space-y-1">
                    @include('livewire.partials.category-tree', [
                        'categories' => $kategorija->children,
                        'level' => $level + 1,
                        'expandedCategories' => $expandedCategories,
                        'selectedCategory' => $selectedCategory
                    ])
                </div>
            @endif
        @else
            <div class="flex items-center">
                <div class="w-4 mr-2"></div>
                <a href="#" wire:click.prevent="$set('category', '{{ $kategorija->slug }}')" 
                   class="flex-1 px-3 py-2 {{ $textSize }} {{ $textColor }} hover:bg-gray-100 rounded-md {{ $selectedCategory === $kategorija->slug ? 'bg-blue-50 text-blue-700' : '' }}">
                    {{ $kategorija->name }}
                </a>
            </div>
        @endif
    </div>
@endforeach 