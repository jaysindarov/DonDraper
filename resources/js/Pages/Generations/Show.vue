<script setup>
import { Head, Link, router, useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { computed, onMounted, onUnmounted, ref } from 'vue'

const props = defineProps({
    generation: Object,
})

const statusConfig = {
    completed: { color: 'text-emerald-400', bg: 'bg-emerald-400/10 border-emerald-500/25', label: 'Completed' },
    processing: { color: 'text-sky-400', bg: 'bg-sky-400/10 border-sky-500/25', label: 'Processing...' },
    pending: { color: 'text-yellow-400', bg: 'bg-yellow-400/10 border-yellow-500/25', label: 'Queued' },
    failed: { color: 'text-rose-400', bg: 'bg-rose-400/10 border-rose-500/25', label: 'Failed' },
}

const status = computed(() => statusConfig[props.generation.status] || statusConfig.pending)
const isInProgress = computed(() => ['pending', 'processing'].includes(props.generation.status))
const steps = computed(() => props.generation.metadata?.steps ?? [])

let pollInterval = null

onMounted(() => {
    if (isInProgress.value) {
        pollInterval = setInterval(() => {
            router.reload({ only: ['generation'], preserveScroll: true })
        }, pollMs.value)
    }
})

onUnmounted(() => {
    if (pollInterval) clearInterval(pollInterval)
})

const stopPollingWhenDone = () => {
    if (!isInProgress.value && pollInterval) {
        clearInterval(pollInterval)
        pollInterval = null
    }
}

router.on('finish', stopPollingWhenDone)

const isVideo = computed(() => props.generation.type === 'video')
const pollMs = computed(() => isVideo.value ? 5000 : 3000)

const download = () => {
    window.location.href = route('generations.download', props.generation.id)
}

const togglePublicForm = useForm({})
const togglePublic = () => {
    togglePublicForm.patch(route('generations.togglePublic', props.generation.id))
}
</script>

<template>
    <Head :title="`Generation #${generation.id} — DonDraper`" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link :href="route('generations.index')" class="text-gray-400 hover:text-white transition-colors p-1">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </Link>
                <h2 class="text-base font-bold text-white">Generation #{{ generation.id }}</h2>
                <span :class="['text-xs px-2.5 py-1 rounded-full border font-medium', status.bg, status.color]">{{ status.label }}</span>
                <span v-if="isInProgress" class="text-xs text-gray-600 animate-pulse">Auto-refreshing...</span>
            </div>
        </template>

        <div class="py-7 px-6 max-w-6xl mx-auto">
            <div class="grid lg:grid-cols-2 gap-7">
                <!-- Result Display -->
                <div class="space-y-4">
                    <div :class="['rounded-2xl overflow-hidden bg-gradient-to-br from-gray-900 to-gray-800 border border-white/5 relative', isVideo ? 'aspect-video' : 'aspect-square']">

                        <video v-if="isVideo && generation.result_url"
                            :src="generation.result_url" controls loop playsinline
                            class="w-full h-full object-contain bg-black" />

                        <img v-else-if="!isVideo && generation.result_url"
                            :src="generation.result_url" :alt="generation.prompt"
                            class="w-full h-full object-cover" />

                        <div v-else class="w-full h-full flex flex-col items-center justify-center gap-4 p-6">
                            <div v-if="isInProgress" class="text-center">
                                <svg class="w-10 h-10 text-sky-400 animate-spin mx-auto" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                                <p class="text-sm text-gray-500 mt-4">
                                    {{ isVideo ? 'Generating your video...' : 'Generating your image...' }}
                                </p>
                            </div>
                            <div v-else-if="generation.status === 'failed'" class="text-center">
                                <div class="w-14 h-14 rounded-2xl bg-rose-500/10 border border-rose-500/20 flex items-center justify-center mx-auto mb-4">
                                    <svg class="w-7 h-7 text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </div>
                                <p class="text-rose-400 font-medium text-sm mb-2">Generation Failed</p>
                                <p v-if="generation.error_message" class="text-xs text-gray-500 max-w-sm">{{ generation.error_message }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Progress Steps -->
                    <div v-if="isInProgress && steps.length > 0" class="bg-[#0A0E1A] border border-white/6 rounded-2xl p-5">
                        <div class="space-y-2.5">
                            <div v-for="(step, i) in steps" :key="i" class="flex items-center gap-3">
                                <div class="flex-shrink-0 w-5 h-5 flex items-center justify-center">
                                    <svg v-if="step.status === 'done'" class="w-5 h-5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    <svg v-else-if="step.status === 'running'" class="w-4 h-4 text-sky-400 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                    </svg>
                                    <svg v-else-if="step.status === 'failed'" class="w-4 h-4 text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                    <div v-else class="w-3 h-3 rounded-full border border-gray-700"></div>
                                </div>
                                <span :class="[
                                    'text-sm transition-colors',
                                    step.status === 'done' ? 'text-gray-500' :
                                    step.status === 'running' ? 'text-white font-medium' :
                                    step.status === 'failed' ? 'text-rose-400' :
                                    'text-gray-600'
                                ]">{{ step.label }}</span>
                            </div>
                        </div>
                        <p class="text-xs text-gray-600 mt-4 animate-pulse">Auto-refreshing every {{ isVideo ? '5' : '3' }}s...</p>
                    </div>

                    <!-- Actions -->
                    <div v-if="generation.result_url" class="flex gap-3 flex-wrap">
                        <button @click="download"
                            class="flex-1 flex items-center justify-center gap-2 bg-gradient-to-r from-sky-500 to-cyan-600 hover:from-sky-400 hover:to-cyan-500 text-white font-semibold py-3 rounded-xl transition-all shadow-lg shadow-sky-500/20 text-sm">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            Download {{ isVideo ? 'MP4' : 'PNG' }}
                        </button>
                        <Link :href="route('generations.create')"
                            class="flex items-center gap-2 bg-white/4 hover:bg-white/8 border border-white/8 text-white font-semibold py-3 px-4 rounded-xl transition-all text-sm">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            New
                        </Link>
                        <button @click="togglePublic"
                            :class="['flex items-center gap-2 border font-semibold py-3 px-4 rounded-xl transition-all text-sm',
                                generation.is_public
                                    ? 'bg-emerald-500/8 border-emerald-500/25 text-emerald-400 hover:bg-emerald-500/15'
                                    : 'bg-white/4 hover:bg-white/8 border-white/8 text-gray-400 hover:text-white']">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path v-if="generation.is_public" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                            </svg>
                            {{ generation.is_public ? 'Public' : 'Make Public' }}
                        </button>
                    </div>
                </div>

                <!-- Details -->
                <div class="space-y-4">
                    <div class="bg-[#0A0E1A] border border-white/6 rounded-2xl p-5">
                        <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">Prompt</h3>
                        <p class="text-white leading-relaxed text-sm">{{ generation.prompt }}</p>
                        <div v-if="generation.negative_prompt" class="mt-4 pt-4 border-t border-white/5">
                            <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Negative Prompt</h3>
                            <p class="text-gray-400 text-sm">{{ generation.negative_prompt }}</p>
                        </div>
                    </div>

                    <!-- Reference People -->
                    <div v-if="generation.reference_persons?.length" class="bg-[#0A0E1A] border border-emerald-500/15 rounded-2xl p-5">
                        <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-4 flex items-center gap-2">
                            <span>🧑‍🤝‍🧑</span> Reference People
                        </h3>
                        <div class="flex gap-3 flex-wrap">
                            <div v-for="person in generation.reference_persons" :key="person.path" class="flex flex-col items-center gap-2">
                                <img :src="`/storage/${person.path}`" :alt="person.name"
                                    class="w-18 h-18 object-cover rounded-xl border border-white/10" style="width:72px;height:72px;" />
                                <span class="text-xs text-gray-400 text-center">{{ person.name }}</span>
                            </div>
                        </div>
                        <p class="text-[10px] text-gray-600 mt-3">Analyzed with GPT-4o Vision · Generated with gpt-image-1 edits endpoint</p>
                    </div>

                    <!-- Product Reference -->
                    <div v-if="generation.product_type || generation.product_image_path || generation.product_image_paths?.length"
                        class="bg-[#0A0E1A] border border-sky-500/15 rounded-2xl p-5">
                        <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-4 flex items-center gap-2">
                            <span>📦</span> Product Reference
                            <span v-if="(generation.product_image_paths?.length ?? 0) > 1"
                                class="ml-auto text-xs text-sky-400 font-normal">
                                {{ generation.product_image_paths.length }} angles
                            </span>
                        </h3>

                        <div v-if="generation.product_image_paths?.length > 0" class="grid grid-cols-4 gap-2 mb-4">
                            <div v-for="(path, i) in generation.product_image_paths" :key="i" class="relative">
                                <img :src="`/storage/${path}`" :alt="`Product angle ${i + 1}`"
                                    class="w-full aspect-square object-cover rounded-xl border border-white/8" />
                                <div class="absolute bottom-1 left-1 text-[9px] font-semibold bg-black/70 text-gray-300 px-1.5 py-0.5 rounded">
                                    {{ ['Front','Side','Back','Detail'][i] ?? `#${i+1}` }}
                                </div>
                            </div>
                        </div>

                        <div v-else-if="generation.product_image_path" class="mb-4">
                            <img :src="`/storage/${generation.product_image_path}`" alt="Product"
                                class="w-20 h-20 object-cover rounded-xl border border-white/8" />
                        </div>

                        <div v-if="generation.product_type" class="text-sm font-medium text-sky-300 mb-1">{{ generation.product_type }}</div>
                        <p class="text-xs text-gray-600">GPT-4o Vision analyzed each angle and combined the descriptions into the generation prompt.</p>
                    </div>

                    <div class="bg-[#0A0E1A] border border-white/6 rounded-2xl p-5">
                        <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-4">Generation Settings</h3>
                        <div class="space-y-2.5">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Model</span>
                                <span class="text-white font-medium text-xs">{{ generation.model }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Type</span>
                                <span class="text-white font-medium text-xs capitalize">{{ generation.type }}</span>
                            </div>
                            <div v-if="generation.width && generation.height" class="flex justify-between text-sm">
                                <span class="text-gray-500">Resolution</span>
                                <span class="text-white font-medium text-xs">{{ generation.width }}×{{ generation.height }}</span>
                            </div>
                            <div v-for="(val, key) in generation.attributes" :key="key" class="flex justify-between text-sm">
                                <span class="text-gray-500 capitalize text-xs">{{ String(key).replace(/_/g, ' ') }}</span>
                                <span class="text-white font-medium text-xs">{{ val }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="bg-[#0A0E1A] border border-white/6 rounded-2xl p-5">
                        <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-4">Metadata</h3>
                        <div class="space-y-2.5">
                            <div class="flex justify-between">
                                <span class="text-xs text-gray-500">Credits Used</span>
                                <span class="text-xs text-sky-400 font-medium">{{ generation.credits_used }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-xs text-gray-500">Created</span>
                                <span class="text-xs text-white">{{ new Date(generation.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit' }) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
