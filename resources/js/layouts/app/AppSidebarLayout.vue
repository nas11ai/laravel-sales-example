<script setup lang="ts">
import { SidebarInset, SidebarProvider } from '@/components/ui/sidebar';
import AppSidebar from '@/components/AppSidebar.vue';

defineProps<{ breadcrumbs?: { title: string; href: string }[] }>();
</script>

<template>
    <SidebarProvider>
        <AppSidebar />
        <SidebarInset>
            <main class="flex flex-1 flex-col gap-4 p-4">
                <slot />
            </main>
        </SidebarInset>
    </SidebarProvider>
</template>
