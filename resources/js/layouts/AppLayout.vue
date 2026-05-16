<script setup lang="ts">
import { Toaster, toast } from 'vue-sonner';
import { usePage } from '@inertiajs/vue3';
import { watch } from 'vue';
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { provideBreadcrumbs } from '@/composables/useBreadcrumbs';
import type { PageProps } from '@/types';

const page = usePage<PageProps>();
const breadcrumbs = provideBreadcrumbs();

watch(
    () => [page.props.flash?.success, page.props.flash?.error],
    ([success, error]) => {
        if (success) toast.success(success as string);
        if (error) toast.error(error as string);
    },
);
</script>

<template>
    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <slot />
    </AppSidebarLayout>
    <Toaster position="top-right" rich-colors />
</template>
