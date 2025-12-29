<script setup>
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted, computed } from 'vue';

// --- Lógica de Tiempo y Turnos (Coherencia con Login) ---
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
        hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true
    });
});

const currentShift = computed(() => {
    const hour = currentTime.value.getHours();
    if (hour >= 6 && hour < 14) return { name: 'Turno Mañana', id: 'T1', color: 'text-amber-500', bg: 'bg-amber-50' };
    if (hour >= 14 && hour < 22) return { name: 'Turno Tarde', id: 'T2', color: 'text-indigo-500', bg: 'bg-indigo-50' };
    return { name: 'Turno Noche', id: 'T3', color: 'text-purple-500', bg: 'bg-purple-50' };
});

// --- Formulario de Registro ---
const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <Head title="Alta de Personal - TRAZABILIDAD" />

    <div class="min-h-screen bg-[#F8FAFC] flex items-center justify-center p-4 sm:p-6 font-sans text-slate-900 relative overflow-hidden">
        
        <div class="absolute inset-0 pointer-events-none" aria-hidden="true">
            <div class="absolute top-[-10%] left-[-10%] w-[500px] h-[500px] bg-blue-100/50 rounded-full blur-[120px]"></div>
            <div class="absolute inset-0 opacity-[0.03]" style="background-image: url('https://www.transparenttextures.com/patterns/carbon-fibre.png');"></div>
        </div>

        <div class="relative w-full max-w-[1100px] grid lg:grid-cols-2 gap-0 bg-white rounded-[3rem] shadow-[0_40px_100px_-20px_rgba(0,0,0,0.15)] overflow-hidden animate-fade-in">
            
            <div class="hidden lg:flex flex-col justify-between p-16 bg-slate-900 relative">
                <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(#ffffff 1px, transparent 0); background-size: 30px 30px;"></div>

                <div class="relative z-10">
                    <div class="inline-flex items-center gap-3 px-4 py-2 bg-emerald-500/10 border border-emerald-500/20 rounded-full mb-8">
                        <span class="text-emerald-400 text-[10px] font-black uppercase tracking-[0.2em]">Protocolo de Onboarding v2.5</span>
                    </div>
                    <h1 class="text-6xl font-black text-white leading-none tracking-tighter uppercase italic">
                        NUEVO<br /><span class="text-indigo-500 not-italic">OPERADOR</span>
                    </h1>
                    <p class="mt-6 text-slate-400 max-w-xs text-lg font-medium leading-relaxed">
                        Complete el registro para obtener sus credenciales de acceso a la planta y seguimiento de líneas.
                    </p>
                </div>

                <div class="relative z-10 space-y-6">
                    <div class="bg-white/5 backdrop-blur-md border border-white/10 p-6 rounded-3xl inline-block min-w-[200px]">
                        <p class="text-[10px] font-black text-indigo-400 uppercase tracking-widest mb-1">Hora de Registro</p>
                        <p class="text-3xl font-mono font-bold text-white tabular-nums">{{ formattedTime }}</p>
                    </div>
                    <div class="flex items-center gap-4 text-slate-500 text-xs font-bold uppercase tracking-widest">
                        <span class="w-12 h-[1px] bg-slate-700"></span>
                        <span></span>
                    </div>
                </div>
            </div>

            <div class="p-10 md:p-16 flex flex-col justify-center">
                <div class="mb-10">
                    <h2 class="text-3xl font-black text-slate-900 tracking-tight uppercase">Crear Cuenta</h2>
                    <p class="text-slate-400 font-bold text-sm mt-1">Ingrese sus datos operativos oficiales</p>
                </div>

                <form @submit.prevent="submit" class="grid grid-cols-1 gap-5">
                    <div class="group">
                        <label for="name" class="block text-[11px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 ml-1">Nombre Completo</label>
                        <input 
                            id="name" 
                            type="text" 
                            v-model="form.name" 
                            class="w-full bg-slate-50 border-slate-200 rounded-2xl px-6 py-4 text-slate-900 font-bold focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-600 transition-all placeholder:text-slate-300" 
                            placeholder="Ej. Juan Pérez"
                            required 
                            autofocus
                        >
                        <InputError class="mt-1 ml-2" :message="form.errors.name" />
                    </div>

                    <div class="group">
                        <label for="email" class="block text-[11px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 ml-1">Correo Corporativo</label>
                        <input 
                            id="email" 
                            type="email" 
                            v-model="form.email" 
                            class="w-full bg-slate-50 border-slate-200 rounded-2xl px-6 py-4 text-slate-900 font-bold focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-600 transition-all placeholder:text-slate-300" 
                            placeholder="usuario@planta.com"
                            required
                        >
                        <InputError class="mt-1 ml-2" :message="form.errors.email" />
                    </div>

                    <div class="grid md:grid-cols-2 gap-4">
                        <div class="group">
                            <label for="password" class="block text-[11px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 ml-1">Contraseña</label>
                            <input 
                                id="password" 
                                type="password" 
                                v-model="form.password" 
                                class="w-full bg-slate-50 border-slate-200 rounded-2xl px-6 py-4 text-slate-900 font-bold focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-600 transition-all" 
                                required
                            >
                        </div>
                        <div class="group">
                            <label for="password_confirmation" class="block text-[11px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 ml-1">Confirmar</label>
                            <input 
                                id="password_confirmation" 
                                type="password" 
                                v-model="form.password_confirmation" 
                                class="w-full bg-slate-50 border-slate-200 rounded-2xl px-6 py-4 text-slate-900 font-bold focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-600 transition-all" 
                                required
                            >
                        </div>
                        <InputError class="mt-1 ml-2 col-span-2" :message="form.errors.password" />
                    </div>

                    <div class="pt-6">
                        <button 
                            type="submit" 
                            :disabled="form.processing" 
                            class="w-full bg-indigo-600 text-white py-5 rounded-2xl font-black text-lg uppercase tracking-[0.2em] shadow-lg shadow-indigo-200 hover:bg-slate-900 hover:-translate-y-1 active:scale-[0.98] transition-all flex items-center justify-center disabled:opacity-50"
                        >
                            <span v-if="!form.processing">Finalizar Registro</span>
                            <svg v-else class="animate-spin h-6 w-6 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </button>
                    </div>
                </form>

                <div class="mt-8 text-center">
                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">
                        ¿Ya tiene una terminal asignada? 
                        <Link :href="route('login')" class="text-indigo-600 hover:text-indigo-800 font-black ml-2 underline decoration-2 underline-offset-4">
                            Iniciar Sesión
                        </Link>
                    </p>
                </div>
            </div>
        </div>

        <div class="absolute bottom-6 w-full text-center opacity-30 pointer-events-none">
            <span class="text-[9px] font-black uppercase tracking-[0.4em] text-slate-500">
                Data Storage System <span class="mx-2">•</span> ISO 9001 Compliant
            </span>
        </div>
    </div>
</template>

<style scoped>
@keyframes fadeIn {
    from { opacity: 0; transform: scale(0.95); }
    to { opacity: 1; transform: scale(1); }
}
.animate-fade-in {
    animation: fadeIn 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

/* Estilo para los inputs de contraseña para evitar que se vea el fondo amarillo de Chrome */
input:-webkit-autofill {
    -webkit-box-shadow: 0 0 0px 1000px #f8fafc inset;
}
</style>