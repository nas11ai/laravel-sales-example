<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import type { PageProps, UserData } from '@/types';
import users from '@/routes/master/users';
import { dashboard } from '@/routes';
import { useBreadcrumb } from '@/composables/useBreadcrumbs';

const { setBreadcrumbs } = useBreadcrumb();

setBreadcrumbs([
    { title: 'Dashboard', href: dashboard().url },
    { title: 'Master User', href: users.index().url },
    { title: 'Edit User' },
]);

const props = defineProps<PageProps<{ user: UserData; roles: string[] }>>();

const form = useForm({
    name: props.user.name,
    email: props.user.email,
    password: '',
    password_confirmation: '',
    role: props.user.role ?? '',
    _method: 'PUT',
});

function submit() {
    form.post(users.update(props.user.id).url);
}
</script>

<template>
    <Head title="Edit User" />

    <div class="max-w-xl space-y-6 p-6">
        <div>
            <h1 class="text-2xl font-semibold">Edit User</h1>
            <p class="text-sm text-muted-foreground">Ubah data pengguna</p>
        </div>

        <form class="space-y-4" @submit.prevent="submit">
            <div class="grid gap-2">
                <Label for="name">Nama</Label>
                <Input
                    id="name"
                    v-model="form.name"
                    placeholder="Nama lengkap"
                />
                <p v-if="form.errors.name" class="text-sm text-destructive">
                    {{ form.errors.name }}
                </p>
            </div>

            <div class="grid gap-2">
                <Label for="email">Email</Label>
                <Input
                    id="email"
                    v-model="form.email"
                    type="email"
                    placeholder="email@example.com"
                />
                <p v-if="form.errors.email" class="text-sm text-destructive">
                    {{ form.errors.email }}
                </p>
            </div>

            <div class="grid gap-2">
                <Label for="role">Role</Label>
                <Select v-model="form.role">
                    <SelectTrigger>
                        <SelectValue placeholder="Pilih role" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem
                            v-for="role in roles"
                            :key="role"
                            :value="role"
                        >
                            {{ role }}
                        </SelectItem>
                    </SelectContent>
                </Select>
                <p v-if="form.errors.role" class="text-sm text-destructive">
                    {{ form.errors.role }}
                </p>
            </div>

            <div class="grid gap-2">
                <Label for="password">Password Baru</Label>
                <Input
                    id="password"
                    v-model="form.password"
                    type="password"
                    placeholder="Kosongkan jika tidak diubah"
                />
                <p v-if="form.errors.password" class="text-sm text-destructive">
                    {{ form.errors.password }}
                </p>
            </div>

            <div class="grid gap-2">
                <Label for="password_confirmation">Konfirmasi Password</Label>
                <Input
                    id="password_confirmation"
                    v-model="form.password_confirmation"
                    type="password"
                    placeholder="Ulangi password baru"
                />
            </div>

            <FormActions
                :processing="form.processing"
                submit-label="Simpan Perubahan"
                :cancel-href="users.index().url"
            />
        </form>
    </div>
</template>
