<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { computed, watch } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import RupiahInput from '@/components/RupiahInput.vue';
import { useRupiah } from '@/composables/useRupiah';
import type { PageProps, SaleOption } from '@/types';
import payments from '@/routes/payments';
import { dashboard } from '@/routes';
import { useBreadcrumb } from '@/composables/useBreadcrumbs';

const { setBreadcrumbs } = useBreadcrumb();

setBreadcrumbs([
    { title: 'Dashboard', href: dashboard().url },
    { title: 'Pembayaran', href: payments.index().url },
    { title: 'Tambah Pembayaran' },
]);

const props = defineProps<
    PageProps<{
        sales: SaleOption[];
        selectedSaleId: number | null;
    }>
>();

const { format } = useRupiah();

const form = useForm({
    sale_id: props.selectedSaleId ? String(props.selectedSaleId) : '',
    tanggal: new Date().toISOString().split('T')[0],
    jumlah: 0,
});

const selectedSale = computed(() =>
    props.sales.find((s) => s.id === parseInt(form.sale_id)),
);

watch(
    () => form.sale_id,
    () => {
        if (selectedSale.value) {
            form.jumlah = selectedSale.value.sisa;
        }
    },
);

const maxJumlah = computed(() => selectedSale.value?.sisa ?? 0);

watch(
    () => form.jumlah,
    (val) => {
        if (selectedSale.value && val > maxJumlah.value) {
            form.jumlah = maxJumlah.value;
        }
    },
);

function submit() {
    form.post(payments.store().url);
}
</script>

<template>
    <Head title="Tambah Pembayaran" />

    <div class="max-w-xl space-y-6 p-6">
        <div>
            <h1 class="text-2xl font-semibold">Tambah Pembayaran</h1>
            <p class="text-sm text-muted-foreground">Buat pembayaran baru</p>
        </div>

        <form class="space-y-4" @submit.prevent="submit">
            <!-- Pilih penjualan -->
            <div class="grid gap-2">
                <Label>Penjualan</Label>
                <Select v-model="form.sale_id">
                    <SelectTrigger>
                        <SelectValue placeholder="Pilih penjualan" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem
                            v-for="sale in sales"
                            :key="sale.id"
                            :value="String(sale.id)"
                        >
                            {{ sale.kode }} — {{ sale.tanggal }} (sisa
                            {{ format(sale.sisa) }})
                        </SelectItem>
                    </SelectContent>
                </Select>
                <p v-if="form.errors.sale_id" class="text-sm text-destructive">
                    {{ form.errors.sale_id }}
                </p>
            </div>

            <!-- Info sale yang dipilih -->
            <div
                v-if="selectedSale"
                class="space-y-2 rounded-lg border bg-muted/50 p-4 text-sm"
            >
                <div class="flex justify-between">
                    <span class="text-muted-foreground">Total Penjualan</span>
                    <span>{{ format(selectedSale.total_amount) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-muted-foreground">Sudah Dibayar</span>
                    <span>{{ format(selectedSale.total_paid) }}</span>
                </div>
                <div class="flex justify-between font-semibold">
                    <span>Sisa Tagihan</span>
                    <span class="text-destructive">{{
                        format(selectedSale.sisa)
                    }}</span>
                </div>
            </div>

            <!-- Tanggal -->
            <div class="grid gap-2">
                <Label for="tanggal">Tanggal Pembayaran</Label>
                <Input id="tanggal" v-model="form.tanggal" type="date" />
                <p v-if="form.errors.tanggal" class="text-sm text-destructive">
                    {{ form.errors.tanggal }}
                </p>
            </div>

            <!-- Jumlah -->
            <div class="grid gap-2">
                <Label for="jumlah">Jumlah Pembayaran</Label>
                <RupiahInput v-model="form.jumlah" :max="maxJumlah" />
                <p
                    v-if="form.jumlah >= maxJumlah && maxJumlah > 0"
                    class="text-sm text-amber-500"
                >
                    Jumlah maksimal: {{ format(maxJumlah) }}
                </p>
                <p v-if="form.errors.jumlah" class="text-sm text-destructive">
                    {{ form.errors.jumlah }}
                </p>
            </div>

            <FormActions
                :processing="form.processing"
                submit-label="Simpan Pembayaran"
                :cancel-href="payments.index().url"
            />
        </form>
    </div>
</template>
