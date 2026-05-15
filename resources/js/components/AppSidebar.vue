<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    CreditCard,
    LayoutDashboard,
    Package,
    ShoppingCart,
} from 'lucide-vue-next';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import NavMain from '@/components/NavMain.vue';
import type { NavItem } from '@/types/navigation';

const navItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
        icon: LayoutDashboard,
    },
    {
        title: 'Penjualan',
        href: '/sales',
        icon: ShoppingCart,
    },
    {
        title: 'Pembayaran',
        href: '/payments',
        icon: CreditCard,
    },
    {
        title: 'Master',
        icon: Package,
        items: [
            { title: 'Item', href: '/master/items' },
            { title: 'User', href: '/master/users' },
        ],
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link href="/dashboard">
                            <span class="font-semibold">Sales App</span>
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="navItems" />
        </SidebarContent>

        <SidebarFooter>
            <div class="p-2 text-xs text-muted-foreground">
                Sales Management
            </div>
        </SidebarFooter>
    </Sidebar>
</template>
