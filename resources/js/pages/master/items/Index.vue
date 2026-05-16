<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import type { ColumnDef } from '@tanstack/vue-table';
import { h } from 'vue';
import { Pencil, Plus, Trash2 } from 'lucide-vue-next';
import DataTable from '@/components/DataTable.vue';
import { Button } from '@/components/ui/button';
import { useRupiah } from '@/composables/useRupiah';
import type { Item, PageProps } from '@/types';

defineProps<
    PageProps<{
        items: {
            data: Item[];
        };
    }>
>();

const { format } = useRupiah();

function deleteItem(id: number) {
    if (!confirm('Yakin ingin menghapus item ini?')) return;
    router.delete(`/master/items/${id}`, {
        preserveScroll: true,
    });
}

const columns: ColumnDef<Item>[] = [
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
        accessorKey: 'nama',
        header: 'Nama Item',
    },
    {
        accessorKey: 'image_url',
        header: 'Gambar',
        enableSorting: false,
        cell: ({ row }) =>
            h(
                'div',
                { class: 'w-12 h-12' },
                row.original.image_url
                    ? h('img', {
                          src: row.original.image_url,
                          alt: row.original.nama,
                          class: 'w-12 h-12 object-cover rounded-md',
                      })
                    : h(
                          'div',
                          {
                              class: 'w-12 h-12 rounded-md bg-muted flex items-center justify-center text-xs text-muted-foreground',
                          },
                          'No img',
                      ),
            ),
    },
    {
        accessorKey: 'harga',
        header: 'Harga',
        cell: ({ row }) => format(row.original.harga),
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
                                `/master/items/${row.original.id}/edit`,
                            ),
                    },
                    () => [h(Pencil, { class: 'size-4' })],
                ),
                h(
                    Button,
                    {
                        variant: 'destructive',
                        size: 'sm',
                        onClick: () => deleteItem(row.original.id),
                    },
                    () => [h(Trash2, { class: 'size-4' })],
                ),
            ]),
    },
];
</script>

<template>
    <Head title="Master Item" />

    <div class="space-y-6 p-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold">Master Item</h1>
                <p class="text-sm text-muted-foreground">
                    Kelola data item produk
                </p>
            </div>
        </div>

        <DataTable
            :columns="columns"
            :data="items.data"
            search-placeholder="Cari item..."
        >
            <template #actions>
                <Button as-child>
                    <Link href="/master/items/create">
                        <Plus class="mr-2 size-4" />
                        Tambah Item
                    </Link>
                </Button>
            </template>
        </DataTable>
    </div>
</template>
