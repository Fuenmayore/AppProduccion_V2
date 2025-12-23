<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const currentRoute = computed(() => page.url);

// --- HELPER DE PERMISOS ---
// Esta función revisa si el permiso existe en el array que enviamos desde HandleInertiaRequests
const can = (permission) => {
    return page.props.auth.permissions.includes(permission);
};

// Función para marcar activo el menú
const isActive = (routePrefix) => currentRoute.value.startsWith(routePrefix);
</script>

<template>
    <aside class="w-64 bg-slate-900 text-white min-h-screen flex flex-col shadow-xl fixed left-0 top-0 bottom-0 z-50">
        
        <div class="h-16 flex items-center justify-center border-b border-slate-700 bg-slate-950 shadow-md">
            <h1 class="text-xl font-bold tracking-wider text-indigo-400">TRA<span class="text-white">ZABILIDAD</span></h1>
        </div>

        <nav class="flex-1 py-6 px-3 space-y-1 overflow-y-auto custom-scrollbar">
            
            <p class="px-3 text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Principal</p>
            
            <Link :href="route('dashboard')" 
                class="flex items-center px-3 py-2.5 rounded-lg transition-colors mb-1"
                :class="route().current('dashboard') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-900/50' : 'text-slate-300 hover:bg-slate-800 hover:text-white'">
                <svg class="w-5 h-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 01-2 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" /></svg>
                Dashboard
            </Link>

            <div class="my-4 border-t border-slate-700/50"></div>

            <template v-if="can('crear-reportes')">
                <p class="px-3 text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Producción</p>
                <template v-for="line in ['a', 'b', 'c', 'd']" :key="line">
                    <Link :href="route('production.index', `linea-${line}`)" 
                        class="flex items-center px-3 py-2 rounded-lg transition-colors mb-1 group"
                        :class="isActive(`/produccion/linea-${line}`) ? 'bg-slate-800 text-indigo-400 border-l-4 border-indigo-500' : 'text-slate-400 hover:bg-slate-800 hover:text-white'">
                        <span class="w-6 h-6 rounded-full bg-slate-700 flex items-center justify-center text-xs font-bold mr-3 border border-slate-600 group-hover:border-indigo-400 shadow-sm">{{ line.toUpperCase() }}</span>
                        Línea {{ line.toUpperCase() }}
                    </Link>
                </template>
                <div class="my-4 border-t border-slate-700/50"></div>
            </template>

            <template v-if="can('gestionar-logistica') || can('ver-trazabilidad')">
                <p class="px-3 text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Logística</p>
                
                <Link v-if="can('gestionar-logistica')" :href="route('silos.index')" 
                    class="flex items-center px-3 py-2 rounded-lg transition-colors mb-1"
                    :class="isActive('/materia-prima') ? 'bg-orange-900/20 text-orange-400 border-l-4 border-orange-500' : 'text-slate-400 hover:bg-slate-800 hover:text-white'">
                    <svg class="w-5 h-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                    Materia Prima
                </Link>

                <Link v-if="can('ver-trazabilidad')" :href="route('traceability.index')" 
                    class="flex items-center px-3 py-2 rounded-lg transition-colors mb-1"
                    :class="isActive('/trazabilidad') ? 'bg-purple-900/20 text-purple-400 border-l-4 border-purple-500' : 'text-slate-400 hover:bg-slate-800 hover:text-white'">
                    <svg class="w-5 h-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path></svg>
                    Trazabilidad
                </Link>

                <div class="my-4 border-t border-slate-700/50"></div>
            </template>

            <template v-if="can('gestionar-parametros') || can('gestionar-usuarios')">
                <p class="px-3 text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Administración</p>

                <Link v-if="can('gestionar-parametros')" :href="route('parameters.index')" 
                    class="flex items-center px-3 py-2 rounded-lg transition-colors mb-1"
                    :class="isActive('/configuracion/parametros') ? 'bg-indigo-900/20 text-indigo-400 border-l-4 border-indigo-500' : 'text-slate-400 hover:bg-slate-800 hover:text-white'">
                    <svg class="w-5 h-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                    Marcas y Productos
                </Link>

                <Link v-if="can('gestionar-usuarios')" :href="route('users.index')" 
                    class="flex items-center px-3 py-2 rounded-lg transition-colors mb-1"
                    :class="isActive('/usuarios') ? 'bg-indigo-900/20 text-indigo-400 border-l-4 border-indigo-500' : 'text-slate-400 hover:bg-slate-800 hover:text-white'">
                    <svg class="w-5 h-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                    Gestión de Usuarios
                </Link>

                <Link v-if="can('gestionar-usuarios')" :href="route('roles.index')" 
                    class="flex items-center px-3 py-2 rounded-lg transition-colors mb-1"
                    :class="isActive('/roles') ? 'bg-indigo-900/20 text-indigo-400 border-l-4 border-indigo-500' : 'text-slate-400 hover:bg-slate-800 hover:text-white'">
                    <svg class="w-5 h-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                    Roles y Permisos
                </Link>

                <div class="my-4 border-t border-slate-700/50"></div>
            </template>

            <p v-if="can('ver-reprocesos')" class="px-3 text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Calidad</p>

            <Link v-if="can('ver-reprocesos')" :href="route('waste.index')" 
                class="flex items-center px-3 py-2 rounded-lg transition-colors mb-1"
                :class="isActive('/reproceso') ? 'bg-red-900/20 text-red-400 border-l-4 border-red-500' : 'text-slate-400 hover:bg-slate-800 hover:text-white'">
                <svg class="w-5 h-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                Reprocesos
            </Link>

            <div v-if="can('ver-reprocesos')" class="my-4 border-t border-slate-700/50"></div>

            <p class="px-3 text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Cuenta</p>

            <Link :href="route('profile.edit')" 
                class="flex items-center px-3 py-2 rounded-lg transition-colors duration-200 text-slate-400 hover:bg-slate-800 hover:text-white">
                <svg class="w-5 h-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                Mi Perfil
            </Link>

            <!-- En tu Sidebar.vue -->
            <Link v-if="can('gestionar-usuarios')" :href="route('users.index')">
                Gestión de Usuarios
            </Link>
            
            <Link :href="route('logout')" method="post" as="button"
                class="w-full flex items-center px-3 py-2 rounded-lg transition-colors duration-200 text-red-400 hover:bg-red-900/30 hover:text-red-300 mt-4">
                <svg class="w-5 h-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                Cerrar Sesión
            </Link>

        </nav>
        
        <div class="p-4 bg-slate-950 border-t border-slate-800 flex items-center">
            <div class="w-8 h-8 rounded-full bg-indigo-600 flex items-center justify-center text-white font-bold select-none uppercase">
                {{ $page.props.auth.user.name.charAt(0) }}
            </div>
            <div class="ml-3 overflow-hidden">
                <p class="text-sm font-medium text-white truncate w-36">{{ $page.props.auth.user.name }}</p>
                <p class="text-[10px] text-slate-500 truncate uppercase font-bold">{{ $page.props.auth.roles[0] }}</p>
            </div>
        </div>
    </aside>
</template>