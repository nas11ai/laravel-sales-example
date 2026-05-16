import { inject, provide, ref, type Ref } from 'vue';

export interface Breadcrumb {
    title: string;
    href?: string;
}

const BREADCRUMB_KEY = Symbol('breadcrumbs');

export function provideBreadcrumbs() {
    const breadcrumbs = ref<Breadcrumb[]>([]);
    provide(BREADCRUMB_KEY, breadcrumbs);
    return breadcrumbs;
}

export function useBreadcrumb() {
    const breadcrumbs = inject<Ref<Breadcrumb[]>>(BREADCRUMB_KEY);

    function setBreadcrumbs(items: Breadcrumb[]) {
        if (breadcrumbs) breadcrumbs.value = items;
    }

    return { setBreadcrumbs };
}
