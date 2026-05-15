import type { LucideIcon } from 'lucide-vue-next';

export type NavItem = {
    title: string;
    href?: string;
    icon?: LucideIcon;
    isActive?: boolean;
    items?: Omit<NavItem, 'items' | 'icon'>[];
};
