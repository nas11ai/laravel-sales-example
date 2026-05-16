<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { Loader2 } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';

withDefaults(
    defineProps<{
        processing?: boolean;
        submitLabel?: string;
        cancelHref?: string;
    }>(),
    {
        processing: false,
        submitLabel: 'Simpan',
        cancelHref: undefined,
    },
);
</script>

<template>
    <div class="flex gap-3 pt-2">
        <Button type="submit" :disabled="processing">
            <Loader2 v-if="processing" class="mr-2 size-4 animate-spin" />
            {{ processing ? 'Menyimpan...' : submitLabel }}
        </Button>
        <Button
            v-if="cancelHref"
            type="button"
            variant="outline"
            @click="router.visit(cancelHref)"
        >
            Batal
        </Button>
    </div>
</template>
