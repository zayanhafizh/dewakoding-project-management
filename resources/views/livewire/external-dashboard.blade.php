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
        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Total Team -->
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
            
            <!-- Total Tickets -->
            <div class="bg-white rounded-lg border border-gray-200 p-6 shadow-sm">
                <div class="flex items-center">
                    <div class="p-2 bg-green-50 rounded-lg">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Total Tickets</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $projectStats['total_tickets'] ?? 0 }}</p>
                    </div>
                </div>
            </div>
            
            <!-- Remaining Days -->
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
            
            <!-- Total Epic -->
            <div class="bg-white rounded-lg border border-gray-200 p-6 shadow-sm">
                <div class="flex items-center">
                    <div class="p-2 bg-purple-50 rounded-lg">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Total Epic</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $projectStats['total_epic'] ?? 0 }}</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Gantt Chart Section -->
        <div class="bg-white rounded-lg border border-gray-200 shadow-sm">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                    <h2 class="text-lg font-medium text-gray-900">Project Timeline</h2>
                    <div class="flex items-center gap-2 text-sm text-gray-500">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        <span>Read Only View</span>
                    </div>
                </div>
            </div>

            <!-- dhtmlxGantt Container -->
            <div class="w-full">
                @if(count($ganttData['data'] ?? []) > 0)
                    <div id="gantt_here" style="width:100%; height:600px;"></div>
                @else
                    <div class="flex flex-col items-center justify-center h-64 text-gray-500 gap-4">
                        <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 002 2z" />
                        </svg>
                        <h3 class="text-lg font-medium">No tickets with due dates</h3>
                        <p class="text-sm">Add due dates to tickets to see the timeline</p>
                    </div>
                @endif
            </div>
        </div>
        
        <!-- Recent Activity Section -->
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
                                        <span class="font-medium">{{ $activity->ticket->name ?? 'Unknown Ticket' }}</span>
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

@push('styles')
    <link rel="stylesheet" href="https://cdn.dhtmlx.com/gantt/edge/dhtmlxgantt.css" type="text/css">
    <link rel="stylesheet" href="{{ asset('css/gantt-timeline.css') }}" type="text/css">
@endpush

