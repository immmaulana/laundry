<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import { applyTheme, THEME_PRESETS } from '@/Composables/theme';

const props = defineProps({
    company: Object,
    presets: {
        type: Array,
        default: () => THEME_PRESETS,
    },
});

const page = usePage();
const editing = computed(() => !!props.company);
const isSuper = computed(() => page.props.auth.user?.role === 'super_admin');
const previewUrl = ref(props.company?.logo_url || null);
const fileInput = ref(null);

const form = useForm({
    name: props.company?.name || '',
    slug: props.company?.slug || '',
    code: props.company?.code || '',
    phone: props.company?.phone || '',
    email: props.company?.email || '',
    address: props.company?.address || '',
    city: props.company?.city || '',
    is_active: props.company?.is_active ?? true,
    primary_color: props.company?.primary_color || '#0B3D8C',
    secondary_color: props.company?.secondary_color || '#64748B',
    logo: null,
    remove_logo: false,
    admin_name: '',
    admin_email: '',
    admin_password: '',
});

watch(
    () => [form.primary_color, form.secondary_color],
    ([primary, secondary]) => {
        applyTheme({ primary_color: primary, secondary_color: secondary });
    },
    { immediate: true },
);

const applyPreset = (preset) => {
    form.primary_color = preset.primary_color;
    form.secondary_color = preset.secondary_color;
};

const onLogoChange = (event) => {
    const file = event.target.files?.[0] || null;
    form.logo = file;
    form.remove_logo = false;

    if (previewUrl.value && previewUrl.value.startsWith('blob:')) {
        URL.revokeObjectURL(previewUrl.value);
    }

    previewUrl.value = file ? URL.createObjectURL(file) : props.company?.logo_url || null;
};

const clearLogo = () => {
    form.logo = null;
    form.remove_logo = true;
    if (fileInput.value) {
        fileInput.value.value = '';
    }
    if (previewUrl.value && previewUrl.value.startsWith('blob:')) {
        URL.revokeObjectURL(previewUrl.value);
    }
    previewUrl.value = null;
};

const payload = (data, withMethod = false) => ({
    ...data,
    ...(withMethod ? { _method: 'put' } : {}),
    is_active: data.is_active ? 1 : 0,
    remove_logo: data.remove_logo ? 1 : 0,
});

const submit = () => {
    if (editing.value) {
        form
            .transform((data) => payload(data, true))
            .post(route('companies.update', props.company.id), {
                forceFormData: true,
                onSuccess: () => form.reset('logo', 'admin_password'),
            });
        return;
    }

    form
        .transform((data) => payload(data))
        .post(route('companies.store'), {
            forceFormData: true,
            onSuccess: () => form.reset('logo', 'admin_password'),
        });
};
</script>

