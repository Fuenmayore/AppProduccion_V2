<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

// Recibimos los lotes activos desde el controlador
const props = defineProps({
    batches: Array,
});

// --- LÓGICA DE CREACIÓN (NUEVO LOTE) ---
const showCreateModal = ref(false);
const form = useForm({
    silo_name: '',
    material_type: 'Semolato', // Valor por defecto
    batch_code: '',
    quantity: '',
});

const submit = () => {
    form.post(route('silos.store'), {
        onSuccess: () => {
            showCreateModal.value = false;
            form.reset();
        }
    });
};

// --- LÓGICA DE CIERRE (TERMINAR LOTE) ---
const closeBatch = (id, name) => {
    if (confirm(`¿Confirmas que el ${name} se ha vaciado por completo? Esta acción lo desactivará.`)) {
        router.put(route('silos.close', id));
    }
};
</script>

<template>
    <Head title="Gestión de Silos" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Gestión de Materia Prima <span class="text-gray-500 text-sm">| Silos y Tolvas</span>
                </h2>
                <button 
                    @click="showCreateModal = true"
                    class="bg-orange-600 hover:bg-orange-700 text-white font-bold py-2 px-4 rounded shadow transition flex items-center"
                >
                    <span class="mr-2 text-xl">+</span> Nuevo Ingreso
                </button>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <div v-if="batches.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    
                    <div v-for="batch in batches" :key="batch.id" class="bg-white rounded-xl shadow-sm border border-orange-200 overflow-hidden relative">
                        <div class="h-2 w-full bg-orange-500"></div>
                        <div class="p-6">
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <h3 class="text-2xl font-bold text-gray-800">{{ batch.silo_name }}</h3>
                                    <p class="text-xs text-gray-500 uppercase font-bold tracking-wide">Lote Interno</p>
                                    <p class="font-mono text-lg text-orange-600 font-bold">{{ batch.internal_batch_code }}</p>
                                </div>
                                <div class="bg-green-100 text-green-800 text-xs font-bold px-2 py-1 rounded-full uppercase">
                                    En Uso
                                </div>
                            </div>

                            <div class="space-y-2 text-sm text-gray-600 mb-6">
                                <div class="flex justify-between">
                                    <span>Fecha Ingreso:</span>
                                    <span class="font-medium">{{ new Date(batch.loading_date).toLocaleDateString() }}</span>
                                </div>
                                </div>

                            <button 
                                @click="closeBatch(batch.id, batch.silo_name)"
                                class="w-full py-2 border border-gray-300 text-gray-600 rounded-lg hover:bg-red-50 hover:text-red-600 hover:border-red-200 transition text-sm font-medium"
                            >
                                Marcar como Vacío
                            </button>
                        </div>
                    </div>

                </div>

                <div v-else class="text-center py-16 bg-white rounded-lg border-2 border-dashed border-gray-300">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No hay silos activos</h3>
                    <p class="mt-1 text-sm text-gray-500">Registra un nuevo ingreso de materia prima para comenzar.</p>
                    <div class="mt-6">
                        <button @click="showCreateModal = true" class="text-orange-600 hover:text-orange-800 font-medium">
                            Registrar Ingreso &rarr;
                        </button>
                    </div>
                </div>

            </div>
        </div>

        <Modal :show="showCreateModal" @close="showCreateModal = false">
            <div class="p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-4 border-b pb-2">
                    Registrar Ingreso a Silo
                </h2>

                <form @submit.prevent="submit">
                    <div class="grid grid-cols-1 gap-4">
                        
                        <div>
                            <label class="block font-medium text-sm text-gray-700">Silo / Ubicación</label>
                            <select v-model="form.silo_name" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-orange-500 focus:border-orange-500" required>
                                <option value="" disabled>Seleccionar...</option>
                                <option value="Silo 1A">Silo 1A</option>
                                <option value="Silo 1B">Silo 1B</option>
                                <option value="Silo 1C">Silo 1C</option>
                                <option value="Silo 2A">Silo 2A</option>
                                <option value="Silo 2B">Silo 2B</option>
                                <option value="Tolva Día">Tolva Día</option>
                                <option value="Big Bag">Big Bag</option>
                            </select>
                        </div>

                        <div>
                            <label class="block font-medium text-sm text-gray-700">Lote del Proveedor</label>
                            <input type="text" v-model="form.batch_code" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-orange-500 focus:border-orange-500" placeholder="Ej: L-45892" required>
                        </div>

                        <div>
                            <label class="block font-medium text-sm text-gray-700">Tipo de Material</label>
                            <select v-model="form.material_type" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-orange-500 focus:border-orange-500">
                                <option value="Semolato">Semolato</option>
                                <option value="Sémola">Sémola</option>
                                <option value="Harina">Harina</option>
                                <option value="Trigo Duro">Trigo Duro</option>
                            </select>
                        </div>

                        <div>
                            <label class="block font-medium text-sm text-gray-700">Cantidad (Kg Estimados)</label>
                            <input type="number" v-model="form.quantity" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-orange-500 focus:border-orange-500" placeholder="0">
                        </div>

                    </div>

                    <div class="mt-8 flex justify-end gap-3">
                        <button type="button" @click="showCreateModal = false" class="px-4 py-2 text-gray-600 hover:text-gray-800 font-medium">
                            Cancelar
                        </button>
                        <button type="submit" :disabled="form.processing" class="bg-orange-600 text-white font-bold px-6 py-2 rounded shadow hover:bg-orange-700 transition disabled:opacity-50">
                            Guardar Ingreso
                        </button>
                    </div>
                </form>
            </div>
        </Modal>

    </AuthenticatedLayout>
</template>