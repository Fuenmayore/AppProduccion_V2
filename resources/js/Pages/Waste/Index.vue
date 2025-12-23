<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { onMounted } from 'vue';

const props = defineProps({
    records: Object
});

// Detección automática de impresión al guardar
onMounted(() => {
    const printId = usePage().props.flash.print_id; // Asumiendo que pasas esto desde el controlador
    if (printId) {
        const url = route('waste.print', printId);
        // Abrir ventana emergente para imprimir
        window.open(url, '_blank', 'width=600,height=600');
    }
});
</script>

        <template>
            <Head title="Control de Reproceso" />
            <AuthenticatedLayout>
                <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800">Reprocesos y Desperdicios</h2>
                
                <Link :href="route('waste.create')" 
                    class="bg-red-600 text-white px-4 py-2 rounded-lg font-bold hover:bg-red-700 shadow-md transition flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                    Nuevo Registro
                </Link>
            </div>
        </template>

        <div class="py-12 max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Línea / Turno</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Producto</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Peso</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Detalle</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Acción</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="record in records.data" :key="record.id">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ record.date }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-bold">
                                {{ record.line.name }} <span class="text-gray-400 font-normal">({{ record.shift?.name }})</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ record.product?.name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-red-600">{{ record.weight_kg }} Kg</td>
                            <td class="px-6 py-4 text-sm text-gray-500 truncate max-w-xs" :title="record.cause_comment">
                                {{ record.cause_comment }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <a :href="route('waste.print', record.id)" target="_blank" class="text-indigo-600 hover:text-indigo-900 flex items-center justify-end gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                    Imprimir
                                </a>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AuthenticatedLayout>
</template>