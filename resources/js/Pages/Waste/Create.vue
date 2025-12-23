<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    lines: Array,
    shifts: Array,
    products: Array,
    types: Array,
    locations: Array,
});

const form = useForm({
    date: new Date().toISOString().substr(0, 10),
    line_id: '',
    shift_id: '',
    product_id: '',
    waste_type: '',
    location: '',
    weight_kg: '',
    observacion: ''
});

const submit = () => {
    form.post(route('waste.store'));
};
</script>

<template>
    <Head title="Registrar Reproceso" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Nuevo Registro de Reproceso</h2>
        </template>

        <div class="py-12">
            <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    
                    <form @submit.prevent="submit" class="space-y-4">
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Fecha</label>
                                <input type="date" v-model="form.date" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Turno</label>
                                <select v-model="form.shift_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                                    <option value="" disabled>Seleccionar...</option>
                                    <option v-for="s in shifts" :key="s.id" :value="s.id">{{ s.name }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Línea de Origen</label>
                                <select v-model="form.line_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                                    <option value="" disabled>Seleccionar...</option>
                                    <option v-for="l in lines" :key="l.id" :value="l.id">{{ l.name }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Producto</label>
                                <select v-model="form.product_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                                    <option value="" disabled>Seleccionar...</option>
                                    <option v-for="p in products" :key="p.id" :value="p.id">{{ p.name }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Tipo de Defecto</label>
                                <select v-model="form.waste_type" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                                    <option value="" disabled>Seleccionar...</option>
                                    <option v-for="t in types" :key="t" :value="t">{{ t }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Lugar de Hallazgo</label>
                                <select v-model="form.location" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                                    <option value="" disabled>Seleccionar...</option>
                                    <option v-for="l in locations" :key="l" :value="l">{{ l }}</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-800">Peso Neto (Kg)</label>
                            <input type="number" step="0.01" v-model="form.weight_kg" class="mt-1 block w-full border-indigo-500 border-2 rounded-md shadow-sm text-lg p-2" placeholder="0.00" required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Observaciones</label>
                            <textarea v-model="form.observacion" rows="2" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"></textarea>
                        </div>

                        <div class="flex justify-end pt-4">
                            <button type="submit" :disabled="form.processing" class="bg-indigo-600 text-white font-bold px-6 py-3 rounded-lg shadow hover:bg-indigo-700 flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                Guardar e Imprimir Sticker
                            </button>
                        </div>

                    </form>

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>