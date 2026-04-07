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

// ── Modal ────────────────────────────────────────────────────────────────────
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

// ── Attribute display helpers ─────────────────────────────────────────────
const ATTR_LABELS = {
    art_style:      'Art Style',
    lighting:       'Lighting',
    mood:           'Mood',
    camera_angle:   'Camera Angle',
    color_palette:  'Color Palette',
    detail_level:   'Detail Level',
    sharpness:      'Sharpness',
    resolution:     'Resolution',
    quality:        'Quality',
}

const ATTR_VALUE_LABELS = {
    // art_style
    photorealistic: 'Photorealistic',
    oil_painting:   'Oil Painting',
    watercolor:     'Watercolor',
    digital_art:    'Digital Art',
    anime:          'Anime',
    sketch:         'Sketch',
    comic:          'Comic',
    cinematic:      'Cinematic',
    minimalist:     'Minimalist',
    abstract:       'Abstract',
    impressionist:  'Impressionist',
    cyberpunk:      'Cyberpunk',
    fantasy:        'Fantasy',
    neon:           'Neon',
    // lighting
    natural:        'Natural',
    golden_hour:    'Golden Hour',
    studio:         'Studio',
    dramatic:       'Dramatic',
    soft:           'Soft',
    backlit:        'Backlit',
    volumetric:     'Volumetric',
    // mood
    joyful:         'Joyful',
    melancholic:    'Melancholic',
    mysterious:     'Mysterious',
    epic:           'Epic',
    serene:         'Serene',
    dark:           'Dark',
    whimsical:      'Whimsical',
    // camera_angle
    eye_level:      'Eye Level',
    birds_eye:      "Bird's Eye",
    worms_eye:      "Worm's Eye",
    dutch_angle:    'Dutch Angle',
    close_up:       'Close-up',
    wide_shot:      'Wide Shot',
    macro:          'Macro',
    // color_palette
    vibrant:        'Vibrant',
    muted:          'Muted',
    monochrome:     'Monochrome',
    warm:           'Warm',
    cool:           'Cool',
    earthy:         'Earthy',
    black_and_white:'Black & White',
    // quality
    standard:       'Standard',
    hd:             'HD',
    ultra_hd:       'Ultra HD',
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
            <h2 class="text-xl font-bold text-white">Community Gallery</h2>
        </template>

        <div class="py-8 px-6 max-w-7xl mx-auto">
            <!-- Title + filters -->
            <div class="flex items-center justify-between mb-8">
                <p class="text-gray-500">Publicly shared AI generations</p>
                <select v-model="typeFilter" @change="applyFilter"
                    class="bg-gray-900/50 border border-white/10 rounded-xl px-4 py-2 text-sm text-white focus:outline-none focus:border-violet-500/50">
                    <option value="">All Types</option>
                    <option value="image">Images</option>
                    <option value="video">Videos</option>
                </select>
            </div>

            <!-- Grid -->
            <div v-if="generations.data.length" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                <div v-for="gen in generations.data" :key="gen.id"
                    class="group relative rounded-2xl overflow-hidden bg-gray-900 border border-white/5 hover:border-violet-500/30 transition-all cursor-pointer"
                    @click="openModal(gen)">
                    <div :class="gen.type === 'video' ? 'aspect-video' : 'aspect-square'">
                        <video v-if="gen.type === 'video' && gen.result_url"
                            :src="gen.result_url" loop muted playsinline autoplay
                            class="w-full h-full object-cover" />
                        <img v-else-if="gen.result_url"
                            :src="gen.result_url" :alt="gen.prompt"
                            class="w-full h-full object-cover" />
                    </div>
                    <!-- Hover overlay -->
                    <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity p-3 flex flex-col justify-end">
                        <p class="text-xs text-white line-clamp-2">{{ gen.prompt }}</p>
                        <p class="text-xs text-gray-400 mt-1">by {{ gen.user?.name }}</p>
                    </div>
                    <div v-if="gen.type === 'video'"
                        class="absolute top-2 right-2 text-xs bg-black/60 text-white px-2 py-0.5 rounded-full">
                        Video
                    </div>
                </div>
            </div>

            <div v-else class="text-center py-24 text-gray-500">
                No public generations yet. Be the first to share!
            </div>

            <!-- Pagination -->
            <div v-if="generations.last_page > 1" class="flex justify-center gap-2 mt-10">
                <Link v-for="link in generations.links" :key="link.label"
                    :href="link.url ?? '#'"
                    :class="['text-xs px-3 py-1.5 rounded-lg transition-all',
                        link.active ? 'bg-violet-600 text-white' : 'bg-white/5 text-gray-400 hover:bg-white/10',
                        !link.url ? 'opacity-40 pointer-events-none' : '']"
                    v-html="link.label" />
            </div>
        </div>
    </AuthenticatedLayout>

    <!-- ── Image / Video Modal ──────────────────────────────────────────────── -->
    <Teleport to="body">
        <div v-if="selected"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm p-4"
            @click="handleBackdropClick">
            <div class="relative bg-gray-900 rounded-2xl border border-white/10 shadow-2xl w-full max-w-5xl max-h-[90vh] flex flex-col md:flex-row overflow-hidden">

                <!-- Close button -->
                <button @click="closeModal"
                    class="absolute top-3 right-3 z-10 w-8 h-8 flex items-center justify-center rounded-full bg-black/50 hover:bg-black/80 text-white transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>

                <!-- Left: image / video -->
                <div class="md:w-3/5 bg-black flex items-center justify-center min-h-64 md:min-h-0">
                    <video v-if="selected.type === 'video' && selected.result_url"
                        :src="selected.result_url" controls loop muted playsinline autoplay
                        class="w-full h-full object-contain max-h-[90vh]" />
                    <img v-else-if="selected.result_url"
                        :src="selected.result_url" :alt="selected.prompt"
                        class="w-full h-full object-contain max-h-[90vh]" />
                </div>

                <!-- Right: details panel -->
                <div class="md:w-2/5 flex flex-col overflow-y-auto p-6 gap-6">
                    <!-- Author -->
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-full bg-violet-600 flex items-center justify-center text-xs font-bold text-white shrink-0">
                            {{ selected.user?.name?.[0]?.toUpperCase() ?? '?' }}
                        </div>
                        <span class="text-sm text-gray-300">{{ selected.user?.name }}</span>
                        <span v-if="selected.model" class="ml-auto text-xs px-2 py-0.5 rounded-full bg-white/5 text-gray-400 border border-white/10">
                            {{ selected.model }}
                        </span>
                    </div>

                    <!-- Prompt -->
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Prompt</p>
                        <p class="text-sm text-gray-200 leading-relaxed">{{ selected.prompt }}</p>
                    </div>

                    <!-- Settings / Attributes -->
                    <div v-if="meaningfulAttrs(selected.attributes).length">
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Image Settings</p>
                        <div class="grid grid-cols-2 gap-2">
                            <div v-for="attr in meaningfulAttrs(selected.attributes)" :key="attr.key"
                                class="bg-white/5 rounded-xl px-3 py-2 border border-white/5">
                                <p class="text-xs text-gray-500 mb-0.5">{{ attr.label }}</p>
                                <p class="text-xs font-medium text-white">{{ attr.value }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Product type -->
                    <div v-if="selected.product_type">
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Product Type</p>
                        <span class="text-xs px-2 py-1 rounded-lg bg-white/5 text-gray-300 border border-white/10">
                            {{ selected.product_type }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </Teleport>
</template>
