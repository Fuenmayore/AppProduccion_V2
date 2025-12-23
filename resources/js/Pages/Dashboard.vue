<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

/**
 * Props del sistema con validación estándar para evitar errores de compilación
 */
const props = defineProps({
    linesStatus: {
        type: Array,
        default: () => []
    },
    stats: {
        type: Object,
        default: () => ({})
    },
    silos: {
        type: Array,
        default: () => []
    },
    topDefects: {
        type: Array,
        default: () => []
    }
});

// --- Lógica de Reloj Real-Time de Alto Rendimiento ---
// Definimos el formateador fuera de la función de actualización para ahorrar ciclos de CPU
const timeFormatter = new Intl.DateTimeFormat('es-ES', {
    hour: '2-digit',
    minute: '2-digit',
    second: '2-digit',
    hour12: false
});

const currentTimeDisplay = ref(props.stats.current_time || '--:--:--');
let timer = null;

const updateClock = () => {
    currentTimeDisplay.value = timeFormatter.format(new Date());
};

onMounted(() => {
    updateClock();
    // Actualización por segundo para mantener precisión
    timer = setInterval(updateClock, 1000);
});

onUnmounted(() => {
    if (timer) clearInterval(timer);
});

// --- Motor de Análisis de Turno (Algoritmo de Detección de Desviaciones) ---
// Centralizamos los cálculos pesados en una propiedad computada reactiva
const shiftAnalytics = computed(() => {
    const lines = props.linesStatus || [];
    if (lines.length === 0) return null;

    const efficiencies = lines.map(l => l.efficiency || 0);
    const sum = efficiencies.reduce((acc, val) => acc + val, 0);
    const avgEfficiency = sum / lines.length;

    // Clasificación de datos basada en la media del turno
    const aboveAverage = lines.filter(l => l.efficiency > avgEfficiency);
    const belowAverage = lines.filter(l => l.efficiency <= avgEfficiency);

    // Lógica predictiva para recomendaciones operativas
    let recommendation = "Flujo de producción balanceado. Mantener ritmo de secado.";
    let trend = "Estable";

    if (avgEfficiency > 75) {
        trend = "Óptima";
    } else if (avgEfficiency < 60) {
        trend = "Crítica";
        recommendation = "Baja eficiencia detectada. Verificar temperatura de moldes y presión.";
    }

    const anomalies = lines.filter(l => l.efficiency < 50 && l.is_active);
    if (anomalies.length > 0) {
        recommendation = `Alerta: ${anomalies.length} línea(s) presentan cuellos de botella técnicos.`;
    }

    return {
        avgEfficiency: avgEfficiency.toFixed(1),
        aboveAverage,
        belowAverage,
        trend,
        recommendation
    };
});

// Helpers para estilos dinámicos rápidos
const getStatusClasses = (color) => {
    const maps = {
        'gray':   'bg-gray-100 text-gray-500 border-gray-200',
        'blue':   'bg-blue-50 text-blue-600 border-blue-200',     
        'green':  'bg-emerald-50 text-emerald-600 border-emerald-200', 
        'yellow': 'bg-amber-50 text-amber-600 border-amber-200',   
        'red':    'bg-rose-50 text-rose-600 border-rose-200',      
    };
    return maps[color] || maps['gray'];
};

const maxWasteValue = computed(() => {
    return (props.topDefects && props.topDefects.length > 0) ? props.topDefects[0].total : 1;
});
</script>

