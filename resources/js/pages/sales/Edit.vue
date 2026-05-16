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
import type { ItemOption, PageProps, SaleEdit } from '@/types';
import sales from '@/routes/sales';
import { dashboard } from '@/routes';
import { useBreadcrumb } from '@/composables/useBreadcrumbs';

const { setBreadcrumbs } = useBreadcrumb();

setBreadcrumbs([
    { title: 'Dashboard', href: dashboard().url },
    { title: 'Penjualan', href: sales.index().url },
    { title: 'Edit Penjualan' },
]);

const props = defineProps<
    PageProps<{
        sale: {
            data: SaleEdit;
        };
        items: ItemOption[];
    }>
>();

const store = useSaleFormStore();
const { format } = useRupiah();

const form = useForm({
    tanggal: props.sale.data.tanggal,
    items: [] as { item_id: number; qty: number }[],
    _method: 'PUT',
});

onMounted(() => {
    store.setRows(
        props.sale.data.items.map((i) => ({
            item_id: i.item_id,
            nama: i.item.nama,
            qty: i.qty,
            price_snapshot: i.price_snapshot,
            total_price: i.total_price,
        })),
    );
});

onUnmounted(() => store.reset());

function submit() {
    const hasEmpty = store.rows.some((r) => !r.item_id);
    if (hasEmpty) return;

    form.items = store.rows.map((r) => ({
        item_id: r.item_id!,
        qty: r.qty,
    }));

    form.post(sales.update(props.sale.data.id).url, {
        forceFormData: true,
    });
}
</script>

<template>
    <Head :title="`Edit ${sale.data.kode}`" />

    <div class="max-w-3xl space-y-6 p-6">
        <div>
            <h1 class="text-2xl font-semibold">Edit Penjualan</h1>
            <p class="text-sm text-muted-foreground">{{ sale.data.kode }}</p>
        </div>

        <form class="space-y-6" @submit.prevent="submit">
            <div class="grid max-w-xs gap-2">
                <Label for="tanggal">Tanggal</Label>
                <Input id="tanggal" v-model="form.tanggal" type="date" />
                <p v-if="form.errors.tanggal" class="text-sm text-destructive">
                    {{ form.errors.tanggal }}
                </p>
            </div>

            <Separator />

            <div
                class="grid grid-cols-12 gap-3 px-1 text-xs font-medium text-muted-foreground"
            >
                <div class="col-span-5">Item</div>
                <div class="col-span-2">Qty</div>
                <div class="col-span-2 text-right">Harga Satuan</div>
                <div class="col-span-2 text-right">Subtotal</div>
                <div class="col-span-1" />
            </div>

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
                submit-label="Simpan Perubahan"
                :cancel-href="sales.show(props.sale.data.id).url"
            />
        </form>
    </div>
</template>
