export function useRupiah() {
    const format = (value: number): string => {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0,
        }).format(value);
    };

    const parse = (value: string): number => {
        return parseInt(value.replace(/\D/g, ''), 10) || 0;
    };

    return { format, parse };
}
