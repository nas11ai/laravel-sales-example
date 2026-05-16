<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { Item, PageProps } from '@/types';
import RupiahInput from '@/components/RupiahInput.vue';
import items from '@/routes/master/items';

const props = defineProps<
    PageProps<{
        item: {
            data: Item;
        };
    }>
>();

const form = useForm({
    kode: props.item.data.kode,
    nama: props.item.data.nama,
    harga: props.item.data.harga,
    image: null as File | null,
    _method: 'PUT',
});

const imagePreview = ref<string | null>(props.item.data.image_url);

function onImageChange(e: Event) {
    const file = (e.target as HTMLInputElement).files?.[0];
    if (!file) return;
    form.image = file;
    imagePreview.value = URL.createObjectURL(file);
}

function submit() {
    form.post(items.update(props.item.data.id).url, {
        forceFormData: true,
    });
}
</script>

<template>
    <Head title="Edit Item" />

    <div class="max-w-xl space-y-6 p-6">
        <div>
            <h1 class="text-2xl font-semibold">Edit Item</h1>
            <p class="text-sm text-muted-foreground">Ubah data item</p>
        </div>

        <form class="space-y-4" @submit.prevent="submit">
            <div class="grid gap-2">
                <Label for="kode">Kode Item</Label>
                <Input id="kode" v-model="form.kode" placeholder="ITM-001" />
                <p v-if="form.errors.kode" class="text-sm text-destructive">
                    {{ form.errors.kode }}
                </p>
            </div>

            <div class="grid gap-2">
                <Label for="nama">Nama Item</Label>
                <Input id="nama" v-model="form.nama" placeholder="Nama item" />
                <p v-if="form.errors.nama" class="text-sm text-destructive">
                    {{ form.errors.nama }}
                </p>
            </div>

            <div class="grid gap-2">
                <Label for="harga">Harga (Rp)</Label>
                <RupiahInput id="harga" v-model="form.harga" />
                <p v-if="form.errors.harga" class="text-sm text-destructive">
                    {{ form.errors.harga }}
                </p>
            </div>

            <div class="grid gap-2">
                <Label for="image">Gambar</Label>
                <img
                    v-if="imagePreview"
                    :src="imagePreview"
                    class="h-32 w-32 rounded-md object-cover"
                    alt="Preview"
                />
                <Input
                    id="image"
                    type="file"
                    accept="image/*"
                    @change="onImageChange"
                />
                <p class="text-xs text-muted-foreground">
                    Kosongkan jika tidak ingin mengubah gambar
                </p>
                <p v-if="form.errors.image" class="text-sm text-destructive">
                    {{ form.errors.image }}
                </p>
            </div>

            <div class="flex gap-3 pt-2">
                <Button type="submit" :disabled="form.processing">
                    {{ form.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
                </Button>
                <Button
                    type="button"
                    variant="outline"
                    @click="$inertia.visit(items.index().url)"
                >
                    Batal
                </Button>
            </div>
        </form>
    </div>
</template>
