import { useEffect, useRef } from 'react';
import { Html5Qrcode } from 'html5-qrcode';

interface CameraScannerProps {
    onScan: (code: string) => void;
    active: boolean;
}

export default function CameraScanner({ onScan, active }: CameraScannerProps) {
    const scannerRef = useRef<Html5Qrcode | null>(null);
    const containerRef = useRef<HTMLDivElement>(null);
    const lastScanRef = useRef('');

    useEffect(() => {
        if (!active || !containerRef.current) {
            return;
        }

        const scanner = new Html5Qrcode(containerRef.current.id);
        scannerRef.current = scanner;

        scanner
            .start(
                { facingMode: 'environment' },
                { fps: 10, qrbox: { width: 250, height: 250 } },
                (decodedText) => {
                    if (decodedText !== lastScanRef.current) {
                        lastScanRef.current = decodedText;
                        onScan(decodedText);
                        setTimeout(() => {
                            lastScanRef.current = '';
                        }, 3000);
                    }
                },
                () => {},
            )
            .catch(() => {});

        return () => {
            scanner
                .stop()
                .then(() => scanner.clear())
                .catch(() => {});
        };
    }, [active, onScan]);

    if (!active) {
        return null;
    }

    return (
        <div
            id="qr-scanner-container"
            ref={containerRef}
            className="mx-auto w-full max-w-sm overflow-hidden rounded-lg"
        />
    );
}
