<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    line: Object,
    report: Object,
    fields: Array,
    variableData: Object, // El JSON con los datos guardados
    observacion: String
});

// Función auxiliar para formatear valores si es necesario
const formatValue = (value) => {
    if (value === null || value === undefined) return '-';
    return value;
};
</script>

<template>
    <Head :title="`Reporte #${report.id}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Reporte #{{ report.id }} <span class="text-gray-500">| {{ line.name }}</span>
                </h2>
                <Link :href="route('production.index', line.slug)" class="text-gray-600 hover:text-gray-900 font-medium">
                    &larr; Volver al listado
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6 p-6 border-l-4 border-indigo-500">
                    <h3 class="text-lg font-bold mb-4">Información General</h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                        <div>
                            <p class="text-xs text-gray-500 uppercase">Fecha</p>
                            <p class="font-medium text-gray-900">{{ report.fecha }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 uppercase">Turno</p>
                            <p class="font-medium text-gray-900">{{ report.shift?.name }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 uppercase">Coordinador</p>
                            <p class="font-medium text-gray-900">{{ report.coordinator?.name }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 uppercase">Operario</p>
                            <p class="font-medium text-gray-900">{{ report.operator?.name }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="flex justify-between mb-4">
                        <h3 class="text-lg font-bold">Variables de Proceso</h3>
                        <span class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded">
                            Registrado: {{ report.created_at }}
                        </span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-x-8 gap-y-6">
                        <template v-for="(field, index) in fields" :key="index">
                            
                            <div v-if="field.type === 'header'" class="col-span-full mt-4 pt-4 border-t border-gray-100">
                                <h4 class="text-md font-semibold text-indigo-600">{{ field.label }}</h4>
                            </div>

                            <div v-else class="bg-gray-50 p-3 rounded-md">
                                <p class="text-xs text-gray-500 uppercase mb-1">{{ field.label }}</p>
                                <p class="font-mono text-lg font-medium text-gray-800">
                                    {{ formatValue(variableData[field.name]) }}
                                </p>
                            </div>

                        </template>
                    </div>

                    <div class="mt-8 pt-4 border-t border-gray-200">
                        <p class="text-sm font-bold text-gray-700 mb-2">Observaciones Generales:</p>
                        <div class="bg-yellow-50 p-4 rounded text-gray-700 italic border border-yellow-200">
                            {{ observacion || 'Sin observaciones registradas.' }}
                        </div>
                    </div>
                    
                    <div class="mt-6 flex justify-end gap-2">
                        <button class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700 text-sm">
                            🖨️ Descargar PDF (Próximamente)
                        </button>
                    </div>

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>