<template>
    <Head title="Panel de Control" />

    <AuthenticatedLayout>
        
        <!-- Sección de Encabezado y Tiempo Real -->
        <div class="mb-8">
            <div class="flex flex-col md:flex-row md:items-end justify-between mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800 tracking-tight">Centro de Control</h2>
                    <p class="text-gray-500 font-medium">{{ stats.date_human }}</p>
                </div>
                
                <div class="mt-4 md:mt-0 flex gap-4">
                    <div class="bg-white px-5 py-3 rounded-xl shadow-sm border border-gray-200 text-center min-w-[90px]">
                        <p class="text-[10px] text-gray-400 uppercase font-black tracking-widest">Semana</p>
                        <p class="text-2xl font-black text-indigo-600 leading-none">{{ stats.week_number }}</p>
                    </div>
                    <div class="bg-white px-5 py-3 rounded-xl shadow-sm border border-gray-200 text-center min-w-[90px]">
                        <p class="text-[10px] text-gray-400 uppercase font-black tracking-widest">Día Juliano</p>
                        <p class="text-2xl font-black text-gray-700 leading-none">{{ stats.julian_day }}</p>
                    </div>
                    <div class="bg-slate-950 px-5 py-3 rounded-xl shadow-lg text-center text-white min-w-[145px] border border-slate-800">
                        <p class="text-[10px] text-slate-500 uppercase font-black tracking-widest">Hora Actual</p>
                        <p class="text-2xl font-black leading-none font-mono text-emerald-400 tracking-tighter">
                            {{ currentTimeDisplay }}
                        </p>
                    </div>
                </div>
            </div>
            
            <!-- Resumen Operativo (KPIs) -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Líneas Activas -->
                <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 relative overflow-hidden flex flex-col justify-between h-full">
                    <div class="absolute top-0 right-0 w-1.5 h-full bg-indigo-500"></div>
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-xs font-bold text-gray-400 uppercase mb-1">Líneas Activas</p>
                            <h3 class="text-3xl font-black text-gray-800">{{ stats.active_lines }}<span class="text-lg text-gray-400 font-medium">/4</span></h3>
                        </div>
                        <div class="p-2 bg-indigo-50 rounded-lg text-indigo-600">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        </div>
                    </div>
                    <div class="mt-4">
                        <div class="text-xs text-gray-500 mb-1 flex justify-between font-bold"><span>Ocupación</span> <span>{{ ((stats.active_lines || 0) / 4) * 100 }}%</span></div>
                        <div class="w-full bg-gray-100 h-2 rounded-full overflow-hidden">
                            <div class="bg-indigo-500 h-full transition-all duration-1000" :style="`width: ${((stats.active_lines || 0) / 4) * 100}%`"></div>
                        </div>
                    </div>
                </div>

                <!-- Registros -->
                <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 relative overflow-hidden flex flex-col justify-between h-full">
                    <div class="absolute top-0 right-0 w-1.5 h-full bg-emerald-500"></div>
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-xs font-bold text-gray-400 uppercase mb-1">Registros Hoy</p>
                            <h3 class="text-3xl font-black text-gray-800">{{ stats.total_records }}</h3>
                        </div>
                        <div class="p-2 bg-emerald-50 rounded-lg text-emerald-600">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012 2v2H9V5z"/></svg>
                        </div>
                    </div>
                    <p class="text-xs text-emerald-600 font-bold mt-2 flex items-center">
                        <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full mr-1.5 animate-pulse"></span>
                        Transmisión activa
                    </p>
                </div>

                <!-- Desperdicio -->
                <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 relative overflow-hidden flex flex-col justify-between h-full">
                    <div class="absolute top-0 right-0 w-1.5 h-full bg-rose-500"></div>
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-xs font-bold text-gray-400 uppercase mb-1 text-rose-500">Desperdicio</p>
                            <h3 class="text-3xl font-black text-gray-800">{{ stats.total_waste }} <span class="text-sm text-gray-500 font-medium">Kg</span></h3>
                        </div>
                        <div class="p-2 bg-rose-50 rounded-lg text-rose-600">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </div>
                    </div>
                    <Link :href="route('waste.index')" class="text-xs text-rose-600 font-bold mt-2 hover:underline">Ver auditoría &rarr;</Link>
                </div>

                <!-- Pareto de Defectos -->
                <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 flex flex-col h-full justify-center">
                    <p class="text-[10px] font-black text-gray-400 uppercase mb-3 tracking-widest">Top Defectos</p>
                    <div v-if="topDefects && topDefects.length > 0" class="space-y-3">
                        <div v-for="(defect, i) in topDefects" :key="i" class="group">
                            <div class="flex justify-between items-center text-[10px] mb-1">
                                <span class="font-bold text-gray-700 truncate w-24" :title="defect.name">{{ defect.name }}</span>
                                <span class="font-black text-rose-600">{{ defect.total }}Kg</span>
                            </div>
                            <div class="w-full bg-gray-100 h-1.5 rounded-full overflow-hidden">
                                <div class="bg-rose-500 h-full transition-all duration-1000 group-hover:bg-rose-600" 
                                     :style="`width: ${(defect.total / maxWasteValue) * 100}%`"
                                ></div>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-center text-xs text-gray-400 italic py-2">Sin registros de pérdida.</div>
                </div>
            </div>
        </div>

        <!-- Módulo de Monitoreo de Líneas -->
        <div class="mb-8">
            <h3 class="text-lg font-bold text-gray-700 mb-4 tracking-tight">Estado Operativo de Líneas</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
                <div v-for="line in linesStatus" :key="line.id" 
                    class="bg-white rounded-2xl shadow-sm border hover:shadow-xl transition-all duration-300 flex flex-col overflow-hidden"
                    :class="line.is_active ? 'border-gray-200' : 'border-gray-100 opacity-90 bg-gray-50'">
                    
                    <div class="p-5 flex-1">
                        <div class="flex justify-between items-start mb-5">
                            <div class="flex items-center">
                                <div class="w-10 h-10 rounded-xl flex items-center justify-center text-white font-black text-lg mr-3 shadow-md transition-colors"
                                     :class="line.is_active ? 'bg-indigo-600' : 'bg-gray-400'">
                                    {{ (line.name || '').replace('Línea ', '') }}
                                </div>
                                <div>
                                    <h4 class="text-sm font-black text-gray-800 leading-none">{{ line.name }}</h4>
                                    <span class="text-[9px] text-gray-400 uppercase font-bold">ID: {{ line.slug }}</span>
                                </div>
                            </div>
                            <span class="px-2.5 py-1 text-[9px] font-black rounded-lg border uppercase tracking-tighter"
                                :class="getStatusClasses(line.status_color)">
                                {{ line.status_text }}
                            </span>
                        </div>

                        <div class="space-y-2.5 text-sm mt-6">
                            <div class="flex justify-between items-center border-b border-gray-50 pb-2">
                                <span class="text-gray-400 text-[10px] uppercase font-black tracking-wider">Responsable</span>
                                <span class="font-bold text-gray-700 truncate w-32 text-right">{{ line.coordinator || '-' }}</span>
                            </div>
                            <div class="flex justify-between items-center pt-1">
                                <span class="text-gray-400 text-[10px] uppercase font-black tracking-wider">Eficiencia</span>
                                <span class="font-black text-gray-900">{{ line.efficiency || 0 }}%</span>
                            </div>
                            <div class="w-full bg-gray-100 rounded-full h-2 overflow-hidden">
                                <div class="h-full transition-all duration-1000" 
                                     :class="(line.efficiency || 0) >= 80 ? 'bg-emerald-500' : ((line.efficiency || 0) >= 50 ? 'bg-amber-400' : 'bg-rose-400')"
                                     :style="`width: ${line.efficiency || 0}%`"
                                ></div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-slate-50 p-4 border-t border-gray-100">
                        <Link :href="line.is_active ? route('production.index', line.slug) : route('production.create', line.slug)"
                            class="flex items-center justify-center w-full py-2.5 rounded-xl text-xs font-black transition-all shadow-sm border uppercase tracking-widest"
                            :class="line.is_active 
                                ? 'bg-white text-indigo-600 border-indigo-200 hover:bg-indigo-50' 
                                : 'bg-indigo-600 text-white border-transparent hover:bg-indigo-700'"
                        >
                            {{ line.is_active ? 'Gestionar' : 'Iniciar' }}
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <!-- MÓDULO INTELIGENTE: Informes de Turno & Análisis Predictivo -->
        <div class="mb-8">
            <h3 class="text-lg font-bold text-gray-700 mb-4 flex items-center tracking-tight">
                <svg class="w-6 h-6 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Informes de Turno e Inteligencia de Proceso
            </h3>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Análisis de Desviaciones (ML Context) -->
                <div class="lg:col-span-2 bg-white rounded-3xl shadow-sm border border-gray-200 p-8">
                    <div class="flex items-start justify-between mb-8">
                        <div>
                            <h4 class="text-lg font-black text-gray-800">Desempeño Comparativo de Producción</h4>
                            <p class="text-xs text-gray-500 mt-1 uppercase font-bold tracking-wider">Identificación de variables fuera de la media</p>
                        </div>
                        <div class="flex flex-col items-end">
                            <span class="px-4 py-1.5 bg-indigo-600 text-white rounded-full text-[10px] font-black uppercase tracking-widest shadow-md">
                                Estado: {{ shiftAnalytics?.trend }}
                            </span>
                            <span class="text-[9px] text-gray-400 mt-2 font-bold uppercase tracking-tighter">Media de eficiencia: {{ shiftAnalytics?.avgEfficiency }}%</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                        <!-- Variables por ENCIMA de la media -->
                        <div class="space-y-4">
                            <h5 class="text-[10px] font-black text-emerald-600 uppercase tracking-widest flex items-center border-b border-emerald-100 pb-2">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 10l7-7 7 7M5 19l7-7 7 7"/></svg>
                                Rendimiento Superior
                            </h5>
                            <div v-for="line in shiftAnalytics?.aboveAverage" :key="line.id" 
                                 class="p-4 bg-emerald-50/40 rounded-2xl border border-emerald-100 flex justify-between items-center transition-all hover:scale-[1.02]">
                                <span class="text-sm font-black text-gray-700">{{ line.name }}</span>
                                <div class="flex flex-col items-end">
                                    <span class="text-xs font-black text-emerald-700">+{{ (line.efficiency - shiftAnalytics.avgEfficiency).toFixed(1) }}%</span>
                                    <span class="text-[10px] font-bold text-emerald-600 opacity-70">Sobre la media</span>
                                </div>
                            </div>
                            <div v-if="!shiftAnalytics?.aboveAverage.length" class="text-xs text-gray-400 italic">No hay datos por encima del promedio.</div>
                        </div>

                        <!-- Variables por DEBAJO de la media -->
                        <div class="space-y-4">
                            <h5 class="text-[10px] font-black text-rose-600 uppercase tracking-widest flex items-center border-b border-rose-100 pb-2">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 14l-7 7-7-7m14-9l-7 7-7-7"/></svg>
                                Alerta de Optimización
                            </h5>
                            <div v-for="line in shiftAnalytics?.belowAverage" :key="line.id" 
                                 class="p-4 bg-rose-50/40 rounded-2xl border border-rose-100 flex justify-between items-center transition-all hover:scale-[1.02]">
                                <span class="text-sm font-black text-gray-700">{{ line.name }}</span>
                                <div class="flex flex-col items-end">
                                    <span class="text-xs font-black text-rose-700">{{ (line.efficiency - shiftAnalytics.avgEfficiency).toFixed(1) }}%</span>
                                    <span class="text-[10px] font-bold text-rose-600 opacity-70">Bajo la media</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Diagnóstico Predictivo -->
                <div class="bg-slate-950 rounded-3xl shadow-2xl border border-slate-800 p-8 flex flex-col justify-between relative overflow-hidden">
                    <!-- Efecto de fondo IA -->
                    <div class="absolute -top-16 -right-16 w-48 h-48 bg-indigo-600/20 rounded-full blur-[80px]"></div>
                    <div class="absolute -bottom-16 -left-16 w-48 h-48 bg-emerald-600/10 rounded-full blur-[80px]"></div>
                    
                    <div class="relative z-10">
                        <div class="w-12 h-12 bg-indigo-500/20 rounded-2xl flex items-center justify-center text-indigo-400 mb-6 border border-indigo-500/30">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        </div>
                        <h4 class="text-white font-black text-xl mb-4 tracking-tight text-balance">Diagnóstico del Asistente de Turno</h4>
                        <div class="bg-slate-900/80 rounded-2xl p-5 border border-slate-800">
                            <p class="text-slate-300 text-sm leading-relaxed font-medium italic">
                                "{{ shiftAnalytics?.recommendation }}"
                            </p>
                        </div>
                    </div>

                    <div class="mt-8 relative z-10">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-[10px] text-slate-500 uppercase font-black tracking-widest">Nivel de Anomalías</span>
                            <span class="text-[10px] text-indigo-400 font-bold uppercase tracking-tighter">Turno Actual</span>
                        </div>
                        <div class="flex gap-2">
                            <div v-for="i in 5" :key="i" 
                                 class="h-1.5 flex-1 rounded-full transition-all duration-500"
                                 :class="i <= (shiftAnalytics?.avgEfficiency < 60 ? 4 : 1) ? 'bg-indigo-500 shadow-[0_0_8px_rgba(99,102,241,0.6)]' : 'bg-slate-800'">
                            </div>
                        </div>
                        <p class="text-[9px] text-slate-500 mt-4 font-bold text-center uppercase tracking-widest opacity-60 italic">Motor de aprendizaje activado</p>
                    </div>
                </div>
            </div>
        </div>

    </AuthenticatedLayout>
</template>