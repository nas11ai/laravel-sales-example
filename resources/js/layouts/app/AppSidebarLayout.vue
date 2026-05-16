<script setup lang="ts">
import {
    SidebarInset,
    SidebarProvider,
    SidebarTrigger,
} from '@/components/ui/sidebar';
import {
    Breadcrumb,
    BreadcrumbItem,
    BreadcrumbLink,
    BreadcrumbList,
    BreadcrumbPage,
    BreadcrumbSeparator,
} from '@/components/ui/breadcrumb';
import { Separator } from '@/components/ui/separator';
import AppSidebar from '@/components/AppSidebar.vue';

interface BreadcrumbItem {
    title: string;
    href?: string;
}

const props = defineProps<{
    breadcrumbs?: BreadcrumbItem[];
}>();
</script>

<template>
    <SidebarProvider>
        <AppSidebar />
        <SidebarInset>
            <!-- Header -->
            <header class="flex h-12 shrink-0 items-center gap-2 border-b px-4">
                <SidebarTrigger class="-ml-1" />
                <Separator orientation="vertical" class="mr-2 h-4" />
                <Breadcrumb v-if="breadcrumbs?.length">
                    <BreadcrumbList>
                        <template
                            v-for="(crumb, index) in breadcrumbs"
                            :key="crumb.title"
                        >
                            <BreadcrumbItem>
                                <BreadcrumbPage
                                    v-if="index === breadcrumbs.length - 1"
                                >
                                    {{ crumb.title }}
                                </BreadcrumbPage>
                                <BreadcrumbLink
                                    v-else
                                    :href="crumb.href ?? '#'"
                                >
                                    {{ crumb.title }}
                                </BreadcrumbLink>
                            </BreadcrumbItem>
                            <BreadcrumbSeparator
                                v-if="index < breadcrumbs.length - 1"
                            />
                        </template>
                    </BreadcrumbList>
                </Breadcrumb>
            </header>

            <main class="flex flex-1 flex-col gap-4 p-4">
                <slot />
            </main>
        </SidebarInset>
    </SidebarProvider>
</template>
