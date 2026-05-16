<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import type { ColumnDef } from '@tanstack/vue-table';
import { h } from 'vue';
import { Pencil, Plus, ShieldCheck, Trash2 } from 'lucide-vue-next';
import DataTable from '@/components/DataTable.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import type { PageProps, RoleData, UserRow } from '@/types';
import userRoutes from '@/routes/master/users';
import { useAuthStore } from '@/stores/useAuthStore';
import { ref } from 'vue';
import RoleComponentSheet from '@/components/RoleComponentSheet.vue';

const props = defineProps<
    PageProps<{
        users: {
            data: UserRow[];
        };
        roles: Record<string, RoleData>;
        groupedPermissions: Record<string, string[]>;
    }>
>();

const auth = useAuthStore();

const sheetOpen = ref(false);
const selectedRole = ref<RoleData | null>(null);

function openRoleSheet(roleName: string | null) {
    if (!roleName || !props.roles[roleName]) return;
    selectedRole.value = props.roles[roleName];
    sheetOpen.value = true;
}

function deleteUser(id: number) {
    if (!confirm('Yakin ingin menghapus user ini?')) return;
    router.delete(userRoutes.destroy(id).url, {
        preserveScroll: true,
    });
}

const columns: ColumnDef<UserRow>[] = [
    {
        header: 'No',
        cell: ({ row }) => row.index + 1,
        enableSorting: false,
    },
    {
        accessorKey: 'name',
        header: 'Nama',
    },
    {
        accessorKey: 'email',
        header: 'Email',
    },
    {
        accessorKey: 'role',
        header: 'Role',
        cell: ({ row }) =>
            h('div', { class: 'flex items-center gap-2' }, [
                h(
                    Badge,
                    { variant: 'secondary' },
                    () => row.original.role ?? '-',
                ),
                auth.can('roles.edit') && row.original.role
                    ? h(
                          Button,
                          {
                              variant: 'ghost',
                              size: 'sm',
                              class: 'h-6 w-6 p-0 text-muted-foreground hover:text-foreground',
                              title: 'Kelola permission role ini',
                              onClick: () => openRoleSheet(row.original.role),
                          },
                          () => h(ShieldCheck, { class: 'size-3.5' }),
                      )
                    : null,
            ]),
    },
    {
        accessorKey: 'created_at',
        header: 'Dibuat',
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
                            router.visit(userRoutes.edit(row.original.id).url),
                    },
                    () => [h(Pencil, { class: 'size-4' })],
                ),
                h(
                    Button,
                    {
                        variant: 'destructive',
                        size: 'sm',
                        onClick: () => deleteUser(row.original.id),
                    },
                    () => [h(Trash2, { class: 'size-4' })],
                ),
            ]),
    },
];
</script>

<template>
    <Head title="Master User" />

    <div class="space-y-6 p-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold">Master User</h1>
                <p class="text-sm text-muted-foreground">
                    Kelola data pengguna aplikasi
                </p>
            </div>
        </div>

        <DataTable
            :columns="columns"
            :data="users.data"
            search-placeholder="Cari user..."
        >
            <template #actions>
                <Button @click="router.visit(userRoutes.create().url)">
                    <Plus class="mr-2 size-4" />
                    Tambah User
                </Button>
            </template>
        </DataTable>
    </div>

    <RoleComponentSheet
        v-model:open="sheetOpen"
        :role="selectedRole"
        :grouped-permissions="groupedPermissions"
    />
</template>
