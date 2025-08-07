<?php

namespace App\Filament\Resources\NotificationResource\Pages;

use App\Filament\Resources\NotificationResource;
use App\Models\Notification;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListNotifications extends ListRecords
{
    protected static string $resource = NotificationResource::class;

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All')
                ->badge(Notification::where('user_id', auth()->id())->count()),
                
            'unread' => Tab::make('Unread')
                ->modifyQueryUsing(fn (Builder $query) => $query->unread())
                ->badge(Notification::where('user_id', auth()->id())->unread()->count())
                ->badgeColor('danger'),
                
            'read' => Tab::make('Read')
                ->modifyQueryUsing(fn (Builder $query) => $query->read())
                ->badge(Notification::where('user_id', auth()->id())->read()->count())
                ->badgeColor('success'),
        ];
    }
}
