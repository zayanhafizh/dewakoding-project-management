<?php

namespace App\Filament\Actions;

use App\Imports\TicketsImport;
use App\Exports\TicketTemplateExport;
use App\Models\Project;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Actions\Action as FormAction;
use Filament\Notifications\Notification;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ImportTicketsAction
{
    public static function make(): Action
    {
        return Action::make('import_tickets')
            ->label('Import from Excel')
            ->icon('heroicon-m-arrow-up-tray')
            ->color('success')
            ->form([
                Section::make('Import Tickets from Excel')
                    ->description('Select a project and upload an Excel file to import tickets. You can download the template below after selecting a project.')
                    ->schema([
                        Select::make('project_id')
                            ->label('Select Project')
                            ->options(function () {
                                return Project::query()
                                    ->whereHas('members', function ($query) {
                                        $query->where('user_id', auth()->id());
                                    })
                                    ->orWhere(function ($query) {
                                        if (auth()->user()->hasRole('super_admin')) {
                                            $query->whereRaw('1=1');
                                        }
                                    })
                                    ->pluck('name', 'id')
                                    ->toArray();
                            })
                            ->required()
                            ->searchable()
                            ->live()
                            ->afterStateUpdated(function ($state, $set) {
                                // Reset file upload when project changes
                                $set('excel_file', null);
                            }),
                        
                        Actions::make([
                            FormAction::make('download_template')
                                ->label('Download Import Template')
                                ->icon('heroicon-m-arrow-down-tray')
                                ->color('info')
                                ->visible(fn ($get) => $get('project_id'))
                                ->action(function ($get) {
                                    $projectId = $get('project_id');
                                    if (!$projectId) {
                                        Notification::make()
                                            ->title('Error')
                                            ->body('Please select a project first.')
                                            ->danger()
                                            ->send();
                                        return;
                                    }
                                    
                                    $project = Project::findOrFail($projectId);
                                    $filename = 'ticket-import-template-' . str($project->name)->slug() . '.xlsx';
                                    
                                    return Excel::download(
                                        new TicketTemplateExport($project),
                                        $filename
                                    );
                                })
                        ])->fullWidth(),
                        
                        FileUpload::make('excel_file')
                            ->label('Excel File')
                            ->helperText('Upload the Excel file with ticket data. Make sure to use the template format above.')
                            ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel'])
                            ->maxSize(5120) // 5MB
                            ->required()
                            ->disk('local')
                            ->directory('temp-imports')
                            ->visibility('private')
                            ->visible(fn ($get) => $get('project_id')),
                    ]),
            ])
            ->action(function (array $data) {
                $project = Project::findOrFail($data['project_id']);
                $filePath = Storage::disk('local')->path($data['excel_file']);
                
                try {
                    $import = new TicketsImport($project);
                    Excel::import($import, $filePath);
                    
                    $importedCount = $import->getImportedCount();
                    $errors = $import->errors();
                    $failures = $import->failures();
                    
                    // Clean up uploaded file
                    Storage::disk('local')->delete($data['excel_file']);
                    
                    if ($importedCount > 0) {
                        $message = "Successfully imported {$importedCount} ticket(s) to project '{$project->name}'.";
                        
                        if (count($errors) > 0 || count($failures) > 0) {
                            $message .= " Some rows had errors and were skipped.";
                        }
                        
                        Notification::make()
                            ->title('Import Completed')
                            ->body($message)
                            ->success()
                            ->send();
                    } else {
                        Notification::make()
                            ->title('Import Failed')
                            ->body('No tickets were imported. Please check your file format and data.')
                            ->warning()
                            ->send();
                    }
                    
                } catch (\Exception $e) {
                    // Clean up uploaded file
                    Storage::disk('local')->delete($data['excel_file']);
                    
                    Notification::make()
                        ->title('Import Error')
                        ->body('An error occurred during import: ' . $e->getMessage())
                        ->danger()
                        ->send();
                }
            });
    }
}