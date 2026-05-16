<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Pencil, Trash2 } from 'lucide-vue-next';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { useRupiah } from '@/composables/useRupiah';
import { useAuthStore } from '@/stores/useAuthStore';
import type { PageProps, SaleDetail } from '@/types';
import sales from '@/routes/sales';
import { dashboard } from '@/routes';
import { useBreadcrumb } from '@/composables/useBreadcrumbs';

const { setBreadcrumbs } = useBreadcrumb();

setBreadcrumbs([
    { title: 'Dashboard', href: dashboard().url },
    { title: 'Penjualan', href: sales.index().url },
    { title: 'Detail Penjualan' },
]);

const props = defineProps<
    PageProps<{
        sale: {
            data: SaleDetail;
        };
    }>
>();
const auth = useAuthStore();
const { format } = useRupiah();

const variantMap: Record<
    string,
    'default' | 'secondary' | 'destructive' | 'outline'
> = {
    destructive: 'destructive',
    success: 'default',
    warning: 'secondary',
};

function deleteSale() {
    if (!confirm(`Yakin ingin menghapus penjualan ${props.sale.data.kode}?`))
        return;
    router.delete(sales.destroy(props.sale.data.id).url, {
        onSuccess: () => router.visit('/sales'),
    });
}
</script>

<template>
    <Head :title="`Detail ${sale.data.kode}`" />

    <div class="max-w-3xl space-y-6 p-6">
        <!-- Header -->
        <div class="flex items-start justify-between">
            <div>
                <h1 class="text-2xl font-semibold">{{ sale.data.kode }}</h1>
                <p class="text-sm text-muted-foreground">
                    {{ sale.data.tanggal_label }}
                </p>
            </div>
            <div class="flex items-center gap-2">
                <Badge
                    :variant="variantMap[sale.data.status_color] ?? 'secondary'"
                >
                    {{ sale.data.status_label }}
                </Badge>
                <Button
                    v-if="sale.data.is_editable && auth.can('sales.edit')"
                    variant="outline"
                    size="sm"
                    @click="router.visit(`/sales/${sale.data.id}/edit`)"
                >
                    <Pencil class="mr-2 size-4" />
                    Edit
                </Button>
                <Button
                    v-if="sale.data.is_editable && auth.can('sales.delete')"
                    variant="destructive"
                    size="sm"
                    @click="deleteSale"
                >
                    <Trash2 class="mr-2 size-4" />
                    Hapus
                </Button>
            </div>
        </div>

        <Separator />

        <!-- Info -->
        <div class="grid grid-cols-2 gap-4 text-sm">
            <div>
                <p class="text-muted-foreground">Dibuat oleh</p>
                <p class="font-medium">
                    {{ sale.data.created_by?.name ?? '-' }}
                </p>
            </div>
            <div>
                <p class="text-muted-foreground">Status</p>
                <p class="font-medium">{{ sale.data.status_label }}</p>
            </div>
        </div>

        <Separator />

        <!-- Items -->
        <div class="space-y-3">
            <h2 class="font-semibold">Item Penjualan</h2>
            <div
                class="grid grid-cols-12 gap-3 px-1 text-xs font-medium text-muted-foreground"
            >
                <div class="col-span-5">Item</div>
                <div class="col-span-2 text-center">Qty</div>
                <div class="col-span-2 text-right">Harga</div>
                <div class="col-span-3 text-right">Subtotal</div>
            </div>
            <div
                v-for="item in sale.data.items"
                :key="item.id"
                class="grid grid-cols-12 items-center gap-3 px-1 text-sm"
            >
                <div class="col-span-5">
                    <p class="font-medium">{{ item.item?.nama }}</p>
                    <p class="text-xs text-muted-foreground">
                        {{ item.item?.kode }}
                    </p>
                </div>
                <div class="col-span-2 text-center">{{ item.qty }}</div>
                <div class="col-span-2 text-right">
                    {{ format(item.price_snapshot) }}
                </div>
                <div class="col-span-3 text-right font-medium">
                    {{ format(item.total_price) }}
                </div>
            </div>
        </div>

        <Separator />

        <!-- Total -->
        <div class="space-y-2 text-sm">
            <div class="flex justify-between">
                <span class="text-muted-foreground">Total Penjualan</span>
                <span class="text-base font-semibold">{{
                    format(sale.data.total_amount)
                }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-muted-foreground">Total Dibayar</span>
                <span class="font-medium">{{
                    format(sale.data.total_paid)
                }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-muted-foreground">Sisa</span>
                <span
                    class="font-medium"
                    :class="
                        sale.data.total_amount - sale.data.total_paid > 0
                            ? 'text-destructive'
                            : 'text-green-600'
                    "
                >
                    {{ format(sale.data.total_amount - sale.data.total_paid) }}
                </span>
            </div>
        </div>

        <!-- Payments -->
        <template v-if="sale.data.payments.length">
            <Separator />
            <div class="space-y-3">
                <h2 class="font-semibold">Riwayat Pembayaran</h2>
                <div
                    v-for="payment in sale.data.payments"
                    :key="payment.id"
                    class="flex items-center justify-between text-sm"
                >
                    <div>
                        <p class="font-medium">{{ payment.kode }}</p>
                        <p class="text-xs text-muted-foreground">
                            {{ payment.tanggal }}
                        </p>
                    </div>
                    <span class="font-medium">{{
                        format(payment.jumlah)
                    }}</span>
                </div>
            </div>
        </template>

        <Separator />

        <Button variant="outline" @click="router.visit(sales.index().url)">
            Kembali ke Daftar
        </Button>
    </div>
</template>
