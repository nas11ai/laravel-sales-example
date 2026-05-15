<script setup lang="ts">
import { computed } from 'vue';
import { Input } from '@/components/ui/input';

const props = defineProps<{
    modelValue: number;
    placeholder?: string;
    disabled?: boolean;
}>();

const emit = defineEmits<{
    'update:modelValue': [value: number];
}>();

const handleBeforeInput = (e: InputEvent) => {
    if (e.data && /\D/.test(e.data)) {
        e.preventDefault();
    }
};

const displayValue = computed({
    get() {
        if (!props.modelValue && props.modelValue !== 0) return '';
        return new Intl.NumberFormat('id-ID').format(props.modelValue);
    },
    set(input: string) {
        const digits = input.replace(/\D/g, '').slice(0, 15);
        emit('update:modelValue', digits ? parseInt(digits, 10) : 0);
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
