<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    users: Array,
    roles: Array
});

// Formulario reactivo de Inertia
const form = useForm({
    name: '',
    email: '',
    password: '',
    role: ''
});

const submit = () => {
    form.post(route('users.store'), {
        onSuccess: () => form.reset(),
    });
};

const deleteUser = (id) => {
    if (confirm('¿Estás seguro de eliminar este usuario?')) {
        form.delete(route('users.destroy', id));
    }
};
</script>

<template>
    <Head title="Gestión de Usuarios" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Control de Usuarios</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    
                    <!-- Formulario de Creación -->
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                        <h3 class="text-lg font-bold text-gray-800 mb-6">Nuevo Usuario</h3>
                        
                        <form @submit.prevent="submit" class="space-y-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Nombre Completo</label>
                                <input v-model="form.name" type="text" class="w-full border-gray-300 rounded-xl" required>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Correo Electrónico</label>
                                <input v-model="form.email" type="email" class="w-full border-gray-300 rounded-xl" required>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Contraseña</label>
                                <input v-model="form.password" type="password" class="w-full border-gray-300 rounded-xl" required>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Asignar Rol</label>
                                <select v-model="form.role" class="w-full border-gray-300 rounded-xl" required>
                                    <option value="" disabled>Seleccionar Rol...</option>
                                    <option v-for="role in roles" :key="role.id" :value="role.name">
                                        {{ role.name }}
                                    </option>
                                </select>
                            </div>

                            <button type="submit" :disabled="form.processing" class="w-full bg-indigo-600 text-white py-3 rounded-xl font-bold shadow-lg hover:bg-indigo-700 transition">
                                Guardar Perfil
                            </button>
                        </form>
                    </div>

                    <!-- Tabla de Usuarios -->
                    <div class="md:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50 border-b border-gray-100">
                                    <th class="p-4 text-[10px] font-black text-gray-400 uppercase">Usuario</th>
                                    <th class="p-4 text-[10px] font-black text-gray-400 uppercase">Rol Actual</th>
                                    <th class="p-4 text-[10px] font-black text-gray-400 uppercase text-right">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                <tr v-for="user in users" :key="user.id" class="hover:bg-gray-50 transition">
                                    <td class="p-4">
                                        <div class="font-bold text-gray-800 text-sm">{{ user.name }}</div>
                                        <div class="text-xs text-gray-400">{{ user.email }}</div>
                                    </td>
                                    <td class="p-4">
                                        <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider border"
                                            :class="user.roles[0]?.name === 'Super Admin' ? 'bg-indigo-50 text-indigo-600 border-indigo-100' : 'bg-slate-50 text-slate-600 border-slate-100'">
                                            {{ user.roles[0]?.name || 'Sin Rol' }}
                                        </span>
                                    </td>
                                    <td class="p-4 text-right">
                                        <button @click="deleteUser(user.id)" class="text-red-500 hover:text-red-700 text-xs font-bold uppercase tracking-widest">Eliminar</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>