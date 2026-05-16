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

export interface SaleItemRow {
    item_id: number | null;
    nama: string;
    qty: number;
    price_snapshot: number;
    total_price: number;
}

export interface SaleRow {
    id: number;
    kode: string;
    tanggal_label: string;
    status: string;
    status_label: string;
    status_color: string;
    total_amount: number;
    is_editable: boolean;
    created_by: { name: string } | null;
}

export interface ItemOption {
    id: number;
    kode: string;
    nama: string;
    harga: number;
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

export interface PaymentRow {
    id: number;
    kode: string;
    tanggal_label: string;
    jumlah: number;
    sale: {
        kode: string;
        status_label: string;
    };
}

export interface SaleOption {
    id: number;
    kode: string;
    tanggal: string;
    total_amount: number;
    total_paid: number;
    sisa: number;
    status_label: string;
}

export interface PaymentEdit {
    id: number;
    kode: string;
    tanggal: string;
    jumlah: number;
    sale: {
        id: number;
        kode: string;
        total_amount: number;
        total_paid: number;
        status_label: string;
        sisa: number;
    };
}

export interface PaymentDetail {
    id: number;
    kode: string;
    tanggal_label: string;
    jumlah: number;
    sale: {
        id: number;
        kode: string;
        total_amount: number;
        status_label: string;
    };
}

export interface SaleDetail {
    id: number;
    kode: string;
    tanggal_label: string;
    status: string;
    status_label: string;
    status_color: string;
    total_amount: number;
    total_paid: number;
    is_editable: boolean;
    created_by: { name: string } | null;
    items: SaleItem[];
    payments: {
        id: number;
        kode: string;
        jumlah: number;
        tanggal: string;
    }[];
}

export interface SaleEdit {
    id: number;
    kode: string;
    tanggal: string;
    items: {
        item_id: number;
        item: { id: number; nama: string; kode: string };
        qty: number;
        price_snapshot: number;
        total_price: number;
    }[];
}

export interface BulanData {
    label: string;
    total: number;
}

export interface ItemData {
    label: string;
    qty: number;
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
