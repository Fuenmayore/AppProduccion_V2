<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    filters: Object,
    reports: Array,
    lines: Array,
});

const form = useForm({
    date_start: props.filters.date_start || '',
    date_end: props.filters.date_end || '',
    line_id: props.filters.line_id || '',
    batch_code: props.filters.batch_code || '',
});

const search = () => {
    form.get(route('traceability.index'), { preserveState: true, preserveScroll: true });
};

const reset = () => {
    form.reset();
    form.get(route('traceability.index'));
};

// --- ESTADÍSTICAS COMPUTADAS EN TIEMPO REAL (Client-Side) ---
// Analizamos los resultados de la búsqueda para mostrar insights valiosos
const stats = computed(() => {
    if (!props.reports.length) return null;

    const products = new Set();
    const batches = new Set();
    let totalVariables = 0;

    props.reports.forEach(r => {
        r.products.forEach(p => products.add(p));
        r.raw_materials.forEach(b => batches.add(b));
        totalVariables += r.variable_count;
    });

    return {
        total_reports: props.reports.length,
        unique_products: products.size,
        unique_batches: batches.size,
        data_points: totalVariables
    };
});
</script>

<template>
    <Head title="Centro de Trazabilidad" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center">
                <div class="p-2 bg-purple-100 rounded-lg mr-3 text-purple-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path></svg>
                </div>
                <h2 class="font-bold text-xl text-gray-800 leading-tight">
                    Trazabilidad Inteligente
                </h2>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                
                <div class="bg-white rounded-xl shadow-md border border-gray-100 overflow-hidden">
                    <div class="bg-gray-50 px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                        <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wide flex items-center">
                            <svg class="w-4 h-4 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                            Filtros de Rastreo
                        </h3>
                        <span class="text-xs text-gray-400" v-if="reports.length > 0">{{ reports.length }} resultados encontrados</span>
                    </div>
                    
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                            <div class="col-span-2 grid grid-cols-2 gap-4 bg-gray-50 p-3 rounded-lg border border-gray-200">
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 mb-1 uppercase">Desde</label>
                                    <input type="date" v-model="form.date_start" class="w-full border-gray-300 rounded-md text-sm focus:ring-indigo-500 focus:border-indigo-500">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 mb-1 uppercase">Hasta</label>
                                    <input type="date" v-model="form.date_end" class="w-full border-gray-300 rounded-md text-sm focus:ring-indigo-500 focus:border-indigo-500">
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-500 mb-1 uppercase">Línea de Producción</label>
                                <select v-model="form.line_id" class="w-full border-gray-300 rounded-md text-sm focus:ring-indigo-500 focus:border-indigo-500 h-10">
                                    <option value="">Todas las líneas</option>
                                    <option v-for="line in lines" :key="line.id" :value="line.id">{{ line.name }}</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-500 mb-1 uppercase text-orange-600">🔍 Rastrear Lote MP</label>
                                <div class="relative">
                                    <input type="text" v-model="form.batch_code" placeholder="Ej: L-45892" class="w-full border-orange-300 rounded-md text-sm focus:ring-orange-500 focus:border-orange-500 pl-8 h-10">
                                    <div class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none">
                                        <svg class="h-4 w-4 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"></path></svg>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end gap-3 pt-4 border-t border-gray-100">
                            <button @click="reset" class="px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition">
                                Limpiar Filtros
                            </button>
                            <button @click="search" :disabled="form.processing" class="px-6 py-2 text-sm text-white font-bold bg-indigo-600 hover:bg-indigo-700 rounded-lg shadow-md hover:shadow-lg transition-all flex items-center transform active:scale-95">
                                <svg v-if="!form.processing" class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                <svg v-else class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                Buscar Registros
                            </button>
                        </div>
                    </div>
                </div>

                <div v-if="stats && stats.total_reports > 0" class="grid grid-cols-2 md:grid-cols-4 gap-4 animate-fade-in-down">
                    <div class="bg-white p-4 rounded-xl shadow-sm border-b-4 border-indigo-500">
                        <p class="text-xs text-gray-500 uppercase font-bold">Turnos Encontrados</p>
                        <p class="text-2xl font-black text-gray-800">{{ stats.total_reports }}</p>
                    </div>
                    <div class="bg-white p-4 rounded-xl shadow-sm border-b-4 border-green-500">
                        <p class="text-xs text-gray-500 uppercase font-bold">Referencias Únicas</p>
                        <p class="text-2xl font-black text-gray-800">{{ stats.unique_products }}</p>
                    </div>
                    <div class="bg-white p-4 rounded-xl shadow-sm border-b-4 border-orange-500">
                        <p class="text-xs text-gray-500 uppercase font-bold">Lotes MP Usados</p>
                        <p class="text-2xl font-black text-gray-800">{{ stats.unique_batches }}</p>
                    </div>
                    <div class="bg-white p-4 rounded-xl shadow-sm border-b-4 border-blue-500">
                        <p class="text-xs text-gray-500 uppercase font-bold">Puntos de Control</p>
                        <p class="text-2xl font-black text-gray-800">{{ stats.data_points }}</p>
                    </div>
                </div>

                <div v-if="reports.length > 0" class="space-y-4">
                    
                    <div v-for="report in reports" :key="report.id" class="bg-white rounded-xl shadow-sm hover:shadow-md transition-shadow border border-gray-100 overflow-hidden">
                        <div class="grid grid-cols-1 lg:grid-cols-12">
                            
                            <div class="lg:col-span-3 bg-gray-50 p-4 flex flex-col justify-center border-r border-gray-100">
                                <div class="flex items-center mb-2">
                                    <span class="w-2 h-8 rounded-full mr-3" :class="{'bg-blue-500': report.line.includes('A'), 'bg-green-500': report.line.includes('B'), 'bg-purple-500': report.line.includes('C'), 'bg-orange-500': report.line.includes('D')}"></span>
                                    <div>
                                        <h4 class="font-bold text-gray-800 text-lg leading-none">{{ report.line }}</h4>
                                        <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">{{ report.shift }}</span>
                                    </div>
                                </div>
                                <div class="text-sm font-semibold text-gray-700 mt-2 flex items-center">
                                    <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    {{ report.date }}
                                </div>
                            </div>

                            <div class="lg:col-span-7 p-4 flex flex-col justify-center">
                                <div class="flex flex-col md:flex-row items-center gap-4 w-full">
                                    
                                    <div class="flex-1 w-full">
                                        <p class="text-[10px] uppercase font-bold text-orange-400 mb-1">Entrada (Lotes MP)</p>
                                        <div v-if="report.raw_materials.length > 0" class="flex flex-wrap gap-2">
                                            <span v-for="batch in report.raw_materials" :key="batch" class="px-2 py-1 bg-orange-50 text-orange-700 text-xs font-bold rounded border border-orange-100 flex items-center">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                                                {{ batch }}
                                            </span>
                                        </div>
                                        <span v-else class="text-xs text-gray-400 italic">Sin registro de lote</span>
                                    </div>

                                    <div class="hidden md:flex text-gray-300">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                                    </div>

                                    <div class="flex-1 w-full text-right md:text-left">
                                        <p class="text-[10px] uppercase font-bold text-green-500 mb-1">Salida (Producto)</p>
                                        <div v-if="report.products.length > 0" class="flex flex-wrap gap-2 justify-end md:justify-start">
                                            <span v-for="prod in report.products" :key="prod" class="px-2 py-1 bg-green-50 text-green-700 text-xs font-bold rounded border border-green-100 flex items-center">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                {{ prod }}
                                            </span>
                                        </div>
                                        <span v-else class="text-xs text-gray-400 italic">Sin producción registrada</span>
                                    </div>

                                </div>
                                
                                <div class="mt-4 pt-3 border-t border-gray-50 flex gap-6 text-xs text-gray-500">
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 mr-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                        Coord: <strong>{{ report.coordinator }}</strong>
                                    </div>
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 mr-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                        Operario: <strong>{{ report.operator }}</strong>
                                    </div>
                                </div>
                            </div>

                            <div class="lg:col-span-2 bg-gray-50 p-4 flex items-center justify-center border-l border-gray-100">
                                <Link :href="route('production.show', [report.line.toLowerCase().replace(' ', '-').replace('í','i'), report.id])" 
                                    class="w-full bg-white border border-indigo-200 text-indigo-600 hover:bg-indigo-600 hover:text-white font-bold py-2 px-4 rounded-lg shadow-sm transition-colors text-center text-sm flex flex-col items-center gap-1">
                                    <span>Ver Informe</span>
                                    <span class="text-[10px] font-normal opacity-70">({{ report.variable_count }} registros)</span>
                                </Link>
                            </div>

                        </div>
                    </div>

                </div>

                <div v-else class="text-center py-16 bg-white rounded-xl border-2 border-dashed border-gray-200">
                    <div class="bg-gray-50 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900">No se encontraron registros</h3>
                    <p class="text-gray-500 max-w-sm mx-auto mt-1">Intenta ajustar los filtros de fecha o busca por un lote de materia prima diferente.</p>
                    <button @click="reset" class="mt-6 text-indigo-600 hover:underline font-medium">Limpiar filtros</button>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.animate-fade-in-down {
    animation: fadeInDown 0.5s ease-out;
}
@keyframes fadeInDown {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>