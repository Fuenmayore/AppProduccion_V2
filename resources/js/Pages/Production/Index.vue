<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({
    line: Object,
    reports: Object, // Paginación
});

// Función para eliminar un reporte desde el listado
const deleteReport = (id) => {
    if (confirm('¿Estás seguro de eliminar este reporte y todos sus registros? Esta acción no se puede deshacer.')) {
        router.delete(route('production.destroy', [props.line.slug, id]));
    }
};
</script>

<template>
    <Head :title="`Reportes - ${line.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ line.name }} <span class="text-gray-500 text-sm">| Historial de Producción</span>
                </h2>
                
                <Link 
                    :href="route('production.create', line.slug)"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded shadow transition flex items-center"
                >
                    <span class="mr-2 text-xl">+</span> Nuevo Turno
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Turno</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Coordinador</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-if="reports.data.length === 0">
                                    <td colspan="6" class="px-6 py-10 text-center text-gray-500">
                                        No hay registros creados aún. ¡Inicia el primer turno!
                                    </td>
                                </tr>
                                <tr v-for="report in reports.data" :key="report.id" class="hover:bg-gray-50 transition">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        #{{ report.id }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ report.production_date }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ report.shift ? report.shift.name : 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ report.coordinator ? report.coordinator.name : 'Sin asignar' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                            :class="report.status === 'Running' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'">
                                            {{ report.status === 'Running' ? 'En Proceso' : report.status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium flex justify-end items-center gap-3">
                                        <Link :href="route('production.show', [line.slug, report.id])" 
                                              class="text-indigo-600 hover:text-indigo-900 font-bold">
                                            Ver / Gestionar
                                        </Link>
                                        
                                        <button @click="deleteReport(report.id)" class="text-red-500 hover:text-red-700" title="Eliminar Reporte">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="px-6 py-4 border-t border-gray-200 flex justify-end gap-1" v-if="reports.links && reports.data.length > 0">
                         <template v-for="(link, k) in reports.links" :key="k">
                            <Link 
                                v-if="link.url"
                                :href="link.url"
                                class="px-3 py-1 border rounded text-sm transition"
                                :class="link.active ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-gray-700 hover:bg-gray-50'"
                                v-html="link.label" 
                            />
                            <span 
                                v-else
                                class="px-3 py-1 border rounded text-sm text-gray-400 bg-gray-50 cursor-not-allowed"
                                v-html="link.label"
                            ></span>
                        </template>
                    </div>

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>