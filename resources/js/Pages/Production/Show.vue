<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ProductionFormFields from '@/Components/ProductionFormFields.vue';
import Modal from '@/Components/Modal.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';

const props = defineProps({
    line: Object,
    report: Object,
    fields: Array,
    variables: Array,
    activeSilos: Array,
    products: Array, 
    brands: Array    
});

// --- Lógica de Filtro de Campos Técnicos ---
const modalFields = computed(() => {
    return props.fields.filter(f => 
        f.name !== 'matricula_molde' && 
        f.name !== 'matricula_molde_1' && 
        f.name !== 'matricula_molde_2'
    );
});

// --- Lógica Eliminar Reporte ---
const deleteReport = () => {
    if (confirm('¿ATENCIÓN: Vas a eliminar TODO el turno?')) {
        router.delete(route('production.destroy', [props.line.slug, props.report.id]));
    }
};

// --- 1. LÓGICA MODAL REGISTRO TÉCNICO (HORA) ---
const showVariableModal = ref(false);
const isEditing = ref(false);
const editingId = ref(null);

const getInitialData = () => {
    const data = { observacion: '', silo_batch_id: '' };
    props.fields.forEach(f => { if(f.type !== 'header') data[f.name] = ''; });
    return data;
};

const form = useForm(getInitialData());

const openCreateModal = () => {
    isEditing.value = false;
    editingId.value = null;
    const lastSiloId = props.variables.length > 0 ? props.variables[0].silo_batch_id : '';
    form.defaults({ ...getInitialData(), silo_batch_id: lastSiloId });
    form.reset();
    showVariableModal.value = true;
};

const openEditModal = (variable) => {
    isEditing.value = true;
    editingId.value = variable.id;
    form.observacion = variable.observacion;
    form.silo_batch_id = variable.silo_batch_id || '';
    props.fields.forEach(f => { if(f.type !== 'header') form[f.name] = variable.data[f.name]; });
    showVariableModal.value = true;
};

const submitVariable = () => {
    const action = isEditing.value 
        ? route('production.update-variable', editingId.value) 
        : route('production.store-variable', props.report.id);
    
    const method = isEditing.value ? 'put' : 'post';
    form[method](action, { onSuccess: () => showVariableModal.value = false });
};

// --- 2. LÓGICA MODAL CAMBIO DE REFERENCIA (MEJORADA) ---
const showReferenceModal = ref(false);

const refForm = useForm({
    end_time_previous: '',
    product_id: '',
    brand_id: '',
    start_time: '',
    matricula_molde1: '',
    matricula_molde2: '',
    observacion: '' // Este campo es el que se guarda como 'notes' en DB
});

// Función para abrir el modal con la hora actual por defecto
const openReferenceModal = () => {
    const now = new Date();
    const timeString = now.toLocaleTimeString('es-ES', { 
        hour: '2-digit', 
        minute: '2-digit', 
        hour12: false 
    });
    
    refForm.end_time_previous = timeString;
    refForm.start_time = timeString;
    refForm.product_id = '';
    refForm.brand_id = '';
    refForm.matricula_molde1 = '';
    refForm.matricula_molde2 = '';
    refForm.observacion = '';
    
    showReferenceModal.value = true;
};

// Automatización: Al seleccionar producto, cargar Marca y Molde por defecto
watch(() => refForm.product_id, (newProductId) => {
    const selectedProduct = props.products.find(p => p.id === newProductId);
    if (selectedProduct) {
        if (selectedProduct.brand_id) refForm.brand_id = selectedProduct.brand_id;
        if (selectedProduct.default_mold) refForm.matricula_molde1 = selectedProduct.default_mold;
    }
});

const currentActiveReference = computed(() => {
    return props.report.references?.find(r => !r.end_time) || props.report.references?.[0];
});

