<?php

namespace App\Filament\Resources;

use App\Filament\Actions\ImportTicketsAction;
use App\Filament\Resources\ProjectResource\Pages;
use App\Filament\Resources\ProjectResource\RelationManagers;
use App\Models\Project;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProjectResource extends Resource
{
    protected static ?string $model = Project::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\RichEditor::make('description')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('ticket_prefix')
                    ->required()
                    ->maxLength(255),
                Forms\Components\DatePicker::make('start_date')
                    ->label('Start Date')
                    ->native(false)
                    ->displayFormat('d/m/Y'),
                Forms\Components\DatePicker::make('end_date')
                    ->label('End Date')
                    ->native(false)
                    ->displayFormat('d/m/Y')
                    ->afterOrEqual('start_date'),
                Forms\Components\Toggle::make('create_default_statuses')
                    ->label('Use Default Ticket Statuses')
                    ->helperText('Create standard Backlog, To Do, In Progress, Review, and Done statuses automatically')
                    ->default(true)
                    ->dehydrated(false)
                    ->visible(fn ($livewire) => $livewire instanceof Pages\CreateProject),
                
                Forms\Components\Toggle::make('is_pinned')
                    ->label('Pin Project')
                    ->helperText('Pinned projects will appear in the dashboard timeline')
                    ->live()
                    ->afterStateUpdated(function ($state, $set) {
                        if ($state) {
                            $set('pinned_date', now());
                        } else {
                            $set('pinned_date', null);
                        }
                    })
                    ->dehydrated(false)
                    ->afterStateHydrated(function ($component, $state, $get) {
                        $component->state(!is_null($get('pinned_date')));
                    }),
                Forms\Components\DateTimePicker::make('pinned_date')
                    ->label('Pinned Date')
                    ->native(false)
                    ->displayFormat('d/m/Y H:i')
                    ->visible(fn ($get) => $get('is_pinned'))
                    ->dehydrated(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('ticket_prefix')
                    ->searchable(),
                Tables\Columns\TextColumn::make('start_date')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_date')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('remaining_days')
                    ->label('Remaining Days')
                    ->getStateUsing(function (Project $record): ?string {
                        if (!$record->end_date) {
                            return null;
                        }
                        
                        return $record->remaining_days . ' days';
                    })
                    ->badge()
                    ->color(fn (Project $record): string => 
                        !$record->end_date ? 'gray' :
                        ($record->remaining_days <= 0 ? 'danger' : 
                        ($record->remaining_days <= 7 ? 'warning' : 'success'))
                    ),
                Tables\Columns\ToggleColumn::make('is_pinned')
                    ->label('Pinned')
                    ->updateStateUsing(function ($record, $state) {
                        // Gunakan method pin/unpin yang sudah ada di model
                        if ($state) {
                            $record->pin();
                        } else {
                            $record->unpin();
                        }
                        return $state;
                    }),
                Tables\Columns\TextColumn::make('members_count')
                    ->counts('members')
                    ->label('Members'),
                Tables\Columns\TextColumn::make('tickets_count')
                    ->counts('tickets')
                    ->label('Tickets'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('client_access')
                    ->label('Client Access')
                    ->icon('heroicon-o-key')
                    ->color('info')
                    ->modalHeading('Client Portal Access')
                    ->modalDescription('Share these credentials with your client to access the project portal.')
                    ->modalContent(function (Project $record) {
                        $clientAccess = $record->clientAccess;
                        
                        if (!$clientAccess) {
                            $clientAccess = $record->generateClientAccess();
                            Log::info('Generated client access for project: ' . $record->name, [
                                'project_id' => $record->id,
                                'access_token' => $clientAccess->access_token,
                                'password' => $clientAccess->password
                            ]);
                        }
                        
                        $portalUrl = url('/client/' . $clientAccess->access_token);
                        
                        return view('filament.components.client-access-modal', [
                            'portalUrl' => $portalUrl,
                            'password' => $clientAccess->password, // Tampilkan password langsung
                            'lastAccessed' => $clientAccess->last_accessed_at ? $clientAccess->last_accessed_at->format('d/m/Y H:i') : null,
                            'isActive' => $clientAccess->is_active,
                            'projectId' => $record->id // Tambahkan project ID untuk regenerate
                        ]);
                    })
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close'),
                Tables\Actions\Action::make('regenerate_client_access')
                    ->label('Regenerate Access')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Regenerate Client Access')
                    ->modalDescription('This will generate new credentials and invalidate the old ones. Are you sure?')
                    ->action(function (Project $record) {
                        // Delete existing access
                        $record->clientAccess()?->delete();
                        
                        // Generate new access
                        $newAccess = $record->generateClientAccess();
                        
                        Log::info('Regenerated client access for project: ' . $record->name, [
                            'project_id' => $record->id,
                            'access_token' => $newAccess->access_token,
                            'password' => $newAccess->password
                        ]);
                        
                        Notification::make()
                            ->title('Client access regenerated successfully')
                            ->body('New credentials have been generated. Please share the new URL and password with your client.')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (Project $record) => $record->clientAccess !== null),
                Tables\Actions\Action::make('external_access')
                    ->label('External Access')
                    ->icon('heroicon-o-globe-alt')
                    ->color('info')
                    ->modalHeading('External Dashboard Access')
                    ->modalDescription('Share these credentials with external users to access the project dashboard.')
                    ->modalContent(function (Project $record) {
                        $externalAccess = $record->externalAccess;
                    
                        if (!$externalAccess) {
                            $externalAccess = $record->generateExternalAccess();
                            Log::info('Generated external access for project: ' . $record->name, [
                                'project_id' => $record->id,
                                'access_token' => $externalAccess->access_token,
                                'password' => $externalAccess->password
                            ]);
                        }
                    
                        $dashboardUrl = url('/external/' . $externalAccess->access_token);
                    
                        return view('filament.components.external-access-modal', [
                            'dashboardUrl' => $dashboardUrl,
                            'password' => $externalAccess->password,
                            'lastAccessed' => $externalAccess->last_accessed_at ? $externalAccess->last_accessed_at->format('d/m/Y H:i') : null,
                            'isActive' => $externalAccess->is_active,
                        ]);
                    })
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close'),
                Tables\Actions\Action::make('regenerate_external_access')
                    ->label('Regenerate Access')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Regenerate External Access')
                    ->modalDescription('This will generate new credentials and invalidate the current ones.')
                    ->action(function (Project $record) {
                        // Hapus akses yang lama
                        $record->externalAccess()?->delete();
                        
                        // Generate yang baru
                        $newAccess = $record->generateExternalAccess();
                        
                        // Log credentials baru
                        Log::info('Regenerated external access for project: ' . $record->name, [
                            'project_id' => $record->id,
                            'access_token' => $newAccess->access_token,
                            'password' => $newAccess->password
                        ]);
                        
                        Notification::make()
                            ->title('External access regenerated successfully')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (Project $record) => $record->externalAccess !== null),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\TicketStatusesRelationManager::class,
            RelationManagers\MembersRelationManager::class,
            RelationManagers\EpicsRelationManager::class,
            RelationManagers\TicketsRelationManager::class,
            RelationManagers\NotesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProjects::route('/'),
            'create' => Pages\CreateProject::route('/create'),
            'edit' => Pages\EditProject::route('/{record}/edit'),
            'gantt-chart' => Pages\ProjectGanttChart::route('/gantt-chart'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        $userIsSuperAdmin = auth()->user() && (
            (method_exists(auth()->user(), 'hasRole') && auth()->user()->hasRole('super_admin'))
            || (isset(auth()->user()->role) && auth()->user()->role === 'super_admin')
        );

        if (! $userIsSuperAdmin) {
            $query->whereHas('members', function (Builder $query) {
                $query->where('user_id', auth()->id());
            });
        }

        return $query;
    }
}