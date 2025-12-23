<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({
    line: Object,
    reports: Object, // Paginación
});

// Función para eliminar un reporte desde el listado
const deleteReport = (id) => {
    if (confirm('¿ATENCIÓN: Vas a eliminar este turno y TODOS sus registros técnicos? Esta acción no se puede deshacer.')) {
        router.delete(route('production.destroy', [props.line.slug, id]));
    }
};
</script>

<template>
    <Head :title="`Reportes - ${line.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ line.name }} <span class="text-gray-500 text-sm">| Historial de Producción</span>
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
                    <div>
                        <h3 class="text-lg font-bold text-gray-700 uppercase tracking-tight">Registros Recientes</h3>
                        <p class="text-xs text-gray-400 font-bold uppercase tracking-widest">Mostrando últimos turnos reportados</p>
                    </div>

                    <Link 
                        :href="route('production.create', line.slug)"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white font-black py-3 px-8 rounded-2xl shadow-xl hover:shadow-indigo-200 transition-all active:scale-95 flex items-center uppercase tracking-widest text-xs"
                    >
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Iniciar Nuevo Turno
                    </Link>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-[2rem] border border-gray-100">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-100">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="px-6 py-4 text-left text-[10px] font-black text-slate-400 uppercase tracking-[0.15em]">ID</th>
                                    <th class="px-6 py-4 text-left text-[10px] font-black text-slate-400 uppercase tracking-[0.15em]">Fecha de Producción</th>
                                    <th class="px-6 py-4 text-left text-[10px] font-black text-slate-400 uppercase tracking-[0.15em]">Turno</th>
                                    <th class="px-6 py-4 text-left text-[10px] font-black text-slate-400 uppercase tracking-[0.15em]">Responsable</th>
                                    <th class="px-6 py-4 text-left text-[10px] font-black text-slate-400 uppercase tracking-[0.15em]">Estado</th>
                                    <th class="px-6 py-4 text-right text-[10px] font-black text-slate-400 uppercase tracking-[0.15em]">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-50">
                                <tr v-if="reports.data.length === 0">
                                    <td colspan="6" class="px-6 py-16 text-center">
                                        <div class="flex flex-col items-center">
                                            <svg class="w-12 h-12 text-gray-200 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                            <p class="text-gray-400 font-bold text-sm">No hay registros aún en esta línea.</p>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-for="report in reports.data" :key="report.id" class="hover:bg-indigo-50/30 transition-colors group">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-xs font-black text-slate-400 group-hover:text-indigo-600 transition-colors">#{{ report.id }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-700">
                                        {{ report.production_date }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-xs font-black px-2.5 py-1 rounded-lg bg-slate-100 text-slate-600 uppercase">{{ report.shift ? report.shift.name : 'N/A' }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <p class="text-sm font-bold text-gray-600">{{ report.coordinator ? report.coordinator.name : 'Sin asignar' }}</p>
                                        <p class="text-[10px] text-gray-400 font-medium">Coordinador de Turno</p>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-3 py-1 text-[10px] font-black rounded-full uppercase tracking-tighter shadow-sm border"
                                            :class="report.status === 'Running' ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : 'bg-gray-50 text-gray-500 border-gray-100'">
                                            {{ report.status === 'Running' ? 'En Operación' : 'Finalizado' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium flex justify-end items-center gap-4">
                                        <Link :href="route('production.show', [line.slug, report.id])" 
                                              class="text-indigo-600 hover:text-indigo-900 font-black uppercase text-[10px] tracking-widest bg-indigo-50 px-4 py-2 rounded-xl transition-all hover:shadow-md">
                                            Gestionar
                                        </Link>
                                        
                                        <button @click="deleteReport(report.id)" class="text-gray-300 hover:text-red-500 transition-colors" title="Eliminar Turno">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="px-6 py-6 border-t border-gray-50 flex justify-center md:justify-end gap-1.5" v-if="reports.links && reports.data.length > 0">
                         <template v-for="(link, k) in reports.links" :key="k">
                            <Link 
                                v-if="link.url"
                                :href="link.url"
                                class="px-4 py-2 rounded-xl text-xs font-black transition-all border uppercase tracking-widest shadow-sm"
                                :class="link.active ? 'bg-indigo-600 text-white border-indigo-600 shadow-indigo-200' : 'bg-white text-gray-500 border-gray-100 hover:bg-indigo-50 hover:text-indigo-600'"
                                v-html="link.label" 
                            />
                            <span 
                                v-else
                                class="px-4 py-2 rounded-xl text-xs font-black text-gray-300 border border-gray-50 bg-gray-50 uppercase tracking-widest cursor-not-allowed"
                                v-html="link.label"
                            ></span>
                        </template>
                    </div>

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>