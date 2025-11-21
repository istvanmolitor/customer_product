@if($categories && $categories->isNotEmpty())
    <div class="space-y-2 {{ isset($level) ? 'ml-6 mt-2' : '' }}">
        @foreach($categories as $category)
            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between p-4">
                    <div class="flex items-center space-x-3 flex-1">
                        @if($category->childCategories && $category->childCategories->isNotEmpty())
                            <x-heroicon-o-folder class="w-5 h-5 text-gray-400" />
                        @else
                            <x-heroicon-o-tag class="w-5 h-5 text-gray-400" />
                        @endif
                        <span class="text-sm font-medium text-gray-900 dark:text-gray-100">
                            {{ $category->name }}
                        </span>
                    </div>

                    <div class="flex items-center space-x-2">
                        <button
                            type="button"
                            wire:click="editCategory({{ $category->id }})"
                            class="p-2 text-gray-500 hover:text-primary-600 dark:hover:text-primary-400 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors"
                            title="Szerkesztés"
                        >
                            <x-heroicon-o-pencil class="w-4 h-4" />
                        </button>

                        <button
                            type="button"
                            wire:click="$dispatch('confirm-delete-category', { categoryId: {{ $category->id }}, categoryName: '{{ addslashes($category->name) }}' })"
                            class="p-2 text-gray-500 hover:text-danger-600 dark:hover:text-danger-400 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors"
                            title="Törlés"
                        >
                            <x-heroicon-o-trash class="w-4 h-4" />
                        </button>
                    </div>
                </div>

                @if($category->childCategories && $category->childCategories->isNotEmpty())
                    <div class="px-4 pb-4">
                        @include('customer_product::filament.pages.sub-categories', [
                            'categories' => $category->childCategories,
                            'level' => ($level ?? 0) + 1
                        ])
                    </div>
                @endif
            </div>
        @endforeach
    </div>
@endif
