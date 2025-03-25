<?php

namespace App\Filament\Resources\TicketResource\Pages;

use App\Filament\Resources\TicketResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

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
}