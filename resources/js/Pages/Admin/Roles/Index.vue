<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    roles: Array,
    permissions: Array
});

// Formulario para crear y eliminar Roles
const roleForm = useForm({ name: '' });

// Formulario para sincronizar Permisos
const syncForm = useForm({ permissions: [] });

const selectedRole = ref(null);

// Roles que el sistema no permite borrar para evitar errores críticos
const protectedRoles = ['Super Admin', 'Coordinador', 'Pastero'];

const selectRole = (role) => {
    selectedRole.value = role;
    // Cargamos los permisos que ya tiene ese rol
    syncForm.permissions = role.permissions.map(p => p.name);
};

const submitRole = () => {
    roleForm.post(route('roles.store'), {
        onSuccess: () => roleForm.reset()
    });
};

const submitPermissions = () => {
    syncForm.post(route('roles.permissions', selectedRole.value.id), {
        preserveScroll: true
    });
};

const deleteRole = (role) => {
    if (confirm(`¿Estás seguro de eliminar el rol "${role.name}"? Los usuarios con este rol perderán sus accesos asociados.`)) {
        roleForm.delete(route('roles.destroy', role.id), {
            onSuccess: () => {
                if (selectedRole.value?.id === role.id) {
                    selectedRole.value = null;
                }
            }
        });
    }
};
</script>

<template>
    <Head title="Gestión de Roles" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Configuración de Perfiles (Roles)</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 text-slate-900">
                    
                    <!-- LADO IZQUIERDO: LISTA DE ROLES -->
                    <div class="md:col-span-1 space-y-6">
                        <!-- Card de Creación -->
                        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                            <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-4">Nuevo Perfil</h3>
                            <form @submit.prevent="submitRole" class="flex gap-2">
                                <input v-model="roleForm.name" type="text" placeholder="Ej: Auditor" 
                                    class="flex-1 border-gray-300 rounded-xl text-sm focus:ring-indigo-500 focus:border-indigo-500" required>
                                <button :disabled="roleForm.processing" class="bg-indigo-600 text-white px-4 py-2 rounded-xl font-bold hover:bg-indigo-700 transition active:scale-95 disabled:opacity-50">
                                    +
                                </button>
                            </form>
                        </div>

                        <!-- Lista de Roles -->
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                            <p class="p-4 text-[10px] font-black text-gray-400 uppercase tracking-widest bg-gray-50 border-b">Estructura Organizacional</p>
                            <div class="divide-y divide-gray-50 max-h-[500px] overflow-y-auto custom-scrollbar">
                                <div v-for="role in roles" :key="role.id" class="group relative">
                                    <button 
                                        @click="selectRole(role)"
                                        :class="selectedRole?.id === role.id ? 'bg-indigo-50 border-l-4 border-indigo-500 text-indigo-700' : 'text-gray-600 hover:bg-gray-50'"
                                        class="w-full text-left p-4 transition-all flex justify-between items-center pr-12">
                                        <span class="font-bold text-sm">{{ role.name }}</span>
                                        <svg class="w-4 h-4 opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path d="M9 5l7 7-7 7" stroke-width="3" />
                                        </svg>
                                    </button>

                                    <!-- Botón de Borrar (Oculto para roles protegidos) -->
                                    <button 
                                        v-if="!protectedRoles.includes(role.name)"
                                        @click.stop="deleteRole(role)"
                                        class="absolute right-3 top-1/2 -translate-y-1/2 p-2 text-gray-300 hover:text-red-500 opacity-0 group-hover:opacity-100 transition-all rounded-lg hover:bg-red-50"
                                        title="Eliminar este rol"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- LADO DERECHO: PERMISOS DEL ROL SELECCIONADO -->
                    <div class="md:col-span-2">
                        <div v-if="selectedRole" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 min-h-[400px]">
                            <div class="flex flex-col sm:flex-row justify-between items-start gap-4 mb-8">
                                <div>
                                    <div class="flex items-center gap-2 mb-1">
                                        <h3 class="text-2xl font-black text-gray-800 uppercase tracking-tight">{{ selectedRole.name }}</h3>
                                        <span v-if="protectedRoles.includes(selectedRole.name)" class="bg-amber-100 text-amber-700 text-[9px] font-bold px-2 py-0.5 rounded-full uppercase tracking-tighter">Sistema</span>
                                    </div>
                                    <p class="text-xs text-gray-500">Define las capacidades y accesos para este perfil operativo.</p>
                                </div>
                                <button @click="submitPermissions" :disabled="syncForm.processing || selectedRole.name === 'Super Admin'" 
                                    class="w-full sm:w-auto bg-slate-900 text-white px-8 py-3 rounded-xl text-xs font-black uppercase tracking-widest shadow-xl active:scale-95 transition-all disabled:opacity-50">
                                    {{ syncForm.processing ? 'Sincronizando...' : 'Guardar Configuración' }}
                                </button>
                            </div>

                            <div v-if="selectedRole.name === 'Super Admin'" class="bg-indigo-50 border border-indigo-100 p-4 rounded-2xl mb-6">
                                <p class="text-xs text-indigo-700 font-medium">
                                    <span class="font-black mr-1 uppercase">Nota:</span> 
                                    El Super Admin posee control total del sistema por defecto. No es necesario gestionar sus permisos manualmente.
                                </p>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <label v-for="permission in permissions" :key="permission.id" 
                                    class="flex items-center p-4 rounded-2xl border transition-all cursor-pointer group/item"
                                    :class="syncForm.permissions.includes(permission.name) ? 'bg-indigo-50 border-indigo-200 ring-1 ring-indigo-200 shadow-sm' : 'bg-white border-gray-100 hover:border-gray-200'">
                                    
                                    <input type="checkbox" :value="permission.name" v-model="syncForm.permissions" 
                                        :disabled="selectedRole.name === 'Super Admin'"
                                        class="w-5 h-5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 disabled:opacity-30">
                                    
                                    <div class="ml-3">
                                        <p class="text-xs font-black text-gray-700 uppercase tracking-wider group-hover/item:text-indigo-600 transition-colors">
                                            {{ permission.name.replace(/-/g, ' ') }}
                                        </p>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Estado Vacío -->
                        <div v-else class="h-full min-h-[400px] flex flex-col items-center justify-center bg-gray-50 rounded-2xl border-2 border-dashed border-gray-200 p-12 text-center text-gray-400">
                            <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                <svg class="w-10 h-10 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" stroke-width="2" />
                                </svg>
                            </div>
                            <p class="font-bold text-gray-500">Selecciona un perfil de la izquierda</p>
                            <p class="text-sm">Podrás gestionar sus permisos y alcances en el sistema.</p>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 5px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #e2e8f0;
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #cbd5e1;
}
</style>