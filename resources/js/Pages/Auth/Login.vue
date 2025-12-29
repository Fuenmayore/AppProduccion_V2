<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted, computed } from 'vue';

defineProps({
    canResetPassword: { type: Boolean },
    status: { type: String },
});

// --- Lógica de Tiempo y Turnos ---
const currentTime = ref(new Date());
let timer;

const updateClock = () => {
    currentTime.value = new Date();
};

onMounted(() => {
    timer = setInterval(updateClock, 1000);
});

onUnmounted(() => {
    clearInterval(timer);
});

const formattedTime = computed(() => {
    return currentTime.value.toLocaleTimeString('es-ES', {
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: true
    });
});

const formattedDate = computed(() => {
    return currentTime.value.toLocaleDateString('es-ES', {
        weekday: 'long',
        day: 'numeric',
        month: 'long'
    });
});

const currentShift = computed(() => {
    const hour = currentTime.value.getHours();
    if (hour >= 6 && hour < 14) return { name: 'Turno Mañana', id: 'T1', color: 'text-amber-500', bg: 'bg-amber-50' };
    if (hour >= 14 && hour < 22) return { name: 'Turno Tarde', id: 'T2', color: 'text-indigo-500', bg: 'bg-indigo-50' };
    return { name: 'Turno Noche', id: 'T3', color: 'text-purple-500', bg: 'bg-purple-50' };
});

