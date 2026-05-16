<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Pencil, Trash2 } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { useRupiah } from '@/composables/useRupiah';
import { useAuthStore } from '@/stores/useAuthStore';
import type { PageProps, PaymentDetail } from '@/types';
import payments from '@/routes/payments';
import sales from '@/routes/sales';

const props = defineProps<
    PageProps<{
        payment: {
            data: PaymentDetail;
        };
    }>
>();
const auth = useAuthStore();
const { format } = useRupiah();

function deletePayment() {
    if (
        !confirm(`Yakin ingin menghapus pembayaran ${props.payment.data.kode}?`)
    )
        return;
    router.delete(payments.destroy(props.payment.data.id).url, {
        onSuccess: () => router.visit('/payments'),
    });
}
</script>

<template>
    <Head :title="`Detail ${payment.data.kode}`" />

    <div class="max-w-xl space-y-6 p-6">
        <div class="flex items-start justify-between">
            <div>
                <h1 class="text-2xl font-semibold">{{ payment.data.kode }}</h1>
                <p class="text-sm text-muted-foreground">
                    {{ payment.data.tanggal_label }}
                </p>
            </div>
            <div class="flex gap-2">
                <Button
                    v-if="auth.can('payments.edit')"
                    variant="outline"
                    size="sm"
                    @click="router.visit(payments.edit(payment.data.id).url)"
                >
                    <Pencil class="mr-2 size-4" />
                    Edit
                </Button>
                <Button
                    v-if="auth.can('payments.delete')"
                    variant="destructive"
                    size="sm"
                    @click="deletePayment"
                >
                    <Trash2 class="mr-2 size-4" />
                    Hapus
                </Button>
            </div>
        </div>

        <Separator />

        <div class="space-y-4 text-sm">
            <div class="flex justify-between">
                <span class="text-muted-foreground">Kode Pembayaran</span>
                <span class="font-medium">{{ payment.data.kode }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-muted-foreground">Tanggal</span>
                <span>{{ payment.data.tanggal_label }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-muted-foreground">Jumlah</span>
                <span class="text-base font-semibold">{{
                    format(payment.data.jumlah)
                }}</span>
            </div>
        </div>

        <Separator />

        <!-- Info penjualan terkait -->
        <div class="space-y-3 rounded-lg border p-4 text-sm">
            <p class="font-semibold">Penjualan Terkait</p>
            <div class="flex justify-between">
                <span class="text-muted-foreground">Kode Penjualan</span>
                <Button
                    variant="link"
                    class="h-auto cursor-pointer p-0 text-sm"
                    @click="router.visit(sales.show(payment.data.sale.id).url)"
                >
                    {{ payment.data.sale.kode }}
                </Button>
            </div>
            <div class="flex justify-between">
                <span class="text-muted-foreground">Total Penjualan</span>
                <span>{{ format(payment.data.sale.total_amount) }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-muted-foreground">Status</span>
                <span>{{ payment.data.sale.status_label }}</span>
            </div>
        </div>

        <Separator />

        <Button variant="outline" @click="router.visit(payments.index().url)">
            Kembali ke Daftar
        </Button>
    </div>
</template>
