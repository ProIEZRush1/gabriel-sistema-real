<script setup>
import FormModal from '@/Components/FormModal.vue';
import Pagination from '@/Components/Pagination.vue';
import PanelLayout from '@/Layouts/PanelLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { reactive, ref, watch } from 'vue';

const props = defineProps({ auxiliares: Object, proyectos: Array, filtros: Object });
const moneda = (v) => new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' }).format(v || 0);

const filtros = reactive({ buscar: props.filtros.buscar ?? '', proyecto_id: props.filtros.proyecto_id ?? '' });
watch(filtros, (val) => router.get(route('auxiliares.index'), { ...val }, { preserveState: true, replace: true }));

const mostrarModal = ref(false);
const editando = ref(null);
const form = useForm({ proyecto_id: '', nombre: '', banco: '', numero_cuenta: '', saldo_inicial: 0 });

const abrirCrear = () => { editando.value = null; form.reset(); if (filtros.proyecto_id) form.proyecto_id = filtros.proyecto_id; form.clearErrors(); mostrarModal.value = true; };
const abrirEditar = (a) => {
    editando.value = a; form.clearErrors();
    Object.assign(form, { proyecto_id: a.proyecto_id, nombre: a.nombre, banco: a.banco, numero_cuenta: a.numero_cuenta, saldo_inicial: a.saldo_inicial });
    mostrarModal.value = true;
};
const guardar = () => {
    const opts = { preserveScroll: true, onSuccess: () => (mostrarModal.value = false) };
    editando.value ? form.put(route('auxiliares.update', editando.value.id), opts) : form.post(route('auxiliares.store'), opts);
};
const eliminar = (a) => { if (confirm(`¿Eliminar el auxiliar ${a.nombre}?`)) router.delete(route('auxiliares.destroy', a.id), { preserveScroll: true }); };

const inputClass = 'w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500';
</script>

<template>
    <Head title="Auxiliares bancarios" />
    <PanelLayout>
        <template #titulo>Auxiliares bancarios</template>
        <div class="rounded-lg bg-white p-5 shadow-sm">
            <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex flex-1 flex-col gap-2 sm:flex-row">
                    <input v-model="filtros.buscar" type="text" placeholder="Buscar auxiliar o banco..." :class="inputClass" class="sm:max-w-xs" />
                    <select v-model="filtros.proyecto_id" :class="inputClass" class="sm:max-w-[14rem]">
                        <option value="">Todos los proyectos</option>
                        <option v-for="p in proyectos" :key="p.id" :value="p.id">{{ p.nombre }}</option>
                    </select>
                </div>
                <button @click="abrirCrear" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">+ Nuevo auxiliar</button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b text-xs uppercase text-gray-500">
                        <tr><th class="py-2 pr-3">Proyecto</th><th class="py-2 pr-3">Auxiliar</th><th class="py-2 pr-3">Banco</th><th class="py-2 pr-3">Cuenta</th><th class="py-2 pr-3 text-right">Saldo inicial</th><th class="py-2 pr-3 text-right">Saldo actual</th><th class="py-2 pr-3 text-right">Movs.</th><th></th></tr>
                    </thead>
                    <tbody>
                        <tr v-for="a in auxiliares.data" :key="a.id" class="border-b border-gray-100">
                            <td class="py-2 pr-3">{{ a.proyecto?.nombre }}</td>
                            <td class="py-2 pr-3 font-medium">{{ a.nombre }}</td>
                            <td class="py-2 pr-3">{{ a.banco ?? '—' }}</td>
                            <td class="py-2 pr-3">{{ a.numero_cuenta ?? '—' }}</td>
                            <td class="py-2 pr-3 text-right">{{ moneda(a.saldo_inicial) }}</td>
                            <td class="py-2 pr-3 text-right font-semibold" :class="a.saldo_actual < 0 ? 'text-rose-600' : 'text-emerald-600'">{{ moneda(a.saldo_actual) }}</td>
                            <td class="py-2 pr-3 text-right">{{ a.movimientos_count }}</td>
                            <td class="py-2 text-right whitespace-nowrap">
                                <button @click="abrirEditar(a)" class="text-indigo-600 hover:underline">Editar</button>
                                <button @click="eliminar(a)" class="ml-3 text-rose-600 hover:underline">Borrar</button>
                            </td>
                        </tr>
                        <tr v-if="!auxiliares.data.length"><td colspan="8" class="py-6 text-center text-gray-400">No hay auxiliares registrados.</td></tr>
                    </tbody>
                </table>
            </div>
            <Pagination :links="auxiliares.links" />
        </div>

        <FormModal :show="mostrarModal" :titulo="editando ? 'Editar auxiliar' : 'Nuevo auxiliar'" :procesando="form.processing" @close="mostrarModal = false" @submit="guardar">
            <div class="sm:col-span-2">
                <label class="mb-1 block text-sm font-medium text-gray-700">Proyecto</label>
                <select v-model="form.proyecto_id" :class="inputClass">
                    <option value="">— Selecciona —</option>
                    <option v-for="p in proyectos" :key="p.id" :value="p.id">{{ p.nombre }}</option>
                </select>
                <p v-if="form.errors.proyecto_id" class="mt-1 text-xs text-rose-600">{{ form.errors.proyecto_id }}</p>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Nombre del auxiliar</label>
                <input v-model="form.nombre" :class="inputClass" />
                <p v-if="form.errors.nombre" class="mt-1 text-xs text-rose-600">{{ form.errors.nombre }}</p>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Banco</label>
                <input v-model="form.banco" :class="inputClass" />
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Número de cuenta</label>
                <input v-model="form.numero_cuenta" :class="inputClass" />
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Saldo inicial</label>
                <input v-model="form.saldo_inicial" type="number" step="0.01" :class="inputClass" />
            </div>
        </FormModal>
    </PanelLayout>
</template>
