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

// --- Lógica de Resaltado ---
const isSearchedBatch = (batch) => {
    if (!form.batch_code) return false;
    return batch.toLowerCase().includes(form.batch_code.toLowerCase());
};

// --- Estadísticas Computadas ---
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
            <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                <div class="flex items-center">
                    <div class="p-3 bg-slate-950 rounded-2xl mr-4 text-indigo-400 shadow-2xl border border-slate-800">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path></svg>
                    </div>
                    <div>
                        <h2 class="font-black text-2xl text-gray-800 tracking-tighter uppercase italic">
                            Traceability <span class="text-indigo-600">Center</span>
                        </h2>
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Genealogía de Producto y Lotes</p>
                    </div>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
                
                <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-100 overflow-hidden">
                    <div class="p-8">
                        <div class="grid grid-cols-1 md:grid-cols-12 gap-8 items-end">
                            <div class="md:col-span-4 grid grid-cols-2 gap-4 bg-slate-50 p-4 rounded-3xl border border-slate-100">
                                <div>
                                    <label class="block text-[10px] font-black text-gray-400 mb-2 uppercase tracking-widest">Inicio</label>
                                    <input type="date" v-model="form.date_start" class="w-full border-none bg-transparent font-bold text-gray-700 focus:ring-0 p-0 text-sm">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black text-gray-400 mb-2 uppercase tracking-widest">Fin</label>
                                    <input type="date" v-model="form.date_end" class="w-full border-none bg-transparent font-bold text-gray-700 focus:ring-0 p-0 text-sm">
                                </div>
                            </div>

                            <div class="md:col-span-3">
                                <label class="block text-[10px] font-black text-gray-400 mb-3 uppercase tracking-widest ml-1">Línea de Producción</label>
                                <select v-model="form.line_id" class="w-full border-slate-200 rounded-2xl text-sm font-bold text-gray-700 h-12 focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="">Todas las líneas</option>
                                    <option v-for="line in lines" :key="line.id" :value="line.id">{{ line.name }}</option>
                                </select>
                            </div>

                            <div class="md:col-span-3">
                                <label class="block text-[10px] font-black text-orange-500 mb-3 uppercase tracking-widest ml-1 italic">🔍 Buscar Lote Sémola</label>
                                <input type="text" v-model="form.batch_code" placeholder="Ej: L-2458" class="w-full border-orange-200 bg-orange-50/30 rounded-2xl text-sm font-black text-gray-700 h-12 focus:ring-orange-500 focus:border-orange-500 placeholder-orange-300">
                            </div>

                            <div class="md:col-span-2 flex flex-col gap-2">
                                <button @click="search" :disabled="form.processing" class="w-full h-12 bg-indigo-600 text-white font-black rounded-2xl shadow-xl shadow-indigo-100 hover:bg-indigo-700 transition-all flex items-center justify-center uppercase text-xs tracking-widest">
                                    Rastrear
                                </button>
                                <button @click="reset" class="text-[10px] font-black text-gray-400 uppercase tracking-widest hover:text-rose-500 transition-colors">Limpiar</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="stats && stats.total_reports > 0" class="grid grid-cols-2 md:grid-cols-4 gap-6 animate-fade-in-down">
                    <div v-for="(val, label, index) in {
                        'Turnos': stats.total_reports, 
                        'Referencias': stats.unique_products, 
                        'Lotes MP': stats.unique_batches, 
                        'Puntos Control': stats.data_points
                    }" :key="index" class="bg-white p-6 rounded-[2rem] shadow-sm border border-slate-50 relative overflow-hidden">
                        <div class="absolute top-0 right-0 w-1.5 h-full" :class="['bg-indigo-500', 'bg-emerald-500', 'bg-orange-500', 'bg-blue-500'][index]"></div>
                        <p class="text-[10px] text-gray-400 uppercase font-black tracking-[0.15em] mb-1">{{ label }}</p>
                        <p class="text-3xl font-black text-slate-800 tracking-tighter">{{ val }}</p>
                    </div>
                </div>

                <div v-if="reports.length > 0" class="space-y-6">
                    <div v-for="report in reports" :key="report.id" class="bg-white rounded-[2.5rem] shadow-sm border border-slate-100 overflow-hidden group hover:shadow-2xl transition-all duration-500">
                        <div class="grid grid-cols-1 lg:grid-cols-12">
                            
                            <div class="lg:col-span-3 bg-slate-50 p-8 flex flex-col justify-between border-r border-slate-100">
                                <div>
                                    <div class="flex items-center gap-3 mb-4">
                                        <div class="w-10 h-10 rounded-xl flex items-center justify-center font-black text-white shadow-lg"
                                            :class="{'bg-blue-500': report.line.includes('A'), 'bg-emerald-500': report.line.includes('B'), 'bg-purple-500': report.line.includes('C'), 'bg-orange-500': report.line.includes('D')}">
                                            {{ report.line.charAt(report.line.length - 1) }}
                                        </div>
                                        <div>
                                            <h4 class="font-black text-slate-800 text-lg leading-none uppercase italic">{{ report.line }}</h4>
                                            <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">{{ report.shift }}</span>
                                        </div>
                                    </div>
                                    <div class="inline-flex items-center px-3 py-1 bg-white rounded-lg border border-slate-200 text-xs font-bold text-slate-600">
                                        <svg class="w-3.5 h-3.5 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                        {{ report.date }}
                                    </div>
                                </div>
                                <div class="mt-6">
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-tighter italic mb-1">Responsable:</p>
                                    <p class="text-[11px] font-bold text-slate-700 truncate">{{ report.operator }}</p>
                                </div>
                            </div>

                            <div class="lg:col-span-7 p-8">
                                <div class="flex flex-col md:flex-row items-center gap-8 relative">
                                    <div class="flex-1 w-full">
                                        <p class="text-[9px] uppercase font-black text-orange-500 mb-3 tracking-[0.2em] flex items-center">
                                            <span class="w-1.5 h-1.5 bg-orange-500 rounded-full mr-2"></span> Entrada (Lotes MP)
                                        </p>
                                        <div class="flex flex-wrap gap-2">
                                            <span v-for="batch in report.raw_materials" :key="batch" 
                                                  class="px-3 py-1.5 text-[10px] font-black rounded-xl border transition-all"
                                                  :class="isSearchedBatch(batch) 
                                                    ? 'bg-orange-500 text-white border-orange-600 shadow-lg scale-110' 
                                                    : 'bg-white text-slate-600 border-slate-200 hover:border-orange-300'">
                                                {{ batch }}
                                            </span>
                                        </div>
                                    </div>

                                    <div class="hidden md:flex text-slate-200">
                                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 5l7 7-7 7M5 5l7 7-7 7"></path></svg>
                                    </div>

                                    <div class="flex-1 w-full text-right md:text-left">
                                        <p class="text-[9px] uppercase font-black text-emerald-500 mb-3 tracking-[0.2em] flex items-center justify-end md:justify-start">
                                            <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full mr-2"></span> Salida (Producto)
                                        </p>
                                        <div class="flex flex-wrap gap-2 justify-end md:justify-start">
                                            <span v-for="prod in report.products" :key="prod" 
                                                  class="px-3 py-1.5 bg-emerald-50 text-emerald-700 text-[10px] font-black rounded-xl border border-emerald-100">
                                                {{ prod }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="lg:col-span-2 bg-slate-900 p-6 flex flex-col items-center justify-center gap-4">
                                <div class="text-center">
                                    <p class="text-[9px] font-black text-indigo-400 uppercase tracking-widest mb-1">Registros</p>
                                    <p class="text-2xl font-black text-white leading-none">{{ report.variable_count }}</p>
                                </div>
                                <Link :href="route('production.show', [report.line_slug, report.id])" 
                                    class="w-full bg-indigo-600 hover:bg-indigo-500 text-white font-black py-4 px-4 rounded-2xl shadow-xl transition-all text-center text-[10px] uppercase tracking-widest flex items-center justify-center gap-2">
                                    Ver Detalle
                                </Link>
                            </div>

                        </div>
                    </div>
                </div>

                <div v-else class="text-center py-24 bg-white rounded-[3rem] border-4 border-dashed border-slate-50 flex flex-col items-center">
                    <div class="bg-slate-50 w-24 h-24 rounded-full flex items-center justify-center mb-6">
                        <svg class="w-10 h-10 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                    <h3 class="text-xl font-black text-slate-800 uppercase italic tracking-tighter">Sin registros</h3>
                    <p class="text-slate-400 text-sm font-bold max-w-xs mt-2 uppercase tracking-widest text-center">No hay coincidencias para los filtros actuales.</p>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.animate-fade-in-down {
    animation: fadeInDown 0.6s cubic-bezier(0.16, 1, 0.3, 1);
}
@keyframes fadeInDown {
    from { opacity: 0; transform: translateY(-20px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>