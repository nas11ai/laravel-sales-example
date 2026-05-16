<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { VisAxis, VisGroupedBar, VisXYContainer } from '@unovis/vue';
import { CreditCard, Package, ShoppingCart } from 'lucide-vue-next';
import {
    ChartContainer,
    ChartCrosshair,
    ChartTooltip,
    ChartTooltipContent,
    componentToString,
} from '@/components/ui/chart';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useRupiah } from '@/composables/useRupiah';
import type { BulanData, ItemData, PageProps } from '@/types';
import { dashboard } from '@/routes';
import { useBreadcrumb } from '@/composables/useBreadcrumbs';

const { setBreadcrumbs } = useBreadcrumb();

setBreadcrumbs([{ title: 'Dashboard' }]);

const props = defineProps<
    PageProps<{
        widgets: {
            total_transaksi: number;
            total_penjualan: number;
            total_qty: number;
        };
        charts: {
            penjualan_per_bulan: BulanData[];
            qty_per_item: ItemData[];
        };
        filters: {
            start_date: string;
            end_date: string;
        };
    }>
>();

const { format } = useRupiah();

const startDate = ref(props.filters.start_date);
const endDate = ref(props.filters.end_date);

let timeout: ReturnType<typeof setTimeout>;
watch([startDate, endDate], ([start, end]) => {
    clearTimeout(timeout);
    timeout = setTimeout(() => {
        return router.get(
            dashboard.url(),
            { start_date: start, end_date: end },
            { preserveState: true, replace: true },
        );
    }, 400);
});

const widgets = [
    {
        title: 'Total Transaksi',
        icon: ShoppingCart,
        value: () => `${props.widgets.total_transaksi} transaksi`,
        description: 'Jumlah penjualan dalam periode',
    },
    {
        title: 'Total Penjualan',
        icon: CreditCard,
        value: () => format(props.widgets.total_penjualan),
        description: 'Total nilai penjualan dalam periode',
    },
    {
        title: 'Total Qty Terjual',
        icon: Package,
        value: () => `${props.widgets.total_qty} item`,
        description: 'Total item terjual dalam periode',
    },
];

const chartBulanConfig = {
    total: {
        label: 'Penjualan (Rp)',
        color: 'var(--chart-1)',
    },
};

const chartItemConfig = {
    qty: {
        label: 'Qty Terjual',
        color: 'var(--chart-2)',
    },
};
</script>

