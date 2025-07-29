<?php

namespace App\Filament\Resources\ProjectResource\Pages;

use App\Filament\Resources\ProjectResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProjects extends ListRecords
{
    protected static string $resource = ProjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('timeline')
                ->label('Timeline')
                ->icon('heroicon-o-calendar')
                ->color('info')
                ->url(fn () => ProjectResource\Pages\ProjectGanttChart::getUrl()),
            Actions\CreateAction::make(),
        ];
    }
}
