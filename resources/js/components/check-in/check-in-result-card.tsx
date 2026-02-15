import { CheckCircle2, XCircle } from 'lucide-react';
import { Card, CardContent } from '@/components/ui/card';
import type { CheckInResult } from '@/types';

export default function CheckInResultCard({
    result,
}: {
    result: CheckInResult;
}) {
    return (
        <Card
            className={
                result.success
                    ? 'border-green-200 bg-green-50 dark:border-green-800 dark:bg-green-950'
                    : 'border-red-200 bg-red-50 dark:border-red-800 dark:bg-red-950'
            }
        >
            <CardContent className="flex items-start gap-3 py-3">
                {result.success ? (
                    <CheckCircle2 className="mt-0.5 h-5 w-5 shrink-0 text-green-600 dark:text-green-400" />
                ) : (
                    <XCircle className="mt-0.5 h-5 w-5 shrink-0 text-red-600 dark:text-red-400" />
                )}
                <div className="min-w-0 flex-1">
                    <p
                        className={`text-sm font-medium ${
                            result.success
                                ? 'text-green-800 dark:text-green-200'
                                : 'text-red-800 dark:text-red-200'
                        }`}
                    >
                        {result.message}
                    </p>
                    {result.membership && (
                        <p className="mt-0.5 text-xs text-muted-foreground">
                            {result.membership.customer_name}
                            {result.membership.plan_name &&
                                ` \u2014 ${result.membership.plan_name}`}
                        </p>
                    )}
                    {result.check_in && (
                        <p className="mt-0.5 text-xs text-muted-foreground">
                            {new Date(
                                result.check_in.created_at,
                            ).toLocaleTimeString()}
                            {result.check_in.gym_name &&
                                ` at ${result.check_in.gym_name}`}
                        </p>
                    )}
                </div>
            </CardContent>
        </Card>
    );
}
