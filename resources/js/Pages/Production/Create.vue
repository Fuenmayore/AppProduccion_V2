<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ProductionFormFields from '@/Components/ProductionFormFields.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { watch, ref, onMounted, onUnmounted } from 'vue';

// Definición corregida de props para evitar errores de compilación
const props = defineProps({
    line: Object,
    fields: Array,
    shifts: Array,
    currentShiftId: Number,
    activeSilos: Array,
    products: Array,
    brands: Array,
    coordinators: Array,
    operators: Array
});

// --- LÓGICA DE RELOJ EN TIEMPO REAL PARA HORA INICIO ---
const currentTime = ref('');
let timer = null;

const updateTime = () => {
    const now = new Date();
    const timeString = now.toLocaleTimeString('es-ES', { 
        hour: '2-digit', 
        minute: '2-digit', 
        second: '2-digit',
        hour12: false 
    });
    currentTime.value = timeString;
    // Sincronizar automáticamente con el campo del formulario
    form.hora_inicio = timeString;
};

onMounted(() => {
    updateTime();
    timer = setInterval(updateTime, 1000);
});

onUnmounted(() => {
    if (timer) clearInterval(timer);
});

// 1. Estructura base del formulario
const formInitialData = {
    fecha: new Date().toISOString().substr(0, 10),
    hora_inicio: '', 
    turno_id: props.currentShiftId,
    coordinator_id: '',
    operator_id: '',
    observacion: '',
    product_id: '',   
    brand_id: '',     
    silo_batch_id: '' 
};

// 2. Carga dinámica de campos técnicos definidos en la configuración de la línea
if (props.fields) {
    props.fields.forEach(field => {
        if (field.type !== 'header') {
            formInitialData[field.name] = ''; 
        }
    });
}

const form = useForm(formInitialData);

// --- AUTOMATIZACIÓN POR PRODUCTO ---
watch(() => form.product_id, (newProductId) => {
    const selectedProduct = props.products.find(p => p.id === newProductId);
    if (selectedProduct) {
        // Llenado automático de moldes según la referencia
        if (Object.prototype.hasOwnProperty.call(form, 'matricula_molde_1')) {
            form.matricula_molde_1 = selectedProduct.default_mold || '';
        }
        if (Object.prototype.hasOwnProperty.call(form, 'matricula_molde')) {
            form.matricula_molde = selectedProduct.default_mold || '';
        }
        
        // Selección automática de marca si el producto la tiene pre-asignada
        if (selectedProduct.brand_id) {
            form.brand_id = selectedProduct.brand_id;
        }
    }
});

const selectedSiloInfo = ref(null);
watch(() => form.silo_batch_id, (newSiloId) => {
    const silo = props.activeSilos.find(s => s.id === newSiloId);
    selectedSiloInfo.value = silo || null;
});

const submit = () => {
    form.post(route('production.store', props.line.slug));
};
</script>

