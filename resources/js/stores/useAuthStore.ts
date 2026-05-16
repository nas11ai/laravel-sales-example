import { defineStore } from 'pinia';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import type { PageProps, User } from '@/types';

export const useAuthStore = defineStore('auth', () => {
    const page = usePage<PageProps>();

    const user = computed<User | null>(() => page.props.auth.user);
    const roles = computed<string[]>(() => page.props.auth.roles ?? []);
    const permissions = computed<string[]>(
        () => page.props.auth.permissions ?? [],
    );

    const isAdmin = computed(() => roles.value.includes('admin'));
    const isStaff = computed(() => roles.value.includes('staff'));

    const can = (permission: string) => permissions.value.includes(permission);

    return { user, roles, permissions, isAdmin, isStaff, can };
});
