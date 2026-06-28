<script setup>
import FormModal from '@/Components/FormModal.vue';
import Modal from '@/Components/Modal.vue';
import Pagination from '@/Components/Pagination.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import PanelLayout from '@/Layouts/PanelLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { reactive, ref, watch } from 'vue';

const props = defineProps({ rentas: Object, propiedades: Array, inquilinos: Array, auxiliares: Array, filtros: Object, reporte: Object });
const moneda = (v) => new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' }).format(v || 0);

const filtros = reactive({ buscar: props.filtros.buscar ?? '', estado: props.filtros.estado ?? '' });
watch(filtros, (val) => router.get(route('rentas.index'), { ...val }, { preserveState: true, replace: true }));

const mostrarModal = ref(false);
const editando = ref(null);
const form = useForm({ propiedad_id: '', inquilino_id: '', periodo: '', monto: 0, fecha_emision: '', fecha_vencimiento: '', tasa_moratoria: 0 });

const abrirCrear = () => { editando.value = null; form.reset(); form.clearErrors(); mostrarModal.value = true; };
const abrirEditar = (r) => {
    editando.value = r; form.clearErrors();
    Object.assign(form, {
        propiedad_id: r.propiedad_id, inquilino_id: r.inquilino_id, periodo: r.periodo, monto: r.monto,
        fecha_emision: r.fecha_emision?.substring(0, 10), fecha_vencimiento: r.fecha_vencimiento?.substring(0, 10),
        tasa_moratoria: r.tasa_moratoria,
    });
    mostrarModal.value = true;
};
const onPropiedadChange = () => {
    const p = props.propiedades.find((x) => x.id === Number(form.propiedad_id));
    if (p && !editando.value) form.monto = p.renta_mensual;
};
const guardar = () => {
    const opts = { preserveScroll: true, onSuccess: () => (mostrarModal.value = false) };
    editando.value ? form.put(route('rentas.update', editando.value.id), opts) : form.post(route('rentas.store'), opts);
};
const eliminar = (r) => { if (confirm(`¿Eliminar la renta ${r.periodo}?`)) router.delete(route('rentas.destroy', r.id), { preserveScroll: true }); };

// Cobro
const mostrarCobro = ref(false);
const rentaCobro = ref(null);
const formCobro = useForm({ fecha_pago: '', auxiliar_id: '' });
const abrirCobro = (r) => {
    rentaCobro.value = r; formCobro.reset(); formCobro.clearErrors();
    formCobro.fecha_pago = new Date().toISOString().substring(0, 10);
    mostrarCobro.value = true;
};
const confirmarCobro = () => {
    formCobro.put(route('rentas.cobrar', rentaCobro.value.id), { preserveScroll: true, onSuccess: () => (mostrarCobro.value = false) });
};

const inputClass = 'w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500';
const estadoColor = { generada: 'bg-blue-100 text-blue-800', cobrada: 'bg-green-100 text-green-800', con_adeudo: 'bg-rose-100 text-rose-800' };
const estadoLabel = { generada: 'Generada', cobrada: 'Cobrada', con_adeudo: 'Con adeudo' };
</script>

