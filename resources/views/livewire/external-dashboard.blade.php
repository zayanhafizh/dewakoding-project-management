<div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center py-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">{{ $project->name }}</h1>
                <p class="text-gray-600 mt-1">{{ config('app.name') }} - External Dashboard</p>
            </div>
            <div class="flex items-center space-x-4">
                <button 
                    wire:click="logout" 
                    class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200 flex items-center space-x-2"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                    </svg>
                    <span>Logout</span>
                </button>
            </div>
        </div>
    </div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-white rounded-lg border border-gray-200 p-6 shadow-sm">
                <div class="flex items-center">
                    <div class="p-2 bg-blue-50 rounded-lg">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Total Team</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $projectStats['total_team'] ?? 0 }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg border border-gray-200 p-6 shadow-sm">
                <div class="flex items-center">
                    <div class="p-2 bg-green-50 rounded-lg">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Progress</p>
                        <div class="flex items-center space-x-2">
                            <p class="text-2xl font-bold {{ $projectStats['progress_percentage'] >= 100 ? 'text-green-600' : ($projectStats['progress_percentage'] >= 75 ? 'text-blue-600' : ($projectStats['progress_percentage'] >= 50 ? 'text-yellow-600' : 'text-gray-900')) }}">
                                {{ $projectStats['progress_percentage'] ?? 0 }}%
                            </p>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                            <div class="bg-gradient-to-r {{ $projectStats['progress_percentage'] >= 100 ? 'from-green-400 to-green-600' : ($projectStats['progress_percentage'] >= 75 ? 'from-blue-400 to-blue-600' : ($projectStats['progress_percentage'] >= 50 ? 'from-yellow-400 to-yellow-600' : 'from-gray-400 to-gray-600')) }} h-2 rounded-full transition-all duration-300" 
                                 style="width: {{ min($projectStats['progress_percentage'] ?? 0, 100) }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg border border-gray-200 p-6 shadow-sm">
                <div class="flex items-center">
                    <div class="p-2 bg-yellow-50 rounded-lg">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Remaining Days</p>
                        <p class="text-2xl font-bold {{ $projectStats['remaining_days'] !== null && $projectStats['remaining_days'] < 0 ? 'text-red-600' : 'text-gray-900' }}">
                            {{ $projectStats['remaining_days'] ?? 'N/A' }}
                        </p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg border border-gray-200 p-6 shadow-sm">
                <div class="flex items-center">
                    <div class="p-2 bg-purple-50 rounded-lg">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Total Tasks</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $projectStats['total_tickets'] ?? 0 }}</p>
                    </div>
                </div>
            </div>
        </div>
        <!-- Tasks Table Section -->
        <div class="bg-white rounded-lg border border-gray-200 shadow-sm">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Project Tasks</h3>
                        <p class="text-sm text-gray-600">All tasks in this project (ordered by creation date)</p>
                    </div>
                    
                    <!-- Filters -->
                    <div class="flex flex-col sm:flex-row gap-3">
                        <!-- Search -->
                        <div class="relative">
                            <input 
                                type="text" 
                                wire:model.live.debounce.300ms="searchTerm"
                                placeholder="Search tasks..."
                                class="w-full sm:w-64 pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            >
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>
                        </div>
                        
                        <!-- Status Filter -->
                        <select 
                            wire:model.live="selectedStatus"
                            class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        >
                            <option value="">All Status</option>
                            @foreach($statuses as $status)
                                <option value="{{ $status->id }}">{{ $status->name }}</option>
                            @endforeach
                        </select>
                        
                        <!-- Clear Filters -->
                        @if($selectedStatus || $searchTerm)
                            <button 
                                wire:click="clearFilters"
                                class="px-3 py-2 text-sm text-gray-600 hover:text-gray-800 border border-gray-300 rounded-lg hover:bg-gray-50"
                            >
                                Clear
                            </button>
                        @endif
                    </div>
                </div>
            </div>
            
            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Code</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Task Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Due Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($this->tickets as $ticket)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $ticket->uuid }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    <div class="font-medium">{{ $ticket->name }}</div>
                                    @if($ticket->description)
                                        <div class="text-gray-500 text-xs mt-1 truncate max-w-xs">
                                           {!! Str::limit(strip_tags($ticket->description, '<b><i><em><strong>'), 60) !!}
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    @if($ticket->due_date)
                                        <div class="{{ $ticket->due_date->isPast() && !in_array($ticket->status->name, ['Completed', 'Done', 'Closed']) ? 'text-red-600 font-medium' : 'text-gray-900' }}">
                                            {{ $ticket->due_date->format('M d, Y') }}
                                        </div>
                                        @if($ticket->due_date->isPast() && !in_array($ticket->status->name, ['Completed', 'Done', 'Closed']))
                                            <div class="text-xs text-red-500">Overdue</div>
                                        @endif
                                    @else
                                        <span class="text-gray-400">No due date</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span 
                                        class="inline-flex px-2 py-1 text-xs font-semibold rounded-full text-white"
                                        style="background-color: {{ $ticket->status->color ?? '#6B7280' }}"
                                    >
                                        {{ $ticket->status->name }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center">
                                    <div class="text-gray-500">
                                        <svg class="w-12 h-12 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                        </svg>
                                        <p class="text-lg font-medium">No tasks found</p>
                                        <p class="text-sm">{{ $searchTerm || $selectedStatus ? 'Try adjusting your filters' : 'No tasks have been created yet' }}</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            @if($this->tickets->hasPages())
                <div class="px-6 py-4 border-t border-gray-200" id="pagination-section">
                    {{ $this->tickets->links() }}
                </div>
            @endif
        </div>
        <div class="bg-white rounded-lg border border-gray-200 shadow-sm">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Recent Activity</h3>
                <p class="text-sm text-gray-600">Latest updates and changes in the project</p>
            </div>
            <div class="p-6">
                @if($recentActivities->count() > 0)
                    <div class="space-y-4">
                        @foreach($recentActivities->take(8) as $activity)
                            <div class="flex items-start space-x-3">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center">
                                        <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm text-gray-900">
                                        <span class="font-medium">{{ $activity->ticket->name ?? 'Unknown Task' }}</span>
                                        @if($activity->status)
                                            moved to <span class="font-medium" style="color: {{ $activity->status->color ?? '#6B7280' }}">{{ $activity->status->name }}</span>
                                        @else
                                            was updated
                                        @endif
                                    </p>
                                    <p class="text-xs text-gray-500">{{ $activity->created_at->diffForHumans() }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                        <p class="text-gray-500">No recent activity</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.addEventListener('livewire:navigated', function() {
            const paginationSection = document.getElementById('pagination-section');
            if (paginationSection) {
                paginationSection.scrollIntoView({ 
                    behavior: 'smooth', 
                    block: 'nearest' 
                });
            }
        });
        
        if (typeof Livewire !== 'undefined') {
            Livewire.hook('message.processed', (message, component) => {
                if (message.updateQueue && message.updateQueue.some(update => 
                    update.payload && update.payload.method === 'gotoPage'
                )) {
                    setTimeout(() => {
                        const tableSection = document.querySelector('.bg-white.rounded-lg.border.border-gray-200.shadow-sm');
                        if (tableSection) {
                            tableSection.scrollIntoView({ 
                                behavior: 'smooth', 
                                block: 'start' 
                            });
                        }
                    }, 100);
                }
            });
        }
        
        document.addEventListener('click', function(e) {
            if (e.target.closest('[wire\\:click*="gotoPage"]') || 
                e.target.closest('a[href*="page="]')) {
                const tableSection = document.querySelector('.bg-white.rounded-lg.border.border-gray-200.shadow-sm');
                if (tableSection) {
                    sessionStorage.setItem('tableScrollPosition', tableSection.getBoundingClientRect().top + window.scrollY);
                }
            }
        });
        
        document.addEventListener('livewire:update', function() {
            const savedPosition = sessionStorage.getItem('tableScrollPosition');
            if (savedPosition) {
                setTimeout(() => {
                    window.scrollTo({
                        top: parseInt(savedPosition) - 20,
                        behavior: 'smooth'
                    });
                    sessionStorage.removeItem('tableScrollPosition');
                }, 50);
            }
        });
    });
</script>
@endpush