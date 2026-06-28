<script setup>
import FormModal from '@/Components/FormModal.vue';
import Pagination from '@/Components/Pagination.vue';
import PanelLayout from '@/Layouts/PanelLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { reactive, ref, watch } from 'vue';

const props = defineProps({ proyectos: Object, filtros: Object });

const filtros = reactive({ buscar: props.filtros.buscar ?? '' });
watch(filtros, (val) => router.get(route('proyectos.index'), { ...val }, { preserveState: true, replace: true }));

const mostrarModal = ref(false);
const editando = ref(null);
const form = useForm({ nombre: '', descripcion: '' });

const abrirCrear = () => { editando.value = null; form.reset(); form.clearErrors(); mostrarModal.value = true; };
const abrirEditar = (p) => { editando.value = p; form.clearErrors(); Object.assign(form, { nombre: p.nombre, descripcion: p.descripcion }); mostrarModal.value = true; };
const guardar = () => {
    const opts = { preserveScroll: true, onSuccess: () => (mostrarModal.value = false) };
    editando.value ? form.put(route('proyectos.update', editando.value.id), opts) : form.post(route('proyectos.store'), opts);
};
const eliminar = (p) => { if (confirm(`¿Eliminar el proyecto ${p.nombre}?`)) router.delete(route('proyectos.destroy', p.id), { preserveScroll: true }); };

const inputClass = 'w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500';
</script>

<template>
    <Head title="Proyectos" />
    <PanelLayout>
        <template #titulo>Proyectos</template>
        <div class="rounded-lg bg-white p-5 shadow-sm">
            <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <input v-model="filtros.buscar" type="text" placeholder="Buscar proyecto..." :class="inputClass" class="sm:max-w-xs" />
                <button @click="abrirCrear" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">+ Nuevo proyecto</button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b text-xs uppercase text-gray-500">
                        <tr><th class="py-2 pr-3">Nombre</th><th class="py-2 pr-3">Descripción</th><th class="py-2 pr-3 text-right">Auxiliares</th><th></th></tr>
                    </thead>
                    <tbody>
                        <tr v-for="p in proyectos.data" :key="p.id" class="border-b border-gray-100">
                            <td class="py-2 pr-3 font-medium">{{ p.nombre }}</td>
                            <td class="py-2 pr-3 text-gray-600">{{ p.descripcion ?? '—' }}</td>
                            <td class="py-2 pr-3 text-right">{{ p.auxiliares_count }}</td>
                            <td class="py-2 text-right whitespace-nowrap">
                                <button @click="abrirEditar(p)" class="text-indigo-600 hover:underline">Editar</button>
                                <button @click="eliminar(p)" class="ml-3 text-rose-600 hover:underline">Borrar</button>
                            </td>
                        </tr>
                        <tr v-if="!proyectos.data.length"><td colspan="4" class="py-6 text-center text-gray-400">No hay proyectos registrados.</td></tr>
                    </tbody>
                </table>
            </div>
            <Pagination :links="proyectos.links" />
        </div>

        <FormModal :show="mostrarModal" :titulo="editando ? 'Editar proyecto' : 'Nuevo proyecto'" :procesando="form.processing" @close="mostrarModal = false" @submit="guardar">
            <div class="sm:col-span-2">
                <label class="mb-1 block text-sm font-medium text-gray-700">Nombre</label>
                <input v-model="form.nombre" :class="inputClass" />
                <p v-if="form.errors.nombre" class="mt-1 text-xs text-rose-600">{{ form.errors.nombre }}</p>
            </div>
            <div class="sm:col-span-2">
                <label class="mb-1 block text-sm font-medium text-gray-700">Descripción</label>
                <textarea v-model="form.descripcion" rows="3" :class="inputClass"></textarea>
            </div>
        </FormModal>
    </PanelLayout>
</template>
