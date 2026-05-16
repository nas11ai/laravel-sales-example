export * from './auth';

import type { PageProps as InertiaPageProps } from '@inertiajs/core';

export interface User {
    id: number;
    name: string;
    email: string;
}

export interface Item {
    id: number;
    kode: string;
    nama: string;
    image_path: string | null;
    image_url: string | null;
    harga: number;
    created_at: string;
    updated_at: string;
}

export interface SaleItem {
    id: number;
    sale_id: number;
    item_id: number;
    item?: Item;
    qty: number;
    price_snapshot: number;
    total_price: number;
}

export interface Sale {
    id: number;
    kode: string;
    tanggal: string;
    status: 'belum_dibayar' | 'belum_dibayar_sepenuhnya' | 'sudah_dibayar';
    total_amount: number;
    created_by: number;
    created_by_user?: User;
    items?: SaleItem[];
    payments?: Payment[];
    created_at: string;
    updated_at: string;
}

export interface Payment {
    id: number;
    kode: string;
    sale_id: number;
    sale?: Sale;
    tanggal: string;
    jumlah: number;
    created_at: string;
    updated_at: string;
}

export type PageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T &
    InertiaPageProps & {
        auth: {
            user: User | null;
            roles: string[];
            permissions: string[];
        };
        flash: {
            success: string | null;
            error: string | null;
        };
    };
