<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useForm, Head } from '@inertiajs/vue3';

const props = defineProps({
    brands: Array,
    products: Array,
    productionLines: Array
});

// Formulario de Marcas
const brandForm = useForm({ name: '' });
// Formulario de Productos
const productForm = useForm({
    name: '',
    default_mold: '',
    brand_id: '',
    allowed_line_ids: []
});

const submitBrand = () => brandForm.post(route('brands.store'), { onSuccess: () => brandForm.reset() });
const submitProduct = () => productForm.post(route('products.store'), { onSuccess: () => productForm.reset() });

const toggleLine = (id) => {
    const index = productForm.allowed_line_ids.indexOf(id);
    if (index > -1) productForm.allowed_line_ids.splice(index, 1);
    else productForm.allowed_line_ids.push(id);
};
</script>

<template>
    <AuthenticatedLayout>
        <div class="p-8 max-w-7xl mx-auto">
            <h1 class="text-2xl font-bold mb-8">Configuración de Producción</h1>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- COLUMNA MARCAS -->
                <div class="bg-white p-6 rounded-xl shadow-sm border">
                    <h2 class="font-bold mb-4">Marcas</h2>
                    <form @submit.prevent="submitBrand" class="mb-6 flex gap-2">
                        <input v-model="brandForm.name" type="text" placeholder="Nueva Marca" class="border-gray-300 rounded-lg flex-1 text-sm">
                        <button class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-bold">+</button>
                    </form>
                    <ul class="space-y-2">
                        <li v-for="brand in brands" :key="brand.id" class="text-sm p-2 bg-gray-50 rounded border text-gray-600">
                            {{ brand.name }}
                        </li>
                    </ul>
                </div>

                <!-- COLUMNA PRODUCTOS (REFERENCIAS) -->
                <div class="md:col-span-2 bg-white p-6 rounded-xl shadow-sm border">
                    <h2 class="font-bold mb-4">Referencias (Figuras de Pasta)</h2>
                    <form @submit.prevent="submitProduct" class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <input v-model="productForm.name" type="text" placeholder="Nombre Figura" class="border-gray-300 rounded-lg text-sm w-full">
                            <input v-model="productForm.default_mold" type="text" placeholder="Molde Sugerido (Matrícula)" class="border-gray-300 rounded-lg text-sm w-full">
                        </div>
                        <select v-model="productForm.brand_id" class="border-gray-300 rounded-lg text-sm w-full">
                            <option value="">Seleccionar Marca Asociada</option>
                            <option v-for="b in brands" :key="b.id" :value="b.id">{{ b.name }}</option>
                        </select>
                        
                        <div>
                            <p class="text-[10px] font-bold text-gray-400 uppercase mb-2">Líneas Autorizadas</p>
                            <div class="flex gap-2">
                                <button v-for="line in productionLines" :key="line.id" type="button"
                                    @click="toggleLine(line.id)"
                                    :class="productForm.allowed_line_ids.includes(line.id) ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-500'"
                                    class="px-3 py-1 rounded-full text-xs font-bold transition">
                                    {{ line.name }}
                                </button>
                            </div>
                        </div>
                        <button class="bg-slate-900 text-white w-full py-2 rounded-lg font-bold text-sm">Registrar Referencia</button>
                    </form>

                    <!-- LISTA DE PRODUCTOS -->
                    <div class="mt-8 border-t pt-4">
                        <table class="w-full text-sm">
                            <tr class="text-gray-400 text-left">
                                <th class="pb-2">Referencia</th>
                                <th class="pb-2">Marca</th>
                                <th class="pb-2 text-right">Líneas</th>
                            </tr>
                            <tr v-for="p in products" :key="p.id" class="border-b last:border-0">
                                <td class="py-2 font-bold">{{ p.name }} <span class="text-xs font-normal text-gray-400">({{ p.default_mold }})</span></td>
                                <td class="py-2">{{ p.brand?.name }}</td>
                                <td class="py-2 text-right flex gap-1 justify-end">
                                    <span v-for="lid in p.allowed_line_ids" :key="lid" class="bg-gray-100 px-2 py-0.5 rounded text-[10px]">
                                        {{ productionLines.find(l => l.id == lid)?.name.split(' ')[1] }}
                                    </span>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>