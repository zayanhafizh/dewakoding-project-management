<x-filament-panels::page>
    
    {{-- Project Selector --}}
    <div class="mb-6">
        <x-filament::section>
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-medium text-gray-900 dark:text-white">
                    {{ $selectedProject ? $selectedProject->name : 'Select Project' }}
                </h2>
                
                <div>
                    <x-filament::input.wrapper>
                        <x-filament::input.select
                            wire:model.live="selectedProjectId"
                        >
                            <option value="">Select Project</option>
                            @foreach($projects as $project)
                                <option value="{{ $project->id }}" {{ $selectedProjectId == $project->id ? 'selected' : '' }}>
                                    {{ $project->name }}
                                </option>
                            @endforeach
                        </x-filament::input.select>
                    </x-filament::input.wrapper>
                </div>
            </div>
        </x-filament::section>
    </div>

    @if($selectedProject)
        <div
            x-data="dragDropHandler()"
            x-init="init()"
            @ticket-moved.window="init()"
            @ticket-updated.window="init()"
            @refresh-board.window="init()"
            class="overflow-x-auto pb-6 relative"
            id="board-container"
            style="max-width: calc(100vw - 2rem);"
        >
            <!-- Shadow indicator untuk scroll kanan -->
            <div class="absolute right-0 top-0 bottom-0 w-12 bg-gradient-to-l from-gray-100 dark:from-gray-800 to-transparent pointer-events-none z-10"
                 x-data="{ visible: false }"
                 x-init="$nextTick(() => { 
                     const container = document.getElementById('board-container');
                     visible = container.scrollWidth > container.clientWidth;
                     container.addEventListener('scroll', () => {
                         visible = container.scrollLeft + container.clientWidth < container.scrollWidth;
                     });
                 })"
                 x-show="visible"
            ></div>

            <div class="inline-flex gap-4 pb-2">
                @foreach ($ticketStatuses as $status)
                    <div 
                        class="status-column rounded-xl border border-gray-200 dark:border-gray-700 flex flex-col bg-gray-50 dark:bg-gray-900"
                        style="width: calc((100vw - 6rem) / 4);"
                        data-status-id="{{ $status->id }}"
                    >
                        <div 
                            class="px-4 py-3 rounded-t-xl border-b border-gray-200 dark:border-gray-700"
                            style="background-color: {{ $status->color ?? '#f3f4f6' }};"
                        >
                            <h3 class="font-medium flex items-center justify-between" style="color: white; text-shadow: 0px 0px 1px rgba(0,0,0,0.5);">
                                <span>{{ $status->name }}</span>
                                <span class="text-sm opacity-80">{{ $status->tickets->count() }}</span>
                            </h3>
                        </div>
                        
                        <div class="p-3 flex flex-col gap-3 h-[calc(100vh-20rem)] overflow-y-auto">
                            @foreach ($status->tickets as $ticket)
                                <div 
                                    class="ticket-card bg-white dark:bg-gray-800 p-3 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 cursor-move"
                                    data-ticket-id="{{ $ticket->id }}"
                                >
                                    <div class="flex justify-between items-center mb-2">
                                        <span class="text-xs font-mono text-gray-500 dark:text-gray-400 px-1.5 py-0.5 bg-gray-100 dark:bg-gray-700 rounded">
                                            {{ $ticket->uuid }}
                                        </span>
                                        @if ($ticket->due_date)
                                            <span class="text-xs px-1.5 py-0.5 rounded {{ $ticket->due_date->isPast() ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300' : 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300' }}">
                                                {{ $ticket->due_date->format('M d') }}
                                            </span>
                                        @endif
                                    </div>
                                    
                                    <h4 class="font-medium text-gray-900 dark:text-white mb-2">{{ $ticket->name }}</h4>
                                    
                                    @if ($ticket->description)
                                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-3 line-clamp-2">
                                            {{ \Illuminate\Support\Str::limit(strip_tags($ticket->description), 100) }}
                                        </p>
                                    @endif
                                    
                                    <div class="flex justify-between items-center mt-2">
                                        @if ($ticket->assignee)
                                            <div class="inline-flex items-center px-2 py-1 rounded-full bg-primary-100 dark:bg-primary-900/40 text-primary-700 dark:text-primary-300 gap-2">
                                                <span class="w-4 h-4 rounded-full bg-primary-500 flex items-center justify-center text-xs text-white mr-1.5">
                                                    {{ substr($ticket->assignee->name, 0, 1) }}
                                                </span>
                                                <span class="text-xs font-medium">{{ $ticket->assignee->name }}</span>
                                            </div>
                                        @else
                                            <div class="inline-flex items-center px-2 py-1 rounded-full bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-400">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400 dark:text-gray-500 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                                                </svg>
                                                <span class="text-xs font-medium">Unassigned</span>
                                            </div>
                                        @endif
                                        
                                        <button
                                            type="button" 
                                            wire:click="showTicketDetails({{ $ticket->id }})"
                                            class="inline-flex items-center justify-center w-8 h-8 text-sm font-medium rounded-lg border border-gray-200 dark:border-gray-700 text-primary-600 hover:text-primary-500 dark:text-primary-500 dark:hover:text-primary-400"
                                        >
                                            <x-heroicon-m-eye class="w-4 h-4" />
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                            
                            @if ($status->tickets->isEmpty())
                                <div class="flex items-center justify-center h-24 text-gray-500 dark:text-gray-400 text-sm italic border border-dashed border-gray-300 dark:border-gray-700 rounded-lg">
                                    No tickets
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
                
                @if ($ticketStatuses->isEmpty())
                    <div class="w-full flex items-center justify-center h-40 text-gray-500 dark:text-gray-400">
                        No status columns found for this project
                    </div>
                @endif
            </div>
        </div>
    @else
        <div class="flex flex-col items-center justify-center h-64 text-gray-500 dark:text-gray-400 gap-4">
            <div class="flex items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800 p-6">
                <x-heroicon-o-view-columns class="w-16 h-16 text-gray-400 dark:text-gray-500" />
            </div>
            <h2 class="text-xl font-medium text-gray-600 dark:text-gray-300">Please select a project first</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Select a project from the dropdown above to view the board
            </p>
        </div>
    @endif
    

    {{-- Drag and Drop Handler Script --}}
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('dragDropHandler', () => ({
                draggingTicket: null,
                
                init() {
                    this.$nextTick(() => {
                        this.removeAllEventListeners();
                        this.attachAllEventListeners();
                    });
                },
                
                removeAllEventListeners() {
                    const tickets = document.querySelectorAll('.ticket-card');
                    tickets.forEach(ticket => {
                        ticket.removeAttribute('draggable');
                        const newTicket = ticket.cloneNode(true);
                        ticket.parentNode.replaceChild(newTicket, ticket);
                    });
                    
                    const columns = document.querySelectorAll('.status-column');
                    columns.forEach(column => {
                        const newColumn = column.cloneNode(false);
                        
                        while (column.firstChild) {
                            newColumn.appendChild(column.firstChild);
                        }
                        
                        if (column.parentNode) {
                            column.parentNode.replaceChild(newColumn, column);
                        }
                    });
                },
                
                attachAllEventListeners() {
                    const tickets = document.querySelectorAll('.ticket-card');
                    tickets.forEach(ticket => {
                        ticket.setAttribute('draggable', true);
                        
                        ticket.addEventListener('dragstart', (e) => {
                            this.draggingTicket = ticket.getAttribute('data-ticket-id');
                            ticket.classList.add('opacity-50');
                            e.dataTransfer.effectAllowed = 'move';
                        });
                        
                        ticket.addEventListener('dragend', () => {
                            ticket.classList.remove('opacity-50');
                            this.draggingTicket = null;
                        });
                        
                        const detailsButton = ticket.querySelector('button');
                        if (detailsButton) {
                            const ticketId = ticket.getAttribute('data-ticket-id');
                            detailsButton.addEventListener('click', () => {
                                const componentId = document.querySelector('[wire\\:id]').getAttribute('wire:id');
                                if (componentId) {
                                    Livewire.find(componentId).showTicketDetails(ticketId);
                                }
                            });
                        }
                    });
                    
                    const columns = document.querySelectorAll('.status-column');
                    columns.forEach(column => {
                        column.addEventListener('dragover', (e) => {
                            e.preventDefault();
                            e.dataTransfer.dropEffect = 'move';
                            column.classList.add('bg-primary-50', 'dark:bg-primary-950');
                        });
                        
                        column.addEventListener('dragleave', () => {
                            column.classList.remove('bg-primary-50', 'dark:bg-primary-950');
                        });
                        
                        column.addEventListener('drop', (e) => {
                            e.preventDefault();
                            column.classList.remove('bg-primary-50', 'dark:bg-primary-950');
                            
                            if (this.draggingTicket) {
                                const statusId = column.getAttribute('data-status-id');
                                const ticketId = this.draggingTicket;
                                this.draggingTicket = null;
                                
                                const componentId = document.querySelector('[wire\\:id]').getAttribute('wire:id');
                                if (componentId) {
                                    Livewire.find(componentId).moveTicket(
                                        parseInt(ticketId), 
                                        parseInt(statusId)
                                    );
                                }
                            }
                        });
                    });
                }
            }));
        });
    </script>
</x-filament-panels::page>