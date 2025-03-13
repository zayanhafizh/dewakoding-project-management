<?php

namespace App\Filament\Pages;

use App\Filament\Resources\TicketResource;
use App\Models\Project;
use App\Models\Ticket;
use App\Models\TicketStatus;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Livewire\Attributes\On;

class ProjectBoardPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-view-columns';
    
    protected static string $view = 'filament.pages.project-board-page';
    
    protected static ?string $title = 'Project Board';
    
    protected static ?string $navigationLabel = 'Project Board';
    
    protected static ?int $navigationSort = 2;
    
    public ?Project $selectedProject = null;
    public Collection $projects;
    public Collection $ticketStatuses;
    public ?Ticket $selectedTicket = null;
    
    public function mount(): void
    {
        if (auth()->user()->hasRole(['super_admin', 'admin'])) {
            $this->projects = Project::all();
        } else {
            $this->projects = auth()->user()->projects;
        }
        
        // Jika ada project, select project pertama
        if ($this->projects->isNotEmpty()) {
            $this->selectProject($this->projects->first()->id);
        }
    }
    
    public function selectProject(int $projectId): void
    {
        $this->selectedProject = Project::find($projectId);
        $this->loadTicketStatuses();
        
        $this->selectedTicket = null;
    }
    
    public function loadTicketStatuses(): void
    {
        if (!$this->selectedProject) {
            $this->ticketStatuses = collect();
            return;
        }
        
        $statuses = $this->selectedProject->ticketStatuses()
            ->orderBy('id')
            ->get();
            
        $isAdmin = auth()->user()->hasRole(['super_admin', 'admin']);
        
        foreach ($statuses as $status) {
            $ticketsQuery = $status->tickets()->orderBy('created_at', 'desc');
            $status->setRelation('tickets', $ticketsQuery->get());
        }
        
        $this->ticketStatuses = $statuses;
    }

    #[On('ticket-moved')]
    public function moveTicket(int $ticketId, int $newStatusId): void
    {
        $ticket = Ticket::find($ticketId);
        
        if (!$this->canManageTicket($ticket)) {
            Notification::make()
                ->title('Permission Denied')
                ->body('You do not have permission to move this ticket.')
                ->danger()
                ->send();
            
            $this->loadTicketStatuses();
            $this->dispatch('ticket-updated');
            return;
        }
        
        if ($ticket && $ticket->project_id === $this->selectedProject?->id) {
            $ticket->update([
                'ticket_status_id' => $newStatusId
            ]);

            $this->loadTicketStatuses();
            $this->dispatch('ticket-updated');
            
            Notification::make()
                ->title('Ticket Berhasil Dipindahkan')
                ->success()
                ->send();
        }
    }

    #[On('refresh-board')]
    public function refreshBoard(): void
    {
        $this->loadTicketStatuses();
    }
    
    public function showTicketDetails(int $ticketId): void
    {
        $ticket = Ticket::with(['assignee', 'status'])->find($ticketId);
        
        $this->selectedTicket = $ticket;
    }
    
    public function closeTicketDetails(): void
    {
        $this->selectedTicket = null;
    }
    
    public function editTicket(int $ticketId): void
    {
        $ticket = Ticket::find($ticketId);
        
        if (!$this->canEditTicket($ticket)) {
            Notification::make()
                ->title('Permission Denied')
                ->body('You do not have permission to edit this ticket.')
                ->danger()
                ->send();
            return;
        }
        
        $this->redirect(TicketResource::getUrl('edit', ['record' => $ticketId]));
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('new_ticket')
                ->label('New Ticket')
                ->icon('heroicon-m-plus')
                ->visible(fn () => $this->selectedProject !== null && auth()->user()->hasRole(['super_admin', 'admin']))
                ->url(fn (): string => TicketResource::getUrl('create', [
                    'project_id' => $this->selectedProject?->id,
                    'ticket_status_id' => $this->selectedProject?->ticketStatuses->first()?->id
                ])),
        ];
    }
    
    private function canViewTicket(?Ticket $ticket): bool
    {
        if (!$ticket) return false;
        
        return auth()->user()->hasRole(['super_admin', 'admin']) 
            || $ticket->user_id === auth()->id();
    }
    
    private function canEditTicket(?Ticket $ticket): bool
    {
        if (!$ticket) return false;
        
        return auth()->user()->hasRole(['super_admin', 'admin']) 
            || $ticket->user_id === auth()->id();
    }
    
    private function canManageTicket(?Ticket $ticket): bool
    {
        if (!$ticket) return false;
        
        return auth()->user()->hasRole(['super_admin', 'admin']) 
            || $ticket->user_id === auth()->id();
    }
}