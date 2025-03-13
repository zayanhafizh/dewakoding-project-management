<div class="mt-2">
    @if (!$isOpen)
        <button
            wire:click="toggleForm"
            type="button"
            class="w-full py-2 px-3 text-sm text-gray-600 dark:text-gray-400 hover:text-primary-600 dark:hover:text-primary-500 flex items-center justify-center border border-dashed border-gray-300 dark:border-gray-700 rounded-lg hover:border-primary-300 dark:hover:border-primary-800 transition-colors"
        >
            <x-heroicon-m-plus class="w-4 h-4 mr-1" />
            Tambah Ticket
        </button>
    @else
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-3">
            <form wire:submit="save">
                <div class="space-y-4">
                    {{ $this->form }}
                </div>
                
                <div class="flex justify-end gap-2 mt-4">
                    <x-filament::button
                        type="button" 
                        color="gray"
                        wire:click="toggleForm"
                    >
                        Batal
                    </x-filament::button>
                    
                    <x-filament::button type="submit">
                        Tambah Ticket
                    </x-filament::button>
                </div>
            </form>
        </div>
    @endif
</div>