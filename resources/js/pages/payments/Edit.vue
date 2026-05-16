<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import RupiahInput from '@/components/RupiahInput.vue';
import { useRupiah } from '@/composables/useRupiah';
import type { PageProps, PaymentEdit } from '@/types';
import payments from '@/routes/payments';
import { computed, watch } from 'vue';
import { dashboard } from '@/routes';
import { useBreadcrumb } from '@/composables/useBreadcrumbs';

const { setBreadcrumbs } = useBreadcrumb();

setBreadcrumbs([
    { title: 'Dashboard', href: dashboard().url },
    { title: 'Pembayaran', href: payments.index().url },
    { title: 'Edit Pembayaran' },
]);

const props = defineProps<
    PageProps<{
        payment: {
            data: PaymentEdit;
        };
    }>
>();
const { format } = useRupiah();

const form = useForm({
    tanggal: props.payment.data.tanggal,
    jumlah: props.payment.data.jumlah,
    _method: 'PUT',
});

function submit() {
    form.post(payments.update(props.payment.data.id).url, {
        forceFormData: true,
    });
}

const maxJumlah = computed(
    () => props.payment.data.jumlah + props.payment.data.sale.sisa,
);

watch(
    () => form.jumlah,
    (val) => {
        if (val > maxJumlah.value) {
            form.jumlah = maxJumlah.value;
        }
    },
);
</script>

<template>
    <Head :title="`Edit ${payment.data.kode}`" />

    <div class="max-w-xl space-y-6 p-6">
        <div>
            <h1 class="text-2xl font-semibold">Edit Pembayaran</h1>
            <p class="text-sm text-muted-foreground">{{ payment.data.kode }}</p>
        </div>

        <div class="space-y-2 rounded-lg border bg-muted/50 p-4 text-sm">
            <p
                class="text-xs font-semibold tracking-wide text-muted-foreground uppercase"
            >
                Penjualan Terkait (tidak dapat diubah)
            </p>
            <div class="flex justify-between">
                <span class="text-muted-foreground">Kode</span>
                <span class="font-medium">{{ payment.data.sale.kode }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-muted-foreground">Total Penjualan</span>
                <span>{{ format(payment.data.sale.total_amount) }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-muted-foreground">Sudah Dibayar</span>
                <span>{{ format(payment.data.sale.total_paid) }}</span>
            </div>
            <div class="flex justify-between font-semibold">
                <span>Sisa Tagihan</span>
                <span class="text-destructive">{{
                    format(payment.data.sale.sisa)
                }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-muted-foreground">Status</span>
                <span>{{ payment.data.sale.status_label }}</span>
            </div>
        </div>

        <form class="space-y-4" @submit.prevent="submit">
            <div class="grid gap-2">
                <Label for="tanggal">Tanggal Pembayaran</Label>
                <Input id="tanggal" v-model="form.tanggal" type="date" />
                <p v-if="form.errors.tanggal" class="text-sm text-destructive">
                    {{ form.errors.tanggal }}
                </p>
            </div>

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
                submit-label="Simpan Perubahan"
                :cancel-href="payments.show(payment.data.id).url"
            />
        </form>
    </div>
</template>
