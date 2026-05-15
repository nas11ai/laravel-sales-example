<script setup lang="ts" generic="TData">
import type {
    ColumnDef,
    SortingState,
    VisibilityState,
} from '@tanstack/vue-table';
import {
    FlexRender,
    getCoreRowModel,
    getFilteredRowModel,
    getPaginationRowModel,
    getSortedRowModel,
    useVueTable,
} from '@tanstack/vue-table';
import { ref } from 'vue';
import { ChevronDown, ChevronUp, ChevronsUpDown } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';

const props = withDefaults(
    defineProps<{
        columns: ColumnDef<TData, any>[];
        data: TData[];
        searchPlaceholder?: string;
        searchKey?: string;
    }>(),
    {
        searchPlaceholder: 'Cari...',
        searchKey: '',
    },
);

const sorting = ref<SortingState>([]);
const globalFilter = ref('');

const table = useVueTable({
    get data() {
        return props.data;
    },
    get columns() {
        return props.columns;
    },
    getCoreRowModel: getCoreRowModel(),
    getSortedRowModel: getSortedRowModel(),
    getFilteredRowModel: getFilteredRowModel(),
    getPaginationRowModel: getPaginationRowModel(),
    state: {
        get sorting() {
            return sorting.value;
        },
        get globalFilter() {
            return globalFilter.value;
        },
    },
    onSortingChange: (updater) => {
        sorting.value =
            typeof updater === 'function' ? updater(sorting.value) : updater;
    },
    onGlobalFilterChange: (updater) => {
        globalFilter.value =
            typeof updater === 'function'
                ? updater(globalFilter.value)
                : updater;
    },
    initialState: {
        pagination: { pageSize: 10 },
    },
});
</script>

<template>
    <div class="space-y-4">
        <!-- Search -->
        <div class="flex items-center justify-between gap-2">
            <Input
                v-model="globalFilter"
                :placeholder="searchPlaceholder"
                class="max-w-sm"
            />
            <slot name="actions" />
        </div>

        <!-- Table -->
        <div class="rounded-md border">
            <Table>
                <TableHeader>
                    <TableRow
                        v-for="headerGroup in table.getHeaderGroups()"
                        :key="headerGroup.id"
                    >
                        <TableHead
                            v-for="header in headerGroup.headers"
                            :key="header.id"
                            :class="
                                header.column.getCanSort()
                                    ? 'cursor-pointer select-none'
                                    : ''
                            "
                            @click="
                                header.column.getToggleSortingHandler()?.(
                                    $event,
                                )
                            "
                        >
                            <div class="flex items-center gap-1">
                                <FlexRender
                                    v-if="!header.isPlaceholder"
                                    :render="header.column.columnDef.header"
                                    :props="header.getContext()"
                                />
                                <template v-if="header.column.getCanSort()">
                                    <ChevronUp
                                        v-if="
                                            header.column.getIsSorted() ===
                                            'asc'
                                        "
                                        class="size-4"
                                    />
                                    <ChevronDown
                                        v-else-if="
                                            header.column.getIsSorted() ===
                                            'desc'
                                        "
                                        class="size-4"
                                    />
                                    <ChevronsUpDown
                                        v-else
                                        class="size-4 opacity-40"
                                    />
                                </template>
                            </div>
                        </TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <template v-if="table.getRowModel().rows.length">
                        <TableRow
                            v-for="row in table.getRowModel().rows"
                            :key="row.id"
                        >
                            <TableCell
                                v-for="cell in row.getVisibleCells()"
                                :key="cell.id"
                            >
                                <FlexRender
                                    :render="cell.column.columnDef.cell"
                                    :props="cell.getContext()"
                                />
                            </TableCell>
                        </TableRow>
                    </template>
                    <TableRow v-else>
                        <TableCell
                            :colspan="columns.length"
                            class="h-24 text-center text-muted-foreground"
                        >
                            Tidak ada data.
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </div>

        <!-- Pagination -->
        <div class="flex items-center justify-between">
            <p class="text-sm text-muted-foreground">
                {{ table.getFilteredRowModel().rows.length }} data ditemukan
            </p>
            <div class="flex items-center gap-2">
                <Button
                    variant="outline"
                    size="sm"
                    :disabled="!table.getCanPreviousPage()"
                    @click="table.previousPage()"
                >
                    Sebelumnya
                </Button>
                <span class="text-sm text-muted-foreground">
                    Halaman {{ table.getState().pagination.pageIndex + 1 }} dari
                    {{ table.getPageCount() }}
                </span>
                <Button
                    variant="outline"
                    size="sm"
                    :disabled="!table.getCanNextPage()"
                    @click="table.nextPage()"
                >
                    Selanjutnya
                </Button>
            </div>
        </div>
    </div>
</template>
