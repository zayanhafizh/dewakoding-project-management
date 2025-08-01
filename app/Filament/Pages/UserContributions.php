<?php

namespace App\Filament\Pages;

use App\Models\User;
use App\Models\Ticket;
use App\Models\TicketHistory;
use App\Models\TicketComment;
use Filament\Pages\Page;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Illuminate\Support\Facades\Auth;

class UserContributions extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar-square';
    protected static ?string $navigationLabel = 'User Contributions';
    protected static ?string $title = 'User Contributions';
    protected static ?int $navigationSort = 5;
    protected static string $view = 'filament.pages.user-contributions';
    protected static ?string $navigationGroup = 'Analytics';
    protected static ?string $slug = 'user-contributions';

    public function getSubheading(): ?string
    {
        return 'Track daily activity and contributions across the team';
    }
    public Collection $users;
    public ?string $selectedUserId = null;
    public ?User $selectedUser = null;
    public string $viewMode = 'all'; // 'all', 'individual'
    public string $timeRange = '3months'; // '1month', '3months', '6months', '1year'

    public function mount(): void
    {
        $currentUser = Auth::user();
        
        if ($currentUser->hasRole('super_admin')) {
            $this->users = User::orderBy('name')->get();
            $this->viewMode = 'all';
        } else {
            $this->users = collect([$currentUser]);
            $this->selectedUserId = (string) $currentUser->id;
            $this->selectedUser = $currentUser;
            $this->viewMode = 'individual';
        }
    }

    public function selectUser(string $userId): void
    {
        $this->selectedUserId = $userId;
        $this->selectedUser = User::find($userId);
        $this->viewMode = 'individual';
    }

    public function showAllUsers(): void
    {
        $this->viewMode = 'all';
        $this->selectedUserId = null;
        $this->selectedUser = null;
    }

    public function setTimeRange(string $range): void
    {
        $this->timeRange = $range;
    }

    public function getUsersActivityData(): array
    {
        $users = $this->viewMode === 'individual' && $this->selectedUser 
            ? collect([$this->selectedUser])
            : $this->users->take(10); // Limit to 10 users for performance

        $activityData = [];
        
        foreach ($users as $user) {
            $activityData[$user->id] = [
                'user' => $user,
                'activity' => $this->getUserDailyActivity($user->id),
                'stats' => $this->getUserStats($user->id)
            ];
        }

        return $activityData;
    }

    private function getUserDailyActivity(int $userId): array
    {
        $days = $this->getDaysFromTimeRange();
        $startDate = Carbon::now()->subDays($days);
        $endDate = Carbon::now();
        
        $activity = [];
        
        // Initialize all days with 0 activity
        $current = $startDate->copy();
        while ($current <= $endDate) {
            $activity[$current->format('Y-m-d')] = 0;
            $current->addDay();
        }
        
        try {
            // Count ticket creations
            $ticketCreations = Ticket::where('created_by', $userId)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
                ->groupBy('date')
                ->pluck('count', 'date')
                ->toArray();
            
            // Count ticket status changes
            $statusChanges = TicketHistory::where('user_id', $userId)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
                ->groupBy('date')
                ->pluck('count', 'date')
                ->toArray();
            
            // Count comments
            $comments = TicketComment::where('user_id', $userId)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
                ->groupBy('date')
                ->pluck('count', 'date')
                ->toArray();
            
            // Merge all activities
            foreach ($activity as $date => $count) {
                $activity[$date] = 
                    ($ticketCreations[$date] ?? 0) + 
                    ($statusChanges[$date] ?? 0) + 
                    ($comments[$date] ?? 0);
            }
        } catch (\Exception $e) {
            \Log::error('Error getting user activity: ' . $e->getMessage());
        }
        
        return $activity;
    }

    private function getUserStats(int $userId): array
    {
        $days = $this->getDaysFromTimeRange();
        $startDate = Carbon::now()->subDays($days);
        $endDate = Carbon::now();
        
        try {
            return [
                'tickets_created' => Ticket::where('created_by', $userId)
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->count(),
                'status_changes' => TicketHistory::where('user_id', $userId)
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->count(),
                'comments_made' => TicketComment::where('user_id', $userId)
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->count(),
                'active_days' => collect($this->getUserDailyActivity($userId))
                    ->filter(fn($count) => $count > 0)
                    ->count()
            ];
        } catch (\Exception $e) {
            \Log::error('Error getting user stats: ' . $e->getMessage());
            return [
                'tickets_created' => 0,
                'status_changes' => 0,
                'comments_made' => 0,
                'active_days' => 0
            ];
        }
    }

    private function getDaysFromTimeRange(): int
    {
        return match($this->timeRange) {
            '1month' => 30,
            '3months' => 90,
            '6months' => 180,
            '1year' => 365,
            default => 90
        };
    }

    public function getActivityLevel(int $count): string
    {
        if ($count === 0) return 'none';
        if ($count <= 2) return 'low';
        if ($count <= 5) return 'medium';
        if ($count <= 10) return 'high';
        return 'very-high';
    }

    public function getWeeksData(): array
    {
        $days = $this->getDaysFromTimeRange();
        $startDate = Carbon::now()->subDays($days)->startOfWeek();
        $weeks = [];
        
        $weeksCount = ceil($days / 7);
        $current = $startDate->copy();
        
        for ($week = 0; $week < $weeksCount; $week++) {
            $weekData = [];
            for ($day = 0; $day < 7; $day++) {
                $weekData[] = [
                    'date' => $current->format('Y-m-d'),
                    'dayOfWeek' => $current->dayOfWeek
                ];
                $current->addDay();
            }
            $weeks[] = $weekData;
        }
        
        return $weeks;
    }

    public function getTimeRangeLabel(): string
    {
        return match($this->timeRange) {
            '1month' => 'Last Month',
            '3months' => 'Last 3 Months',
            '6months' => 'Last 6 Months',
            '1year' => 'Last Year',
            default => 'Last 3 Months'
        };
    }
}