@push('scripts')
    <script src="https://cdn.dhtmlx.com/gantt/edge/dhtmlxgantt.js"></script>
    <script>
        window.ganttState = window.ganttState || {
            initialized: false,
            currentProjectId: '{{ $project->id }}'
        };
        
        function getGanttData() {
            return @json($ganttData ?? ['data' => [], 'links' => []]);
        }
        
        function waitForGantt(callback, maxAttempts = 50) {
            let attempts = 0;
            function check() {
                attempts++;
                if (typeof gantt !== 'undefined' && gantt.init) {
                    callback();
                } else if (attempts < maxAttempts) {
                    setTimeout(check, 100);
                } else {
                    console.error('dhtmlxGantt failed to load after', maxAttempts * 100, 'ms');
                    showErrorMessage('Failed to load Gantt library');
                }
            }
            check();
        }
        
        function waitForContainer(callback, maxAttempts = 30) {
            let attempts = 0;
            function check() {
                attempts++;
                const container = document.getElementById('gantt_here');
                if (container && container.offsetParent !== null) {
                    callback();
                } else if (attempts < maxAttempts) {
                    setTimeout(check, 100);
                } else {
                    console.error('Gantt container not found or not visible after', maxAttempts * 100, 'ms');
                    showErrorMessage('Gantt container not available');
                }
            }
            check();
        }
        
        function showErrorMessage(message = 'Error loading timeline') {
            const container = document.getElementById('gantt_here');
            if (container) {
                container.innerHTML = `
                    <div class="flex flex-col items-center justify-center h-64 text-gray-500 gap-4">
                        <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <h3 class="text-lg font-medium">${message}</h3>
                        <p class="text-sm">Please refresh the page or contact support</p>
                        <button onclick="location.reload()" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                            Refresh Page
                        </button>
                    </div>
                `;
            }
        }
        
        function initializeGanttSafely() {
            waitForContainer(() => {
                waitForGantt(() => {
                    initializeGantt();
                });
            });
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            initializeGanttSafely();
            
            if (typeof Livewire !== 'undefined') {
                setupLivewireListeners();
            } else {
                document.addEventListener('livewire:init', setupLivewireListeners);
            }
        });
        
        if (document.readyState === 'loading') {
            setTimeout(() => {
                initializeGanttSafely();
                if (typeof Livewire !== 'undefined') {
                    setupLivewireListeners();
                }
            }, 100);
        }
        
        function setupLivewireListeners() {
            Livewire.on('refreshData', () => {
                setTimeout(() => {
                    initializeGanttSafely();
                }, 100);
            });
            
            Livewire.on('refreshGanttChart', () => {
                setTimeout(() => {
                    initializeGanttSafely();
                }, 200);
            });
        }
    
        function initializeGantt() {
            try {
                const ganttData = getGanttData();
                
                if (!ganttData.data || ganttData.data.length === 0) {
                    const container = document.getElementById('gantt_here');
                    if (container) {
                        container.innerHTML = '<div class="p-4 text-center text-gray-500">No timeline data available</div>';
                    }
                    return;
                }
        
                const container = document.getElementById('gantt_here');
                if (!container) {
                    throw new Error('Gantt container not found');
                }
                
                if (typeof gantt === 'undefined' || !gantt.init) {
                    throw new Error('dhtmlxGantt library not properly loaded');
                }
                
                try {
                    gantt.config.date_format = "%Y-%m-%d %H:%i";
                    gantt.config.xml_date = "%Y-%m-%d %H:%i";
                    
                    let minDate = new Date();
                    let maxDate = new Date();
                    
                    if (ganttData.data && ganttData.data.length > 0) {
                        const dates = ganttData.data.map(task => {
                            const startDate = new Date(task.start_date);
                            const endDate = new Date(task.end_date);
                            return [startDate, endDate];
                        }).flat().filter(date => !isNaN(date.getTime()));
                        
                        if (dates.length > 0) {
                            minDate = new Date(Math.min(...dates));
                            maxDate = new Date(Math.max(...dates));
                            
                            minDate.setMonth(minDate.getMonth() - 1);
                            maxDate.setMonth(maxDate.getMonth() + 1);
                        }
                    }
                    
                    gantt.config.start_date = minDate;
                    gantt.config.end_date = maxDate;
                    
                    gantt.config.scales = [
                        {unit: "month", step: 1, format: "%F %Y"},
                        {unit: "day", step: 1, format: "%j"}
                    ];
                    
                    gantt.config.fit_tasks = true;
                    gantt.config.auto_scheduling = false;
                    gantt.config.auto_scheduling_strict = false;
                    
                    gantt.config.readonly = true;
                    gantt.config.drag_move = false;
                    gantt.config.drag_resize = false;
                    gantt.config.drag_progress = false;
                    gantt.config.drag_links = false;
                    
                    gantt.config.grid_width = 350;
                    gantt.config.row_height = 40;
                    gantt.config.task_height = 32;
                    gantt.config.bar_height = 24;
                    
                    gantt.config.smart_rendering = true;
                    gantt.config.static_background = true;
                    
                    gantt.config.columns = [
                        {name: "text", label: "Task Name", width: 200, tree: true},
                        {name: "status", label: "Status", width: 100, align: "center"},
                        {name: "duration", label: "Duration", width: 50, align: "center"}
                    ];
                    
                    gantt.templates.task_class = function(start, end, task) {
                        return task.is_overdue ? "overdue" : "";
                    };
                    
                    gantt.templates.tooltip_text = function(start, end, task) {
                        return `<b>Task:</b> ${task.text}<br/>
                                <b>Status:</b> ${task.status}<br/>
                                <b>Duration:</b> ${task.duration} day(s)<br/>
                                <b>Progress:</b> ${Math.round(task.progress * 100)}%<br/>
                                <b>Start:</b> ${gantt.templates.tooltip_date_format(start)}<br/>
                                <b>End:</b> ${gantt.templates.tooltip_date_format(end)}
                                ${task.is_overdue ? '<br/><b style="color: #ef4444;">⚠️ OVERDUE</b>' : ''}`;
                    };
                } catch (configError) {
                    console.error('Error configuring gantt:', configError);
                    throw new Error('Failed to configure Gantt chart');
                }
                
                try {
                    if (!window.ganttState.initialized) {
                        gantt.init("gantt_here");
                        window.ganttState.initialized = true;
                    }
                } catch (initError) {
                    console.error('Error initializing gantt:', initError);
                    throw new Error('Failed to initialize Gantt chart');
                }
                
                try {
                    gantt.clearAll();
                    
                    if (!Array.isArray(ganttData.data)) {
                        throw new Error('Invalid gantt data format: data must be an array');
                    }
                    
                    const processedData = {
                        data: ganttData.data.map(task => {
                            const convertDate = (dateStr) => {
                                if (!dateStr) return dateStr;
                                try {
                                    const parts = dateStr.split(' ');
                                    const datePart = parts[0];
                                    const timePart = parts[1] || '00:00';
                                    const [day, month, year] = datePart.split('-');
                                    return `${year}-${month.padStart(2, '0')}-${day.padStart(2, '0')} ${timePart}`;
                                } catch (e) {
                                    console.warn('Error converting date:', dateStr, e);
                                    return dateStr;
                                }
                            };
                            
                            return {
                                ...task,
                                start_date: convertDate(task.start_date),
                                end_date: convertDate(task.end_date)
                            };
                        }),
                        links: ganttData.links || []
                    };
                    
                    for (let i = 0; i < processedData.data.length; i++) {
                        const task = processedData.data[i];
                        if (!task.id || !task.text || !task.start_date || !task.end_date) {
                            console.warn('Invalid task data at index', i, task);
                            continue;
                        }
                    }
                    
                    gantt.parse(processedData);
                    
                } catch (parseError) {
                    console.error('Error parsing gantt data:', parseError);
                    throw new Error('Failed to load Gantt data');
                }
                
            } catch (error) {
                console.error('Error initializing dhtmlxGantt:', error);
                showErrorMessage(error.message || 'Error loading timeline');
            }
        }
    </script>
@endpush