<template>
    <Head title="Rentas" />
    <PanelLayout>
        <template #titulo>Rentas y reportes</template>

        <!-- Reporte -->
        <div class="mb-4 grid grid-cols-2 gap-3 sm:grid-cols-5">
            <div class="rounded-lg bg-white p-4 shadow-sm"><p class="text-xs text-gray-500">Generadas</p><p class="text-xl font-bold text-blue-600">{{ reporte.generadas }}</p></div>
            <div class="rounded-lg bg-white p-4 shadow-sm"><p class="text-xs text-gray-500">Cobradas</p><p class="text-xl font-bold text-green-600">{{ reporte.cobradas }}</p></div>
            <div class="rounded-lg bg-white p-4 shadow-sm"><p class="text-xs text-gray-500">Con adeudo</p><p class="text-xl font-bold text-rose-600">{{ reporte.con_adeudo }}</p></div>
            <div class="rounded-lg bg-white p-4 shadow-sm"><p class="text-xs text-gray-500">Total cobrado</p><p class="text-lg font-bold text-emerald-600">{{ moneda(reporte.total_cobrado) }}</p></div>
            <div class="rounded-lg bg-white p-4 shadow-sm"><p class="text-xs text-gray-500">Interés moratorio</p><p class="text-lg font-bold text-rose-600">{{ moneda(reporte.interes_acumulado) }}</p></div>
        </div>

        <div class="rounded-lg bg-white p-5 shadow-sm">
            <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex flex-1 flex-col gap-2 sm:flex-row">
                    <input v-model="filtros.buscar" type="text" placeholder="Buscar por periodo, propiedad, inquilino..." :class="inputClass" class="sm:max-w-xs" />
                    <select v-model="filtros.estado" :class="inputClass" class="sm:max-w-[12rem]">
                        <option value="">Todos los estados</option><option value="generada">Generada</option><option value="cobrada">Cobrada</option><option value="con_adeudo">Con adeudo</option>
                    </select>
                </div>
                <button @click="abrirCrear" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">+ Generar renta</button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b text-xs uppercase text-gray-500">
                        <tr><th class="py-2 pr-3">Periodo</th><th class="py-2 pr-3">Propiedad</th><th class="py-2 pr-3">Inquilino</th><th class="py-2 pr-3 text-right">Monto</th><th class="py-2 pr-3 text-right">Vence</th><th class="py-2 pr-3 text-right">Días atraso</th><th class="py-2 pr-3 text-right">Interés mora.</th><th class="py-2 pr-3">Estado</th><th></th></tr>
                    </thead>
                    <tbody>
                        <tr v-for="r in rentas.data" :key="r.id" class="border-b border-gray-100">
                            <td class="py-2 pr-3 font-medium">{{ r.periodo }}</td>
                            <td class="py-2 pr-3">{{ r.propiedad?.nombre }}</td>
                            <td class="py-2 pr-3">{{ r.inquilino?.nombre }}</td>
                            <td class="py-2 pr-3 text-right">{{ moneda(r.monto) }}</td>
                            <td class="py-2 pr-3 text-right">{{ r.fecha_vencimiento?.substring(0, 10) }}</td>
                            <td class="py-2 pr-3 text-right" :class="r.dias_atraso > 0 ? 'text-rose-600 font-semibold' : ''">{{ r.dias_atraso }}</td>
                            <td class="py-2 pr-3 text-right">{{ moneda(r.estado === 'cobrada' ? r.interes_moratorio : r.interes_calculado) }}</td>
                            <td class="py-2 pr-3"><span :class="estadoColor[r.estado]" class="rounded-full px-2 py-0.5 text-xs">{{ estadoLabel[r.estado] }}</span></td>
                            <td class="py-2 text-right whitespace-nowrap">
                                <button v-if="r.estado !== 'cobrada'" @click="abrirCobro(r)" class="text-emerald-600 hover:underline">Cobrar</button>
                                <button @click="abrirEditar(r)" class="ml-3 text-indigo-600 hover:underline">Editar</button>
                                <button @click="eliminar(r)" class="ml-3 text-rose-600 hover:underline">Borrar</button>
                            </td>
                        </tr>
                        <tr v-if="!rentas.data.length"><td colspan="9" class="py-6 text-center text-gray-400">No hay rentas registradas.</td></tr>
                    </tbody>
                </table>
            </div>
            <Pagination :links="rentas.links" />
        </div>

        <!-- Modal crear/editar -->
        <FormModal :show="mostrarModal" :titulo="editando ? 'Editar renta' : 'Generar renta'" :procesando="form.processing" @close="mostrarModal = false" @submit="guardar">
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Propiedad</label>
                <select v-model="form.propiedad_id" @change="onPropiedadChange" :class="inputClass">
                    <option value="">— Selecciona —</option>
                    <option v-for="p in propiedades" :key="p.id" :value="p.id">{{ p.nombre }}</option>
                </select>
                <p v-if="form.errors.propiedad_id" class="mt-1 text-xs text-rose-600">{{ form.errors.propiedad_id }}</p>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Inquilino</label>
                <select v-model="form.inquilino_id" :class="inputClass">
                    <option value="">— Selecciona —</option>
                    <option v-for="i in inquilinos" :key="i.id" :value="i.id">{{ i.nombre }}</option>
                </select>
                <p v-if="form.errors.inquilino_id" class="mt-1 text-xs text-rose-600">{{ form.errors.inquilino_id }}</p>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Periodo (ej. 2026-06)</label>
                <input v-model="form.periodo" placeholder="2026-06" :class="inputClass" />
                <p v-if="form.errors.periodo" class="mt-1 text-xs text-rose-600">{{ form.errors.periodo }}</p>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Monto</label>
                <input v-model="form.monto" type="number" step="0.01" :class="inputClass" />
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Fecha de emisión</label>
                <input v-model="form.fecha_emision" type="date" :class="inputClass" />
                <p v-if="form.errors.fecha_emision" class="mt-1 text-xs text-rose-600">{{ form.errors.fecha_emision }}</p>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Fecha de vencimiento</label>
                <input v-model="form.fecha_vencimiento" type="date" :class="inputClass" />
                <p v-if="form.errors.fecha_vencimiento" class="mt-1 text-xs text-rose-600">{{ form.errors.fecha_vencimiento }}</p>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Tasa moratoria (% mensual)</label>
                <input v-model="form.tasa_moratoria" type="number" step="0.01" :class="inputClass" />
            </div>
        </FormModal>

        <!-- Modal cobro -->
        <Modal :show="mostrarCobro" max-width="lg" @close="mostrarCobro = false">
            <form @submit.prevent="confirmarCobro" class="p-6">
                <h2 class="mb-1 text-lg font-semibold text-gray-800">Cobrar renta</h2>
                <p v-if="rentaCobro" class="mb-4 text-sm text-gray-500">
                    {{ rentaCobro.propiedad?.nombre }} · {{ rentaCobro.periodo }} · Monto {{ moneda(rentaCobro.monto) }}
                    <span v-if="rentaCobro.dias_atraso > 0" class="block text-rose-600">
                        {{ rentaCobro.dias_atraso }} días de atraso · Interés moratorio: {{ moneda(rentaCobro.interes_calculado) }} · Total: {{ moneda(rentaCobro.total_a_pagar) }}
                    </span>
                </p>
                <div class="space-y-4">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Fecha de pago</label>
                        <input v-model="formCobro.fecha_pago" type="date" :class="inputClass" />
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Registrar en auxiliar bancario (opcional)</label>
                        <select v-model="formCobro.auxiliar_id" :class="inputClass">
                            <option value="">— No registrar movimiento —</option>
                            <option v-for="a in auxiliares" :key="a.id" :value="a.id">{{ a.proyecto?.nombre }} · {{ a.nombre }}</option>
                        </select>
                        <p class="mt-1 text-xs text-gray-400">Si eliges un auxiliar, se genera un movimiento de cobro interconectado.</p>
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton type="button" @click="mostrarCobro = false">Cancelar</SecondaryButton>
                    <PrimaryButton :disabled="formCobro.processing">Confirmar cobro</PrimaryButton>
                </div>
            </form>
        </Modal>
    </PanelLayout>
</template>
