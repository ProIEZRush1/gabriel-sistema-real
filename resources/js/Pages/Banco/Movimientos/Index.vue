<script setup>
import FormModal from '@/Components/FormModal.vue';
import Pagination from '@/Components/Pagination.vue';
import PanelLayout from '@/Layouts/PanelLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { reactive, ref, watch } from 'vue';

const props = defineProps({ movimientos: Object, auxiliares: Array, filtros: Object, resumen: Object });
const moneda = (v) => new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' }).format(v || 0);

const filtros = reactive({ buscar: props.filtros.buscar ?? '', tipo: props.filtros.tipo ?? '', auxiliar_id: props.filtros.auxiliar_id ?? '' });
watch(filtros, (val) => router.get(route('movimientos.index'), { ...val }, { preserveState: true, replace: true }));

const mostrarModal = ref(false);
const editando = ref(null);
const form = useForm({ auxiliar_id: '', tipo: 'cobro', monto: 0, fecha: '', referencia: '', descripcion: '' });

const abrirCrear = () => { editando.value = null; form.reset(); form.fecha = new Date().toISOString().substring(0, 10); if (filtros.auxiliar_id) form.auxiliar_id = filtros.auxiliar_id; form.clearErrors(); mostrarModal.value = true; };
const abrirEditar = (m) => {
    editando.value = m; form.clearErrors();
    Object.assign(form, { auxiliar_id: m.auxiliar_id, tipo: m.tipo, monto: m.monto, fecha: m.fecha?.substring(0, 10), referencia: m.referencia, descripcion: m.descripcion });
    mostrarModal.value = true;
};
const guardar = () => {
    const opts = { preserveScroll: true, onSuccess: () => (mostrarModal.value = false) };
    editando.value ? form.put(route('movimientos.update', editando.value.id), opts) : form.post(route('movimientos.store'), opts);
};
const eliminar = (m) => { if (confirm('¿Eliminar este movimiento?')) router.delete(route('movimientos.destroy', m.id), { preserveScroll: true }); };

const inputClass = 'w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500';
const tipoColor = { cobro: 'bg-green-100 text-green-800', pago: 'bg-rose-100 text-rose-800', transferencia: 'bg-amber-100 text-amber-800' };
</script>

<template>
    <Head title="Movimientos" />
    <PanelLayout>
        <template #titulo>Movimientos bancarios</template>

        <div class="mb-4 grid grid-cols-1 gap-3 sm:grid-cols-3">
            <div class="rounded-lg bg-white p-4 shadow-sm"><p class="text-xs text-gray-500">Total cobros</p><p class="text-xl font-bold text-green-600">{{ moneda(resumen.cobros) }}</p></div>
            <div class="rounded-lg bg-white p-4 shadow-sm"><p class="text-xs text-gray-500">Total pagos</p><p class="text-xl font-bold text-rose-600">{{ moneda(resumen.pagos) }}</p></div>
            <div class="rounded-lg bg-white p-4 shadow-sm"><p class="text-xs text-gray-500">Total transferencias</p><p class="text-xl font-bold text-amber-600">{{ moneda(resumen.transferencias) }}</p></div>
        </div>

        <div class="rounded-lg bg-white p-5 shadow-sm">
            <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex flex-1 flex-col gap-2 sm:flex-row">
                    <input v-model="filtros.buscar" type="text" placeholder="Buscar referencia..." :class="inputClass" class="sm:max-w-xs" />
                    <select v-model="filtros.tipo" :class="inputClass" class="sm:max-w-[10rem]">
                        <option value="">Todos los tipos</option><option value="cobro">Cobro</option><option value="pago">Pago</option><option value="transferencia">Transferencia</option>
                    </select>
                    <select v-model="filtros.auxiliar_id" :class="inputClass" class="sm:max-w-[14rem]">
                        <option value="">Todos los auxiliares</option>
                        <option v-for="a in auxiliares" :key="a.id" :value="a.id">{{ a.proyecto?.nombre }} · {{ a.nombre }}</option>
                    </select>
                </div>
                <button @click="abrirCrear" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">+ Nuevo movimiento</button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b text-xs uppercase text-gray-500">
                        <tr><th class="py-2 pr-3">Fecha</th><th class="py-2 pr-3">Auxiliar</th><th class="py-2 pr-3">Tipo</th><th class="py-2 pr-3 text-right">Monto</th><th class="py-2 pr-3">Referencia</th><th class="py-2 pr-3">Origen</th><th></th></tr>
                    </thead>
                    <tbody>
                        <tr v-for="m in movimientos.data" :key="m.id" class="border-b border-gray-100">
                            <td class="py-2 pr-3">{{ m.fecha?.substring(0, 10) }}</td>
                            <td class="py-2 pr-3">{{ m.auxiliar?.nombre }}</td>
                            <td class="py-2 pr-3"><span :class="tipoColor[m.tipo]" class="rounded-full px-2 py-0.5 text-xs capitalize">{{ m.tipo }}</span></td>
                            <td class="py-2 pr-3 text-right">{{ moneda(m.monto) }}</td>
                            <td class="py-2 pr-3">{{ m.referencia ?? '—' }}</td>
                            <td class="py-2 pr-3">
                                <span v-if="m.renta_id" class="text-xs text-indigo-600">Renta #{{ m.renta_id }}</span>
                                <span v-else class="text-xs text-gray-400">Manual</span>
                            </td>
                            <td class="py-2 text-right whitespace-nowrap">
                                <button @click="abrirEditar(m)" class="text-indigo-600 hover:underline">Editar</button>
                                <button @click="eliminar(m)" class="ml-3 text-rose-600 hover:underline">Borrar</button>
                            </td>
                        </tr>
                        <tr v-if="!movimientos.data.length"><td colspan="7" class="py-6 text-center text-gray-400">No hay movimientos registrados.</td></tr>
                    </tbody>
                </table>
            </div>
            <Pagination :links="movimientos.links" />
        </div>

        <FormModal :show="mostrarModal" :titulo="editando ? 'Editar movimiento' : 'Nuevo movimiento'" :procesando="form.processing" @close="mostrarModal = false" @submit="guardar">
            <div class="sm:col-span-2">
                <label class="mb-1 block text-sm font-medium text-gray-700">Auxiliar bancario</label>
                <select v-model="form.auxiliar_id" :class="inputClass">
                    <option value="">— Selecciona —</option>
                    <option v-for="a in auxiliares" :key="a.id" :value="a.id">{{ a.proyecto?.nombre }} · {{ a.nombre }}</option>
                </select>
                <p v-if="form.errors.auxiliar_id" class="mt-1 text-xs text-rose-600">{{ form.errors.auxiliar_id }}</p>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Tipo</label>
                <select v-model="form.tipo" :class="inputClass">
                    <option value="cobro">Cobro</option><option value="pago">Pago</option><option value="transferencia">Transferencia</option>
                </select>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Monto</label>
                <input v-model="form.monto" type="number" step="0.01" :class="inputClass" />
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Fecha</label>
                <input v-model="form.fecha" type="date" :class="inputClass" />
                <p v-if="form.errors.fecha" class="mt-1 text-xs text-rose-600">{{ form.errors.fecha }}</p>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Referencia</label>
                <input v-model="form.referencia" :class="inputClass" />
            </div>
            <div class="sm:col-span-2">
                <label class="mb-1 block text-sm font-medium text-gray-700">Descripción</label>
                <textarea v-model="form.descripcion" rows="2" :class="inputClass"></textarea>
            </div>
        </FormModal>
    </PanelLayout>
</template>
