<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    linesStatus: { type: Array, default: () => [] },
    stats: { type: Object, default: () => ({}) },
    silos: { type: Array, default: () => [] },
    topDefects: { type: Array, default: () => [] }
});

// --- Reloj Industrial de Alta Precisión ---
const timeFormatter = new Intl.DateTimeFormat('es-ES', {
    hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false
});
const currentTimeDisplay = ref('--:--:--');
let timer = null;
const updateClock = () => { currentTimeDisplay.value = timeFormatter.format(new Date()); };

onMounted(() => { updateClock(); timer = setInterval(updateClock, 1000); });
onUnmounted(() => { if (timer) clearInterval(timer); });

// --- Motor de Diagnóstico Avanzado ---
const plantIntelligence = computed(() => {
    const lines = props.linesStatus || [];
    const silos = props.silos || [];
    const totalEfficiency = lines.reduce((acc, l) => acc + (l.efficiency || 0), 0) / (lines.length || 1);
    
    // Alertas de supervisión (Retrasos)
    const stalledLines = lines.filter(l => l.is_active && l.minutes_since > 75);
    // Alertas de silos (Materia Prima)
    const criticalSilos = silos.filter(s => s.percentage < 30);
    
    let shiftInsight = "Operación estable. Mantener ritmo de secado.";
    let urgent = false;

    if (stalledLines.length > 0) {
        shiftInsight = `ALERTA: ${stalledLines[0].name} sin reportes. ¡Verificar operario!`;
        urgent = true;
    } else if (criticalSilos.length > 0) {
        shiftInsight = `LOGÍSTICA: Nivel bajo en ${criticalSilos[0].name}. Cargar ${criticalSilos[0].material}.`;
        urgent = true;
    }

    return {
        efficiency: totalEfficiency.toFixed(1),
        shiftInsight,
        urgent,
        planCompliance: Math.min(totalEfficiency, 100),
        stalledCount: stalledLines.length,
        criticalSilosCount: criticalSilos.length
    };
});

const getStatusStyles = (color) => {
    const map = {
        'green': 'bg-emerald-500 text-white shadow-[0_0_10px_rgba(16,185,129,0.3)]',
        'yellow': 'bg-amber-500 text-white shadow-[0_0_10px_rgba(245,158,11,0.3)]',
        'red': 'bg-rose-500 text-white shadow-[0_0_10px_rgba(244,63,94,0.3)]',
        'blue': 'bg-blue-500 text-white shadow-[0_0_10px_rgba(59,130,246,0.3)]',
    };
    return map[color] || 'bg-gray-400 text-white';
};
</script>

