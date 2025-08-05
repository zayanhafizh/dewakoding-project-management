<?php

namespace App\Livewire;

use App\Models\ExternalAccess;
use App\Models\Project;
use App\Models\Ticket;
use App\Models\TicketStatus;
use App\Models\TicketPriority;
use App\Models\TicketHistory;
use Livewire\Component;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class ExternalDashboard extends Component
{
    public $project;
    public $tickets;
    public $token;
    public $selectedStatus = null;
    public $selectedPriority = null;
    public $searchTerm = '';
    public $totalTickets = 0;
    public $completedTickets = 0;
    public $progressPercentage = 0;
    public $statuses;
    public $priorities;
    
    // Widget-style properties
    public $ticketsByStatus = [];
    public $ticketsByPriority = [];
    public $recentTickets = [];
    public $recentActivities = [];
    public $projectStats = [];
    public $monthlyTrend = [];
    public $overdueTickets = 0;
    public $newTicketsThisWeek = 0;
    public $completedThisWeek = 0;
    
    // Gantt chart properties
    public $ganttData = [];

    public function mount($token)
    {
        $this->token = $token;
        
        // Cek apakah sudah terautentikasi
        if (!Session::get('external_authenticated_' . $token)) {
            return redirect()->route('external.login', $token);
        }

        $externalAccess = ExternalAccess::where('access_token', $token)
            ->where('is_active', true)
            ->first();

        if (!$externalAccess) {
            abort(404, 'External access not found');
        }

        $this->project = $externalAccess->project;
        
        // Load statuses and priorities
        $this->statuses = TicketStatus::where('project_id', $this->project->id)
            ->orderBy('name')
            ->get();
            
        $this->priorities = TicketPriority::orderBy('name')
            ->get();

        // Update last accessed
        $externalAccess->updateLastAccessed();

        $this->loadTickets();
        $this->loadDashboardData();
        $this->loadWidgetData();
        $this->loadGanttData();
    }

    public function loadTickets()
    {
        $query = $this->project->tickets()
            ->with(['status', 'priority', 'assignees'])
            ->when($this->selectedStatus, function ($q) {
                $q->where('ticket_status_id', $this->selectedStatus);
            })
            ->when($this->selectedPriority, function ($q) {
                $q->where('priority_id', $this->selectedPriority);
            })
            ->when($this->searchTerm, function ($q) {
                $q->where(function ($query) {
                    $query->where('name', 'like', '%' . $this->searchTerm . '%')
                          ->orWhere('description', 'like', '%' . $this->searchTerm . '%');
                });
            });
    
        $this->tickets = $query->get();
        $this->totalTickets = $this->tickets->count();
    }
    
    public function loadDashboardData()
    {
        // Load tickets by status
        $this->ticketsByStatus = TicketStatus::where('project_id', $this->project->id)
            ->withCount(['tickets' => function($query) {
                $query->where('project_id', $this->project->id);
            }])
            ->orderBy('name')
            ->get()
            ->map(function($status) {
                return [
                    'status_name' => $status->name,
                    'color' => $status->color ?? '#6B7280',
                    'count' => $status->tickets_count
                ];
            })
            ->toArray();
            
        // Load tickets by priority
        $this->ticketsByPriority = $this->project->tickets()
            ->leftJoin('ticket_priorities', 'tickets.priority_id', '=', 'ticket_priorities.id')
            ->selectRaw('COALESCE(ticket_priorities.name, "No Priority") as priority_name, COUNT(*) as count')
            ->groupBy('ticket_priorities.id', 'ticket_priorities.name')
            ->orderBy('ticket_priorities.name')
            ->pluck('count', 'priority_name')
            ->toArray();
            
        // Load recent tickets
        $this->recentTickets = $this->project->tickets()
            ->with(['status', 'priority'])
            ->orderBy('updated_at', 'desc')
            ->limit(10)
            ->get();
    }
    
    public function loadWidgetData()
    {
        // Calculate remaining days
        $remainingDays = null;
        if ($this->project->end_date) {
            $remainingDays = (int) Carbon::now()->diffInDays(Carbon::parse($this->project->end_date), false);
        }
        
        // Project stats - Updated for new requirements
        $this->projectStats = [
            'total_team' => $this->project->users()->count(),
            'total_tickets' => $this->project->tickets()->count(),
            'remaining_days' => $remainingDays,
            'total_epic' => $this->project->tickets()
                ->whereHas('priority', function($q) {
                    $q->whereIn('name', ['Epic', 'High', 'Critical']);
                })->count(),
            
            // Keep the old keys for backward compatibility
            'completed_tickets' => $this->project->tickets()
                ->whereHas('status', function($q) {
                    $q->whereIn('name', ['Completed', 'Done', 'Closed']);
                })->count(),
            'in_progress_tickets' => $this->project->tickets()
                ->whereHas('status', function($q) {
                    $q->whereIn('name', ['In Progress', 'Doing']);
                })->count(),
            'overdue_tickets' => $this->project->tickets()
                ->where('due_date', '<', Carbon::now())
                ->whereHas('status', function($q) {
                    $q->whereNotIn('name', ['Completed', 'Done', 'Closed']);
                })->count(),
        ];
        
        // Weekly stats
        $this->newTicketsThisWeek = $this->project->tickets()
            ->where('created_at', '>=', Carbon::now()->subDays(7))
            ->count();
            
        $this->completedThisWeek = $this->project->tickets()
            ->whereHas('status', function($q) {
                $q->whereIn('name', ['Completed', 'Done', 'Closed']);
            })
            ->where('updated_at', '>=', Carbon::now()->subDays(7))
            ->count();
        
        // Monthly trend data
        $this->monthlyTrend = $this->project->tickets()
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, COUNT(*) as count')
            ->where('created_at', '>=', Carbon::now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('count', 'month')
            ->toArray();
        
        // Recent activities from ticket history
        $this->recentActivities = TicketHistory::whereHas('ticket', function($q) {
                $q->where('project_id', $this->project->id);
            })
            ->with(['ticket', 'status'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
    }
    
    public function loadGanttData()
    {
        try {
            $tickets = $this->project->tickets()
                ->select('id', 'name', 'due_date', 'created_at', 'ticket_status_id')
                ->with(['status:id,name,color'])
                ->whereNotNull('due_date')
                ->orderBy('due_date')
                ->get();

            if ($tickets->isEmpty()) {
                $this->ganttData = ['data' => [], 'links' => []];
                return;
            }

            $ganttTasks = [];
            $now = Carbon::now();

            foreach ($tickets as $ticket) {
                if (!$ticket->due_date) {
                    continue;
                }
                
                try {
                    $startDate = $ticket->created_at ? Carbon::parse($ticket->created_at) : $now->copy()->subDays(7);
                    $endDate = Carbon::parse($ticket->due_date);
                    
                    if ($endDate->lte($startDate)) {
                        $endDate = $startDate->copy()->addDays(1);
                    }
                    
                    $progress = $this->getSimpleProgress($ticket->status->name ?? '') / 100;
                    $isOverdue = $endDate->lt($now) && $progress < 1;
                    
                    $taskData = [
                        'id' => (string) $ticket->id,
                        'text' => $this->truncateName($ticket->name ?? 'Untitled Ticket'),
                        'start_date' => $startDate->format('d-m-Y H:i'),
                        'end_date' => $endDate->format('d-m-Y H:i'),
                        'duration' => max(1, $startDate->diffInDays($endDate)),
                        'progress' => max(0, min(1, $progress)),
                        'type' => 'task',
                        'readonly' => true,
                        'color' => $isOverdue ? '#ef4444' : ($ticket->status->color ?? '#3b82f6'),
                        'textColor' => '#ffffff',
                        'status' => $ticket->status->name ?? 'Unknown',
                        'is_overdue' => $isOverdue
                    ];
                    
                    $ganttTasks[] = $taskData;
                    
                } catch (\Exception $e) {
                    \Log::error('Error processing ticket ' . $ticket->id . ': ' . $e->getMessage());
                    continue;
                }
            }
            
            $this->ganttData = [
                'data' => $ganttTasks,
                'links' => []
            ];
            
        } catch (\Exception $e) {
            \Log::error('Error generating gantt data: ' . $e->getMessage());
            $this->ganttData = ['data' => [], 'links' => []];
        }
    }
    
    private function truncateName($name, $length = 50): string
    {
        return strlen($name) > $length ? substr($name, 0, $length) . '...' : $name;
    }

    private function getSimpleProgress($statusName): int
    {
        if (!$this->project || empty($statusName)) {
            return 0;
        }
        
        try {
            $statuses = $this->project->ticketStatuses()
                ->orderBy('sort_order')
                ->get();
            
            if ($statuses->isEmpty()) {
                return 0;
            }
            
            $currentStatus = $statuses->firstWhere('name', $statusName);
            
            if (!$currentStatus) {
                return 0;
            }
            
            $totalStatuses = $statuses->count();
            $currentPosition = $statuses->search(function ($status) use ($currentStatus) {
                return $status->id === $currentStatus->id;
            });
            
            if ($currentPosition === false) {
                return 0;
            }
            
            $progress = (($currentPosition + 1) / $totalStatuses) * 100;
            
            return (int) round(max(0, min(100, $progress)));
        } catch (\Exception $e) {
            \Log::error('Error calculating progress: ' . $e->getMessage());
            return 0;
        }
    }

    public function updatedSelectedStatus()
    {
        $this->loadTickets();
    }

    public function updatedSelectedPriority()
    {
        $this->loadTickets();
    }

    public function updatedSearchTerm()
    {
        $this->loadTickets();
    }

    public function render()
    {
        return view('livewire.external-dashboard')
            ->layout('layouts.external');
    }
}