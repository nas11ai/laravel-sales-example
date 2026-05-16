<script setup lang="ts">
import { Head, useForm, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';
import { Separator } from '@/components/ui/separator';
import roles from '@/routes/master/roles';
import { RoleData } from '@/types';

defineOptions({
    inheritAttrs: false,
});

const props = defineProps<{
    role: RoleData;
    groupedPermissions: Record<string, string[]>;
}>();

const form = useForm({
    permissions: Array.from(props.role.permissions ?? []) as string[],
});

function togglePermission(permission: string) {
    const idx = form.permissions.indexOf(permission);

    if (idx === -1) {
        form.permissions.push(permission);
    } else {
        form.permissions.splice(idx, 1);
    }
}

function toggleGroup(permissions: string[]) {
    const allChecked = permissions.every((p) => form.permissions.includes(p));

    if (allChecked) {
        form.permissions = form.permissions.filter(
            (p) => !permissions.includes(p),
        );
    } else {
        permissions.forEach((p) => {
            if (!form.permissions.includes(p)) {
                form.permissions.push(p);
            }
        });
    }
}

function isGroupChecked(permissions: string[]) {
    return permissions.every((p) => form.permissions.includes(p));
}

function isGroupIndeterminate(permissions: string[]) {
    const checked = permissions.filter((p) => form.permissions.includes(p));

    return checked.length > 0 && checked.length < permissions.length;
}

function submit() {
    form.submit(roles.update(props.role).method, roles.update(props.role).url);
}

const permissionLabel: Record<string, string> = {
    dashboard: 'Dashboard',
    items: 'Item',
    users: 'User',
    roles: 'Role & Permission',
    sales: 'Penjualan',
    payments: 'Pembayaran',
};

const actionLabel: Record<string, string> = {
    view: 'Lihat',
    create: 'Tambah',
    edit: 'Edit',
    delete: 'Hapus',
};
</script>

<template>
    <Head :title="`Edit Permission — ${props.role.name}`" />

    <div class="max-w-2xl space-y-6 p-6">
        <div class="flex items-center gap-3">
            <div>
                <h1 class="text-2xl font-semibold">Edit Permission</h1>

                <p class="text-sm text-muted-foreground">
                    Role:

                    <Badge variant="secondary" class="ml-1 capitalize">
                        {{ props.role.name }}
                    </Badge>
                </p>
            </div>
        </div>

        <form class="space-y-6" @submit.prevent="submit">
            <div
                v-for="(permissions, group) in props.groupedPermissions"
                :key="group"
                class="space-y-3 rounded-lg border p-4"
            >
                <div class="flex items-center gap-3">
                    <Checkbox
                        :id="`group-${group}`"
                        :model-value="isGroupChecked(permissions)"
                        :indeterminate="isGroupIndeterminate(permissions)"
                        @update:model-value="toggleGroup(permissions)"
                    />

                    <Label
                        :for="`group-${group}`"
                        class="cursor-pointer font-semibold"
                    >
                        {{ permissionLabel[group] ?? group }}
                    </Label>
                </div>

                <Separator />

                <div class="grid grid-cols-2 gap-2 pl-6 sm:grid-cols-4">
                    <div
                        v-for="permission in permissions"
                        :key="permission"
                        class="flex items-center gap-2"
                    >
                        <Checkbox
                            :id="permission"
                            :model-value="form.permissions.includes(permission)"
                            @update:model-value="togglePermission(permission)"
                        />

                        <Label :for="permission" class="cursor-pointer text-sm">
                            {{
                                actionLabel[permission.split('.')[1]] ??
                                permission.split('.')[1]
                            }}
                        </Label>
                    </div>
                </div>
            </div>

            <div class="flex gap-3">
                <Button type="submit" :disabled="form.processing">
                    {{ form.processing ? 'Menyimpan...' : 'Simpan Permission' }}
                </Button>

                <Button
                    type="button"
                    variant="outline"
                    @click="router.visit(roles.index().url)"
                >
                    Batal
                </Button>
            </div>
        </form>
    </div>
</template>
