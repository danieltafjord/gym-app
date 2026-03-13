import { useCallback, useEffect, useState } from 'react';
import {
    Area,
    AreaChart,
    Line,
    ComposedChart,
    ReferenceLine,
    ResponsiveContainer,
    XAxis,
    YAxis,
} from 'recharts';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Skeleton } from '@/components/ui/skeleton';
import { cn } from '@/lib/utils';

interface OccupancyData {
    date: string;
    is_today: boolean;
    hours: Record<string, number>;
    current_hour: number | null;
    current_count: number | null;
    max_capacity: number;
    predictions: Record<string, number> | null;
}

interface ChartDataPoint {
    hour: number;
    label: string;
    count: number;
    ratio: number;
    predicted: number | null;
}

type OccupancyLevel = 'empty' | 'moderate' | 'busy' | 'full';

const DAY_LABELS = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

function getOccupancyLevel(ratio: number): OccupancyLevel {
    if (ratio <= 0.2) {
        return 'empty';
    }
    if (ratio <= 0.5) {
        return 'moderate';
    }
    if (ratio <= 0.8) {
        return 'busy';
    }
    return 'full';
}

function getOccupancyDisplay(level: OccupancyLevel): {
    label: string;
    className: string;
} {
    switch (level) {
        case 'empty':
            return {
                label: 'Empty',
                className:
                    'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800',
            };
        case 'moderate':
            return {
                label: 'Moderate',
                className:
                    'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300 border-amber-200 dark:border-amber-800',
            };
        case 'busy':
            return {
                label: 'Busy',
                className:
                    'bg-orange-100 text-orange-800 dark:bg-orange-900/40 dark:text-orange-300 border-orange-200 dark:border-orange-800',
            };
        case 'full':
            return {
                label: 'Full',
                className:
                    'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300 border-red-200 dark:border-red-800',
            };
    }
}

function formatHourLabel(hour: number): string {
    if (hour === 0 || hour === 24) {
        return '12am';
    }
    if (hour === 12) {
        return '12pm';
    }
    return hour < 12 ? `${hour}am` : `${hour - 12}pm`;
}

function buildWeekDays(): { date: string; dayLabel: string; isToday: boolean }[] {
    const today = new Date();
    const days: { date: string; dayLabel: string; isToday: boolean }[] = [];

    for (let i = -6; i <= 0; i++) {
        const d = new Date(today);
        d.setDate(today.getDate() + i);

        const yyyy = d.getFullYear();
        const mm = String(d.getMonth() + 1).padStart(2, '0');
        const dd = String(d.getDate()).padStart(2, '0');

        days.push({
            date: `${yyyy}-${mm}-${dd}`,
            dayLabel: DAY_LABELS[d.getDay()],
            isToday: i === 0,
        });
    }

    return days;
}

function transformToChartData(data: OccupancyData): ChartDataPoint[] {
    const maxCapacity = data.max_capacity;
    const predictions = data.predictions;

    return Object.entries(data.hours).map(([hourStr, count]) => {
        const hour = parseInt(hourStr, 10);
        return {
            hour,
            label: formatHourLabel(hour),
            count,
            ratio: maxCapacity > 0 ? count / maxCapacity : 0,
            predicted: predictions ? (predictions[hourStr] ?? null) : null,
        };
    });
}

