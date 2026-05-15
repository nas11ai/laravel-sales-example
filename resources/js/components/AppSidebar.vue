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
import NavUser from '@/components/NavUser.vue';
import type { NavItem } from '@/types/navigation';
import { dashboard as dashboardIndex } from '@/routes';
import { index as salesIndex } from '@/routes/sales';
import { index as paymentsIndex } from '@/routes/payments';
import { index as masterItemsIndex } from '@/routes/master/items';
import { index as masterUsersIndex } from '@/routes/master/users';

const appName = import.meta.env.VITE_APP_NAME || 'Sales App';

const navItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboardIndex().url,
        icon: LayoutDashboard,
    },
    {
        title: 'Penjualan',
        href: salesIndex().url,
        icon: ShoppingCart,
    },
    {
        title: 'Pembayaran',
        href: paymentsIndex().url,
        icon: CreditCard,
    },
    {
        title: 'Master',
        icon: Package,
        items: [
            { title: 'Item', href: masterItemsIndex().url },
            { title: 'User', href: masterUsersIndex().url },
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
                        <Link :href="dashboardIndex().url">
                            <span class="font-semibold">{{ appName }}</span>
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="navItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavUser />
        </SidebarFooter>
    </Sidebar>
</template>
