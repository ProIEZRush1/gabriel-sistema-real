<script setup>
import PanelLayout from '@/Layouts/PanelLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    metricas: { type: Object, default: () => ({}) },
    ultimas_rentas: { type: Array, default: () => [] },
    ultimos_movimientos: { type: Array, default: () => [] },
});

const moneda = (v) =>
    new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' }).format(v || 0);

const tarjetas = [
    { titulo: 'Seguros activos', clave: 'seguros', icono: '🛡️', ruta: 'seguros.index', color: 'bg-indigo-500' },
    { titulo: 'Agentes de venta', clave: 'agentes', icono: '🧑‍💼', ruta: 'agentes.index', color: 'bg-violet-500' },
    { titulo: 'Propiedades', clave: 'propiedades', icono: '🏢', ruta: 'propiedades.index', color: 'bg-emerald-500' },
    { titulo: 'Rentas registradas', clave: 'rentas', icono: '💰', ruta: 'rentas.index', color: 'bg-amber-500' },
    { titulo: 'Rentas con adeudo', clave: 'rentas_con_adeudo', icono: '⚠️', ruta: 'rentas.index', color: 'bg-rose-500' },
    { titulo: 'Proyectos', clave: 'proyectos', icono: '📁', ruta: 'proyectos.index', color: 'bg-sky-500' },
    { titulo: 'Auxiliares bancarios', clave: 'auxiliares', icono: '🏦', ruta: 'auxiliares.index', color: 'bg-teal-500' },
    { titulo: 'Movimientos', clave: 'movimientos', icono: '🔁', ruta: 'movimientos.index', color: 'bg-fuchsia-500' },
];

const estadoColor = {
    generada: 'bg-blue-100 text-blue-800',
    cobrada: 'bg-green-100 text-green-800',
    con_adeudo: 'bg-rose-100 text-rose-800',
};
</script>

<template>
    <Head title="Dashboard" />

    <PanelLayout>
        <template #titulo>Panel de administración</template>

        <!-- Tarjetas de métricas -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <Link
                v-for="t in tarjetas"
                :key="t.clave"
                :href="route(t.ruta)"
                class="flex items-center gap-4 rounded-lg border border-gray-100 bg-white p-5 shadow-sm transition hover:shadow-md"
            >
                <div :class="t.color" class="flex h-12 w-12 items-center justify-center rounded-lg text-2xl">
                    {{ t.icono }}
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-800">{{ metricas[t.clave] ?? 0 }}</p>
                    <p class="text-sm text-gray-500">{{ t.titulo }}</p>
                </div>
            </Link>
        </div>

        <!-- Indicadores financieros -->
        <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div class="rounded-lg border border-gray-100 bg-white p-5 shadow-sm">
                <p class="text-sm text-gray-500">Total cobrado (rentas)</p>
                <p class="mt-1 text-2xl font-bold text-emerald-600">{{ moneda(metricas.total_cobrado) }}</p>
            </div>
            <div class="rounded-lg border border-gray-100 bg-white p-5 shadow-sm">
                <p class="text-sm text-gray-500">Interés moratorio acumulado</p>
                <p class="mt-1 text-2xl font-bold text-rose-600">{{ moneda(metricas.interes_acumulado) }}</p>
            </div>
            <div class="rounded-lg border border-gray-100 bg-white p-5 shadow-sm">
                <p class="text-sm text-gray-500">Suma asegurada total</p>
                <p class="mt-1 text-2xl font-bold text-indigo-600">{{ moneda(metricas.suma_asegurada) }}</p>
            </div>
        </div>

        <!-- Listas recientes -->
        <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-2">
            <div class="rounded-lg border border-gray-100 bg-white p-5 shadow-sm">
                <h3 class="mb-3 font-semibold text-gray-800">Últimas rentas</h3>
                <table class="w-full text-sm">
                    <tbody>
                        <tr v-for="r in ultimas_rentas" :key="r.id" class="border-t border-gray-100">
                            <td class="py-2">{{ r.propiedad?.nombre }}</td>
                            <td class="py-2 text-gray-500">{{ r.periodo }}</td>
                            <td class="py-2 text-right">{{ moneda(r.monto) }}</td>
                            <td class="py-2 text-right">
                                <span class="rounded-full px-2 py-0.5 text-xs" :class="estadoColor[r.estado]">{{ r.estado }}</span>
                            </td>
                        </tr>
                        <tr v-if="!ultimas_rentas.length"><td class="py-2 text-gray-400">Sin registros.</td></tr>
                    </tbody>
                </table>
            </div>
            <div class="rounded-lg border border-gray-100 bg-white p-5 shadow-sm">
                <h3 class="mb-3 font-semibold text-gray-800">Últimos movimientos bancarios</h3>
                <table class="w-full text-sm">
                    <tbody>
                        <tr v-for="m in ultimos_movimientos" :key="m.id" class="border-t border-gray-100">
                            <td class="py-2 capitalize">{{ m.tipo }}</td>
                            <td class="py-2 text-gray-500">{{ m.referencia }}</td>
                            <td class="py-2 text-right">{{ moneda(m.monto) }}</td>
                        </tr>
                        <tr v-if="!ultimos_movimientos.length"><td class="py-2 text-gray-400">Sin registros.</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </PanelLayout>
</template>