<template>
    <Head title="Dashboard" />

    <div class="space-y-6 p-6">
        <Card>
            <CardHeader>
                <CardTitle class="text-base">Filter Periode</CardTitle>
            </CardHeader>
            <CardContent class="flex flex-wrap items-end gap-4">
                <div class="grid gap-1.5">
                    <Label>Dari Tanggal</Label>
                    <Input v-model="startDate" type="date" class="w-44" />
                </div>
                <div class="grid gap-1.5">
                    <Label>Sampai Tanggal</Label>
                    <Input v-model="endDate" type="date" class="w-44" />
                </div>
            </CardContent>
        </Card>

        <div class="grid gap-4 sm:grid-cols-3">
            <Card v-for="widget in widgets" :key="widget.title">
                <CardHeader
                    class="flex flex-row items-center justify-between pb-2"
                >
                    <CardTitle
                        class="text-sm font-medium text-muted-foreground"
                    >
                        {{ widget.title }}
                    </CardTitle>
                    <component
                        :is="widget.icon"
                        class="size-4 text-muted-foreground"
                    />
                </CardHeader>
                <CardContent>
                    <p class="text-2xl font-semibold">{{ widget.value() }}</p>
                    <p class="mt-1 text-xs text-muted-foreground">
                        {{ widget.description }}
                    </p>
                </CardContent>
            </Card>
        </div>

        <div class="grid gap-4 lg:grid-cols-2">
            <Card>
                <CardHeader>
                    <CardTitle class="text-base">Penjualan per Bulan</CardTitle>
                    <p class="text-xs text-muted-foreground">
                        12 bulan terakhir
                    </p>
                </CardHeader>
                <CardContent>
                    <template v-if="charts.penjualan_per_bulan.length">
                        <ChartContainer
                            :config="chartBulanConfig"
                            class="h-64 w-full"
                        >
                            <VisXYContainer :data="charts.penjualan_per_bulan">
                                <VisGroupedBar
                                    :x="(_: BulanData, i: number) => i"
                                    :y="(d: BulanData) => d.total"
                                    :color="chartBulanConfig.total.color"
                                />
                                <VisAxis
                                    type="x"
                                    :x="(_: BulanData, i: number) => i"
                                    :tick-format="
                                        (i: number) =>
                                            charts.penjualan_per_bulan[i]
                                                ?.label ?? ''
                                    "
                                    :tick-values="
                                        charts.penjualan_per_bulan.map(
                                            (_, i) => i,
                                        )
                                    "
                                    :tick-line="false"
                                    :domain-line="false"
                                />
                                <VisAxis
                                    type="y"
                                    :tick-format="(v: number) => format(v)"
                                    :tick-line="false"
                                    :domain-line="false"
                                    :grid-line="true"
                                />
                                <ChartTooltip />
                                <ChartCrosshair
                                    :template="
                                        componentToString(
                                            chartBulanConfig,
                                            ChartTooltipContent,
                                            {
                                                labelFormatter: (
                                                    v: number | Date,
                                                ) =>
                                                    charts.penjualan_per_bulan[
                                                        Number(v)
                                                    ]?.label ?? '',
                                            },
                                        )
                                    "
                                    :color="[chartBulanConfig.total.color]"
                                />
                            </VisXYContainer>
                        </ChartContainer>
                    </template>
                    <div
                        v-else
                        class="flex h-64 items-center justify-center text-sm text-muted-foreground"
                    >
                        Belum ada data penjualan
                    </div>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle class="text-base"
                        >Qty Terjual per Item</CardTitle
                    >
                    <p class="text-xs text-muted-foreground">
                        Top 10 item dalam periode yang dipilih
                    </p>
                </CardHeader>
                <CardContent>
                    <template v-if="charts.qty_per_item.length">
                        <ChartContainer
                            :config="chartItemConfig"
                            class="h-64 w-full"
                        >
                            <VisXYContainer :data="charts.qty_per_item">
                                <VisGroupedBar
                                    :x="(_: ItemData, i: number) => i"
                                    :y="(d: ItemData) => d.qty"
                                    :color="chartItemConfig.qty.color"
                                />
                                <VisAxis
                                    type="x"
                                    :x="(_: ItemData, i: number) => i"
                                    :tick-format="
                                        (i: number) =>
                                            charts.qty_per_item[i]?.label ?? ''
                                    "
                                    :tick-values="
                                        charts.qty_per_item.map((_, i) => i)
                                    "
                                    :tick-line="false"
                                    :domain-line="false"
                                />
                                <VisAxis
                                    type="y"
                                    :tick-line="false"
                                    :domain-line="false"
                                    :grid-line="true"
                                />
                                <ChartTooltip />
                                <ChartCrosshair
                                    :template="
                                        componentToString(
                                            chartItemConfig,
                                            ChartTooltipContent,
                                            {
                                                labelFormatter: (
                                                    v: number | Date,
                                                ) =>
                                                    charts.qty_per_item[
                                                        Number(v)
                                                    ]?.label ?? '',
                                            },
                                        )
                                    "
                                    :color="[chartItemConfig.qty.color]"
                                />
                            </VisXYContainer>
                        </ChartContainer>
                    </template>
                    <div
                        v-else
                        class="flex h-64 items-center justify-center text-sm text-muted-foreground"
                    >
                        Belum ada data dalam periode ini
                    </div>
                </CardContent>
            </Card>
        </div>
    </div>
</template>
