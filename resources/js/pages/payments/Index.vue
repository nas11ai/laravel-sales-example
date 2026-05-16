<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import type { ColumnDef } from '@tanstack/vue-table';
import { h, ref, watch } from 'vue';
import { Eye, Pencil, Plus, Trash2 } from 'lucide-vue-next';
import DataTable from '@/components/DataTable.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useRupiah } from '@/composables/useRupiah';
import { useAuthStore } from '@/stores/useAuthStore';
import type { PageProps, PaymentRow } from '@/types';
import paymentRoute from '@/routes/payments';
import { dashboard } from '@/routes';
import { useBreadcrumb } from '@/composables/useBreadcrumbs';

const { setBreadcrumbs } = useBreadcrumb();

setBreadcrumbs([
    { title: 'Dashboard', href: dashboard().url },
    { title: 'Pembayaran' },
]);

const props = defineProps<
    PageProps<{
        payments: {
            data: PaymentRow[];
        };
        filters: {
            start_date?: string;
            end_date?: string;
        };
    }>
>();

const auth = useAuthStore();
const { format } = useRupiah();

const startDate = ref(props.filters.start_date ?? '');
const endDate = ref(props.filters.end_date ?? '');

let timeout: ReturnType<typeof setTimeout>;
watch([startDate, endDate], ([start, end]) => {
    clearTimeout(timeout);
    timeout = setTimeout(() => {
        router.get(
            paymentRoute.index().url,
            { start_date: start, end_date: end },
            { preserveState: true, replace: true },
        );
    }, 400);
});

function deletePayment(id: number, kode: string) {
    if (!confirm(`Yakin ingin menghapus pembayaran ${kode}?`)) return;
    router.delete(paymentRoute.destroy(id).url, { preserveScroll: true });
}

const columns: ColumnDef<PaymentRow>[] = [
    {
        header: 'No',
        cell: ({ row }) => row.index + 1,
        enableSorting: false,
    },
    {
        accessorKey: 'kode',
        header: 'Kode Pembayaran',
    },
    {
        accessorKey: 'tanggal_label',
        header: 'Tanggal',
    },
    {
        accessorKey: 'sale',
        header: 'Penjualan',
        cell: ({ row }) => row.original.sale?.kode ?? '-',
    },
    {
        accessorKey: 'jumlah',
        header: 'Jumlah',
        cell: ({ row }) => format(row.original.jumlah),
    },
    {
        id: 'actions',
        header: 'Aksi',
        enableSorting: false,
        cell: ({ row }) =>
            h('div', { class: 'flex items-center gap-2' }, [
                h(
                    Button,
                    {
                        variant: 'outline',
                        size: 'sm',
                        onClick: () =>
                            router.visit(
                                paymentRoute.show(row.original.id).url,
                            ),
                    },
                    () => [h(Eye, { class: 'size-4' })],
                ),
                auth.can('payments.edit')
                    ? h(
                          Button,
                          {
                              variant: 'outline',
                              size: 'sm',
                              onClick: () =>
                                  router.visit(
                                      paymentRoute.edit(row.original.id).url,
                                  ),
                          },
                          () => [h(Pencil, { class: 'size-4' })],
                      )
                    : null,
                auth.can('payments.delete')
                    ? h(
                          Button,
                          {
                              variant: 'destructive',
                              size: 'sm',
                              onClick: () =>
                                  deletePayment(
                                      row.original.id,
                                      row.original.kode,
                                  ),
                          },
                          () => [h(Trash2, { class: 'size-4' })],
                      )
                    : null,
            ]),
    },
];
</script>

<template>
    <Head title="Pembayaran" />

    <div class="space-y-6 p-6">
        <div>
            <h1 class="text-2xl font-semibold">Pembayaran</h1>
            <p class="text-sm text-muted-foreground">Kelola data pembayaran</p>
        </div>

        <div class="flex flex-wrap items-end gap-4">
            <div class="grid gap-1">
                <Label>Dari Tanggal</Label>
                <Input v-model="startDate" type="date" class="w-44" />
            </div>
            <div class="grid gap-1">
                <Label>Sampai Tanggal</Label>
                <Input v-model="endDate" type="date" class="w-44" />
            </div>
        </div>

        <DataTable
            :columns="columns"
            :data="payments.data"
            search-placeholder="Cari pembayaran..."
        >
            <template #actions>
                <Button
                    v-if="auth.can('payments.create')"
                    @click="router.visit(paymentRoute.create().url)"
                >
                    <Plus class="mr-2 size-4" />
                    Tambah Pembayaran
                </Button>
            </template>
        </DataTable>
    </div>
</template>
