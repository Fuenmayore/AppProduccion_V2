<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

/**
 * Definición de propiedades con sintaxis de objeto para mayor estabilidad.
 */
defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

/**
 * Inicialización del formulario de autenticación.
 */
const form = useForm({
    email: '',
    password: '',
    remember: false,
});

/**
 * Procesa el envío de las credenciales al sistema.
 */
const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Acceso al Sistema - TRAZABILIDAD" />

    <!-- Contenedor principal con fondo de alta visibilidad para descanso visual de adultos -->
    <div class="min-h-screen bg-slate-50 flex items-center justify-center p-4 sm:p-6 font-sans text-slate-900 selection:bg-indigo-100">
        
        <!-- Elementos decorativos sutiles de fondo (Arquitectura de luz) -->
        <div class="absolute inset-0 pointer-events-none overflow-hidden" aria-hidden="true">
            <div class="absolute top-0 right-0 w-1/3 h-1/3 bg-indigo-100/40 rounded-full blur-[100px]"></div>
            <div class="absolute bottom-0 left-0 w-1/4 h-1/4 bg-blue-100/30 rounded-full blur-[80px]"></div>
            <!-- Patrón de rejilla industrial minimalista -->
            <div class="absolute inset-0 opacity-[0.05]" style="background-image: radial-gradient(#6366f1 1.5px, transparent 0); background-size: 40px 40px;"></div>
        </div>

        <div class="relative w-full max-w-lg z-10 animate-fade-in-up">
            
            <!-- Identidad Visual: El nombre centrado en el propósito: TRAZABILIDAD -->
            <div class="flex flex-col items-center mb-10 group">
                <div class="relative mb-6">
                    <div class="absolute inset-0 bg-indigo-600 rounded-2xl blur-xl opacity-10 group-hover:opacity-30 transition-opacity"></div>
                    <div class="relative w-20 h-20 bg-slate-900 rounded-2xl flex items-center justify-center shadow-2xl transition-transform group-hover:scale-105 group-hover:rotate-2">
                        <!-- Icono de Seguridad/Planta -->
                        <svg class="w-12 h-12 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                </div>
                <h1 class="text-4xl font-black tracking-tighter text-slate-800 uppercase italic leading-none text-center">
                    <span class="text-indigo-600 not-italic">TRAZABILIDAD</span>
                </h1>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.5em] mt-3 italic">Industrial Monitoring Stack</p>
            </div>

            <!-- Tarjeta de Login (Diseño Premium Blanco) -->
            <div class="bg-white border border-slate-200 rounded-[3rem] shadow-[0_30px_70px_-15px_rgba(0,0,0,0.08)] p-10 md:p-14 relative overflow-hidden">
                <!-- Barra de acento superior indicando seguridad -->
                <div class="absolute top-0 left-0 w-full h-1.5 bg-indigo-600"></div>

                <!-- Mensaje de estado de Laravel -->
                <div v-if="status" class="mb-8 p-4 bg-emerald-50 border border-emerald-100 rounded-2xl text-emerald-700 text-sm font-bold text-center italic">
                    {{ status }}
                </div>

                <h2 class="text-2xl font-black text-slate-800 mb-10 uppercase tracking-tight border-b border-slate-50 pb-5">Ingreso de Personal</h2>

                <form @submit.prevent="submit" class="space-y-8">
                    <!-- Campo: Correo Electrónico Operativo -->
                    <div class="space-y-3">
                        <label for="email" class="block text-[11px] font-black text-slate-400 uppercase tracking-widest ml-2">Email Autorizado</label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-6 flex items-center pointer-events-none text-slate-300 group-focus-within:text-indigo-500 transition-colors">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                            <input 
                                id="email" 
                                type="email" 
                                v-model="form.email" 
                                class="w-full bg-slate-50 border border-slate-200 rounded-2xl pl-14 pr-6 py-5 text-slate-900 text-lg focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-600 transition-all placeholder:text-slate-300 font-bold" 
                                placeholder="usuario@empresa.com"
                                required 
                                autofocus
                                autocomplete="username"
                            >
                        </div>
                        <InputError class="mt-1 ml-4" :message="form.errors.email" />
                    </div>

                    <!-- Campo: Llave de Seguridad -->
                    <div class="space-y-3">
                        <div class="flex justify-between items-center px-2">
                            <label for="password" class="block text-[11px] font-black text-slate-400 uppercase tracking-widest">Contraseña</label>
                            <Link v-if="canResetPassword" :href="route('password.request')" class="text-[10px] font-bold text-indigo-600 hover:text-indigo-800 transition-colors uppercase tracking-tight">
                                ¿Recuperar clave?
                            </Link>
                        </div>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-6 flex items-center pointer-events-none text-slate-300 group-focus-within:text-indigo-500 transition-colors">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </div>
                            <input 
                                id="password" 
                                type="password" 
                                v-model="form.password" 
                                class="w-full bg-slate-50 border border-slate-200 rounded-2xl pl-14 pr-6 py-5 text-slate-900 text-lg focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-600 transition-all placeholder:text-slate-300 font-bold" 
                                placeholder="••••••••"
                                required 
                                autocomplete="current-password"
                            >
                        </div>
                        <InputError class="mt-1 ml-4" :message="form.errors.password" />
                    </div>

                    <!-- Recordar sesión activa -->
                    <div class="flex items-center px-4">
                        <label class="flex items-center cursor-pointer group">
                            <Checkbox name="remember" v-model:checked="form.remember" class="w-6 h-6 rounded-lg border-slate-300 text-indigo-600 focus:ring-indigo-500 shadow-sm" />
                            <span class="ms-4 text-sm font-bold text-slate-500 group-hover:text-slate-800 transition-colors uppercase tracking-tight">Mantener sesión activa</span>
                        </label>
                    </div>

                    <!-- Botón de Acción Principal -->
                    <div class="pt-6">
                        <button 
                            type="submit" 
                            :disabled="form.processing" 
                            class="w-full bg-slate-900 text-white py-6 rounded-[2rem] font-black text-xl uppercase tracking-[0.2em] shadow-[0_20px_50px_-10px_rgba(15,23,42,0.4)] hover:shadow-indigo-200 hover:bg-indigo-600 hover:-translate-y-1 active:scale-95 transition-all border-b-8 border-slate-950 flex items-center justify-center disabled:opacity-50"
                        >
                            <span v-if="!form.processing">Entrar al Sistema</span>
                            <span v-else class="flex items-center">
                                <svg class="animate-spin -ml-1 mr-3 h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Validando...
                            </span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Registro / Ayuda para el usuario -->
            <div class="mt-12 text-center">
                <p class="text-xs font-black text-slate-400 uppercase tracking-widest leading-loose">
                    ¿Requiere acceso para esta planta? <br />
                    <Link :href="route('register')" class="text-indigo-600 hover:underline font-bold transition-all">
                        Solicitar cuenta de terminal
                    </Link>
                </p>
            </div>
        </div>

        <!-- Footer de Seguridad Técnica -->
        <div class="absolute bottom-6 w-full text-center pointer-events-none opacity-40">
            <p class="text-[9px] font-black text-slate-400 uppercase tracking-[0.6em]">
                Protocolo de Trazabilidad Seguro <span class="mx-2 text-indigo-300">•</span> v2.5.0 Stable Build
            </p>
        </div>
    </div>
</template>

<style scoped>
/* Estilos para asegurar legibilidad en el autocompletado */
input:-webkit-autofill,
input:-webkit-autofill:hover, 
input:-webkit-autofill:focus {
  -webkit-text-fill-color: #0f172a;
  -webkit-box-shadow: 0 0 0px 1000px #f8fafc inset;
  transition: background-color 5000s ease-in-out 0s;
}

@keyframes fadeInUp {
    from { 
        opacity: 0; 
        transform: translateY(30px); 
    }
    to { 
        opacity: 1; 
        transform: translateY(0); 
    }
}

.animate-fade-in-up {
    animation: fadeInUp 1s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

.font-sans {
    font-family: 'Inter', system-ui, -apple-system, sans-serif;
}
</style>