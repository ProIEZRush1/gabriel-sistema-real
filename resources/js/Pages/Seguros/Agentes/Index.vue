<script setup>
import FormModal from '@/Components/FormModal.vue';
import Pagination from '@/Components/Pagination.vue';
import PanelLayout from '@/Layouts/PanelLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { reactive, ref, watch } from 'vue';

const props = defineProps({ agentes: Object, filtros: Object });

const filtros = reactive({ buscar: props.filtros.buscar ?? '' });
watch(filtros, (val) => router.get(route('agentes.index'), { ...val }, { preserveState: true, replace: true }));

const mostrarModal = ref(false);
const editando = ref(null);
const form = useForm({ nombre: '', email: '', telefono: '', comision: 0, activo: true });

const abrirCrear = () => { editando.value = null; form.reset(); form.clearErrors(); mostrarModal.value = true; };
const abrirEditar = (a) => {
    editando.value = a; form.clearErrors();
    Object.assign(form, { nombre: a.nombre, email: a.email, telefono: a.telefono, comision: a.comision, activo: a.activo });
    mostrarModal.value = true;
};
const guardar = () => {
    const opts = { preserveScroll: true, onSuccess: () => (mostrarModal.value = false) };
    editando.value ? form.put(route('agentes.update', editando.value.id), opts) : form.post(route('agentes.store'), opts);
};
const eliminar = (a) => { if (confirm(`¿Eliminar al agente ${a.nombre}?`)) router.delete(route('agentes.destroy', a.id), { preserveScroll: true }); };

const inputClass = 'w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500';
</script>

<template>
    <Head title="Agentes de venta" />
    <PanelLayout>
        <template #titulo>Agentes de venta</template>
        <div class="rounded-lg bg-white p-5 shadow-sm">
            <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <input v-model="filtros.buscar" type="text" placeholder="Buscar agente..." :class="inputClass" class="sm:max-w-xs" />
                <button @click="abrirCrear" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">+ Nuevo agente</button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b text-xs uppercase text-gray-500">
                        <tr><th class="py-2 pr-3">Nombre</th><th class="py-2 pr-3">Email</th><th class="py-2 pr-3">Teléfono</th><th class="py-2 pr-3 text-right">Comisión</th><th class="py-2 pr-3 text-right">Seguros</th><th class="py-2 pr-3">Activo</th><th></th></tr>
                    </thead>
                    <tbody>
                        <tr v-for="a in agentes.data" :key="a.id" class="border-b border-gray-100">
                            <td class="py-2 pr-3 font-medium">{{ a.nombre }}</td>
                            <td class="py-2 pr-3">{{ a.email ?? '—' }}</td>
                            <td class="py-2 pr-3">{{ a.telefono ?? '—' }}</td>
                            <td class="py-2 pr-3 text-right">{{ a.comision }}%</td>
                            <td class="py-2 pr-3 text-right">{{ a.seguros_count }}</td>
                            <td class="py-2 pr-3"><span :class="a.activo ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600'" class="rounded-full px-2 py-0.5 text-xs">{{ a.activo ? 'Sí' : 'No' }}</span></td>
                            <td class="py-2 text-right whitespace-nowrap">
                                <button @click="abrirEditar(a)" class="text-indigo-600 hover:underline">Editar</button>
                                <button @click="eliminar(a)" class="ml-3 text-rose-600 hover:underline">Borrar</button>
                            </td>
                        </tr>
                        <tr v-if="!agentes.data.length"><td colspan="7" class="py-6 text-center text-gray-400">No hay agentes registrados.</td></tr>
                    </tbody>
                </table>
            </div>
            <Pagination :links="agentes.links" />
        </div>

        <FormModal :show="mostrarModal" :titulo="editando ? 'Editar agente' : 'Nuevo agente'" :procesando="form.processing" @close="mostrarModal = false" @submit="guardar">
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Nombre</label>
                <input v-model="form.nombre" :class="inputClass" />
                <p v-if="form.errors.nombre" class="mt-1 text-xs text-rose-600">{{ form.errors.nombre }}</p>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Email</label>
                <input v-model="form.email" type="email" :class="inputClass" />
                <p v-if="form.errors.email" class="mt-1 text-xs text-rose-600">{{ form.errors.email }}</p>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Teléfono</label>
                <input v-model="form.telefono" :class="inputClass" />
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Comisión (%)</label>
                <input v-model="form.comision" type="number" step="0.01" :class="inputClass" />
            </div>
            <div class="flex items-center gap-2 sm:col-span-2">
                <input v-model="form.activo" type="checkbox" id="activo" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
                <label for="activo" class="text-sm text-gray-700">Agente activo</label>
            </div>
        </FormModal>
    </PanelLayout>
</template>
