import { useEffect, useRef } from 'react';

interface BarcodeListenerProps {
    onScan: (code: string) => void;
    minLength?: number;
    maxDelay?: number;
}

export default function BarcodeListener({
    onScan,
    minLength = 24,
    maxDelay = 50,
}: BarcodeListenerProps) {
    const bufferRef = useRef('');
    const lastKeyTimeRef = useRef(0);

    useEffect(() => {
        function handleKeyDown(e: KeyboardEvent) {
            const now = Date.now();

            if (
                e.target instanceof HTMLInputElement ||
                e.target instanceof HTMLTextAreaElement ||
                e.target instanceof HTMLSelectElement
            ) {
                return;
            }

            if (now - lastKeyTimeRef.current > maxDelay) {
                bufferRef.current = '';
            }

            lastKeyTimeRef.current = now;

            if (e.key === 'Enter') {
                if (bufferRef.current.length >= minLength) {
                    onScan(bufferRef.current);
                }
                bufferRef.current = '';
                return;
            }

            if (e.key.length === 1) {
                bufferRef.current += e.key;
            }
        }

        window.addEventListener('keydown', handleKeyDown);

        return () => window.removeEventListener('keydown', handleKeyDown);
    }, [onScan, minLength, maxDelay]);

    return null;
}
