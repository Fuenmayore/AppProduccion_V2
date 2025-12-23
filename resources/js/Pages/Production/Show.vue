<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ProductionFormFields from '@/Components/ProductionFormFields.vue';
import Modal from '@/Components/Modal.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    line: Object,
    report: Object,
    fields: Array,
    variables: Array,
});

// --- Lógica Eliminar Reporte ---
const deleteReport = () => {
    if (confirm('¿ATENCIÓN: Vas a eliminar TODO el turno?')) {
        router.delete(route('production.destroy', [props.line.slug, props.report.id]));
    }
};

// --- Lógica Modal Variables ---
const showVariableModal = ref(false);
const isEditing = ref(false);
const editingId = ref(null);

// Datos iniciales vacíos
const emptyData = { observacion: '' };
props.fields.forEach(f => { if(f.type !== 'header') emptyData[f.name] = ''; });

const form = useForm({...emptyData});

const openCreateModal = () => {
    isEditing.value = false;
    editingId.value = null;
    form.defaults({...emptyData});
    form.reset();
    showVariableModal.value = true;
};

const openEditModal = (variable) => {
    isEditing.value = true;
    editingId.value = variable.id;
    form.observacion = variable.observacion;
    props.fields.forEach(f => {
        if(f.type !== 'header') form[f.name] = variable.data[f.name];
    });
    showVariableModal.value = true;
};

const submitVariable = () => {
    if (isEditing.value) {
        form.put(route('production.update-variable', editingId.value), {
            onSuccess: () => showVariableModal.value = false
        });
    } else {
        form.post(route('production.store-variable', props.report.id), {
            onSuccess: () => showVariableModal.value = false
        });
    }
};

const deleteVariable = (id) => {
    if(confirm('¿Borrar este registro?')) {
        router.delete(route('production.destroy-variable', id));
    }
};

const formatValue = (v) => (v === null || v === undefined || v === '') ? '-' : v;
</script>

<template>
    <Head :title="`Reporte #${report.id}`" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ line.name }} <span class="text-gray-500">| Reporte #{{ report.id }}</span>
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <div class="bg-white shadow-sm sm:rounded-lg mb-6 p-6 border-l-4 border-indigo-500 relative">
                    <button @click="deleteReport" class="absolute top-4 right-4 text-red-500 hover:text-red-700 text-xs font-bold uppercase border border-red-200 px-2 py-1 rounded hover:bg-red-50">
                        Eliminar Turno
                    </button>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                        <div><p class="text-xs text-gray-500 uppercase">Fecha</p><p class="font-medium">{{ report.production_date }}</p></div>
                        <div><p class="text-xs text-gray-500 uppercase">Turno</p><p class="font-medium">{{ report.shift?.name }}</p></div>
                        <div><p class="text-xs text-gray-500 uppercase">Coordinador</p><p class="font-medium">{{ report.coordinator?.name }}</p></div>
                        <div><p class="text-xs text-gray-500 uppercase">Operario</p><p class="font-medium">{{ report.operator?.name }}</p></div>
                    </div>
                </div>

                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-bold text-gray-700">Registro Horario</h3>
                    
                    <button 
                        @click="openCreateModal"
                        class="bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-6 rounded-lg shadow-md transition transform hover:scale-105 flex items-center"
                    >
                        <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        Registrar Nueva Hora
                    </button>
                </div>

                <div v-if="variables.length === 0" class="text-center py-16 bg-white rounded-lg border-2 border-dashed border-gray-300">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No hay registros aún</h3>
                    <p class="mt-1 text-sm text-gray-500">Comienza registrando la primera hora del turno.</p>
                    <div class="mt-6">
                        <button @click="openCreateModal" class="text-indigo-600 hover:text-indigo-900 font-bold">
                            + Registrar Hora Ahora
                        </button>
                    </div>
                </div>

                <div v-for="(variable, vIndex) in variables" :key="variable.id" class="bg-white shadow-sm sm:rounded-lg p-6 mb-4 relative group hover:shadow-md transition">
                    
                    <div class="absolute top-4 right-4 flex gap-2 opacity-100 md:opacity-0 group-hover:opacity-100 transition-opacity">
                        <button @click="openEditModal(variable)" class="bg-indigo-50 text-indigo-600 p-2 rounded hover:bg-indigo-100" title="Editar">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                        </button>
                        <button @click="deleteVariable(variable.id)" class="bg-red-50 text-red-600 p-2 rounded hover:bg-red-100" title="Eliminar">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                        </button>
                    </div>

                    <div class="flex items-center mb-4 border-b pb-2">
                        <span class="bg-indigo-100 text-indigo-700 px-3 py-1 rounded-full text-sm font-bold mr-3">
                            {{ new Date('1970-01-01T' + variable.recorded_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}) }}
                        </span>
                        <span class="text-sm text-gray-400 font-medium">Registro #{{ variables.length - vIndex }}</span>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                        <template v-for="(field, index) in fields" :key="index">
                            <div v-if="field.type !== 'header'" class="p-2 bg-gray-50 rounded border border-gray-100">
                                <p class="text-[10px] text-gray-500 uppercase truncate font-bold mb-1" :title="field.label">{{ field.label }}</p>
                                <p class="font-mono font-semibold text-gray-800 text-base truncate">
                                    {{ formatValue(variable.data[field.name]) }}
                                </p>
                            </div>
                        </template>
                    </div>
                    
                    <div v-if="variable.observacion" class="mt-4 text-sm text-gray-600 italic bg-yellow-50 p-3 rounded border border-yellow-100">
                        <strong>Nota:</strong> {{ variable.observacion }}
                    </div>
                </div>

            </div>
        </div>

        <Modal :show="showVariableModal" @close="showVariableModal = false">
            <div class="p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-4 border-b pb-2">
                    {{ isEditing ? 'Editar' : 'Registrar' }} Datos de Proceso
                </h2>
                <form @submit.prevent="submitVariable">
                    <ProductionFormFields :fields="fields" :form="form" />
                    <div class="mt-6">
                        <label class="block font-medium text-sm text-gray-700 mb-1">Observación</label>
                        <textarea v-model="form.observacion" class="w-full border-gray-300 rounded-md shadow-sm" rows="2"></textarea>
                    </div>
                    <div class="mt-8 flex justify-end gap-3">
                        <button type="button" @click="showVariableModal = false" class="px-4 py-2 text-gray-600 hover:text-gray-800">Cancelar</button>
                        <button type="submit" :disabled="form.processing" class="bg-indigo-600 text-white font-bold px-6 py-2 rounded hover:bg-indigo-700 transition">
                            {{ isEditing ? 'Actualizar' : 'Guardar' }}
                        </button>
                    </div>
                </form>
            </div>
        </Modal>

    </AuthenticatedLayout>
</template>