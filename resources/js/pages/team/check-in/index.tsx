import { Head, Link } from '@inertiajs/react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import Heading from '@/components/heading';
import AppLayout from '@/layouts/app-layout';
import type { BreadcrumbItem, CheckIn, Gym, PaginatedData, Team } from '@/types';
import team from '@/routes/team';
import { index as checkInsIndex } from '@/routes/team/check-ins';

const METHOD_LABELS: Record<string, string> = {
    qr_scan: 'QR Scan',
    barcode_scanner: 'Barcode',
    manual_entry: 'Manual',
};

export default function CheckInHistoryPage({
    team: currentTeam,
    checkIns,
    gyms,
}: {
    team: Team;
    checkIns: PaginatedData<CheckIn>;
    gyms: Gym[];
}) {
    const hasMultipleGyms = gyms.length > 1;

    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: currentTeam.name,
            href: team.show(currentTeam.slug).url,
        },
        {
            title: 'Check-In Log',
            href: checkInsIndex.url(currentTeam.slug),
        },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`${currentTeam.name} - Check-In Log`} />

            <div className="space-y-6 p-4">
                <Heading
                    title="Check-In Log"
                    description="View the history of member check-ins."
                />

                <div className="overflow-hidden rounded-lg border">
                    <table className="w-full text-sm">
                        <thead className="border-b bg-muted/50">
                            <tr>
                                <th className="px-4 py-3 text-left font-medium">
                                    Member
                                </th>
                                <th className="px-4 py-3 text-left font-medium">
                                    Plan
                                </th>
                                {hasMultipleGyms && (
                                    <th className="px-4 py-3 text-left font-medium">
                                        Gym
                                    </th>
                                )}
                                <th className="px-4 py-3 text-left font-medium">
                                    Method
                                </th>
                                <th className="px-4 py-3 text-left font-medium">
                                    Staff
                                </th>
                                <th className="px-4 py-3 text-left font-medium">
                                    Time
                                </th>
                            </tr>
                        </thead>
                        <tbody className="divide-y">
                            {checkIns.data.length === 0 ? (
                                <tr>
                                    <td
                                        colSpan={hasMultipleGyms ? 6 : 5}
                                        className="px-4 py-8 text-center text-muted-foreground"
                                    >
                                        No check-ins found.
                                    </td>
                                </tr>
                            ) : (
                                checkIns.data.map((checkIn) => (
                                    <tr key={checkIn.id}>
                                        <td className="px-4 py-3 font-medium">
                                            {checkIn.membership?.user
                                                ?.name ??
                                                checkIn.membership
                                                    ?.customer_name ??
                                                'Unknown'}
                                        </td>
                                        <td className="px-4 py-3 text-muted-foreground">
                                            {checkIn.membership?.plan
                                                ?.name ?? '-'}
                                        </td>
                                        {hasMultipleGyms && (
                                            <td className="px-4 py-3 text-muted-foreground">
                                                {checkIn.gym?.name ?? '-'}
                                            </td>
                                        )}
                                        <td className="px-4 py-3">
                                            <Badge variant="secondary">
                                                {METHOD_LABELS[
                                                    checkIn.method
                                                ] ?? checkIn.method}
                                            </Badge>
                                        </td>
                                        <td className="px-4 py-3 text-muted-foreground">
                                            {checkIn.checked_in_by_user
                                                ?.name ?? '-'}
                                        </td>
                                        <td className="px-4 py-3 text-muted-foreground">
                                            {new Date(
                                                checkIn.created_at,
                                            ).toLocaleString()}
                                        </td>
                                    </tr>
                                ))
                            )}
                        </tbody>
                    </table>
                </div>

                {checkIns.last_page > 1 && (
                    <div className="flex items-center justify-center gap-2">
                        {checkIns.links.map((link, index) => (
                            <Button
                                key={index}
                                variant={
                                    link.active ? 'default' : 'outline'
                                }
                                size="sm"
                                disabled={!link.url}
                                asChild={!!link.url}
                            >
                                {link.url ? (
                                    <Link
                                        href={link.url}
                                        dangerouslySetInnerHTML={{
                                            __html: link.label,
                                        }}
                                    />
                                ) : (
                                    <span
                                        dangerouslySetInnerHTML={{
                                            __html: link.label,
                                        }}
                                    />
                                )}
                            </Button>
                        ))}
                    </div>
                )}
            </div>
        </AppLayout>
    );
}
