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
import type { PageProps } from '@/types';
import users from '@/routes/master/users';

const props = defineProps<PageProps<{ roles: string[] }>>();

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role: '',
});

function submit() {
    form.post(users.store().url);
}
</script>

<template>
    <Head title="Tambah User" />

    <div class="max-w-xl space-y-6 p-6">
        <div>
            <h1 class="text-2xl font-semibold">Tambah User</h1>
            <p class="text-sm text-muted-foreground">Tambah pengguna baru</p>
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
                <Label for="password">Password</Label>
                <Input
                    id="password"
                    v-model="form.password"
                    type="password"
                    placeholder="Min. 8 karakter"
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
                    placeholder="Ulangi password"
                />
            </div>

            <div class="flex gap-3 pt-2">
                <Button type="submit" :disabled="form.processing">
                    {{ form.processing ? 'Menyimpan...' : 'Simpan' }}
                </Button>
                <Button
                    type="button"
                    variant="outline"
                    @click="router.visit(users.index().url)"
                >
                    Batal
                </Button>
            </div>
        </form>
    </div>
</template>
