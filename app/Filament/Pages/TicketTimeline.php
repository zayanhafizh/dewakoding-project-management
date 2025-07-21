<?php

namespace App\Filament\Pages;

use App\Models\Project;
use App\Models\Ticket;
use Auth;
use Carbon\Carbon;
use Filament\Pages\Page;
use Filament\Notifications\Notification;
use Illuminate\Support\Collection;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;

class TicketTimeline extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-calendar';
    protected static ?string $navigationLabel = 'Timeline';
    protected static ?string $title = 'Project Timeline';
    protected static ?int $navigationSort = 2;
    protected static string $view = 'filament.pages.ticket-timeline';
    protected static ?string $navigationGroup = 'Project Management';
    protected static ?string $slug = 'ticket-timeline/{project_id?}';

    public ?string $projectId = null;
    public Collection $projects;
    public ?Project $selectedProject = null;

    protected $listeners = [
        'refreshData' => '$refresh'
    ];

    public function mount($project_id = null): void
    {
        try {
            $user = Auth::user();

            if ($user->hasRole('super_admin')) {
                $this->projects = Project::all();
            } else {
                $this->projects = $user->projects ?? collect();
            }

            if ($project_id && $this->projects->contains('id', $project_id)) {
                $this->projectId = (string) $project_id;
                $this->selectedProject = Project::find($project_id);
            }
        } catch (\Exception $e) {
            \Log::error('Error in TicketTimeline mount: ' . $e->getMessage());
            
            Notification::make()
                ->title('Error loading page')
                ->danger()
                ->send();
        }
    }

    public function updatedProjectId($value): void
    {
        if ($value) {
            $this->selectProject($value);
        } else {
            $this->selectedProject = null;
            $this->projectId = null;
            $this->redirect(static::getUrl());
        }
    }

    public function selectProject($projectId): void
    {
        $this->projectId = (string) $projectId;
        $this->selectedProject = Project::find($projectId);

        if ($this->selectedProject && $this->projects->contains('id', $projectId)) {
            $this->dispatch('redirectToProject', url: static::getUrl(['project_id' => $projectId]));
        } else {
            Notification::make()
                ->title('Project Not Found')
                ->danger()
                ->send();
                
            $this->selectedProject = null;
            $this->projectId = null;
        }
    }

    public function getTicketsProperty(): Collection
    {
        if (!$this->projectId) {
            return collect();
        }

        return Ticket::select('id', 'name', 'due_date', 'created_at', 'ticket_status_id')
            ->with(['status:id,name,color'])
            ->where('project_id', $this->projectId)
            ->whereNotNull('due_date')
            ->orderBy('due_date')
            ->get();
    }

    public function getGanttDataProperty(): array
    {
        if (!$this->selectedProject) {
            return ['data' => [], 'links' => []];
        }
    
        $tickets = $this->tickets;
        if ($tickets->isEmpty()) {
            return ['data' => [], 'links' => []];
        }
    
        $ganttTasks = [];
        $now = Carbon::now();
    
        foreach ($tickets as $ticket) {
            if (!$ticket->due_date) {
                continue;
            }
            
            $startDate = $ticket->created_at ? Carbon::parse($ticket->created_at) : $now->copy()->subDays(7);
            $endDate = Carbon::parse($ticket->due_date);
            
            // Validasi tanggal
            if ($endDate->lte($startDate)) {
                $endDate = $startDate->copy()->addDays(1);
            }
            
            $progress = $this->getSimpleProgress($ticket->status->name ?? '') / 100;
            $isOverdue = $endDate->lt($now) && $progress < 1;
            
            // Format data untuk dhtmlxGantt
            $ganttTasks[] = [
                'id' => $ticket->id,
                'text' => $this->truncateName($ticket->name ?? 'Untitled Ticket'),
                'start_date' => $startDate->format('d-m-Y H:i'),
                'end_date' => $endDate->format('d-m-Y H:i'),
                'duration' => $startDate->diffInDays($endDate) ?: 1,
                'progress' => $progress,
                'type' => 'task',
                'readonly' => true,
                'color' => $isOverdue ? '#ef4444' : ($ticket->status->color ?? '#3b82f6'),
                'textColor' => '#ffffff',
                'status' => $ticket->status->name ?? 'Unknown',
                'is_overdue' => $isOverdue
            ];
        }
        
        return [
            'data' => $ganttTasks,
            'links' => [] // Tidak ada dependencies untuk saat ini
        ];
    }

    private function truncateName($name, $length = 50): string
    {
        return strlen($name) > $length ? substr($name, 0, $length) . '...' : $name;
    }

    private function getSimpleProgress($statusName): int
    {
        if (!$this->selectedProject || empty($statusName)) {
            return 0;
        }
        
        try {
            $statuses = $this->selectedProject->ticketStatuses()
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
}