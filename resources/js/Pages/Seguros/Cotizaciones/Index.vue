<script setup>
import FormModal from '@/Components/FormModal.vue';
import Pagination from '@/Components/Pagination.vue';
import PanelLayout from '@/Layouts/PanelLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { reactive, ref, watch } from 'vue';

const props = defineProps({ cotizaciones: Object, seguros: Array, filtros: Object });

const moneda = (v) => new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' }).format(v || 0);

const filtros = reactive({ seguro_id: props.filtros.seguro_id ?? '' });
watch(filtros, (val) => router.get(route('cotizaciones.index'), { ...val }, { preserveState: true, replace: true }));

const mostrarModal = ref(false);
const editando = ref(null);
const form = useForm({ seguro_id: '', aseguradora: '', prima: 0, cobertura: 0, condiciones: '' });

const abrirCrear = () => { editando.value = null; form.reset(); if (filtros.seguro_id) form.seguro_id = filtros.seguro_id; form.clearErrors(); mostrarModal.value = true; };
const abrirEditar = (c) => {
    editando.value = c; form.clearErrors();
    Object.assign(form, { seguro_id: c.seguro_id, aseguradora: c.aseguradora, prima: c.prima, cobertura: c.cobertura, condiciones: c.condiciones });
    mostrarModal.value = true;
};
const guardar = () => {
    const opts = { preserveScroll: true, onSuccess: () => (mostrarModal.value = false) };
    editando.value ? form.put(route('cotizaciones.update', editando.value.id), opts) : form.post(route('cotizaciones.store'), opts);
};
const eliminar = (c) => { if (confirm('¿Eliminar esta cotización?')) router.delete(route('cotizaciones.destroy', c.id), { preserveScroll: true }); };
const seleccionar = (c) => router.put(route('cotizaciones.seleccionar', c.id), {}, { preserveScroll: true });

const inputClass = 'w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500';
const etiquetaSeguro = (s) => `${s.poliza} — ${s.asegurado} (${s.tipo})`;
</script>

<template>
    <Head title="Cotizaciones" />
    <PanelLayout>
        <template #titulo>Comparar cotizaciones</template>
        <div class="rounded-lg bg-white p-5 shadow-sm">
            <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <select v-model="filtros.seguro_id" :class="inputClass" class="sm:max-w-md">
                    <option value="">Todas las pólizas</option>
                    <option v-for="s in seguros" :key="s.id" :value="s.id">{{ etiquetaSeguro(s) }}</option>
                </select>
                <button @click="abrirCrear" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">+ Nueva cotización</button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b text-xs uppercase text-gray-500">
                        <tr><th class="py-2 pr-3">Póliza</th><th class="py-2 pr-3">Aseguradora</th><th class="py-2 pr-3 text-right">Prima</th><th class="py-2 pr-3 text-right">Cobertura</th><th class="py-2 pr-3">Seleccionada</th><th></th></tr>
                    </thead>
                    <tbody>
                        <tr v-for="c in cotizaciones.data" :key="c.id" class="border-b border-gray-100" :class="c.seleccionada ? 'bg-green-50' : ''">
                            <td class="py-2 pr-3 font-medium">{{ c.seguro?.poliza }}</td>
                            <td class="py-2 pr-3">{{ c.aseguradora }}</td>
                            <td class="py-2 pr-3 text-right">{{ moneda(c.prima) }}</td>
                            <td class="py-2 pr-3 text-right">{{ moneda(c.cobertura) }}</td>
                            <td class="py-2 pr-3">
                                <span v-if="c.seleccionada" class="rounded-full bg-green-100 px-2 py-0.5 text-xs text-green-800">✔ Elegida</span>
                                <button v-else @click="seleccionar(c)" class="text-xs text-indigo-600 hover:underline">Elegir</button>
                            </td>
                            <td class="py-2 text-right whitespace-nowrap">
                                <button @click="abrirEditar(c)" class="text-indigo-600 hover:underline">Editar</button>
                                <button @click="eliminar(c)" class="ml-3 text-rose-600 hover:underline">Borrar</button>
                            </td>
                        </tr>
                        <tr v-if="!cotizaciones.data.length"><td colspan="6" class="py-6 text-center text-gray-400">No hay cotizaciones registradas.</td></tr>
                    </tbody>
                </table>
            </div>
            <Pagination :links="cotizaciones.links" />
        </div>

        <FormModal :show="mostrarModal" :titulo="editando ? 'Editar cotización' : 'Nueva cotización'" :procesando="form.processing" @close="mostrarModal = false" @submit="guardar">
            <div class="sm:col-span-2">
                <label class="mb-1 block text-sm font-medium text-gray-700">Póliza / Seguro</label>
                <select v-model="form.seguro_id" :class="inputClass">
                    <option value="">— Selecciona —</option>
                    <option v-for="s in seguros" :key="s.id" :value="s.id">{{ etiquetaSeguro(s) }}</option>
                </select>
                <p v-if="form.errors.seguro_id" class="mt-1 text-xs text-rose-600">{{ form.errors.seguro_id }}</p>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Aseguradora</label>
                <input v-model="form.aseguradora" :class="inputClass" />
                <p v-if="form.errors.aseguradora" class="mt-1 text-xs text-rose-600">{{ form.errors.aseguradora }}</p>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Prima</label>
                <input v-model="form.prima" type="number" step="0.01" :class="inputClass" />
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Cobertura</label>
                <input v-model="form.cobertura" type="number" step="0.01" :class="inputClass" />
            </div>
            <div class="sm:col-span-2">
                <label class="mb-1 block text-sm font-medium text-gray-700">Condiciones</label>
                <textarea v-model="form.condiciones" rows="3" :class="inputClass"></textarea>
            </div>
        </FormModal>
    </PanelLayout>
</template>
