<script setup>
import { ref } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import { Link, usePage } from '@inertiajs/vue3';

const showingSidebar = ref(false);
const page = usePage();

const secciones = [
    {
        titulo: 'General',
        items: [{ nombre: 'Dashboard', ruta: 'dashboard', icono: '📊' }],
    },
    {
        titulo: 'Seguros',
        items: [
            { nombre: 'Pólizas', ruta: 'seguros.index', icono: '🛡️' },
            { nombre: 'Cotizaciones', ruta: 'cotizaciones.index', icono: '📑' },
            { nombre: 'Agentes de venta', ruta: 'agentes.index', icono: '🧑‍💼' },
        ],
    },
    {
        titulo: 'Rentas',
        items: [
            { nombre: 'Rentas y reportes', ruta: 'rentas.index', icono: '💰' },
            { nombre: 'Propiedades', ruta: 'propiedades.index', icono: '🏢' },
            { nombre: 'Inquilinos', ruta: 'inquilinos.index', icono: '👥' },
        ],
    },
    {
        titulo: 'Auxiliar Bancario',
        items: [
            { nombre: 'Movimientos', ruta: 'movimientos.index', icono: '🔁' },
            { nombre: 'Auxiliares', ruta: 'auxiliares.index', icono: '🏦' },
            { nombre: 'Proyectos', ruta: 'proyectos.index', icono: '📁' },
        ],
    },
];

const esActiva = (ruta) => route().current(ruta);
</script>

<template>
    <div class="min-h-screen bg-gray-100">
        <!-- Sidebar -->
        <aside
            :class="showingSidebar ? 'translate-x-0' : '-translate-x-full'"
            class="fixed inset-y-0 left-0 z-30 flex w-64 flex-col bg-slate-900 text-slate-100 transition-transform duration-200 lg:translate-x-0"
        >
            <div class="flex h-16 shrink-0 items-center gap-2 border-b border-slate-700 px-6">
                <ApplicationLogo class="h-8 w-auto fill-current text-white" />
                <span class="text-lg font-semibold">Overcloud</span>
            </div>

            <nav class="flex-1 space-y-6 overflow-y-auto px-3 py-5">
                <div v-for="seccion in secciones" :key="seccion.titulo">
                    <p class="px-3 pb-1 text-xs font-semibold uppercase tracking-wider text-slate-400">
                        {{ seccion.titulo }}
                    </p>
                    <Link
                        v-for="item in seccion.items"
                        :key="item.ruta"
                        :href="route(item.ruta)"
                        :class="
                            esActiva(item.ruta)
                                ? 'bg-indigo-600 text-white'
                                : 'text-slate-300 hover:bg-slate-800 hover:text-white'
                        "
                        class="mt-0.5 flex items-center gap-3 rounded-md px-3 py-2 text-sm font-medium"
                    >
                        <span>{{ item.icono }}</span>
                        <span>{{ item.nombre }}</span>
                    </Link>
                </div>
            </nav>

            <div class="border-t border-slate-700 px-6 py-4 text-xs text-slate-400">
                Desarrollado por Overcloud
            </div>
        </aside>

        <!-- Overlay móvil -->
        <div
            v-if="showingSidebar"
            class="fixed inset-0 z-20 bg-black/40 lg:hidden"
            @click="showingSidebar = false"
        />

        <!-- Contenido -->
        <div class="lg:pl-64">
            <header class="sticky top-0 z-10 flex h-16 items-center justify-between border-b border-gray-200 bg-white px-4 sm:px-6">
                <button
                    class="rounded-md p-2 text-gray-500 hover:bg-gray-100 lg:hidden"
                    @click="showingSidebar = !showingSidebar"
                >
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>

                <div class="flex flex-1 items-center">
                    <h1 class="text-lg font-semibold text-gray-800">
                        <slot name="titulo">Panel de administración</slot>
                    </h1>
                </div>

                <Dropdown align="right" width="48">
                    <template #trigger>
                        <button
                            type="button"
                            class="inline-flex items-center rounded-md border border-transparent bg-white px-3 py-2 text-sm font-medium text-gray-600 hover:text-gray-900"
                        >
                            {{ page.props.auth.user.name }}
                            <svg class="-me-0.5 ms-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </template>
                    <template #content>
                        <DropdownLink :href="route('profile.edit')">Perfil</DropdownLink>
                        <DropdownLink :href="route('logout')" method="post" as="button">Cerrar sesión</DropdownLink>
                    </template>
                </Dropdown>
            </header>

            <!-- Mensajes flash -->
            <div
                v-if="page.props.flash && page.props.flash.success"
                class="mx-4 mt-4 rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800 sm:mx-6"
            >
                {{ page.props.flash.success }}
            </div>

            <main class="p-4 sm:p-6">
                <slot />
            </main>

            <footer class="px-6 py-4 text-center text-xs text-gray-400">
                Desarrollado por Overcloud
            </footer>
        </div>
    </div>
</template>
