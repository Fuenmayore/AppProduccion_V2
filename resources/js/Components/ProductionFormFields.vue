<script setup>
defineProps({
    fields: Array, // La configuración de campos (config/lines.php)
    form: Object,  // El objeto formulario de Inertia
});
</script>

<template>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <template v-for="(field, index) in fields" :key="index">
            
            <div v-if="field.type === 'header'" class="col-span-full mt-4 pt-4 border-t border-gray-100">
                <h3 class="text-lg font-bold text-indigo-600 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                    {{ field.label }}
                </h3>
            </div>

            <div v-else-if="['number', 'text', 'date', 'time'].includes(field.type)">
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
</template>