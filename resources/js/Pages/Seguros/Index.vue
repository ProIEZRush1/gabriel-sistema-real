<script setup>
import FormModal from '@/Components/FormModal.vue';
import Pagination from '@/Components/Pagination.vue';
import PanelLayout from '@/Layouts/PanelLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { reactive, ref, watch } from 'vue';

const props = defineProps({
    seguros: Object,
    agentes: Array,
    filtros: Object,
    resumen: Object,
});

const moneda = (v) =>
    new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' }).format(v || 0);

const filtros = reactive({ buscar: props.filtros.buscar ?? '', tipo: props.filtros.tipo ?? '' });
watch(filtros, (val) => {
    router.get(route('seguros.index'), { ...val }, { preserveState: true, replace: true });
});

const mostrarModal = ref(false);
const editando = ref(null);
const form = useForm({
    poliza: '', tipo: 'inmueble', aseguradora: '', asegurado: '', beneficiario: '',
    condiciones: '', suma_asegurada: 0, prima: 0, vigencia_inicio: '', vigencia_fin: '',
    estado: 'activo', agente_id: '',
});

const abrirCrear = () => {
    editando.value = null;
    form.reset();
    form.clearErrors();
    mostrarModal.value = true;
};
const abrirEditar = (s) => {
    editando.value = s;
    form.clearErrors();
    Object.assign(form, {
        poliza: s.poliza, tipo: s.tipo, aseguradora: s.aseguradora, asegurado: s.asegurado,
        beneficiario: s.beneficiario, condiciones: s.condiciones, suma_asegurada: s.suma_asegurada,
        prima: s.prima, vigencia_inicio: s.vigencia_inicio?.substring(0, 10) ?? '',
        vigencia_fin: s.vigencia_fin?.substring(0, 10) ?? '', estado: s.estado,
        agente_id: s.agente_id ?? '',
    });
    mostrarModal.value = true;
};
const guardar = () => {
    const opts = { preserveScroll: true, onSuccess: () => (mostrarModal.value = false) };
    if (editando.value) form.put(route('seguros.update', editando.value.id), opts);
    else form.post(route('seguros.store'), opts);
};
const eliminar = (s) => {
    if (confirm(`¿Eliminar la póliza ${s.poliza}?`)) {
        router.delete(route('seguros.destroy', s.id), { preserveScroll: true });
    }
};

const tipoLabel = { inmueble: 'Inmueble', auto: 'Auto', medico: 'Médico' };
const inputClass = 'w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500';
</script>

