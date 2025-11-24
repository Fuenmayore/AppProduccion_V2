<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

// Recibimos los datos desde el Controlador
const props = defineProps({
    line: Object,
    fields: Array,       // La configuración de campos (config/lines.php)
    shifts: Array,
    coordinators: Array,
    operators: Array,
});

// Inicializamos el formulario dinámico
// Creamos un objeto vacío y lo llenamos con las claves de 'fields'
const formInitialData = {
    fecha: new Date().toISOString().substr(0, 10),
    turno_id: '',
    coordinator_id: '',
    operator_id: '',
    observacion: '',
};

// Agregamos dinámicamente los campos variables al objeto del formulario
props.fields.forEach(field => {
    if (field.type !== 'header') {
        formInitialData[field.name] = ''; 
    }
});

const form = useForm(formInitialData);

const submit = () => {
    form.post(route('production.store', props.line.slug));
};
</script>

<template>
    <Head :title="`Nuevo Reporte - ${line.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ line.name }} <span class="text-gray-500 text-sm">| Nuevo Registro de Proceso</span>
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    
                    <form @submit.prevent="submit">
                        
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8 bg-blue-50 p-4 rounded-lg border border-blue-100">
                            <div>
                                <label class="block font-medium text-sm text-gray-700">Fecha</label>
                                <input type="date" v-model="form.fecha" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full" required>
                            </div>
                            <div>
                                <label class="block font-medium text-sm text-gray-700">Turno</label>
                                <select v-model="form.turno_id" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full" required>
                                    <option value="" disabled>Seleccionar...</option>
                                    <option v-for="shift in shifts" :key="shift.id" :value="shift.id">{{ shift.name }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="block font-medium text-sm text-gray-700">Coordinador</label>
                                <select v-model="form.coordinator_id" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full" required>
                                    <option value="" disabled>Seleccionar...</option>
                                    <option v-for="user in coordinators" :key="user.id" :value="user.id">{{ user.name }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="block font-medium text-sm text-gray-700">Operario</label>
                                <select v-model="form.operator_id" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full" required>
                                    <option value="" disabled>Seleccionar...</option>
                                    <option v-for="user in operators" :key="user.id" :value="user.id">{{ user.name }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            
                            <template v-for="(field, index) in fields" :key="index">
                                
                                <div v-if="field.type === 'header'" class="col-span-full mt-4 border-b border-gray-200 pb-2">
                                    <h3 class="text-lg font-bold text-indigo-600 flex items-center">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                                        {{ field.label }}
                                    </h3>
                                </div>

                                <div v-else-if="['number', 'text', 'date'].includes(field.type)">
                                    <label class="block font-medium text-sm text-gray-700 mb-1">{{ field.label }}</label>
                                    <input 
                                        :type="field.type" 
                                        :step="field.step || 'any'"
                                        v-model="form[field.name]"
                                        class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full"
                                    >
                                </div>

                                <div v-else-if="field.type === 'select'">
                                    <label class="block font-medium text-sm text-gray-700 mb-1">{{ field.label }}</label>
                                    <select 
                                        v-model="form[field.name]"
                                        class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full"
                                    >
                                        <option v-for="opt in field.options" :key="opt" :value="opt">{{ opt }}</option>
                                    </select>
                                </div>

                            </template>

                        </div>

                        <div class="mt-8">
                            <label class="block font-medium text-sm text-gray-700">Observaciones Generales</label>
                            <textarea v-model="form.observacion" rows="3" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full"></textarea>
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <button 
                                type="submit" 
                                :disabled="form.processing"
                                class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                            >
                                Guardar Registro
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>