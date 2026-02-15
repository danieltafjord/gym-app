import { Head, router, usePage } from '@inertiajs/react';
import { Camera } from 'lucide-react';
import { useCallback, useEffect, useRef, useState } from 'react';
import BarcodeListener from '@/components/check-in/barcode-listener';
import CameraScanner from '@/components/check-in/camera-scanner';
import CheckInResultCard from '@/components/check-in/check-in-result-card';
import { Button } from '@/components/ui/button';
import type { CheckInResult, CheckInSettings } from '@/types';
import { store } from '@/routes/public/kiosk';

interface Props {
    team: { name: string; slug: string };
    gym: { id: number; name: string; slug: string };
    settings: CheckInSettings;
}

export default function CheckInKioskPage({ team, gym, settings }: Props) {
    const { flash } = usePage().props;
    const [cameraActive, setCameraActive] = useState(
        settings.kiosk_mode === 'camera',
    );
    const [results, setResults] = useState<CheckInResult[]>([]);
    const [processing, setProcessing] = useState(false);
    const isCamera = settings.kiosk_mode === 'camera';

    useEffect(() => {
        if (flash.checkInResult) {
            setResults((prev) => [flash.checkInResult!, ...prev].slice(0, 10));
            setProcessing(false);
        }
    }, [flash.checkInResult]);

    const submitCode = useCallback(
        (
            code: string,
            method: 'qr_scan' | 'barcode_scanner' | 'manual_entry',
        ) => {
            if (processing || !code.trim()) {
                return;
            }
            setProcessing(true);
            router.post(
                store.url({ team: team.slug, gym: gym.slug }),
                {
                    access_code: code.trim().toUpperCase(),
                    method,
                },
                { preserveState: true, preserveScroll: true },
            );
        },
        [team.slug, gym.slug, processing],
    );

    const handleBarcodeScan = useCallback(
        (code: string) => submitCode(code, 'barcode_scanner'),
        [submitCode],
    );

    const handleCameraScan = useCallback(
        (code: string) => submitCode(code, 'qr_scan'),
        [submitCode],
    );

    return (
        <>
            <Head title={`${gym.name} - Check-In`} />

            {!isCamera && <BarcodeListener onScan={handleBarcodeScan} />}

            <div className="flex min-h-screen flex-col bg-background">
                <header className="border-b bg-muted/30 px-6 py-4">
                    <h1 className="text-center text-xl font-semibold">
                        {gym.name}
                    </h1>
                    <p className="text-center text-sm text-muted-foreground">
                        {isCamera
                            ? 'Scan your QR code to check in'
                            : 'Scan your barcode to check in'}
                    </p>
                </header>

                <main className="mx-auto flex w-full max-w-4xl flex-1 flex-col gap-6 p-6 lg:flex-row">
                    <div className="flex flex-1 flex-col items-center gap-4">
                        {isCamera ? (
                            <>
                                {!cameraActive && (
                                    <Button
                                        size="lg"
                                        onClick={() =>
                                            setCameraActive(true)
                                        }
                                    >
                                        <Camera className="mr-2 h-5 w-5" />
                                        Start Camera
                                    </Button>
                                )}
                                <CameraScanner
                                    active={cameraActive}
                                    onScan={handleCameraScan}
                                />
                            </>
                        ) : (
                            <div className="flex flex-1 items-center justify-center">
                                <p className="text-lg text-muted-foreground">
                                    {processing
                                        ? 'Processing...'
                                        : 'Ready to scan'}
                                </p>
                            </div>
                        )}
                    </div>

                    <div className="flex-1 space-y-3">
                        <h3 className="text-sm font-medium">
                            Recent Check-Ins
                        </h3>
                        {results.length === 0 ? (
                            <p className="text-sm text-muted-foreground">
                                No check-ins yet.
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
                </main>
            </div>
        </>
    );
}
