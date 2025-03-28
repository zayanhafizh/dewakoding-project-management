<?php

namespace App\Filament\Resources\TicketResource\Pages;

use App\Filament\Resources\TicketResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Group;

class ViewTicket extends ViewRecord
{
    protected static string $resource = TicketResource::class;
    
    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->visible(function () {
                    $ticket = $this->getRecord();
                    
                    return auth()->user()->hasRole(['super_admin', 'admin']) 
                        || $ticket->user_id === auth()->id();
                }),
                
            Actions\Action::make('back')
                ->label('Back to Board')
                ->color('gray')
                ->url(fn () => route('filament.admin.pages.project-board')),
        ];
    }
    
    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Grid::make(3)
                    ->schema([
                        Group::make([
                            Section::make()
                                ->schema([
                                    TextEntry::make('uuid')
                                        ->label('Ticket ID')
                                        ->copyable(),
                                        
                                    TextEntry::make('name')
                                        ->label('Ticket Name'),
                                        
                                    TextEntry::make('project.name')
                                        ->label('Project'),
                                ])
                        ])->columnSpan(1),
                        
                        Group::make([
                            Section::make()
                                ->schema([
                                    TextEntry::make('status.name')
                                        ->label('Status')
                                        ->badge()
                                        ->color(fn($state) => match ($state) {
                                            'To Do' => 'warning',
                                            'In Progress' => 'info',
                                            'Review' => 'primary',
                                            'Done' => 'success',
                                            default => 'gray',
                                        }),
                                        
                                    TextEntry::make('assignee.name')
                                        ->label('Assignee'),
                                        
                                    TextEntry::make('due_date')
                                        ->label('Due Date')
                                        ->date(),
                                ])
                        ])->columnSpan(1),
                        
                        Group::make([
                            Section::make()
                                ->schema([
                                    TextEntry::make('created_at')
                                        ->label('Created At')
                                        ->dateTime(),
                                        
                                    TextEntry::make('updated_at')
                                        ->label('Updated At')
                                        ->dateTime(),
                                ])
                        ])->columnSpan(1),
                    ]),
                    
                Section::make('Description')
                    ->schema([
                        TextEntry::make('description')
                            ->hiddenLabel()
                            ->html()
                            ->columnSpanFull(),
                    ]),
                    
                Section::make('Status History')
                    ->collapsible()
                    ->schema([
                        TextEntry::make('histories')
                            ->hiddenLabel()
                            ->view('filament.resources.ticket-resource.timeline-history')
                    ]),
            ]);
    }
}