<script setup>
import { Head, Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
    generation: Object,
})

const statusConfig = {
    completed: { color: 'text-emerald-400', bg: 'bg-emerald-400/10 border-emerald-500/30', label: 'Completed' },
    processing: { color: 'text-blue-400', bg: 'bg-blue-400/10 border-blue-500/30', label: 'Processing...' },
    pending: { color: 'text-yellow-400', bg: 'bg-yellow-400/10 border-yellow-500/30', label: 'Queued' },
    failed: { color: 'text-rose-400', bg: 'bg-rose-400/10 border-rose-500/30', label: 'Failed' },
}

const status = statusConfig[props.generation.status] || statusConfig.pending

const downloadImage = async () => {
    if (!props.generation.result_url) return
    const a = document.createElement('a')
    a.href = props.generation.result_url
    a.download = `dondraper-${props.generation.id}.png`
    a.target = '_blank'
    a.click()
}
</script>

<template>
    <Head :title="`Generation #${generation.id} — DonDraper`" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link :href="route('generations.index')" class="text-gray-400 hover:text-white transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </Link>
                <h2 class="text-xl font-bold text-white">Generation #{{ generation.id }}</h2>
                <span :class="['text-xs px-2.5 py-1 rounded-full border font-medium', status.bg, status.color]">{{ status.label }}</span>
            </div>
        </template>

        <div class="py-8 px-6 max-w-6xl mx-auto">
            <div class="grid lg:grid-cols-2 gap-8">
                <!-- Image Display -->
                <div class="space-y-4">
                    <div class="aspect-square rounded-3xl overflow-hidden bg-gradient-to-br from-gray-900 to-gray-800 border border-white/5 relative">
                        <img v-if="generation.result_url" :src="generation.result_url" :alt="generation.prompt" class="w-full h-full object-cover" />
                        <div v-else class="w-full h-full flex flex-col items-center justify-center gap-4">
                            <div v-if="generation.status === 'processing' || generation.status === 'pending'">
                                <svg class="w-16 h-16 text-violet-400 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                                <p class="text-sm text-gray-400 mt-4 text-center">AI is working on your image...</p>
                            </div>
                            <div v-else-if="generation.status === 'failed'" class="text-center px-6">
                                <div class="text-5xl mb-4">❌</div>
                                <p class="text-rose-400 font-medium mb-2">Generation Failed</p>
                                <p v-if="generation.error_message" class="text-xs text-gray-500">{{ generation.error_message }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div v-if="generation.result_url" class="flex gap-3">
                        <button @click="downloadImage"
                            class="flex-1 flex items-center justify-center gap-2 bg-gradient-to-r from-violet-500 to-fuchsia-600 hover:from-violet-400 hover:to-fuchsia-500 text-white font-semibold py-3 rounded-xl transition-all shadow-lg shadow-violet-500/25">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            Download
                        </button>
                        <Link :href="route('generations.create')"
                            class="flex items-center gap-2 bg-white/5 hover:bg-white/10 border border-white/10 text-white font-semibold py-3 px-5 rounded-xl transition-all">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            Regenerate
                        </Link>
                    </div>
                </div>

                <!-- Details -->
                <div class="space-y-5">
                    <div class="bg-gray-900/50 border border-white/5 rounded-2xl p-6">
                        <h3 class="text-sm font-semibold text-gray-400 mb-3">Prompt</h3>
                        <p class="text-white leading-relaxed">{{ generation.prompt }}</p>
                        <div v-if="generation.negative_prompt" class="mt-4">
                            <h3 class="text-sm font-semibold text-gray-400 mb-2">Negative Prompt</h3>
                            <p class="text-gray-400 text-sm">{{ generation.negative_prompt }}</p>
                        </div>
                    </div>

                    <div class="bg-gray-900/50 border border-white/5 rounded-2xl p-6">
                        <h3 class="text-sm font-semibold text-gray-400 mb-4">Generation Settings</h3>
                        <div class="space-y-3">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Model</span>
                                <span class="text-white font-medium">{{ generation.model }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Type</span>
                                <span class="text-white font-medium capitalize">{{ generation.type }}</span>
                            </div>
                            <div v-if="generation.width && generation.height" class="flex justify-between text-sm">
                                <span class="text-gray-500">Resolution</span>
                                <span class="text-white font-medium">{{ generation.width }}×{{ generation.height }}</span>
                            </div>
                            <div v-for="(val, key) in generation.attributes" :key="key" class="flex justify-between text-sm">
                                <span class="text-gray-500 capitalize">{{ String(key).replace(/_/g, ' ') }}</span>
                                <span class="text-white font-medium">{{ val }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-900/50 border border-white/5 rounded-2xl p-6">
                        <h3 class="text-sm font-semibold text-gray-400 mb-4">Metadata</h3>
                        <div class="space-y-3 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-500">Credits Used</span>
                                <span class="text-violet-400 font-medium">{{ generation.credits_used }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Created</span>
                                <span class="text-white">{{ new Date(generation.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit' }) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