export default function GymOccupancyTracker({
    occupancyUrl,
}: {
    occupancyUrl: string;
}) {
    const [data, setData] = useState<OccupancyData | null>(null);
    const [loading, setLoading] = useState(true);
    const [selectedDate, setSelectedDate] = useState<string | null>(null);
    const weekDays = buildWeekDays();

    const fetchData = useCallback(
        async (date?: string) => {
            setLoading(true);
            try {
                const url = new URL(occupancyUrl, window.location.origin);
                if (date) {
                    url.searchParams.set('date', date);
                }
                const response = await fetch(url.toString());
                if (response.ok) {
                    const json = await response.json();
                    setData(json);
                }
            } finally {
                setLoading(false);
            }
        },
        [occupancyUrl],
    );

    useEffect(() => {
        fetchData(selectedDate ?? undefined);
    }, [fetchData, selectedDate]);

    const chartData = data ? transformToChartData(data) : [];
    const hasPredictions = data?.predictions !== null;

    // Determine current occupancy level (for today only)
    const currentLevel =
        data?.is_today && data.current_count !== null
            ? getOccupancyLevel(data.current_count / data.max_capacity)
            : null;

    // For past days, get peak level
    const peakRatio =
        data && !data.is_today
            ? Math.max(...chartData.map((d) => d.ratio), 0)
            : null;
    const peakLevel = peakRatio !== null ? getOccupancyLevel(peakRatio) : null;

    const displayLevel = currentLevel ?? peakLevel;
    const displayInfo = displayLevel ? getOccupancyDisplay(displayLevel) : null;

    return (
        <Card>
            <CardHeader className="flex flex-row items-center justify-between gap-4">
                <div className="flex items-center gap-3">
                    <CardTitle className="text-base">Gym Activity</CardTitle>
                    {displayInfo && !loading && (
                        <Badge
                            variant="outline"
                            className={cn('text-xs', displayInfo.className)}
                        >
                            {data?.is_today ? 'Right now: ' : 'Peak: '}
                            {displayInfo.label}
                        </Badge>
                    )}
                </div>
            </CardHeader>

            <CardContent className="space-y-4">
                {/* Day selector */}
                <div className="flex gap-1">
                    {weekDays.map((day) => (
                        <button
                            key={day.date}
                            type="button"
                            onClick={() => setSelectedDate(day.isToday ? null : day.date)}
                            className={cn(
                                'flex-1 rounded-md px-2 py-1.5 text-xs font-medium transition-colors',
                                (day.isToday && selectedDate === null) ||
                                    selectedDate === day.date
                                    ? 'bg-primary text-primary-foreground'
                                    : 'text-muted-foreground hover:bg-accent hover:text-accent-foreground',
                            )}
                        >
                            {day.dayLabel}
                        </button>
                    ))}
                </div>

                {/* Chart */}
                {loading ? (
                    <Skeleton className="h-40 w-full" />
                ) : (
                    <div className="h-40">
                        <ResponsiveContainer width="100%" height="100%">
                            <ComposedChart
                                data={chartData}
                                margin={{
                                    top: 4,
                                    right: 4,
                                    bottom: 0,
                                    left: 4,
                                }}
                            >
                                <defs>
                                    <linearGradient
                                        id="occupancyGradient"
                                        x1="0"
                                        y1="0"
                                        x2="0"
                                        y2="1"
                                    >
                                        <stop
                                            offset="0%"
                                            stopColor="var(--color-chart-1)"
                                            stopOpacity={0.3}
                                        />
                                        <stop
                                            offset="100%"
                                            stopColor="var(--color-chart-1)"
                                            stopOpacity={0.02}
                                        />
                                    </linearGradient>
                                </defs>
                                <XAxis
                                    dataKey="label"
                                    axisLine={false}
                                    tickLine={false}
                                    tick={{
                                        fontSize: 10,
                                        fill: 'var(--color-muted-foreground)',
                                    }}
                                    interval="preserveStartEnd"
                                    tickFormatter={(value, index) => {
                                        // Show every 3 hours
                                        if (index % 3 === 0) {
                                            return value;
                                        }
                                        return '';
                                    }}
                                />
                                <YAxis hide domain={[0, 'auto']} />
                                {data?.is_today && data.current_hour !== null && (
                                    <ReferenceLine
                                        x={formatHourLabel(data.current_hour)}
                                        stroke="var(--color-muted-foreground)"
                                        strokeDasharray="3 3"
                                        strokeOpacity={0.5}
                                    />
                                )}
                                <Area
                                    type="monotone"
                                    dataKey="count"
                                    stroke="var(--color-chart-1)"
                                    strokeWidth={2}
                                    fill="url(#occupancyGradient)"
                                    dot={false}
                                    activeDot={false}
                                />
                                {hasPredictions && (
                                    <Line
                                        type="monotone"
                                        dataKey="predicted"
                                        stroke="var(--color-chart-2)"
                                        strokeWidth={1.5}
                                        strokeDasharray="4 3"
                                        dot={false}
                                        activeDot={false}
                                        connectNulls
                                    />
                                )}
                            </ComposedChart>
                        </ResponsiveContainer>
                    </div>
                )}

                <div className="flex items-center justify-between">
                    <p className="text-xs text-muted-foreground">
                        {data?.is_today
                            ? 'Live activity throughout the day'
                            : 'Activity throughout the day'}
                    </p>
                    {hasPredictions && !loading && (
                        <div className="flex items-center gap-1.5">
                            <span
                                className="inline-block h-px w-3 border-t-[1.5px] border-dashed"
                                style={{ borderColor: 'var(--color-chart-2)' }}
                            />
                            <span className="text-xs text-muted-foreground">
                                Predicted
                            </span>
                        </div>
                    )}
                </div>
            </CardContent>
        </Card>
    );
}
