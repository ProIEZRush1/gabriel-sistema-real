<script setup>
import FormModal from '@/Components/FormModal.vue';
import Pagination from '@/Components/Pagination.vue';
import PanelLayout from '@/Layouts/PanelLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { reactive, ref, watch } from 'vue';

const props = defineProps({ inquilinos: Object, filtros: Object });

const filtros = reactive({ buscar: props.filtros.buscar ?? '' });
watch(filtros, (val) => router.get(route('inquilinos.index'), { ...val }, { preserveState: true, replace: true }));

const mostrarModal = ref(false);
const editando = ref(null);
const form = useForm({ nombre: '', email: '', telefono: '', identificacion: '' });

const abrirCrear = () => { editando.value = null; form.reset(); form.clearErrors(); mostrarModal.value = true; };
const abrirEditar = (i) => {
    editando.value = i; form.clearErrors();
    Object.assign(form, { nombre: i.nombre, email: i.email, telefono: i.telefono, identificacion: i.identificacion });
    mostrarModal.value = true;
};
const guardar = () => {
    const opts = { preserveScroll: true, onSuccess: () => (mostrarModal.value = false) };
    editando.value ? form.put(route('inquilinos.update', editando.value.id), opts) : form.post(route('inquilinos.store'), opts);
};
const eliminar = (i) => { if (confirm(`¿Eliminar al inquilino ${i.nombre}?`)) router.delete(route('inquilinos.destroy', i.id), { preserveScroll: true }); };

const inputClass = 'w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500';
</script>

<template>
    <Head title="Inquilinos" />
    <PanelLayout>
        <template #titulo>Inquilinos</template>
        <div class="rounded-lg bg-white p-5 shadow-sm">
            <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <input v-model="filtros.buscar" type="text" placeholder="Buscar inquilino..." :class="inputClass" class="sm:max-w-xs" />
                <button @click="abrirCrear" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">+ Nuevo inquilino</button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b text-xs uppercase text-gray-500">
                        <tr><th class="py-2 pr-3">Nombre</th><th class="py-2 pr-3">Email</th><th class="py-2 pr-3">Teléfono</th><th class="py-2 pr-3">Identificación</th><th class="py-2 pr-3 text-right">Rentas</th><th></th></tr>
                    </thead>
                    <tbody>
                        <tr v-for="i in inquilinos.data" :key="i.id" class="border-b border-gray-100">
                            <td class="py-2 pr-3 font-medium">{{ i.nombre }}</td>
                            <td class="py-2 pr-3">{{ i.email ?? '—' }}</td>
                            <td class="py-2 pr-3">{{ i.telefono ?? '—' }}</td>
                            <td class="py-2 pr-3">{{ i.identificacion ?? '—' }}</td>
                            <td class="py-2 pr-3 text-right">{{ i.rentas_count }}</td>
                            <td class="py-2 text-right whitespace-nowrap">
                                <button @click="abrirEditar(i)" class="text-indigo-600 hover:underline">Editar</button>
                                <button @click="eliminar(i)" class="ml-3 text-rose-600 hover:underline">Borrar</button>
                            </td>
                        </tr>
                        <tr v-if="!inquilinos.data.length"><td colspan="6" class="py-6 text-center text-gray-400">No hay inquilinos registrados.</td></tr>
                    </tbody>
                </table>
            </div>
            <Pagination :links="inquilinos.links" />
        </div>

        <FormModal :show="mostrarModal" :titulo="editando ? 'Editar inquilino' : 'Nuevo inquilino'" :procesando="form.processing" @close="mostrarModal = false" @submit="guardar">
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
                <label class="mb-1 block text-sm font-medium text-gray-700">Identificación (RFC/INE)</label>
                <input v-model="form.identificacion" :class="inputClass" />
            </div>
        </FormModal>
    </PanelLayout>
</template>
