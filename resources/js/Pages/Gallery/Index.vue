<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
    generations: Object,
    filters: Object,
})

const typeFilter = ref(props.filters?.type ?? '')

function applyFilter() {
    router.get(route('gallery'), { type: typeFilter.value || undefined }, { preserveState: true })
}

const selected = ref(null)

function openModal(gen) {
    selected.value = gen
    document.body.style.overflow = 'hidden'
}

function closeModal() {
    selected.value = null
    document.body.style.overflow = ''
}

function handleBackdropClick(e) {
    if (e.target === e.currentTarget) closeModal()
}

const ATTR_LABELS = {
    art_style: 'Art Style',
    lighting: 'Lighting',
    mood: 'Mood',
    camera_angle: 'Camera Angle',
    color_palette: 'Color Palette',
    detail_level: 'Detail Level',
    sharpness: 'Sharpness',
    resolution: 'Resolution',
    quality: 'Quality',
}

const ATTR_VALUE_LABELS = {
    photorealistic: 'Photorealistic', oil_painting: 'Oil Painting', watercolor: 'Watercolor',
    digital_art: 'Digital Art', anime: 'Anime', sketch: 'Sketch', comic: 'Comic',
    cinematic: 'Cinematic', minimalist: 'Minimalist', abstract: 'Abstract',
    impressionist: 'Impressionist', cyberpunk: 'Cyberpunk', fantasy: 'Fantasy', neon: 'Neon',
    natural: 'Natural', golden_hour: 'Golden Hour', studio: 'Studio', dramatic: 'Dramatic',
    soft: 'Soft', backlit: 'Backlit', volumetric: 'Volumetric',
    joyful: 'Joyful', melancholic: 'Melancholic', mysterious: 'Mysterious', epic: 'Epic',
    serene: 'Serene', dark: 'Dark', whimsical: 'Whimsical',
    eye_level: 'Eye Level', birds_eye: "Bird's Eye", worms_eye: "Worm's Eye",
    dutch_angle: 'Dutch Angle', close_up: 'Close-up', wide_shot: 'Wide Shot', macro: 'Macro',
    vibrant: 'Vibrant', muted: 'Muted', monochrome: 'Monochrome', warm: 'Warm',
    cool: 'Cool', earthy: 'Earthy', black_and_white: 'Black & White',
    standard: 'Standard', hd: 'HD', ultra_hd: 'Ultra HD',
}

function formatAttrValue(key, value) {
    if (key === 'detail_level' || key === 'sharpness') return `${value} / 10`
    return ATTR_VALUE_LABELS[value] ?? value
}

function meaningfulAttrs(attrs) {
    if (!attrs) return []
    return Object.entries(attrs)
        .filter(([k, v]) => ATTR_LABELS[k] && v !== null && v !== undefined && v !== '')
        .map(([k, v]) => ({ key: k, label: ATTR_LABELS[k], value: formatAttrValue(k, v) }))
}
</script>

