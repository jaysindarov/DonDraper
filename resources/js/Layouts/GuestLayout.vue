<script setup>
import { Link } from '@inertiajs/vue3'
</script>

<template>
    <div class="min-h-screen bg-gray-950 text-white flex items-center justify-center px-6">
        <div class="w-full max-w-sm">
            <Link :href="route('home')" class="flex items-center gap-2.5 mb-10 justify-center">
                <div class="w-8 h-8 rounded-xl bg-gradient-to-br from-sky-500 to-cyan-600 flex items-center justify-center font-black text-sm">D</div>
                <span class="font-bold">Don<span class="text-transparent bg-clip-text bg-gradient-to-r from-sky-400 to-cyan-400">Draper</span></span>
            </Link>
            <slot />
        </div>
    </div>
</template>
