<script setup lang="ts">
import { Trash2 } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { useRupiah } from '@/composables/useRupiah';
import type { AcceptableValue } from 'reka-ui';
import { ItemOption, SaleItemRow } from '@/types';
import { ref, watch } from 'vue';

const props = defineProps<{
    row: SaleItemRow;
    index: number;
    items: ItemOption[];
    canRemove: boolean;
}>();

const emit = defineEmits<{
    'update:item': [index: number, itemId: number, harga: number, nama: string];
    'update:qty': [index: number, qty: number];
    remove: [index: number];
}>();

const localQty = ref(String(props.row.qty));
const isFocused = ref(false);
const qtyError = ref('');

const { format } = useRupiah();

function onItemChange(val: AcceptableValue) {
    if (typeof val !== 'string') return;

    const item = props.items.find((i) => i.id === parseInt(val));

    if (!item) return;

    emit('update:item', props.index, item.id, item.harga, item.nama);
}

// sync dari parent hanya kalau tidak fokus
watch(
    () => props.row.qty,
    (val) => {
        if (!isFocused.value) {
            localQty.value = String(val);
        }
    },
);

watch(localQty, (val) => {
    if (!isFocused.value) return;

    const num = parseInt(val);

    if (val === '' || isNaN(num)) {
        qtyError.value = '';
        return;
    }

    if (num < 1) {
        qtyError.value = 'Minimal 1';
        return;
    }

    qtyError.value = '';
    emit('update:qty', props.index, num);
});

function onQtyFocus() {
    isFocused.value = true;
}

function onQtyBlur() {
    isFocused.value = false;
    const val = parseInt(localQty.value);
    const final = isNaN(val) || val < 1 ? 1 : val > 100 ? 100 : val;
    localQty.value = String(final);
    qtyError.value = '';
    emit('update:qty', props.index, final);
}

function onQtyKeydown(e: KeyboardEvent) {
    const input = e.target as HTMLInputElement;
    const current = input.value;

    // block hapus kalau nilai sudah 1 atau akan jadi kosong
    if (['Backspace', 'Delete'].includes(e.key)) {
        if (current.length <= 1) {
            e.preventDefault();
            qtyError.value = 'Minimal 1';
        }
        return;
    }

    // allow arrow keys, tab
    if (['ArrowLeft', 'ArrowRight', 'Tab'].includes(e.key)) return;

    // allow only digits
    if (!/^\d$/.test(e.key)) {
        e.preventDefault();
        return;
    }

    // block kalau hasil next > 100
    const next = parseInt(current + e.key);
    if (next > 100) {
        e.preventDefault();
        qtyError.value = 'Maksimal 100';
    }
}
</script>

<template>
    <div class="grid grid-cols-12 items-center gap-3">
        <!-- Item select -->
        <div class="col-span-5">
            <Select
                :model-value="row.item_id ? String(row.item_id) : ''"
                @update:model-value="onItemChange"
            >
                <SelectTrigger>
                    <SelectValue placeholder="Pilih item" />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem
                        v-for="item in items"
                        :key="item.id"
                        :value="String(item.id)"
                    >
                        {{ item.nama }} ({{ item.kode }})
                    </SelectItem>
                </SelectContent>
            </Select>
        </div>

        <!-- Qty -->
        <div class="col-span-2">
            <Input
                type="number"
                min="1"
                max="100"
                v-model="localQty"
                @focus="onQtyFocus"
                @blur="onQtyBlur"
                @keydown="onQtyKeydown"
                :class="qtyError ? 'border-destructive' : ''"
                placeholder="Qty"
            />
            <p v-if="qtyError" class="mt-1 text-xs text-destructive">
                {{ qtyError }}
            </p>
        </div>

        <!-- Harga -->
        <div class="col-span-2 text-right text-sm text-muted-foreground">
            {{ row.price_snapshot ? format(row.price_snapshot) : '-' }}
        </div>

        <!-- Total -->
        <div class="col-span-2 text-right text-sm font-medium">
            {{ row.total_price ? format(row.total_price) : '-' }}
        </div>

        <!-- Remove -->
        <div class="col-span-1 flex justify-end">
            <Button
                type="button"
                variant="ghost"
                size="sm"
                :disabled="!canRemove"
                @click="emit('remove', index)"
            >
                <Trash2 class="size-4 text-destructive" />
            </Button>
        </div>
    </div>
</template>
