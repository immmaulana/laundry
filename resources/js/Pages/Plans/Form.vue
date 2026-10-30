<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PlanIcon from '@/Components/PlanIcon.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    plan: Object,
    icons: {
        type: Array,
        default: () => [],
    },
});

const editing = computed(() => !!props.plan);
const previewUrl = ref(props.plan?.logo_url || null);
const fileInput = ref(null);

const form = useForm({
    code: props.plan?.code || '',
    plan: props.plan?.plan || '',
    price: props.plan?.price || 0,
    description: props.plan?.description || '',
    icon: props.plan?.icon || 'wash',
    logo: null,
    remove_logo: false,
});

const onLogoChange = (event) => {
    const file = event.target.files?.[0] || null;
    form.logo = file;
    form.remove_logo = false;
    if (previewUrl.value?.startsWith('blob:')) URL.revokeObjectURL(previewUrl.value);
    previewUrl.value = file ? URL.createObjectURL(file) : props.plan?.logo_url || null;
};

const clearLogo = () => {
    form.logo = null;
    form.remove_logo = true;
    if (fileInput.value) fileInput.value.value = '';
    if (previewUrl.value?.startsWith('blob:')) URL.revokeObjectURL(previewUrl.value);
    previewUrl.value = null;
};

const submit = () => {
    const payload = (data, withMethod = false) => ({
        ...data,
        ...(withMethod ? { _method: 'put' } : {}),
        remove_logo: data.remove_logo ? 1 : 0,
    });

    if (editing.value) {
        form.transform((data) => payload(data, true)).post(route('plan.update', props.plan.id), {
            forceFormData: true,
            onSuccess: () => form.reset('logo'),
        });
        return;
    }

    form.transform((data) => payload(data)).post(route('plan.store'), {
        forceFormData: true,
        onSuccess: () => form.reset('logo'),
    });
};
</script>

<template>
    <Head :title="editing ? 'Edit layanan' : 'Tambah layanan'" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h1 class="text-lg font-bold text-soft-900">{{ editing ? 'Edit layanan' : 'Tambah layanan' }}</h1>
                <p class="text-sm text-soft-500">Atur nama, ikon, dan harga paket</p>
            </div>
        </template>

        <form class="card max-w-xl space-y-5 p-6" @submit.prevent="submit">
            <div>
                <label class="label-field">Ikon layanan</label>
                <div class="grid grid-cols-4 gap-2 sm:grid-cols-6">
                    <button
                        v-for="item in icons"
                        :key="item.key"
                        type="button"
                        class="flex flex-col items-center gap-1.5 rounded-2xl border p-2 transition"
                        :class="
                            form.icon === item.key
                                ? 'border-brand-400 bg-brand-50 ring-1 ring-brand-200'
                                : 'border-soft-200 bg-white hover:border-brand-200'
                        "
                        @click="form.icon = item.key"
                    >
                        <PlanIcon :name="item.key" size="sm" />
                        <span class="text-[10px] font-medium text-soft-600">{{ item.label }}</span>
                    </button>
                </div>
            </div>

            <div>
                <label class="label-field">Logo custom (opsional)</label>
                <div class="flex items-center gap-4">
                    <PlanIcon :name="form.icon" :logo-url="previewUrl" size="lg" />
                    <div class="min-w-0 flex-1 space-y-2">
                        <input
                            ref="fileInput"
                            type="file"
                            accept="image/png,image/jpeg,image/webp,image/svg+xml"
                            class="block w-full text-sm text-soft-600 file:mr-3 file:rounded-xl file:border-0 file:bg-brand-50 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-brand-800"
                            @change="onLogoChange"
                        />
                        <p class="text-xs text-soft-500">Kalau diisi, menggantikan ikon preset</p>
                        <button
                            v-if="previewUrl"
                            type="button"
                            class="text-xs font-semibold text-rose-600"
                            @click="clearLogo"
                        >
                            Hapus logo
                        </button>
                        <p v-if="form.errors.logo" class="text-xs text-rose-600">{{ form.errors.logo }}</p>
                    </div>
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="label-field">Kode</label>
                    <input v-model="form.code" type="text" class="input-field" required />
                </div>
                <div>
                    <label class="label-field">Harga</label>
                    <input v-model="form.price" type="number" min="0" class="input-field" required />
                </div>
            </div>
            <div>
                <label class="label-field">Nama layanan</label>
                <input v-model="form.plan" type="text" class="input-field" required />
            </div>
            <div>
                <label class="label-field">Deskripsi</label>
                <input v-model="form.description" type="text" class="input-field" placeholder="Per kilogram / per piece" />
            </div>
            <div class="flex gap-3 pt-2">
                <button type="submit" class="btn-primary" :disabled="form.processing">Simpan</button>
                <Link :href="route('plan.index')" class="btn-secondary">Batal</Link>
            </div>
        </form>
    </AuthenticatedLayout>
</template>
