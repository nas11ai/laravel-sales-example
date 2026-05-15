import { ref, computed, watch } from 'vue';

export function useRupiahInput(initialValue: number = 0) {
    const rawValue = ref<number>(initialValue);

    const displayValue = computed({
        get() {
            if (!rawValue.value && rawValue.value !== 0) return '';
            return new Intl.NumberFormat('id-ID').format(rawValue.value);
        },
        set(input: string) {
            const digits = input.replace(/\D/g, '');
            rawValue.value = digits ? parseInt(digits, 10) : 0;
        },
    });

    return { rawValue, displayValue };
}