<template>
    <Head :title="`Nuevo Reporte - ${line.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ line.name }} <span class="text-gray-500 text-sm">| Nuevo Turno</span>
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <form @submit.prevent="submit">
                        
                        <!-- Panel de Datos Generales -->
                        <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-8 bg-blue-50 p-4 rounded-xl border border-blue-100">
                            <div>
                                <label class="block font-bold text-[10px] text-blue-600 uppercase tracking-wider mb-1">Fecha</label>
                                <input type="date" v-model="form.fecha" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full" required>
                            </div>
                            <div>
                                <label class="block font-bold text-[10px] text-blue-600 uppercase tracking-wider mb-1">Hora Inicio (Auto)</label>
                                <input type="text" v-model="form.hora_inicio" class="border-gray-300 bg-gray-100 font-mono focus:ring-0 rounded-md shadow-sm w-full font-bold text-gray-700" readonly>
                            </div>
                            <div>
                                <label class="block font-bold text-[10px] text-blue-600 uppercase tracking-wider mb-1">Turno</label>
                                <select v-model="form.turno_id" class="border-gray-300 bg-gray-200 text-gray-600 rounded-md shadow-sm w-full cursor-not-allowed" disabled>
                                    <option v-for="shift in shifts" :key="shift.id" :value="shift.id">{{ shift.name }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="block font-bold text-[10px] text-blue-600 uppercase tracking-wider mb-1">Coordinador</label>
                                <select v-model="form.coordinator_id" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full" required>
                                    <option value="" disabled>Seleccionar...</option>
                                    <option v-for="user in coordinators" :key="user.id" :value="user.id">{{ user.name }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="block font-bold text-[10px] text-blue-600 uppercase tracking-wider mb-1">Operario</label>
                                <select v-model="form.operator_id" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full" required>
                                    <option value="" disabled>Seleccionar...</option>
                                    <option v-for="user in operators" :key="user.id" :value="user.id">{{ user.name }}</option>
                                </select>
                            </div>
                        </div>

                        <!-- Sección de Referencia de Producción y Marcas -->
                        <div class="bg-white p-6 rounded-2xl shadow-sm mb-6 border border-gray-200 relative overflow-hidden">
                            <div class="absolute top-0 left-0 w-1.5 h-full bg-indigo-500"></div>
                            <h3 class="text-lg font-bold text-gray-800 mb-6 flex items-center">
                                <svg class="w-6 h-6 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                                Referencia y Parámetros de Marca
                            </h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                <div>
                                    <label class="block font-black text-[11px] text-gray-400 uppercase tracking-widest mb-2">Referencia de Pasta</label>
                                    <select v-model="form.product_id" class="border-gray-300 rounded-xl w-full focus:ring-indigo-500 focus:border-indigo-500 bg-indigo-50/30 p-3" required>
                                        <option value="" disabled>Seleccionar Figura...</option>
                                        <option v-for="prod in products" :key="prod.id" :value="prod.id">
                                            {{ prod.name }}
                                        </option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block font-black text-[11px] text-gray-400 uppercase tracking-widest mb-2">Marca / Comercialización</label>
                                    <select v-model="form.brand_id" class="border-gray-300 rounded-xl w-full focus:ring-indigo-500 focus:border-indigo-500 bg-white p-3" required>
                                        <option value="" disabled>Seleccionar Marca...</option>
                                        <option v-for="brand in brands" :key="brand.id" :value="brand.id">
                                            {{ brand.name }}
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Sección de Materia Prima -->
                        <div class="mb-8 bg-orange-50 p-6 rounded-2xl border border-orange-100 relative overflow-hidden">
                            <div class="absolute top-0 left-0 w-1.5 h-full bg-orange-400"></div>
                            <div class="flex flex-col md:flex-row justify-between items-start gap-8">
                                <div class="w-full md:w-1/2">
                                    <h3 class="text-lg font-bold text-orange-800 mb-4 flex items-center">
                                        <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                                        Abastecimiento de Sémola
                                    </h3>
                                    <label class="block font-bold text-[11px] text-orange-700 uppercase tracking-wider mb-2">Lote de Silo Activo</label>
                                    <select v-model="form.silo_batch_id" class="border-gray-300 focus:border-orange-500 focus:ring-orange-500 rounded-xl shadow-sm w-full bg-white p-3" required>
                                        <option value="" disabled>Seleccionar Lote...</option>
                                        <option v-for="silo in activeSilos" :key="silo.id" :value="silo.id">
                                            {{ silo.silo_name }} — {{ silo.internal_batch_code }}
                                        </option>
                                    </select>
                                    <div v-if="activeSilos.length === 0" class="mt-3 p-3 bg-red-100 text-red-700 rounded-lg text-xs font-bold">
                                        No hay silos activos disponibles.
                                    </div>
                                </div>

                                <!-- Detalle del Lote -->
                                <div v-if="selectedSiloInfo" class="w-full md:w-1/3 bg-white p-5 rounded-2xl border border-orange-200 shadow-sm">
                                    <p class="text-[10px] text-gray-400 uppercase font-black tracking-widest mb-3">Información del Material</p>
                                    <div class="flex justify-between items-center mb-2">
                                        <span class="text-sm text-gray-500">Tipo:</span>
                                        <span class="text-sm font-black text-gray-800 uppercase">{{ selectedSiloInfo.raw_material?.material_type }}</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-gray-500">Disponible:</span>
                                        <div>
                                            <span class="text-lg font-black text-emerald-600">{{ selectedSiloInfo.current_quantity }}</span>
                                            <span class="ml-1 text-xs font-bold text-gray-400">Kg</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Variables Técnicas dinámicas -->
                        <div class="mb-4 border-b border-gray-100 pb-3 mt-10">
                            <h3 class="text-lg font-bold text-gray-700 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path></svg>
                                Variables de Proceso (Hora 1)
                            </h3>
                        </div>

                        <ProductionFormFields :fields="fields" :form="form" />

                        <!-- Área de Comentarios -->
                        <div class="mt-10">
                            <label class="block font-bold text-[10px] text-gray-400 uppercase tracking-widest mb-2">Observaciones Generales</label>
                            <textarea v-model="form.observacion" rows="3" class="border-gray-300 rounded-xl w-full focus:ring-indigo-500 focus:border-indigo-500 shadow-sm" placeholder="Ingrese aquí novedades del inicio del turno..."></textarea>
                        </div>

                        <!-- Acciones -->
                        <div class="flex justify-end mt-10">
                            <button type="submit" :disabled="form.processing" class="bg-slate-900 text-white font-black px-10 py-4 rounded-2xl shadow-xl hover:bg-slate-800 transition-all active:scale-95 flex items-center uppercase tracking-widest text-sm disabled:opacity-50">
                                <svg v-if="form.processing" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                <span v-if="!form.processing">Registrar e Iniciar Producción</span>
                                <span v-else>Guardando...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>