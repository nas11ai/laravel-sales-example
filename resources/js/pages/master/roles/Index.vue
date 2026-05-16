<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import type { ColumnDef } from '@tanstack/vue-table';
import { h } from 'vue';
import { Pencil } from 'lucide-vue-next';
import DataTable from '@/components/DataTable.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import type { PageProps, RoleRow } from '@/types';
import rolesRoute from '@/routes/master/roles';

defineProps<
    PageProps<{
        roles: {
            data: RoleRow[];
        };
    }>
>();

const columns: ColumnDef<RoleRow>[] = [
    {
        header: 'No',
        cell: ({ row }) => row.index + 1,
        enableSorting: false,
    },
    {
        accessorKey: 'name',
        header: 'Role',
        cell: ({ row }) =>
            h(
                Badge,
                { variant: 'secondary', class: 'capitalize' },
                () => row.original.name,
            ),
    },
    {
        accessorKey: 'permissions_count',
        header: 'Jumlah Permission',
        cell: ({ row }) => `${row.original.permissions_count} permission`,
    },
    {
        accessorKey: 'permissions',
        header: 'Permissions',
        enableSorting: false,
        cell: ({ row }) =>
            h(
                'div',
                { class: 'flex flex-wrap gap-1' },
                row.original.permissions.map((p) =>
                    h(
                        Badge,
                        { key: p, variant: 'outline', class: 'text-xs' },
                        () => p,
                    ),
                ),
            ),
    },
    {
        id: 'actions',
        header: 'Aksi',
        enableSorting: false,
        cell: ({ row }) =>
            h(
                Button,
                {
                    variant: 'outline',
                    size: 'sm',
                    onClick: () =>
                        router.visit(rolesRoute.edit(row.original.id).url),
                },
                () => [h(Pencil, { class: 'size-4' }), ' Edit Permission'],
            ),
    },
];
</script>

<template>
    <Head title="Role & Permission" />

    <div class="space-y-6 p-6">
        <div>
            <h1 class="text-2xl font-semibold">Role & Permission</h1>
            <p class="text-sm text-muted-foreground">
                Kelola permission untuk setiap role
            </p>
        </div>

        <DataTable
            :columns="columns"
            :data="roles.data"
            search-placeholder="Cari role..."
        />
    </div>
</template>