<template>
    <Head title="Community Gallery — DonDraper" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-base font-bold text-white">Community Gallery</h2>
                <select v-model="typeFilter" @change="applyFilter"
                    class="bg-[#0A0E1A] border border-white/8 hover:border-white/15 rounded-xl px-3 py-2 text-sm text-white focus:outline-none focus:border-sky-500/40 transition-all cursor-pointer">
                    <option value="" class="bg-[#0A0E1A] text-white">All Types</option>
                    <option value="image" class="bg-[#0A0E1A] text-white">Images</option>
                    <option value="video" class="bg-[#0A0E1A] text-white">Videos</option>
                </select>
            </div>
        </template>

        <div class="py-7 px-6 max-w-7xl mx-auto">
            <p class="text-xs text-gray-500 mb-6">Publicly shared AI generations from the community</p>

            <!-- Grid -->
            <div v-if="generations.data.length" class="columns-2 md:columns-3 lg:columns-4 gap-3 space-y-3">
                <div v-for="gen in generations.data" :key="gen.id"
                    class="group relative rounded-2xl overflow-hidden bg-[#0A0E1A] border border-white/5 hover:border-sky-500/25 transition-all cursor-pointer break-inside-avoid mb-3"
                    @click="openModal(gen)">
                    <div :class="gen.type === 'video' ? 'aspect-video' : ''">
                        <video v-if="gen.type === 'video' && gen.result_url"
                            :src="gen.result_url" loop muted playsinline autoplay
                            class="w-full h-full object-cover" />
                        <img v-else-if="gen.result_url"
                            :src="gen.result_url" :alt="gen.prompt"
                            class="w-full h-auto object-cover block" />
                        <div v-else class="aspect-square bg-gradient-to-br from-gray-800 to-gray-900 flex items-center justify-center">
                            <svg class="w-8 h-8 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                    </div>
                    <!-- Hover overlay -->
                    <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity p-3 flex flex-col justify-end">
                        <p class="text-xs text-white line-clamp-2 mb-1">{{ gen.prompt }}</p>
                        <p class="text-[10px] text-gray-400">by {{ gen.user?.name }}</p>
                    </div>
                    <div v-if="gen.type === 'video'"
                        class="absolute top-2 left-2 text-[10px] bg-black/60 text-white px-2 py-0.5 rounded-full backdrop-blur-sm border border-white/10">
                        Video
                    </div>
                </div>
            </div>

            <div v-else class="text-center py-32">
                <div class="w-16 h-16 rounded-2xl bg-sky-500/10 border border-sky-500/20 flex items-center justify-center mx-auto mb-5">
                    <svg class="w-8 h-8 text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <h3 class="text-lg font-bold text-white mb-2">No public generations yet</h3>
                <p class="text-gray-500 text-sm">Be the first to share your creations with the community!</p>
            </div>

            <!-- Pagination -->
            <div v-if="generations.last_page > 1" class="flex justify-center gap-2 mt-10">
                <Link v-for="link in generations.links" :key="link.label"
                    :href="link.url ?? '#'"
                    :class="['text-xs px-3 py-2 rounded-xl transition-all font-medium',
                        link.active ? 'bg-gradient-to-r from-sky-500 to-cyan-600 text-white shadow-lg shadow-sky-500/20' : 'bg-white/4 border border-white/8 text-gray-400 hover:text-white hover:bg-white/8',
                        !link.url ? 'opacity-30 pointer-events-none' : '']"
                    v-html="link.label" />
            </div>
        </div>
    </AuthenticatedLayout>

    <!-- Modal -->
    <Teleport to="body">
        <div v-if="selected"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/85 backdrop-blur-sm p-4"
            @click="handleBackdropClick">
            <div class="relative bg-[#0A0E1A] rounded-2xl border border-white/8 shadow-2xl w-full max-w-5xl max-h-[92vh] flex flex-col md:flex-row overflow-hidden">

                <!-- Close button -->
                <button @click="closeModal"
                    class="absolute top-3 right-3 z-10 w-8 h-8 flex items-center justify-center rounded-xl bg-black/60 hover:bg-black/80 text-white transition-colors border border-white/10">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>

                <!-- Left: image/video -->
                <div class="md:w-3/5 bg-black flex items-center justify-center min-h-64 md:min-h-0">
                    <video v-if="selected.type === 'video' && selected.result_url"
                        :src="selected.result_url" controls loop muted playsinline autoplay
                        class="w-full h-full object-contain max-h-[92vh]" />
                    <img v-else-if="selected.result_url"
                        :src="selected.result_url" :alt="selected.prompt"
                        class="w-full h-full object-contain max-h-[92vh]" />
                </div>

                <!-- Right: details -->
                <div class="md:w-2/5 flex flex-col overflow-y-auto p-6 gap-5">
                    <!-- Author -->
                    <div class="flex items-center gap-2.5">
                        <div class="w-7 h-7 rounded-full bg-gradient-to-br from-sky-500 to-cyan-600 flex items-center justify-center text-xs font-bold text-white shrink-0">
                            {{ selected.user?.name?.[0]?.toUpperCase() ?? '?' }}
                        </div>
                        <span class="text-sm text-gray-300 font-medium">{{ selected.user?.name }}</span>
                        <span v-if="selected.model" class="ml-auto text-xs px-2 py-0.5 rounded-full bg-white/4 text-gray-400 border border-white/8">
                            {{ selected.model }}
                        </span>
                    </div>

                    <!-- Prompt -->
                    <div>
                        <p class="text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-2">Prompt</p>
                        <p class="text-sm text-gray-200 leading-relaxed">{{ selected.prompt }}</p>
                    </div>

                    <!-- Attributes -->
                    <div v-if="meaningfulAttrs(selected.attributes).length">
                        <p class="text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-3">Image Settings</p>
                        <div class="grid grid-cols-2 gap-2">
                            <div v-for="attr in meaningfulAttrs(selected.attributes)" :key="attr.key"
                                class="bg-white/4 rounded-xl px-3 py-2 border border-white/5">
                                <p class="text-[10px] text-gray-500 mb-0.5">{{ attr.label }}</p>
                                <p class="text-xs font-medium text-white">{{ attr.value }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Product type -->
                    <div v-if="selected.product_type">
                        <p class="text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-2">Product Type</p>
                        <span class="text-xs px-2.5 py-1 rounded-lg bg-white/4 text-gray-300 border border-white/8">
                            {{ selected.product_type }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </Teleport>
</template>
