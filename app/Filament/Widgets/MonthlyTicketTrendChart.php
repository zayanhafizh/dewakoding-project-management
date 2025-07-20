<?php

namespace App\Filament\Widgets;

use App\Models\Ticket;
use Filament\Widgets\ChartWidget;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MonthlyTicketTrendChart extends ChartWidget
{
    use HasWidgetShield;
    
    protected static ?string $heading = 'Monthly Ticket Creation Trend';
    
    protected static ?int $sort = 4;
    
    protected int | string | array $columnSpan = [
        'md' => 2,
        'xl' => 2,
    ];
    
    protected static ?string $maxHeight = '300px';
    
    protected static ?string $pollingInterval = '60s';
    
    protected function getData(): array
    {
        $user = auth()->user();
        $isSuperAdmin = $user->hasRole('super_admin');
        
        // Get the earliest ticket date
        $earliestTicketQuery = Ticket::query();
        
        if (!$isSuperAdmin) {
            $earliestTicketQuery->whereHas('project.members', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            });
        }
        
        $earliestTicket = $earliestTicketQuery->orderBy('created_at', 'asc')->first();
        
        if (!$earliestTicket) {
            return [
                'datasets' => [],
                'labels' => [],
            ];
        }
        
        // Calculate months from earliest ticket to now
        $startDate = Carbon::parse($earliestTicket->created_at)->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();
        
        $months = collect();
        $currentDate = $startDate->copy();
        
        while ($currentDate->lte($endDate)) {
            $months->push($currentDate->copy());
            $currentDate->addMonth();
        }
        
        $labels = $months->map(function ($month) {
            return $month->format('M Y');
        })->toArray();
        
        // Query tickets created per month
        $ticketsQuery = Ticket::query()
            ->select(
                DB::raw('YEAR(created_at) as year'),
                DB::raw('MONTH(created_at) as month'),
                DB::raw('COUNT(*) as total')
            )
            ->where('created_at', '>=', $startDate)
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month');
            
        if (!$isSuperAdmin) {
            $ticketsQuery->whereHas('project.members', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            });
        }
        
        $ticketData = $ticketsQuery->get()->keyBy(function ($item) {
            return $item->year . '-' . str_pad($item->month, 2, '0', STR_PAD_LEFT);
        });
        
        // Fill data for each month
        $data = $months->map(function ($month) use ($ticketData) {
            $key = $month->format('Y-m');
            return $ticketData->get($key)->total ?? 0;
        })->toArray();
        
        return [
            'datasets' => [
                [
                    'label' => 'Tickets Created',
                    'data' => $data,
                    'borderColor' => '#3B82F6',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'borderWidth' => 2,
                    'fill' => true,
                    'tension' => 0.4,
                ],
            ],
            'labels' => $labels,
        ];
    }
    
    protected function getType(): string
    {
        return 'line';
    }
    
    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'top',
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'stepSize' => 1,
                    ],
                ],
            ],
            'responsive' => true,
            'maintainAspectRatio' => false,
        ];
    }
}