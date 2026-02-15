import { Head, router, usePage } from '@inertiajs/react';
import { Camera, Keyboard } from 'lucide-react';
import { useCallback, useEffect, useRef, useState } from 'react';
import BarcodeListener from '@/components/check-in/barcode-listener';
import CameraScanner from '@/components/check-in/camera-scanner';
import CheckInResultCard from '@/components/check-in/check-in-result-card';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import AppLayout from '@/layouts/app-layout';
import type {
    BreadcrumbItem,
    CheckInResult,
    CheckInSettings,
    Gym,
    Team,
} from '@/types';
import { store } from '@/routes/team/check-in';
import team from '@/routes/team';

interface Props {
    team: Team;
    gyms: Gym[];
    settings: CheckInSettings;
}

export default function ScannerPage({
    team: currentTeam,
    gyms,
    settings,
}: Props) {
    const { flash } = usePage().props;
    const [cameraActive, setCameraActive] = useState(false);
    const [manualCode, setManualCode] = useState('');
    const [gymId, setGymId] = useState<string>(
        gyms.length === 1 ? String(gyms[0].id) : '',
    );
    const [results, setResults] = useState<CheckInResult[]>([]);
    const [processing, setProcessing] = useState(false);
    const inputRef = useRef<HTMLInputElement>(null);

    useEffect(() => {
        if (flash.checkInResult) {
            setResults((prev) => [flash.checkInResult!, ...prev].slice(0, 10));
            setProcessing(false);
        }
    }, [flash.checkInResult]);

    const submitCode = useCallback(
        (code: string, method: 'qr_scan' | 'barcode_scanner' | 'manual_entry') => {
            if (processing || !code.trim()) {
                return;
            }
            setProcessing(true);
            router.post(
                store.url(currentTeam.slug),
                {
                    access_code: code.trim().toUpperCase(),
                    gym_id: gymId || null,
                    method,
                },
                { preserveState: true, preserveScroll: true },
            );
        },
        [currentTeam.slug, gymId, processing],
    );

    const handleBarcodeScan = useCallback(
        (code: string) => submitCode(code, 'barcode_scanner'),
        [submitCode],
    );

    const handleCameraScan = useCallback(
        (code: string) => submitCode(code, 'qr_scan'),
        [submitCode],
    );

    function handleManualSubmit(e: React.FormEvent) {
        e.preventDefault();
        submitCode(manualCode, 'manual_entry');
        setManualCode('');
    }

    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: currentTeam.name,
            href: team.show(currentTeam.slug).url,
        },
        {
            title: 'Check-In',
            href: store.url(currentTeam.slug),
        },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`${currentTeam.name} - Check-In`} />

            <BarcodeListener onScan={handleBarcodeScan} />

            <div className="space-y-6 p-4">
                <Heading
                    title="Check-In Scanner"
                    description="Scan a member's QR code or enter their access code."
                />

                <div className="grid gap-6 lg:grid-cols-2">
                    <div className="space-y-4">
                        {gyms.length > 1 && (
                            <div className="grid gap-2">
                                <Label htmlFor="gym">Gym Location</Label>
                                <Select
                                    value={gymId}
                                    onValueChange={setGymId}
                                >
                                    <SelectTrigger id="gym">
                                        <SelectValue placeholder="Select a gym" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {gyms.map((gym) => (
                                            <SelectItem
                                                key={gym.id}
                                                value={String(gym.id)}
                                            >
                                                {gym.name}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>
                        )}

                        <div className="flex gap-2">
                            <Button
                                variant={
                                    cameraActive ? 'default' : 'outline'
                                }
                                onClick={() =>
                                    setCameraActive(!cameraActive)
                                }
                            >
                                <Camera className="mr-2 h-4 w-4" />
                                {cameraActive
                                    ? 'Stop Camera'
                                    : 'Start Camera'}
                            </Button>
                        </div>

                        <CameraScanner
                            active={cameraActive}
                            onScan={handleCameraScan}
                        />

                        <form
                            onSubmit={handleManualSubmit}
                            className="flex gap-2"
                        >
                            <div className="flex-1">
                                <Label htmlFor="manual-code" className="sr-only">
                                    Access Code
                                </Label>
                                <Input
                                    ref={inputRef}
                                    id="manual-code"
                                    placeholder="Enter access code..."
                                    value={manualCode}
                                    onChange={(e) =>
                                        setManualCode(e.target.value)
                                    }
                                    maxLength={24}
                                    autoComplete="off"
                                />
                            </div>
                            <Button
                                type="submit"
                                disabled={
                                    processing || manualCode.length < 24
                                }
                            >
                                <Keyboard className="mr-2 h-4 w-4" />
                                Check In
                            </Button>
                        </form>

                        <p className="text-xs text-muted-foreground">
                            Hardware barcode scanners are automatically
                            detected. Just scan a code while this page is
                            open.
                        </p>
                    </div>

                    <div className="space-y-3">
                        <h3 className="text-sm font-medium">
                            Recent Check-Ins
                        </h3>
                        {results.length === 0 ? (
                            <p className="text-sm text-muted-foreground">
                                No check-ins yet. Scan a QR code to get
                                started.
                            </p>
                        ) : (
                            <div className="space-y-2">
                                {results.map((result, index) => (
                                    <CheckInResultCard
                                        key={index}
                                        result={result}
                                    />
                                ))}
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
