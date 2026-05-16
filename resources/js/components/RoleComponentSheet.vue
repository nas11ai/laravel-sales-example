<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
import {
    Sheet,
    SheetContent,
    SheetDescription,
    SheetFooter,
    SheetHeader,
    SheetTitle,
} from '@/components/ui/sheet';
import roleRoutes from '@/routes/master/roles';

interface RoleData {
    id: number;
    name: string;
    permissions: string[];
}

const props = defineProps<{
    open: boolean;
    role: RoleData | null;
    groupedPermissions: Record<string, string[]>;
}>();

const emit = defineEmits<{
    'update:open': [value: boolean];
}>();

const form = useForm({
    permissions: [] as string[],
    _method: 'PUT',
});

// Sync form setiap kali role berubah
watch(
    () => props.role,
    (role) => {
        if (role) form.permissions = [...role.permissions];
    },
    { immediate: true },
);

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
            if (!form.permissions.includes(p)) form.permissions.push(p);
        });
    }
}

function isGroupChecked(permissions: string[]): boolean {
    return permissions.every((p) => form.permissions.includes(p));
}

function isGroupIndeterminate(permissions: string[]): boolean {
    const checked = permissions.filter((p) => form.permissions.includes(p));
    return checked.length > 0 && checked.length < permissions.length;
}

function submit() {
    if (!props.role) return;
    form.post(roleRoutes.update(props.role).url, {
        onSuccess: () => emit('update:open', false),
    });
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
    <Sheet :open="open" @update:open="emit('update:open', $event)">
        <SheetContent class="w-full overflow-y-auto sm:max-w-lg">
            <SheetHeader>
                <SheetTitle class="flex items-center gap-2">
                    Kelola Permission
                    <Badge v-if="role" variant="secondary" class="capitalize">
                        {{ role.name }}
                    </Badge>
                </SheetTitle>
                <SheetDescription>
                    Centang permission yang ingin diberikan ke role ini.
                </SheetDescription>
            </SheetHeader>

            <form class="mt-6 space-y-4" @submit.prevent="submit">
                <div
                    v-for="(permissions, group) in groupedPermissions"
                    :key="group"
                    class="space-y-3 rounded-lg border p-4"
                >
                    <!-- Group header -->
                    <div class="flex items-center gap-3">
                        <Checkbox
                            :id="`sheet-group-${group}`"
                            :model-value="isGroupChecked(permissions)"
                            :indeterminate="isGroupIndeterminate(permissions)"
                            @update:model-value="toggleGroup(permissions)"
                        />
                        <Label
                            :for="`sheet-group-${group}`"
                            class="cursor-pointer font-semibold"
                        >
                            {{ permissionLabel[group] ?? group }}
                        </Label>
                    </div>

                    <Separator />

                    <!-- Individual permissions -->
                    <div class="grid grid-cols-2 gap-2 pl-6">
                        <div
                            v-for="permission in permissions"
                            :key="permission"
                            class="flex items-center gap-2"
                        >
                            <Checkbox
                                :id="`sheet-${permission}`"
                                :model-value="
                                    form.permissions.includes(permission)
                                "
                                @update:model-value="
                                    togglePermission(permission)
                                "
                            />
                            <Label
                                :for="`sheet-${permission}`"
                                class="cursor-pointer text-sm"
                            >
                                {{
                                    actionLabel[permission.split('.')[1]] ??
                                    permission.split('.')[1]
                                }}
                            </Label>
                        </div>
                    </div>
                </div>

                <SheetFooter class="mt-6">
                    <Button
                        type="button"
                        variant="outline"
                        @click="emit('update:open', false)"
                    >
                        Batal
                    </Button>
                    <Button type="submit" :disabled="form.processing">
                        {{
                            form.processing
                                ? 'Menyimpan...'
                                : 'Simpan Permission'
                        }}
                    </Button>
                </SheetFooter>
            </form>
        </SheetContent>
    </Sheet>
</template>
