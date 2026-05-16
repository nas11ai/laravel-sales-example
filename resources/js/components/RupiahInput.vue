<script setup lang="ts">
import { computed } from 'vue';
import { Input } from '@/components/ui/input';

const props = defineProps<{
    modelValue: number;
    placeholder?: string;
    disabled?: boolean;
    max?: number;
}>();

const emit = defineEmits<{
    'update:modelValue': [value: number];
}>();

const handleBeforeInput = (e: InputEvent) => {
    if (!e.data) return;

    if (/\D/.test(e.data)) {
        e.preventDefault();
        return;
    }

    if (props.max !== undefined) {
        const input = e.target as HTMLInputElement;
        const raw = input.value.replace(/\D/g, '');

        const start = input.selectionStart ?? raw.length;
        const end = input.selectionEnd ?? raw.length;

        const rawBefore = input.value.slice(0, start).replace(/\D/g, '');
        const rawAfter = input.value.slice(end).replace(/\D/g, '');
        const nextRaw = rawBefore + e.data + rawAfter;
        const nextValue = parseInt(nextRaw, 10);

        if (nextValue > props.max) {
            e.preventDefault();
        }
    }
};

const displayValue = computed({
    get() {
        if (!props.modelValue && props.modelValue !== 0) return '';
        return new Intl.NumberFormat('id-ID').format(props.modelValue);
    },
    set(input: string) {
        const digits = input.replace(/\D/g, '').slice(0, 15);
        let value = digits ? parseInt(digits, 10) : 0;

        if (props.max !== undefined && value > props.max) {
            value = props.max;
        }

        emit('update:modelValue', value);
    },
});
</script>

<template>
    <div class="relative">
        <span
            class="absolute top-1/2 left-3 -translate-y-1/2 text-sm text-muted-foreground"
        >
            Rp
        </span>
        <Input
            v-model="displayValue"
            :placeholder="placeholder ?? '0'"
            :disabled="disabled"
            class="pl-9"
            inputmode="numeric"
            @beforeinput="handleBeforeInput"
        />
    </div>
</template>