<template>
    <Head title="Seguros" />
    <PanelLayout>
        <template #titulo>Seguros</template>

        <div class="mb-4 grid grid-cols-2 gap-3 sm:grid-cols-4">
            <div class="rounded-lg bg-white p-4 shadow-sm"><p class="text-xs text-gray-500">Total</p><p class="text-xl font-bold">{{ resumen.total }}</p></div>
            <div class="rounded-lg bg-white p-4 shadow-sm"><p class="text-xs text-gray-500">Inmuebles</p><p class="text-xl font-bold">{{ resumen.inmueble }}</p></div>
            <div class="rounded-lg bg-white p-4 shadow-sm"><p class="text-xs text-gray-500">Autos</p><p class="text-xl font-bold">{{ resumen.auto }}</p></div>
            <div class="rounded-lg bg-white p-4 shadow-sm"><p class="text-xs text-gray-500">Médicos</p><p class="text-xl font-bold">{{ resumen.medico }}</p></div>
        </div>

        <div class="rounded-lg bg-white p-5 shadow-sm">
            <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex flex-1 flex-col gap-2 sm:flex-row">
                    <input v-model="filtros.buscar" type="text" placeholder="Buscar póliza, asegurado..." :class="inputClass" class="sm:max-w-xs" />
                    <select v-model="filtros.tipo" :class="inputClass" class="sm:max-w-[12rem]">
                        <option value="">Todos los tipos</option>
                        <option value="inmueble">Inmueble</option>
                        <option value="auto">Auto</option>
                        <option value="medico">Médico</option>
                    </select>
                </div>
                <button @click="abrirCrear" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">+ Nuevo seguro</button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b text-xs uppercase text-gray-500">
                        <tr>
                            <th class="py-2 pr-3">Póliza</th><th class="py-2 pr-3">Tipo</th>
                            <th class="py-2 pr-3">Asegurado</th><th class="py-2 pr-3">Agente</th>
                            <th class="py-2 pr-3 text-right">Suma asegurada</th><th class="py-2 pr-3 text-right">Prima</th>
                            <th class="py-2 pr-3">Estado</th><th class="py-2 pr-3 text-right">Cotiz.</th><th class="py-2"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="s in seguros.data" :key="s.id" class="border-b border-gray-100">
                            <td class="py-2 pr-3 font-medium">{{ s.poliza }}</td>
                            <td class="py-2 pr-3">{{ tipoLabel[s.tipo] }}</td>
                            <td class="py-2 pr-3">{{ s.asegurado }}</td>
                            <td class="py-2 pr-3">{{ s.agente?.nombre ?? '—' }}</td>
                            <td class="py-2 pr-3 text-right">{{ moneda(s.suma_asegurada) }}</td>
                            <td class="py-2 pr-3 text-right">{{ moneda(s.prima) }}</td>
                            <td class="py-2 pr-3"><span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs capitalize">{{ s.estado }}</span></td>
                            <td class="py-2 pr-3 text-right">{{ s.cotizaciones_count }}</td>
                            <td class="py-2 text-right whitespace-nowrap">
                                <button @click="abrirEditar(s)" class="text-indigo-600 hover:underline">Editar</button>
                                <button @click="eliminar(s)" class="ml-3 text-rose-600 hover:underline">Borrar</button>
                            </td>
                        </tr>
                        <tr v-if="!seguros.data.length"><td colspan="9" class="py-6 text-center text-gray-400">No hay seguros registrados.</td></tr>
                    </tbody>
                </table>
            </div>
            <Pagination :links="seguros.links" />
        </div>

        <FormModal :show="mostrarModal" :titulo="editando ? 'Editar seguro' : 'Nuevo seguro'" :procesando="form.processing" @close="mostrarModal = false" @submit="guardar">
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Póliza</label>
                <input v-model="form.poliza" :class="inputClass" />
                <p v-if="form.errors.poliza" class="mt-1 text-xs text-rose-600">{{ form.errors.poliza }}</p>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Tipo</label>
                <select v-model="form.tipo" :class="inputClass">
                    <option value="inmueble">Inmueble</option><option value="auto">Auto</option><option value="medico">Médico</option>
                </select>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Aseguradora</label>
                <input v-model="form.aseguradora" :class="inputClass" />
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Asegurado</label>
                <input v-model="form.asegurado" :class="inputClass" />
                <p v-if="form.errors.asegurado" class="mt-1 text-xs text-rose-600">{{ form.errors.asegurado }}</p>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Beneficiario</label>
                <input v-model="form.beneficiario" :class="inputClass" />
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Agente de venta</label>
                <select v-model="form.agente_id" :class="inputClass">
                    <option value="">— Sin agente —</option>
                    <option v-for="a in agentes" :key="a.id" :value="a.id">{{ a.nombre }}</option>
                </select>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Suma asegurada</label>
                <input v-model="form.suma_asegurada" type="number" step="0.01" :class="inputClass" />
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Prima</label>
                <input v-model="form.prima" type="number" step="0.01" :class="inputClass" />
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Vigencia inicio</label>
                <input v-model="form.vigencia_inicio" type="date" :class="inputClass" />
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Vigencia fin</label>
                <input v-model="form.vigencia_fin" type="date" :class="inputClass" />
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Estado</label>
                <select v-model="form.estado" :class="inputClass">
                    <option value="activo">Activo</option><option value="vencido">Vencido</option><option value="cancelado">Cancelado</option>
                </select>
            </div>
            <div class="sm:col-span-2">
                <label class="mb-1 block text-sm font-medium text-gray-700">Condiciones</label>
                <textarea v-model="form.condiciones" rows="3" :class="inputClass"></textarea>
            </div>
        </FormModal>
    </PanelLayout>
</template>
