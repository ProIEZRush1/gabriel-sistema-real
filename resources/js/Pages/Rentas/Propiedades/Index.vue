<script setup>
import FormModal from '@/Components/FormModal.vue';
import Pagination from '@/Components/Pagination.vue';
import PanelLayout from '@/Layouts/PanelLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { reactive, ref, watch } from 'vue';

const props = defineProps({ propiedades: Object, filtros: Object });
const moneda = (v) => new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' }).format(v || 0);

const filtros = reactive({ buscar: props.filtros.buscar ?? '', estado: props.filtros.estado ?? '' });
watch(filtros, (val) => router.get(route('propiedades.index'), { ...val }, { preserveState: true, replace: true }));

const mostrarModal = ref(false);
const editando = ref(null);
const form = useForm({ nombre: '', tipo: 'departamento', direccion: '', propietario: '', renta_mensual: 0, estado: 'disponible' });

const abrirCrear = () => { editando.value = null; form.reset(); form.clearErrors(); mostrarModal.value = true; };
const abrirEditar = (p) => {
    editando.value = p; form.clearErrors();
    Object.assign(form, { nombre: p.nombre, tipo: p.tipo, direccion: p.direccion, propietario: p.propietario, renta_mensual: p.renta_mensual, estado: p.estado });
    mostrarModal.value = true;
};
const guardar = () => {
    const opts = { preserveScroll: true, onSuccess: () => (mostrarModal.value = false) };
    editando.value ? form.put(route('propiedades.update', editando.value.id), opts) : form.post(route('propiedades.store'), opts);
};
const eliminar = (p) => { if (confirm(`¿Eliminar la propiedad ${p.nombre}?`)) router.delete(route('propiedades.destroy', p.id), { preserveScroll: true }); };

const inputClass = 'w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500';
const estadoColor = { disponible: 'bg-blue-100 text-blue-800', rentada: 'bg-green-100 text-green-800', mantenimiento: 'bg-amber-100 text-amber-800' };
</script>

<template>
    <Head title="Propiedades" />
    <PanelLayout>
        <template #titulo>Propiedades</template>
        <div class="rounded-lg bg-white p-5 shadow-sm">
            <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex flex-1 flex-col gap-2 sm:flex-row">
                    <input v-model="filtros.buscar" type="text" placeholder="Buscar propiedad..." :class="inputClass" class="sm:max-w-xs" />
                    <select v-model="filtros.estado" :class="inputClass" class="sm:max-w-[12rem]">
                        <option value="">Todos los estados</option><option value="disponible">Disponible</option><option value="rentada">Rentada</option><option value="mantenimiento">Mantenimiento</option>
                    </select>
                </div>
                <button @click="abrirCrear" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">+ Nueva propiedad</button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b text-xs uppercase text-gray-500">
                        <tr><th class="py-2 pr-3">Nombre</th><th class="py-2 pr-3">Tipo</th><th class="py-2 pr-3">Dirección</th><th class="py-2 pr-3">Propietario</th><th class="py-2 pr-3 text-right">Renta</th><th class="py-2 pr-3">Estado</th><th class="py-2 pr-3 text-right">Rentas</th><th></th></tr>
                    </thead>
                    <tbody>
                        <tr v-for="p in propiedades.data" :key="p.id" class="border-b border-gray-100">
                            <td class="py-2 pr-3 font-medium">{{ p.nombre }}</td>
                            <td class="py-2 pr-3 capitalize">{{ p.tipo }}</td>
                            <td class="py-2 pr-3">{{ p.direccion ?? '—' }}</td>
                            <td class="py-2 pr-3">{{ p.propietario ?? '—' }}</td>
                            <td class="py-2 pr-3 text-right">{{ moneda(p.renta_mensual) }}</td>
                            <td class="py-2 pr-3"><span :class="estadoColor[p.estado]" class="rounded-full px-2 py-0.5 text-xs capitalize">{{ p.estado }}</span></td>
                            <td class="py-2 pr-3 text-right">{{ p.rentas_count }}</td>
                            <td class="py-2 text-right whitespace-nowrap">
                                <button @click="abrirEditar(p)" class="text-indigo-600 hover:underline">Editar</button>
                                <button @click="eliminar(p)" class="ml-3 text-rose-600 hover:underline">Borrar</button>
                            </td>
                        </tr>
                        <tr v-if="!propiedades.data.length"><td colspan="8" class="py-6 text-center text-gray-400">No hay propiedades registradas.</td></tr>
                    </tbody>
                </table>
            </div>
            <Pagination :links="propiedades.links" />
        </div>

        <FormModal :show="mostrarModal" :titulo="editando ? 'Editar propiedad' : 'Nueva propiedad'" :procesando="form.processing" @close="mostrarModal = false" @submit="guardar">
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Nombre</label>
                <input v-model="form.nombre" :class="inputClass" />
                <p v-if="form.errors.nombre" class="mt-1 text-xs text-rose-600">{{ form.errors.nombre }}</p>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Tipo</label>
                <select v-model="form.tipo" :class="inputClass">
                    <option value="casa">Casa</option><option value="departamento">Departamento</option><option value="local">Local comercial</option><option value="oficina">Oficina</option><option value="terreno">Terreno</option>
                </select>
            </div>
            <div class="sm:col-span-2">
                <label class="mb-1 block text-sm font-medium text-gray-700">Dirección</label>
                <input v-model="form.direccion" :class="inputClass" />
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Propietario</label>
                <input v-model="form.propietario" :class="inputClass" />
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Renta mensual</label>
                <input v-model="form.renta_mensual" type="number" step="0.01" :class="inputClass" />
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Estado</label>
                <select v-model="form.estado" :class="inputClass">
                    <option value="disponible">Disponible</option><option value="rentada">Rentada</option><option value="mantenimiento">Mantenimiento</option>
                </select>
            </div>
        </FormModal>
    </PanelLayout>
</template>
