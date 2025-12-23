<script setup>
import { ref } from 'vue';
import Sidebar from '@/Components/Sidebar.vue';

const showingNavigationDropdown = ref(false);
</script>

<template>
    <div class="min-h-screen bg-gray-100 flex">
        
        <Sidebar />

        <main class="flex-1 ml-64 flex flex-col min-w-0 overflow-hidden h-screen">
            
            <header class="bg-white shadow z-10" v-if="$slots.header">
                <div class="py-4 px-8 flex items-center justify-between h-16">
                    <slot name="header" />
                </div>
            </header>
            
            <div class="flex-1 overflow-y-auto p-8 scroll-smooth">
                <slot />
            </div>

        </main>
    </div>
</template>