import { defineStore } from 'pinia';
import { ref, computed } from 'vue';

export interface SaleFormItem {
    item_id: number | null;
    nama: string;
    qty: number;
    price_snapshot: number;
    total_price: number;
}

export const useSaleFormStore = defineStore('saleForm', () => {
    const rows = ref<SaleFormItem[]>([
        { item_id: null, nama: '', qty: 1, price_snapshot: 0, total_price: 0 },
    ]);

    const totalAmount = computed(() =>
        rows.value.reduce((sum, row) => sum + row.total_price, 0),
    );

    function addRow() {
        rows.value.push({
            item_id: null,
            nama: '',
            qty: 1,
            price_snapshot: 0,
            total_price: 0,
        });
    }

    function removeRow(index: number) {
        if (rows.value.length === 1) return;
        rows.value.splice(index, 1);
    }

    function updateItem(
        index: number,
        itemId: number,
        harga: number,
        nama: string,
    ) {
        const row = rows.value[index];
        row.item_id = itemId;
        row.nama = nama;
        row.price_snapshot = harga;
        row.total_price = harga * row.qty;
    }

    function updateQty(index: number, qty: number) {
        const row = rows.value[index];
        row.qty = qty;
        row.total_price = row.price_snapshot * qty;
    }

    function setRows(initial: SaleFormItem[]) {
        rows.value = initial;
    }

    function reset() {
        rows.value = [
            {
                item_id: null,
                nama: '',
                qty: 1,
                price_snapshot: 0,
                total_price: 0,
            },
        ];
    }

    return {
        rows,
        totalAmount,
        addRow,
        removeRow,
        updateItem,
        updateQty,
        setRows,
        reset,
    };
});
