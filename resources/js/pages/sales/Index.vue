<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import type { ColumnDef } from '@tanstack/vue-table';
import { h, ref, watch } from 'vue';
import { Eye, Pencil, Plus, Trash2 } from 'lucide-vue-next';
import DataTable from '@/components/DataTable.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useRupiah } from '@/composables/useRupiah';
import { useAuthStore } from '@/stores/useAuthStore';
import type { PageProps, SaleRow } from '@/types';
import saleRoutes from '@/routes/sales';
import { dashboard } from '@/routes';
import { useBreadcrumb } from '@/composables/useBreadcrumbs';

const props = defineProps<
    PageProps<{
        sales: { data: SaleRow[] };
        filters: {
            start_date?: string;
            end_date?: string;
        };
    }>
>();

const { setBreadcrumbs } = useBreadcrumb();

setBreadcrumbs([
    { title: 'Dashboard', href: dashboard().url },
    { title: 'Penjualan' },
]);

const auth = useAuthStore();
const { format } = useRupiah();

const startDate = ref(props.filters.start_date ?? '');
const endDate = ref(props.filters.end_date ?? '');

let timeout: ReturnType<typeof setTimeout>;
watch([startDate, endDate], ([start, end]) => {
    clearTimeout(timeout);
    timeout = setTimeout(() => {
        router.get(
            saleRoutes.index().url,
            { start_date: start, end_date: end },
            {
                preserveState: true,
                replace: true,
            },
        );
    }, 400);
});

function deleteSale(id: number, kode: string) {
    if (!confirm(`Yakin ingin menghapus penjualan ${kode}?`)) return;
    router.delete(saleRoutes.destroy(id).url, { preserveScroll: true });
}

const variantMap: Record<
    string,
    'default' | 'secondary' | 'destructive' | 'outline'
> = {
    destructive: 'destructive',
    success: 'default',
    warning: 'secondary',
};

const columns: ColumnDef<SaleRow>[] = [
    {
        header: 'No',
        cell: ({ row }) => row.index + 1,
        enableSorting: false,
    },
    {
        accessorKey: 'kode',
        header: 'Kode',
    },
    {
        accessorKey: 'tanggal_label',
        header: 'Tanggal',
    },
    {
        accessorKey: 'total_amount',
        header: 'Total',
        cell: ({ row }) => format(row.original.total_amount),
    },
    {
        accessorKey: 'status_label',
        header: 'Status',
        cell: ({ row }) =>
            h(
                Badge,
                {
                    variant:
                        variantMap[row.original.status_color] ?? 'secondary',
                },
                () => row.original.status_label,
            ),
    },
    {
        accessorKey: 'created_by',
        header: 'Dibuat Oleh',
        cell: ({ row }) => row.original.created_by?.name ?? '-',
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
                            router.visit(saleRoutes.show(row.original.id).url),
                    },
                    () => [h(Eye, { class: 'size-4' })],
                ),
                row.original.is_editable && auth.can('sales.edit')
                    ? h(
                          Button,
                          {
                              variant: 'outline',
                              size: 'sm',
                              onClick: () =>
                                  router.visit(
                                      saleRoutes.edit(row.original.id).url,
                                  ),
                          },
                          () => [h(Pencil, { class: 'size-4' })],
                      )
                    : null,
                row.original.is_editable && auth.can('sales.delete')
                    ? h(
                          Button,
                          {
                              variant: 'destructive',
                              size: 'sm',
                              onClick: () =>
                                  deleteSale(
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
    <Head title="Penjualan" />

    <div class="space-y-6 p-6">
        <div>
            <h1 class="text-2xl font-semibold">Penjualan</h1>
            <p class="text-sm text-muted-foreground">Kelola data penjualan</p>
        </div>

        <!-- Filter tanggal -->
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
            :data="sales.data"
            search-placeholder="Cari penjualan..."
        >
            <template #actions>
                <Button
                    v-if="auth.can('sales.create')"
                    @click="router.visit(saleRoutes.create().url)"
                >
                    <Plus class="mr-2 size-4" />
                    Tambah Penjualan
                </Button>
            </template>
        </DataTable>
    </div>
</template>