<template>
    <Head title="Panel de Control Industrial" />

    <AuthenticatedLayout>
        <div class="mb-8 flex flex-col lg:flex-row justify-between items-center gap-4">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 bg-indigo-600 rounded-2xl flex items-center justify-center shadow-lg">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-10V4m0 10V4m-5 1h1m4 0h1m-5 4h1m4 0h1"/></svg>
                </div>
                <div>
                    <h2 class="text-2xl font-black text-gray-800 uppercase tracking-tighter italic">MES Production <span class="text-indigo-600">v2</span></h2>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">{{ stats.date_human }}</p>
                </div>
            </div>

            <div class="flex items-center gap-2 bg-slate-950 p-1.5 rounded-2xl border border-slate-800 shadow-2xl">
                <div class="px-5 py-2 text-center border-r border-slate-800">
                    <p class="text-[8px] text-slate-500 font-black uppercase tracking-widest">Semana</p>
                    <p class="text-xl font-black text-white leading-none">{{ stats.week_number }}</p>
                </div>
                <div class="px-5 py-2 text-center border-r border-slate-800">
                    <p class="text-[8px] text-slate-500 font-black uppercase tracking-widest">Día Juliano</p>
                    <p class="text-xl font-black text-indigo-400 leading-none">{{ stats.julian_day }}</p>
                </div>
                <div class="px-8 py-2 text-center">
                    <p class="text-[8px] text-slate-500 font-black uppercase tracking-widest">Reloj Planta</p>
                    <p class="text-2xl font-black text-emerald-400 font-mono leading-none tracking-tighter">{{ currentTimeDisplay }}</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            <div class="lg:col-span-2 bg-white rounded-[2.5rem] p-8 shadow-sm border border-gray-100 relative overflow-hidden">
                <h3 class="text-xs font-black text-indigo-600 uppercase tracking-[0.2em] mb-6 flex items-center">
                    <span class="w-2 h-2 bg-indigo-600 rounded-full mr-2 animate-ping"></span>
                    Estado Maestro del Turno
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-center">
                    <div class="space-y-6">
                        <div class="p-6 rounded-3xl border transition-all" 
                             :class="plantIntelligence.urgent ? 'bg-rose-50 border-rose-100' : 'bg-slate-50 border-gray-100'">
                            <p class="text-sm font-bold leading-relaxed italic"
                               :class="plantIntelligence.urgent ? 'text-rose-700' : 'text-gray-700'">
                                "{{ plantIntelligence.shiftInsight }}"
                            </p>
                        </div>
                        <div class="flex gap-4">
                            <div class="flex-1 bg-emerald-50 border border-emerald-100 p-4 rounded-2xl">
                                <p class="text-[9px] font-black text-emerald-600 uppercase mb-1">Ritmo OK</p>
                                <p class="text-xl font-black text-emerald-700">{{ linesStatus.length - plantIntelligence.stalledCount }}</p>
                            </div>
                            <div class="flex-1 bg-rose-50 border border-rose-100 p-4 rounded-2xl">
                                <p class="text-[9px] font-black text-rose-600 uppercase mb-1">Atrasos</p>
                                <p class="text-xl font-black text-rose-700">{{ plantIntelligence.stalledCount }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="relative flex justify-center">
                        <div class="w-40 h-40 rounded-full border-[12px] border-gray-100 flex items-center justify-center relative">
                            <div class="text-center">
                                <p class="text-[8px] font-black text-gray-400 uppercase">Eficiencia<br>Promedio</p>
                                <p class="text-2xl font-black text-gray-800">{{ plantIntelligence.efficiency }}%</p>
                            </div>
                            <svg class="absolute -inset-3 w-46 h-46 rotate-[-90deg]">
                                <circle cx="92" cy="92" r="80" fill="none" stroke="currentColor" stroke-width="12" 
                                    class="text-indigo-600 transition-all duration-1000"
                                    :stroke-dasharray="`${(parseFloat(plantIntelligence.efficiency) * 502) / 100} 502`" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-slate-900 rounded-[2.5rem] p-8 text-white shadow-2xl relative overflow-hidden">
                <div class="absolute -bottom-10 -right-10 w-40 h-40 bg-indigo-600/20 rounded-full blur-3xl"></div>
                <h3 class="text-xs font-black text-indigo-400 uppercase tracking-[0.2em] mb-8">Niveles de Sémola</h3>
                
                <div class="space-y-6">
                    <div v-for="silo in silos.slice(0, 3)" :key="silo.id">
                        <div class="flex justify-between items-end mb-2">
                            <div>
                                <p class="text-[10px] font-black text-slate-500 uppercase">{{ silo.material }}</p>
                                <p class="text-sm font-bold text-white">{{ silo.name }}</p>
                            </div>
                            <p class="text-lg font-black" :class="silo.percentage < 30 ? 'text-rose-400 animate-pulse' : 'text-emerald-400'">
                                {{ silo.percentage }}%
                            </p>
                        </div>
                        <div class="w-full bg-slate-800 h-2.5 rounded-full overflow-hidden border border-slate-700">
                            <div class="h-full transition-all duration-1000"
                                 :class="silo.percentage < 30 ? 'bg-rose-500 shadow-[0_0_10px_rgba(244,63,94,0.6)]' : 'bg-emerald-500'"
                                 :style="`width: ${silo.percentage}%`" />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
            <div v-for="line in linesStatus" :key="line.id" 
                 class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm hover:shadow-xl transition-all group overflow-hidden relative">
                
                <div class="flex justify-between items-start mb-6">
                    <div :class="getStatusStyles(line.status_color)" class="w-12 h-12 rounded-2xl flex items-center justify-center font-black text-xl">
                        {{ line.name.split(' ').pop() }}
                    </div>
                    <div class="text-right">
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest leading-none mb-1">Último Reporte</p>
                        <p class="text-base font-black text-gray-800">{{ line.last_record }}</p>
                        <p class="text-[10px] font-bold uppercase tracking-tighter"
                           :class="line.minutes_since > 75 ? 'text-rose-600 animate-pulse' : 'text-indigo-500'">
                            {{ line.time_since }}
                        </p>
                    </div>
                </div>

                <div class="space-y-4">
                    <div class="bg-indigo-50/50 p-4 rounded-2xl border border-indigo-100/50">
                        <p class="text-[9px] font-black text-indigo-400 uppercase mb-1">Referencia en Proceso</p>
                        <p class="text-sm font-black text-gray-800 truncate leading-tight">{{ line.product }}</p>
                        <p class="text-[10px] font-bold text-gray-500 uppercase tracking-wide mt-1">{{ line.brand }}</p>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div class="bg-gray-50 p-3 rounded-2xl border border-gray-100">
                            <p class="text-[8px] font-black text-gray-400 uppercase mb-0.5">Operario</p>
                            <p class="text-[11px] font-bold text-gray-700 truncate">{{ line.operator.split(' ')[0] }}</p>
                        </div>
                        <div class="bg-gray-50 p-3 rounded-2xl border border-gray-100">
                            <p class="text-[8px] font-black text-gray-400 uppercase mb-0.5">Eficiencia</p>
                            <p class="text-[11px] font-black text-gray-900">{{ line.efficiency }}%</p>
                        </div>
                    </div>

                    <Link :href="line.is_active ? route('production.index', line.slug) : route('production.create', line.slug)"
                          class="w-full py-3 rounded-2xl flex items-center justify-center text-xs font-black uppercase tracking-widest transition-all shadow-sm"
                          :class="line.is_active ? 'bg-white text-indigo-600 border border-indigo-100 hover:bg-indigo-600 hover:text-white' : 'bg-slate-900 text-white hover:bg-slate-800 shadow-lg'">
                        {{ line.is_active ? 'Supervisar' : 'Iniciar Turno' }}
                    </Link>
                </div>

                <div class="absolute bottom-0 left-0 w-full h-1" :class="`bg-${line.status_color}-500`" v-if="line.is_active"></div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>