<template>
    <Head :title="editing ? 'Edit perusahaan' : 'Tambah perusahaan'" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h1 class="text-lg font-bold text-soft-900">
                    {{ editing ? 'Edit perusahaan' : 'Tambah perusahaan' }}
                </h1>
                <p class="text-sm text-soft-500">
                    {{ isSuper ? 'Data tenant / PT pelanggan' : 'Profil, logo & warna panel perusahaan Anda' }}
                </p>
            </div>
        </template>

        <form class="card max-w-2xl space-y-5 p-6" @submit.prevent="submit">
            <div>
                <label class="label-field">Logo perusahaan</label>
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                    <div
                        class="flex h-20 w-20 shrink-0 items-center justify-center overflow-hidden rounded-2xl border border-soft-200 bg-white shadow-soft"
                    >
                        <img
                            v-if="previewUrl"
                            :src="previewUrl"
                            alt="Logo"
                            class="h-full w-full object-contain p-2"
                        />
                        <span v-else class="text-xs font-semibold text-soft-400">No logo</span>
                    </div>
                    <div class="min-w-0 flex-1 space-y-2">
                        <input
                            ref="fileInput"
                            type="file"
                            accept="image/png,image/jpeg,image/webp,image/svg+xml"
                            class="block w-full text-sm text-soft-600 file:mr-3 file:rounded-xl file:border-0 file:bg-brand-50 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-brand-800 hover:file:bg-brand-100"
                            @change="onLogoChange"
                        />
                        <p class="text-xs text-soft-500">PNG, JPG, WEBP, atau SVG · maks 2MB</p>
                        <button
                            v-if="previewUrl"
                            type="button"
                            class="text-xs font-semibold text-rose-600 hover:text-rose-700"
                            @click="clearLogo"
                        >
                            Hapus logo
                        </button>
                        <p v-if="form.errors.logo" class="text-xs text-rose-600">{{ form.errors.logo }}</p>
                    </div>
                </div>
            </div>

            <div>
                <label class="label-field">Nama perusahaan</label>
                <input v-model="form.name" type="text" class="input-field" required placeholder="PT Fresh Wash Nusantara" />
                <p v-if="form.errors.name" class="mt-1 text-xs text-rose-600">{{ form.errors.name }}</p>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="label-field">Kode</label>
                    <input
                        v-model="form.code"
                        type="text"
                        class="input-field uppercase"
                        required
                        :readonly="!isSuper && editing"
                        placeholder="PTA"
                    />
                    <p v-if="form.errors.code" class="mt-1 text-xs text-rose-600">{{ form.errors.code }}</p>
                </div>
                <div>
                    <label class="label-field">Kota</label>
                    <input v-model="form.city" type="text" class="input-field" placeholder="Jakarta" />
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="label-field">Telepon</label>
                    <input v-model="form.phone" type="text" class="input-field" />
                </div>
                <div>
                    <label class="label-field">Email perusahaan</label>
                    <input v-model="form.email" type="email" class="input-field" />
                </div>
            </div>

            <div>
                <label class="label-field">Alamat</label>
                <textarea v-model="form.address" rows="2" class="input-field" />
            </div>

            <div class="rounded-2xl border border-soft-200 bg-soft-50/60 p-4">
                <div class="mb-3">
                    <p class="text-sm font-semibold text-soft-900">Warna panel admin</p>
                    <p class="text-xs text-soft-500">Preview langsung di sidebar & tombol</p>
                </div>

                <div class="mb-4 flex flex-wrap gap-2">
                    <button
                        v-for="preset in presets"
                        :key="preset.name"
                        type="button"
                        class="inline-flex items-center gap-2 rounded-xl border border-soft-200 bg-white px-3 py-1.5 text-xs font-medium text-soft-700 transition hover:border-brand-300"
                        @click="applyPreset(preset)"
                    >
                        <span class="h-3.5 w-3.5 rounded-full" :style="{ background: preset.primary_color }" />
                        {{ preset.name }}
                    </button>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="label-field">Warna utama</label>
                        <div class="flex items-center gap-3">
                            <input v-model="form.primary_color" type="color" class="h-10 w-14 cursor-pointer rounded-lg border border-soft-200 bg-white p-1" />
                            <input v-model="form.primary_color" type="text" class="input-field font-mono uppercase" maxlength="7" />
                        </div>
                    </div>
                    <div>
                        <label class="label-field">Warna sekunder</label>
                        <div class="flex items-center gap-3">
                            <input v-model="form.secondary_color" type="color" class="h-10 w-14 cursor-pointer rounded-lg border border-soft-200 bg-white p-1" />
                            <input v-model="form.secondary_color" type="text" class="input-field font-mono uppercase" maxlength="7" />
                        </div>
                    </div>
                </div>

                <div class="mt-4 flex flex-wrap items-center gap-2">
                    <span class="rounded-xl bg-brand-600 px-3 py-1.5 text-xs font-semibold text-white">Tombol utama</span>
                    <span class="rounded-xl bg-brand-50 px-3 py-1.5 text-xs font-semibold text-brand-800 ring-1 ring-brand-100">Badge</span>
                    <span class="rounded-xl border border-soft-200 bg-white px-3 py-1.5 text-xs font-semibold text-soft-700">Sekunder</span>
                </div>
            </div>

            <label v-if="isSuper" class="flex items-center gap-2 text-sm text-soft-700">
                <input v-model="form.is_active" type="checkbox" class="rounded border-soft-300 text-brand-600 focus:ring-brand-500" />
                Perusahaan aktif
            </label>

            <div v-if="isSuper && !editing" class="space-y-3 rounded-2xl border border-dashed border-soft-200 bg-soft-50/70 p-4">
                <p class="text-sm font-semibold text-soft-800">Admin perusahaan (opsional)</p>
                <div class="grid gap-3 sm:grid-cols-2">
                    <input v-model="form.admin_name" type="text" class="input-field" placeholder="Nama admin" />
                    <input v-model="form.admin_email" type="email" class="input-field" placeholder="Email admin" />
                </div>
                <input v-model="form.admin_password" type="password" class="input-field" placeholder="Password admin (min 8)" />
                <p v-if="form.errors.admin_email" class="text-xs text-rose-600">{{ form.errors.admin_email }}</p>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="btn-primary" :disabled="form.processing">Simpan</button>
                <Link
                    v-if="isSuper"
                    :href="route('companies.index')"
                    class="btn-secondary"
                >
                    Kembali
                </Link>
            </div>
        </form>
    </AuthenticatedLayout>
</template>
