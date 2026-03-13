<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\CheckIn;
use App\Models\Team;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class GymOccupancyController extends Controller
{
    public function show(Request $request, Team $team, string $gymSlug): JsonResponse
    {
        abort_unless($team->is_active, 404);

        $gym = $team->gyms()
            ->where('slug', $gymSlug)
            ->active()
            ->firstOrFail();

        abort_unless($gym->occupancy_tracking_enabled && $gym->max_capacity, 404);

        $request->validate([
            'date' => ['sometimes', 'date_format:Y-m-d'],
        ]);

        $date = $request->input('date')
            ? Carbon::createFromFormat('Y-m-d', $request->input('date'))
            : Carbon::today();

        // Only allow viewing the current week (today +/- 6 days)
        $weekStart = Carbon::today()->subDays(6)->startOfDay();
        $weekEnd = Carbon::today()->endOfDay();

        abort_unless($date->between($weekStart, $weekEnd), 422);

        $driver = DB::getDriverName();
        $hourExpression = $driver === 'pgsql'
            ? 'EXTRACT(HOUR FROM created_at)::integer'
            : "CAST(strftime('%H', created_at) AS INTEGER)";

        $hourlyCounts = CheckIn::query()
            ->where('gym_id', $gym->id)
            ->whereDate('created_at', $date)
            ->selectRaw("{$hourExpression} as hour, COUNT(*) as count")
            ->groupByRaw($hourExpression)
            ->orderByRaw($hourExpression)
            ->pluck('count', 'hour');

        // Build a full 24-hour array
        $hours = collect(range(0, 23))->mapWithKeys(fn (int $hour) => [
            $hour => $hourlyCounts->get($hour, 0),
        ]);

        $isToday = $date->isToday();
        $currentHour = (int) Carbon::now()->format('G');
        $currentCount = $isToday ? $hours->get($currentHour, 0) : null;

        $response = [
            'date' => $date->toDateString(),
            'is_today' => $isToday,
            'hours' => $hours,
            'current_hour' => $isToday ? $currentHour : null,
            'current_count' => $currentCount,
            'max_capacity' => $gym->max_capacity,
            'predictions' => null,
        ];

        if ($gym->show_occupancy_predictions) {
            $response['predictions'] = $this->calculatePredictions($gym->id, $date, $hourExpression);
        }

        return response()->json($response);
    }

    /**
     * Calculate predicted occupancy using weighted historical averages.
     *
     * Looks at check-ins from the same day-of-week over the past 12 weeks,
     * applies exponential decay weighting so recent weeks matter more,
     * and requires at least 4 weeks of data to produce a prediction.
     *
     * @return array<int, float>|null
     */
    private function calculatePredictions(int $gymId, Carbon $date, string $hourExpression): ?array
    {
        $dayOfWeek = $date->dayOfWeek;
        $lookbackWeeks = 12;
        $minimumWeeks = 4;

        // Collect dates for the same day-of-week over the past N weeks
        $historicalDates = [];
        for ($i = 1; $i <= $lookbackWeeks; $i++) {
            $historicalDates[] = $date->copy()->subWeeks($i);
        }

        // Count how many of those weeks actually have check-in data
        $weeksWithData = CheckIn::query()
            ->where('gym_id', $gymId)
            ->where(function ($query) use ($historicalDates) {
                foreach ($historicalDates as $d) {
                    $query->orWhereDate('created_at', $d);
                }
            })
            ->selectRaw('DATE(created_at) as check_date')
            ->groupByRaw('DATE(created_at)')
            ->pluck('check_date');

        if ($weeksWithData->count() < $minimumWeeks) {
            return null;
        }

        // Fetch hourly counts for each historical date
        $historicalData = CheckIn::query()
            ->where('gym_id', $gymId)
            ->where(function ($query) use ($historicalDates) {
                foreach ($historicalDates as $d) {
                    $query->orWhereDate('created_at', $d);
                }
            })
            ->selectRaw("DATE(created_at) as check_date, {$hourExpression} as hour, COUNT(*) as count")
            ->groupByRaw("DATE(created_at), {$hourExpression}")
            ->get();

        // Organize by date -> hour -> count
        $byDate = [];
        foreach ($historicalData as $row) {
            $byDate[$row->check_date][$row->hour] = (int) $row->count;
        }

        // Calculate weighted averages per hour
        // More recent weeks get higher weight using exponential decay (decay factor 0.85)
        $decay = 0.85;
        $predictions = [];

        for ($hour = 0; $hour <= 23; $hour++) {
            $weightedSum = 0;
            $totalWeight = 0;

            foreach ($historicalDates as $index => $d) {
                $dateStr = $d->toDateString();
                if (! isset($byDate[$dateStr])) {
                    continue;
                }

                $count = $byDate[$dateStr][$hour] ?? 0;
                $weight = $decay ** $index; // week 1 ago = 0.85^0 = 1.0, week 2 ago = 0.85^1, etc.
                $weightedSum += $count * $weight;
                $totalWeight += $weight;
            }

            $predictions[$hour] = $totalWeight > 0
                ? round($weightedSum / $totalWeight, 1)
                : 0;
        }

        return $predictions;
    }
}