// --- Formulario ---
const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Acceso al Sistema - TRAZABILIDAD" />

    <div class="min-h-screen bg-[#F8FAFC] flex items-center justify-center p-4 sm:p-6 font-sans text-slate-900 selection:bg-indigo-100 relative overflow-hidden">
        
        <div class="absolute inset-0 pointer-events-none" aria-hidden="true">
            <div class="absolute top-[-10%] right-[-10%] w-[500px] h-[500px] bg-indigo-100/50 rounded-full blur-[120px]"></div>
            <div class="absolute bottom-[-10%] left-[-10%] w-[400px] h-[400px] bg-blue-100/40 rounded-full blur-[100px]"></div>
            <div class="absolute inset-0 opacity-[0.03]" style="background-image: url('https://www.transparenttextures.com/patterns/carbon-fibre.png');"></div>
        </div>

        <div class="relative w-full max-w-[1100px] grid lg:grid-cols-2 gap-0 bg-white rounded-[3rem] shadow-[0_40px_100px_-20px_rgba(0,0,0,0.15)] overflow-hidden animate-fade-in">
            
            <div class="hidden lg:flex flex-col justify-between p-16 bg-slate-900 relative">
                <div class="absolute top-0 left-0 w-full h-full opacity-10 pointer-events-none">
                    <div class="absolute inset-0" style="background-image: radial-gradient(#ffffff 1px, transparent 0); background-size: 30px 30px;"></div>
                </div>

                <div class="relative z-10">
                    <div class="inline-flex items-center gap-3 px-4 py-2 bg-indigo-500/10 border border-indigo-500/20 rounded-full mb-8">
                        <span class="relative flex h-3 w-3">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-indigo-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-3 w-3 bg-indigo-500"></span>
                        </span>
                        <span class="text-indigo-100 text-[10px] font-black uppercase tracking-[0.2em]">Terminal Operativa Activa</span>
                    </div>

                    <h1 class="text-6xl font-black text-white leading-none tracking-tighter uppercase italic">
                        TRAZA<br /><span class="text-indigo-500 not-italic">BILIDAD</span>
                    </h1>
                    <p class="mt-6 text-slate-400 max-w-xs text-lg font-medium leading-relaxed">
                        Sistema de control de producción y gestión de variables en tiempo real.
                    </p>
                </div>

                <div class="relative z-10 space-y-6">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-white/5 backdrop-blur-md border border-white/10 p-6 rounded-3xl">
                            <p class="text-[10px] font-black text-indigo-400 uppercase tracking-widest mb-1">Hora Local</p>
                            <p class="text-3xl font-mono font-bold text-white tabular-nums">{{ formattedTime }}</p>
                        </div>
                        <div class="bg-white/5 backdrop-blur-md border border-white/10 p-6 rounded-3xl">
                            <p class="text-[10px] font-black text-indigo-400 uppercase tracking-widest mb-1">Estado del Turno</p>
                            <div class="flex items-center gap-2">
                                <span class="text-2xl font-bold text-white uppercase italic">{{ currentShift.id }}</span>
                                <span class="text-[10px] font-bold text-slate-400 leading-tight">{{ currentShift.name }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="px-2">
                        <p class="text-sm font-bold text-slate-500 capitalize">{{ formattedDate }}</p>
                    </div>
                </div>
            </div>

            <div class="p-10 md:p-16 lg:p-20 flex flex-col justify-center">
                <div class="mb-10 lg:hidden flex flex-col items-center">
                    <h1 class="text-3xl font-black text-slate-900 italic tracking-tighter">TRAZABILIDAD</h1>
                    <div :class="`mt-2 px-4 py-1 rounded-full text-[10px] font-black uppercase tracking-widest ${currentShift.bg} ${currentShift.color}`">
                        {{ currentShift.name }} • {{ formattedTime }}
                    </div>
                </div>

                <div class="mb-12">
                    <h2 class="text-3xl font-black text-slate-900 tracking-tight uppercase">Ingreso</h2>
                    <p class="text-slate-400 font-bold text-sm mt-1">Identifíquese para registrar producción</p>
                </div>

                <div v-if="status" class="mb-6 p-4 bg-emerald-50 border border-emerald-100 rounded-2xl text-emerald-700 text-sm font-bold italic">
                    {{ status }}
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                    <div class="group">
                        <label for="email" class="block text-[11px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 ml-1">Usuario / Email</label>
                        <div class="relative">
                            <input 
                                id="email" 
                                type="email" 
                                v-model="form.email" 
                                class="w-full bg-slate-50 border-slate-200 rounded-2xl px-6 py-5 text-slate-900 font-bold focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-600 transition-all placeholder:text-slate-300" 
                                placeholder="nombre@empresa.com"
                                required 
                                autofocus
                            >
                        </div>
                        <InputError class="mt-2 ml-2" :message="form.errors.email" />
                    </div>

                    <div>
                        <div class="flex justify-between items-center mb-2 px-1">
                            <label for="password" class="block text-[11px] font-black text-slate-400 uppercase tracking-[0.2em]">Contraseña</label>
                            <Link v-if="canResetPassword" :href="route('password.request')" class="text-[10px] font-black text-indigo-600 hover:text-indigo-800 uppercase tracking-tighter">
                                ¿Olvidó su clave?
                            </Link>
                        </div>
                        <input 
                            id="password" 
                            type="password" 
                            v-model="form.password" 
                            class="w-full bg-slate-50 border-slate-200 rounded-2xl px-6 py-5 text-slate-900 font-bold focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-600 transition-all placeholder:text-slate-300" 
                            placeholder="••••••••"
                            required 
                        >
                        <InputError class="mt-2 ml-2" :message="form.errors.password" />
                    </div>

                    <div class="flex items-center px-2">
                        <label class="flex items-center cursor-pointer group">
                            <Checkbox name="remember" v-model:checked="form.remember" class="w-5 h-5 rounded-md border-slate-300 text-slate-900 focus:ring-slate-900" />
                            <span class="ms-3 text-sm font-bold text-slate-500 group-hover:text-slate-900 transition-colors">Recordar mi terminal</span>
                        </label>
                    </div>

                    <div class="pt-4">
                        <button 
                            type="submit" 
                            :disabled="form.processing" 
                            class="w-full bg-slate-900 text-white py-6 rounded-2xl font-black text-lg uppercase tracking-[0.2em] shadow-xl hover:bg-indigo-600 hover:-translate-y-1 active:scale-[0.98] transition-all flex items-center justify-center disabled:opacity-50"
                        >
                            <span v-if="!form.processing">Acceder al Sistema</span>
                            <svg v-else class="animate-spin h-6 w-6 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </button>
                    </div>
                </form>

                <div class="mt-10 pt-10 border-t border-slate-100">
                    <p class="text-center text-[11px] font-bold text-slate-400 uppercase tracking-widest">
                        ¿Nueva estación? 
                        <Link :href="route('register')" class="text-indigo-600 hover:text-indigo-800 ml-2">Registrar cuenta</Link>
                    </p>
                </div>
            </div>
        </div>

        <div class="absolute bottom-6 w-full flex justify-center gap-8 opacity-30 pointer-events-none">
            <span class="text-[10px] font-black uppercase tracking-[0.3em]">Encrypted Node 01</span>
            <span class="text-[10px] font-black uppercase tracking-[0.3em]">v2.5.0-STABLE</span>
        </div>
    </div>
</template>

<style scoped>
@keyframes fadeIn {
    from { opacity: 0; transform: scale(0.98) translateY(10px); }
    to { opacity: 1; transform: scale(1) translateY(0); }
}
.animate-fade-in {
    animation: fadeIn 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}
</style>