const submitReferenceChange = () => {
    refForm.post(route('production.store-reference', props.report.id), {
        onSuccess: () => {
            showReferenceModal.value = false;
            refForm.reset();
        }
    });
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
                
                <div class="bg-white shadow-sm sm:rounded-[2rem] mb-6 p-8 border-l-8 border-indigo-600 relative overflow-hidden">
                    <button @click="deleteReport" class="absolute top-6 right-8 text-red-400 hover:text-red-600 text-[10px] font-black uppercase tracking-widest transition-colors">
                        Eliminar Turno Completo
                    </button>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
                        <div><p class="text-[10px] text-gray-400 uppercase font-black tracking-widest mb-1">Fecha</p><p class="font-bold text-lg text-gray-800">{{ report.production_date }}</p></div>
                        <div><p class="text-[10px] text-gray-400 uppercase font-black tracking-widest mb-1">Turno</p><p class="font-bold text-lg text-gray-800">{{ report.shift?.name }}</p></div>
                        <div><p class="text-[10px] text-gray-400 uppercase font-black tracking-widest mb-1">Coordinador</p><p class="font-bold text-lg text-gray-800">{{ report.coordinator?.name }}</p></div>
                        <div><p class="text-[10px] text-gray-400 uppercase font-black tracking-widest mb-1">Operario</p><p class="font-bold text-lg text-gray-800">{{ report.operator?.name }}</p></div>
                    </div>
                </div>

                <div class="mb-10">
                    <h3 class="text-xs font-black text-slate-400 uppercase tracking-[0.2em] mb-4 flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                        Historial de Productos en el Turno
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                        <div v-for="ref in report.references" :key="ref.id" 
                             class="bg-white border border-slate-100 rounded-3xl p-5 shadow-sm relative overflow-hidden group hover:shadow-md transition-all">
                            
                            <div v-if="!ref.end_time" class="absolute top-0 right-0 bg-emerald-500 text-white text-[8px] font-black px-3 py-1 uppercase tracking-widest rounded-bl-xl animate-pulse">
                                Activo
                            </div>

                            <div class="flex flex-col h-full">
                                <div>
                                    <p class="text-[9px] font-black text-indigo-500 uppercase tracking-widest mb-1">{{ ref.brand?.name }}</p>
                                    <h4 class="text-base font-black text-gray-800 leading-tight mb-3">{{ ref.product?.name }}</h4>
                                    
                                    <div class="flex flex-wrap gap-2 mb-3">
                                        <span class="text-[9px] bg-slate-50 text-slate-500 border border-slate-100 px-2 py-1 rounded-lg font-bold" v-if="ref.molds_used?.m1">
                                            Molde: {{ ref.molds_used.m1 }}
                                        </span>
                                    </div>

                                    <div v-if="ref.notes" class="mt-2 mb-4 p-2.5 bg-amber-50 rounded-xl border border-amber-100">
                                        <p class="text-[9px] text-amber-700 font-bold italic leading-tight uppercase tracking-tighter mb-1">Nota de cambio:</p>
                                        <p class="text-[10px] text-amber-800 font-medium leading-relaxed">{{ ref.notes }}</p>
                                    </div>
                                </div>

                                <div class="flex justify-between items-center pt-4 border-t border-slate-50 mt-auto">
                                    <div class="text-center flex-1">
                                        <p class="text-[8px] font-black text-gray-400 uppercase mb-0.5">Inicio</p>
                                        <p class="text-xs font-black text-gray-700">{{ ref.start_time.substring(0,5) }}</p>
                                    </div>
                                    <div class="flex-none px-2 text-slate-200">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M17 8l4 4m0 0l-4 4m4-4H3" /></svg>
                                    </div>
                                    <div class="text-center flex-1">
                                        <p class="text-[8px] font-black text-gray-400 uppercase mb-0.5">Cierre</p>
                                        <p class="text-xs font-black" :class="ref.end_time ? 'text-gray-700' : 'text-emerald-500 italic uppercase text-[10px] tracking-tighter'">
                                            {{ ref.end_time ? ref.end_time.substring(0,5) : 'En Marcha' }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
                    <div>
                        <h3 class="text-xl font-black text-gray-800 italic uppercase tracking-tighter">Bitácora Técnica</h3>
                        <p class="text-xs text-gray-400 font-bold uppercase tracking-widest">Registros horarios de proceso</p>
                    </div>
                    
                    <div class="flex gap-4">
                        <button @click="openReferenceModal" class="bg-slate-900 hover:bg-black text-white font-black py-3.5 px-8 rounded-2xl shadow-xl transition-all active:scale-95 flex items-center uppercase text-[10px] tracking-[0.15em]">
                            <svg class="w-4 h-4 mr-2 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" /></svg>
                            Cambio Referencia
                        </button>

                        <button @click="openCreateModal" class="bg-indigo-600 hover:bg-indigo-700 text-white font-black py-3.5 px-8 rounded-2xl shadow-xl shadow-indigo-200 transition-all active:scale-95 flex items-center uppercase text-[10px] tracking-[0.15em]">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            Registrar Hora
                        </button>
                    </div>
                </div>

                <div v-for="(variable, vIndex) in variables" :key="variable.id" class="bg-white shadow-sm rounded-[2rem] p-8 mb-6 relative group border border-slate-100 hover:shadow-lg transition-all">
                    <div class="absolute top-6 right-8 flex gap-3 opacity-0 group-hover:opacity-100 transition-opacity">
                        <button @click="openEditModal(variable)" class="bg-indigo-50 text-indigo-600 p-3 rounded-xl hover:bg-indigo-100 transition-colors"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg></button>
                        <button @click="deleteVariable(variable.id)" class="bg-rose-50 text-rose-600 p-3 rounded-xl hover:bg-rose-100 transition-colors"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg></button>
                    </div>

                    <div class="flex items-center mb-6 border-b border-slate-50 pb-4 justify-between">
                        <div class="flex items-center">
                            <span class="bg-slate-900 text-white px-5 py-2 rounded-2xl text-xs font-black mr-4 shadow-lg shadow-slate-200">
                                {{ new Date('1970-01-01T' + variable.recorded_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}) }}
                            </span>
                            <span class="text-[10px] text-slate-400 font-black uppercase tracking-[0.2em]">Registro Técnico #{{ variables.length - vIndex }}</span>
                        </div>
                        <div v-if="variable.silo_batch" class="text-[10px] font-black bg-orange-50 text-orange-600 px-4 py-2 rounded-xl border border-orange-100 uppercase tracking-widest">
                            Tolva: {{ variable.silo_batch.silo_name }}
                        </div>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-6">
                        <template v-for="(field, index) in fields" :key="index">
                            <div v-if="field.type !== 'header' && !field.name.includes('molde')" class="p-3 bg-slate-50/50 rounded-2xl border border-slate-100/50 group-hover:bg-white group-hover:border-indigo-100 transition-colors">
                                <p class="text-[8px] text-slate-400 uppercase font-black tracking-widest mb-1">{{ field.label }}</p>
                                <p class="font-bold text-gray-800 text-sm leading-none">{{ formatValue(variable.data[field.name]) }}</p>
                            </div>
                        </template>
                    </div>
                    <div v-if="variable.observacion" class="mt-6 p-4 bg-slate-50 rounded-2xl border-l-4 border-indigo-400 text-xs text-gray-600 italic">
                        <strong>Nota técnica:</strong> {{ variable.observacion }}
                    </div>
                </div>
            </div>
        </div>

        <Modal :show="showReferenceModal" @close="showReferenceModal = false">
            <div class="p-8">
                <h2 class="text-xl font-black text-gray-800 mb-6 flex items-center italic uppercase tracking-tighter border-b border-slate-100 pb-4">
                    <svg class="w-6 h-6 mr-3 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" /></svg>
                    Nuevo Cambio de Referencia
                </h2>
                <form @submit.prevent="submitReferenceChange">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="bg-slate-50 p-6 rounded-3xl border border-slate-100">
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">Referencia Anterior</p>
                            <p class="text-xs font-black text-indigo-600 mb-2 truncate">Activo: {{ currentActiveReference?.product?.name }}</p>
                            <label class="block text-[10px] font-black text-slate-500 uppercase mb-2">Hora Final</label>
                            <input type="time" v-model="refForm.end_time_previous" class="w-full border-slate-200 rounded-2xl shadow-sm text-sm font-black" required>
                        </div>

                        <div class="bg-indigo-50/50 p-6 rounded-3xl border border-indigo-100">
                            <p class="text-[10px] font-black text-indigo-400 uppercase tracking-widest mb-4">Nueva Referencia</p>
                            <p class="text-xs font-black text-slate-400 mb-2 italic">Entrando a línea ahora...</p>
                            <label class="block text-[10px] font-black text-slate-500 uppercase mb-2">Hora Inicio</label>
                            <input type="time" v-model="refForm.start_time" class="w-full border-slate-200 rounded-2xl shadow-sm text-sm font-black" required>
                        </div>

                        <div class="col-span-full grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-[10px] font-black text-slate-500 uppercase mb-2">Figura / Producto</label>
                                <select v-model="refForm.product_id" class="w-full border-slate-200 rounded-2xl shadow-sm text-sm font-black" required>
                                    <option value="" disabled>Seleccionar Figura...</option>
                                    <option v-for="p in products" :key="p.id" :value="p.id">{{ p.name }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-slate-500 uppercase mb-2">Marca</label>
                                <select v-model="refForm.brand_id" class="w-full border-slate-200 rounded-2xl shadow-sm text-sm font-black" required>
                                    <option value="" disabled>Seleccionar Marca...</option>
                                    <option v-for="b in brands" :key="b.id" :value="b.id">{{ b.name }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-span-full grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[10px] font-black text-slate-500 uppercase mb-2">Matrícula Molde 1</label>
                                <input type="text" v-model="refForm.matricula_molde1" placeholder="Ej: M-101" class="w-full border-slate-200 rounded-2xl shadow-sm text-sm font-bold uppercase">
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-slate-500 uppercase mb-2">Matrícula Molde 2</label>
                                <input type="text" v-model="refForm.matricula_molde2" placeholder="Opcional" class="w-full border-slate-200 rounded-2xl shadow-sm text-sm font-bold uppercase">
                            </div>
                        </div>

                        <div class="col-span-full">
                            <label class="block text-[10px] font-black text-slate-500 uppercase mb-2">Observación del Cambio</label>
                            <textarea v-model="refForm.observacion" rows="2" class="w-full border-slate-200 rounded-2xl shadow-sm text-sm" placeholder="Indique el motivo del cambio, estado de moldes, etc..."></textarea>
                        </div>
                    </div>

                    <div class="mt-10 flex justify-end gap-4">
                        <button type="button" @click="showReferenceModal = false" class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] px-4 transition-colors hover:text-rose-500">Cancelar</button>
                        <button type="submit" :disabled="refForm.processing" class="bg-indigo-600 text-white font-black px-10 py-4 rounded-2xl shadow-xl shadow-indigo-100 uppercase text-[10px] tracking-widest hover:bg-indigo-700 transition-all disabled:opacity-50">Confirmar Cambio</button>
                    </div>
                </form>
            </div>
        </Modal>

        <Modal :show="showVariableModal" @close="showVariableModal = false">
            <div class="p-8">
                <h2 class="text-xl font-black text-gray-800 mb-6 flex items-center italic uppercase tracking-tighter">
                    <svg class="w-6 h-6 mr-3 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Registro de Proceso
                </h2>
                <form @submit.prevent="submitVariable">
                    <div class="mb-8 p-6 bg-orange-50 rounded-3xl border border-orange-100">
                        <label class="block font-black text-[10px] text-orange-600 uppercase tracking-widest mb-3">Materia Prima en Uso</label>
                        <select v-model="form.silo_batch_id" class="w-full border-slate-200 rounded-2xl shadow-sm focus:ring-orange-500 text-sm font-bold">
                            <option value="">Mantener silo actual...</option>
                            <option v-for="silo in activeSilos" :key="silo.id" :value="silo.id">{{ silo.silo_name }} — {{ silo.internal_batch_code }}</option>
                        </select>
                    </div>
                    <ProductionFormFields :fields="modalFields" :form="form" />
                    <div class="mt-8">
                        <label class="block font-black text-[10px] text-slate-400 uppercase tracking-widest mb-3">Notas de la Hora</label>
                        <textarea v-model="form.observacion" class="w-full border-slate-200 rounded-2xl shadow-sm" rows="2"></textarea>
                    </div>
                    <div class="mt-8 flex justify-end gap-4">
                        <button type="button" @click="showVariableModal = false" class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Cancelar</button>
                        <button type="submit" class="bg-indigo-600 text-white font-black px-10 py-4 rounded-2xl shadow-xl uppercase text-[10px] tracking-widest">Guardar Registro</button>
                    </div>
                </form>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>