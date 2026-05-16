<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { onMounted, onUnmounted } from 'vue';
import { Plus } from 'lucide-vue-next';
import SaleItemRow from '@/components/SaleItemRow.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
import { useRupiah } from '@/composables/useRupiah';
import { useSaleFormStore } from '@/stores/useSaleFormStore';
import type { ItemOption, PageProps } from '@/types';
import sales from '@/routes/sales';
import { dashboard } from '@/routes';
import { useBreadcrumb } from '@/composables/useBreadcrumbs';
import FormActions from '@/components/FormActions.vue';

const { setBreadcrumbs } = useBreadcrumb();

setBreadcrumbs([
    { title: 'Dashboard', href: dashboard().url },
    { title: 'Penjualan', href: sales.index().url },
    { title: 'Tambah Penjualan' },
]);

const props = defineProps<PageProps<{ items: ItemOption[] }>>();

const store = useSaleFormStore();
const { format } = useRupiah();

const form = useForm({
    tanggal: new Date().toISOString().split('T')[0],
    items: [] as { item_id: number; qty: number }[],
});

onMounted(() => store.reset());
onUnmounted(() => store.reset());

function submit() {
    const hasEmpty = store.rows.some((r) => !r.item_id);
    if (hasEmpty) return;

    form.items = store.rows.map((r) => ({
        item_id: r.item_id!,
        qty: r.qty,
    }));

    form.post(sales.store().url);
}
</script>

<template>
    <Head title="Tambah Penjualan" />

    <div class="max-w-3xl space-y-6 p-6">
        <div>
            <h1 class="text-2xl font-semibold">Tambah Penjualan</h1>
            <p class="text-sm text-muted-foreground">Buat penjualan baru</p>
        </div>

        <form class="space-y-6" @submit.prevent="submit">
            <!-- Tanggal -->
            <div class="grid max-w-xs gap-2">
                <Label for="tanggal">Tanggal</Label>
                <Input id="tanggal" v-model="form.tanggal" type="date" />
                <p v-if="form.errors.tanggal" class="text-sm text-destructive">
                    {{ form.errors.tanggal }}
                </p>
            </div>

            <Separator />

            <!-- Header kolom -->
            <div
                class="grid grid-cols-12 gap-3 px-1 text-xs font-medium text-muted-foreground"
            >
                <div class="col-span-5">Item</div>
                <div class="col-span-2">Qty</div>
                <div class="col-span-2 text-right">Harga Satuan</div>
                <div class="col-span-2 text-right">Subtotal</div>
                <div class="col-span-1" />
            </div>

            <!-- Item rows -->
            <div class="space-y-3">
                <SaleItemRow
                    v-for="(row, index) in store.rows"
                    :key="index"
                    :row="row"
                    :index="index"
                    :items="items"
                    :can-remove="store.rows.length > 1"
                    @update:item="store.updateItem"
                    @update:qty="store.updateQty"
                    @remove="store.removeRow"
                />
            </div>

            <p v-if="form.errors.items" class="text-sm text-destructive">
                {{ form.errors.items }}
            </p>

            <Button
                type="button"
                variant="outline"
                size="sm"
                @click="store.addRow"
            >
                <Plus class="mr-2 size-4" />
                Tambah Item
            </Button>

            <Separator />

            <!-- Total -->
            <div class="flex justify-end">
                <div class="space-y-1 text-right">
                    <p class="text-sm text-muted-foreground">Total Penjualan</p>
                    <p class="text-2xl font-semibold">
                        {{ format(store.totalAmount) }}
                    </p>
                </div>
            </div>

            <FormActions
                :processing="form.processing"
                submit-label="Simpan Penjualan"
                :cancel-href="sales.index().url"
            />
        </form>
    </div>
</template>
