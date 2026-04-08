<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3'
import { ref, computed, watch } from 'vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
    recentGenerations: Array,
    stats: Object,
    imageModels: Array,
    videoModels: Array,
})

// ── Generation type ──
const generationType = ref('image')
const creditCost = computed(() => generationType.value === 'video' ? 5 : 1)

// ── Models ──
const showModelPicker = ref(false)
const models = computed(() => generationType.value === 'image' ? props.imageModels : props.videoModels)
const providers = computed(() => {
    const seen = new Map()
    for (const m of models.value) {
        if (!seen.has(m.provider)) seen.set(m.provider, [])
        seen.get(m.provider).push(m)
    }
    return [...seen.entries()].map(([name, items]) => ({ name, items }))
})
const activeProvider = ref(null)
const selectedModel = ref(null)

// Init default model
const initModel = () => {
    const list = models.value
    const rec = list.find(m => m.recommended) || list[0]
    selectedModel.value = rec
    activeProvider.value = rec?.provider ?? list[0]?.provider
}
initModel()
watch(generationType, initModel)

const selectModel = (m) => {
    selectedModel.value = m
    showModelPicker.value = false
}

const providerInitial = (name) => {
    const map = { OpenAI: 'O', Google: 'G', xAI: 'X', ElevenLabs: 'E' }
    return map[name] || name.charAt(0)
}
const providerColor = (name) => {
    const map = {
        OpenAI: 'from-emerald-500 to-teal-600',
        Google: 'from-blue-500 to-sky-600',
        xAI: 'from-rose-500 to-pink-600',
        ElevenLabs: 'from-violet-500 to-purple-600',
    }
    return map[name] || 'from-gray-500 to-gray-600'
}

// ── Reference files ──
const showPersonRef = ref(false)
const showProductRef = ref(false)
const person1Preview = ref(null)
const person2Preview = ref(null)
const productPreviews = ref([])

const handlePersonFile = (event, num) => {
    const file = event.target.files[0]
    if (!file) return
    form[`person_${num}_image`] = file
    const reader = new FileReader()
    reader.onload = (e) => { if (num === 1) person1Preview.value = e.target.result; else person2Preview.value = e.target.result }
    reader.readAsDataURL(file)
}

const handleProductFiles = (event) => {
    const files = Array.from(event.target.files).slice(0, 4)
    form.product_images = files
    productPreviews.value = []
    files.forEach(f => {
        const reader = new FileReader()
        reader.onload = (e) => productPreviews.value.push(e.target.result)
        reader.readAsDataURL(f)
    })
}

const removePersonRef = (num) => {
    form[`person_${num}_image`] = null
    form[`person_${num}_name`] = ''
    if (num === 1) person1Preview.value = null; else person2Preview.value = null
}

const removeProductRef = () => {
    form.product_images = []
    form.product_type = ''
    productPreviews.value = []
}

// ── Form ──
const form = useForm({
    type: 'image',
    model: '',
    prompt: '',
    negative_prompt: '',
    product_type: '',
    product_images: [],
    person_1_name: '',
    person_1_image: null,
    person_2_name: '',
    person_2_image: null,
})

const generate = () => {
    if (!form.prompt.trim()) return
    form.type = generationType.value
    form.model = selectedModel.value?.id ?? ''
    form.post(route('generations.store'), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            form.reset()
            person1Preview.value = null
            person2Preview.value = null
            productPreviews.value = []
            showPersonRef.value = false
            showProductRef.value = false
        },
    })
}

const statusColor = (status) => {
    const map = {
        completed: 'text-emerald-400 bg-emerald-400/10',
        processing: 'text-sky-400 bg-sky-400/10',
        pending: 'text-yellow-400 bg-yellow-400/10',
        failed: 'text-rose-400 bg-rose-400/10',
    }
    return map[status] || 'text-gray-400 bg-gray-400/10'
}
</script>

<template>
    <Head title="Dashboard — DonDraper" />
    <AuthenticatedLayout>
        <div class="py-8 px-5 max-w-4xl mx-auto space-y-8">

            <!-- ═══ AI Generator Chat ═══ -->
            <div class="bg-[#0A0E1A] border border-white/8 rounded-3xl shadow-2xl shadow-black/40 overflow-visible">

                <!-- Type switcher + header -->
                <div class="px-6 pt-5 pb-4 flex items-center justify-between border-b border-white/5">
                    <h1 class="text-xl font-black text-white">AI {{ generationType === 'image' ? 'Image' : 'Video' }} Generator</h1>
                    <div class="flex items-center bg-white/5 rounded-xl p-0.5 border border-white/8">
                        <button @click="generationType = 'image'"
                            :class="['px-3.5 py-1.5 rounded-lg text-xs font-semibold transition-all', generationType === 'image' ? 'bg-sky-500/20 text-sky-300 shadow-sm' : 'text-gray-500 hover:text-gray-300']">
                            <span class="flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                Image
                            </span>
                        </button>
                        <button @click="generationType = 'video'"
                            :class="['px-3.5 py-1.5 rounded-lg text-xs font-semibold transition-all', generationType === 'video' ? 'bg-sky-500/20 text-sky-300 shadow-sm' : 'text-gray-500 hover:text-gray-300']">
                            <span class="flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.277A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                Video
                            </span>
                        </button>
                    </div>
                </div>

                <!-- Prompt textarea -->
                <div class="px-6 py-5">
                    <textarea
                        v-model="form.prompt"
                        @keydown.ctrl.enter="generate"
                        rows="3"
                        placeholder="Describe the image you want to generate…"
                        class="w-full bg-transparent text-white placeholder-gray-600 text-base resize-none outline-none leading-relaxed"
                    />
                    <p v-if="form.errors.prompt" class="text-xs text-rose-400 mt-1">{{ form.errors.prompt }}</p>
                    <p v-if="form.errors.credits" class="text-xs text-rose-400 mt-1">{{ form.errors.credits }}</p>
                </div>

                <!-- Reference attachments -->
                <div v-if="showPersonRef || showProductRef" class="px-6 pb-4 space-y-4">

                    <!-- Person references -->
                    <div v-if="showPersonRef" class="bg-white/3 border border-white/6 rounded-2xl p-4">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Reference People</span>
                            <button @click="showPersonRef = false; removePersonRef(1); removePersonRef(2)" class="text-xs text-gray-600 hover:text-gray-400 transition-colors">Remove</button>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div v-for="n in 2" :key="n">
                                <div v-if="n === 1 ? person1Preview : person2Preview" class="relative">
                                    <img :src="n === 1 ? person1Preview : person2Preview" class="w-full h-28 object-cover rounded-xl" />
                                    <button @click="removePersonRef(n)" class="absolute top-1 right-1 w-5 h-5 bg-black/60 rounded-full flex items-center justify-center text-gray-400 hover:text-white">
                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                    <input v-model="form[`person_${n}_name`]" type="text" :placeholder="`Person ${n} name`"
                                        class="mt-2 w-full bg-white/5 border border-white/8 rounded-lg px-3 py-1.5 text-xs text-white placeholder-gray-600 outline-none focus:border-sky-500/40" />
                                </div>
                                <label v-else class="flex flex-col items-center justify-center h-28 border border-dashed border-white/10 hover:border-sky-500/30 rounded-xl cursor-pointer transition-colors">
                                    <svg class="w-6 h-6 text-gray-600 mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    <span class="text-[10px] text-gray-600">Person {{ n }}</span>
                                    <input type="file" accept="image/*" class="hidden" @change="handlePersonFile($event, n)" />
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Product reference -->
                    <div v-if="showProductRef" class="bg-white/3 border border-white/6 rounded-2xl p-4">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Product Reference</span>
                            <button @click="showProductRef = false; removeProductRef()" class="text-xs text-gray-600 hover:text-gray-400 transition-colors">Remove</button>
                        </div>
                        <input v-model="form.product_type" type="text" placeholder="Product type (e.g. sneaker, watch, perfume)"
                            class="w-full bg-white/5 border border-white/8 rounded-xl px-3 py-2 text-sm text-white placeholder-gray-600 outline-none focus:border-sky-500/40 mb-3" />
                        <div class="flex gap-2 flex-wrap">
                            <div v-for="(preview, i) in productPreviews" :key="i" class="relative w-20 h-20 rounded-xl overflow-hidden">
                                <img :src="preview" class="w-full h-full object-cover" />
                            </div>
                            <label v-if="productPreviews.length < 4"
                                class="w-20 h-20 flex flex-col items-center justify-center border border-dashed border-white/10 hover:border-sky-500/30 rounded-xl cursor-pointer transition-colors">
                                <svg class="w-5 h-5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v16m8-8H4"/></svg>
                                <span class="text-[9px] text-gray-600 mt-0.5">Add photo</span>
                                <input type="file" accept="image/*" multiple class="hidden" @change="handleProductFiles" />
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Bottom toolbar -->
                <div class="px-4 pb-4 flex items-center gap-2 relative">

                    <!-- Model picker button -->
                    <div class="relative">
                        <button @click="showModelPicker = !showModelPicker"
                            class="flex items-center gap-2 bg-white/5 hover:bg-white/8 border border-white/10 hover:border-white/18 rounded-xl px-3 py-2 transition-all">
                            <div :class="`w-5 h-5 rounded-md bg-gradient-to-br ${providerColor(selectedModel?.provider)} flex items-center justify-center text-[9px] font-black text-white`">
                                {{ providerInitial(selectedModel?.provider) }}
                            </div>
                            <span class="text-sm font-semibold text-gray-200">{{ selectedModel?.label }}</span>
                            <svg :class="['w-3.5 h-3.5 text-gray-500 transition-transform', showModelPicker ? 'rotate-180' : '']" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                        </button>

                        <!-- Model dropdown -->
                        <Transition enter-from-class="opacity-0 scale-95 translate-y-1" enter-active-class="transition duration-150 ease-out" leave-to-class="opacity-0 scale-95 translate-y-1" leave-active-class="transition duration-100 ease-in">
                            <div v-if="showModelPicker"
                                class="absolute bottom-full mb-2 left-0 w-[480px] bg-[#0D1221] border border-white/10 rounded-2xl shadow-2xl shadow-black/60 overflow-hidden z-50">
                                <div class="p-3 border-b border-white/5">
                                    <span class="text-[10px] font-bold text-gray-500 uppercase tracking-widest">Models</span>
                                </div>
                                <div class="flex min-h-[200px]">
                                    <!-- Providers -->
                                    <div class="w-40 border-r border-white/5 p-2 space-y-0.5">
                                        <button v-for="p in providers" :key="p.name"
                                            @click="activeProvider = p.name"
                                            :class="['w-full flex items-center gap-2.5 px-3 py-2.5 rounded-xl transition-all text-left', activeProvider === p.name ? 'bg-white/8 text-white' : 'text-gray-400 hover:bg-white/4 hover:text-white']">
                                            <div :class="`w-6 h-6 rounded-lg bg-gradient-to-br ${providerColor(p.name)} flex items-center justify-center text-[9px] font-black text-white flex-shrink-0`">
                                                {{ providerInitial(p.name) }}
                                            </div>
                                            <span class="text-sm font-medium truncate">{{ p.name }}</span>
                                        </button>
                                    </div>
                                    <!-- Models for active provider -->
                                    <div class="flex-1 p-2 space-y-0.5">
                                        <button v-for="m in providers.find(p => p.name === activeProvider)?.items ?? []" :key="m.id"
                                            @click="selectModel(m)"
                                            :class="['w-full flex items-center justify-between px-3 py-3 rounded-xl transition-all text-left', selectedModel?.id === m.id ? 'bg-sky-500/10 border border-sky-500/20' : 'hover:bg-white/4 border border-transparent']">
                                            <div class="flex items-center gap-2.5 min-w-0">
                                                <div :class="`w-7 h-7 rounded-lg bg-gradient-to-br ${providerColor(m.provider)} flex items-center justify-center flex-shrink-0`">
                                                    <svg class="w-3.5 h-3.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                                                </div>
                                                <div class="min-w-0">
                                                    <div class="flex items-center gap-1.5">
                                                        <span class="text-sm font-semibold text-white truncate">{{ m.label }}</span>
                                                        <span v-if="m.recommended" class="text-[9px] font-bold px-1.5 py-0.5 rounded-md bg-emerald-500/15 text-emerald-400 flex-shrink-0">REC</span>
                                                    </div>
                                                    <p class="text-[11px] text-gray-500 truncate mt-0.5">{{ m.description }}</p>
                                                </div>
                                            </div>
                                            <svg v-if="selectedModel?.id === m.id" class="w-4 h-4 text-sky-400 flex-shrink-0 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </Transition>
                    </div>

                    <!-- Reference buttons -->
                    <button v-if="generationType === 'image'" @click="showPersonRef = !showPersonRef"
                        :class="['flex items-center gap-1.5 px-2.5 py-2 rounded-lg text-xs font-medium border transition-all', showPersonRef ? 'bg-sky-500/10 border-sky-500/25 text-sky-300' : 'bg-white/4 border-white/8 text-gray-500 hover:text-gray-300 hover:border-white/15']">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        Person
                    </button>
                    <button v-if="generationType === 'image'" @click="showProductRef = !showProductRef"
                        :class="['flex items-center gap-1.5 px-2.5 py-2 rounded-lg text-xs font-medium border transition-all', showProductRef ? 'bg-sky-500/10 border-sky-500/25 text-sky-300' : 'bg-white/4 border-white/8 text-gray-500 hover:text-gray-300 hover:border-white/15']">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        Product
                    </button>

                    <div class="flex-1" />

                    <!-- Credit cost -->
                    <div class="flex items-center gap-1.5 text-xs text-gray-600">
                        <svg class="w-3 h-3 text-sky-500/50" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        {{ creditCost }} cr
                    </div>

                    <!-- Generate -->
                    <button @click="generate" :disabled="!form.prompt.trim() || form.processing"
                        class="flex items-center gap-2 bg-gradient-to-r from-sky-500 to-cyan-600 hover:from-sky-400 hover:to-cyan-500 disabled:opacity-40 disabled:cursor-not-allowed text-white font-bold px-5 py-2.5 rounded-xl text-sm transition-all hover:scale-105 shadow-lg shadow-sky-500/20">
                        <svg v-if="form.processing" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        <svg v-else class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        Generate
                    </button>
                </div>
            </div>

            <!-- Click outside to close model picker -->
            <div v-if="showModelPicker" class="fixed inset-0 z-40" @click="showModelPicker = false" />

            <!-- ═══ Stats ═══ -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-[#0A0E1A] border border-white/6 rounded-2xl p-5">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-8 h-8 rounded-xl bg-sky-500/10 border border-sky-500/18 flex items-center justify-center">
                            <svg class="w-4 h-4 text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                        <span class="text-xs text-gray-500 font-medium">Total Generations</span>
                    </div>
                    <div class="text-3xl font-black text-transparent bg-clip-text bg-gradient-to-r from-sky-400 to-cyan-400">{{ stats.total }}</div>
                </div>

                <div class="bg-[#0A0E1A] border border-white/6 rounded-2xl p-5">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-8 h-8 rounded-xl bg-emerald-500/10 border border-emerald-500/18 flex items-center justify-center">
                            <svg class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <span class="text-xs text-gray-500 font-medium">Completed</span>
                    </div>
                    <div class="text-3xl font-black text-transparent bg-clip-text bg-gradient-to-r from-emerald-400 to-teal-400">{{ stats.completed }}</div>
                </div>

                <div class="bg-[#0A0E1A] border border-white/6 rounded-2xl p-5">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-8 h-8 rounded-xl bg-amber-500/10 border border-amber-500/18 flex items-center justify-center">
                            <svg class="w-4 h-4 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        </div>
                        <span class="text-xs text-gray-500 font-medium">Credits Left</span>
                    </div>
                    <div class="flex items-end gap-3">
                        <div class="text-3xl font-black text-transparent bg-clip-text bg-gradient-to-r from-amber-400 to-orange-400">{{ stats.credits }}</div>
                        <Link :href="route('billing')" class="text-xs text-sky-400 hover:text-sky-300 mb-1 transition-colors">Top up →</Link>
                    </div>
                </div>
            </div>

            <!-- ═══ Recent Generations ═══ -->
            <div v-if="stats.total === 0"
                class="bg-gradient-to-br from-sky-600/8 to-cyan-600/5 border border-sky-500/15 rounded-2xl p-12 text-center">
                <div class="w-14 h-14 rounded-2xl bg-sky-500/12 border border-sky-500/20 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-7 h-7 text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                </div>
                <h3 class="text-lg font-bold mb-2 text-white">Your gallery is empty</h3>
                <p class="text-gray-500 text-sm">Type a prompt above and hit Generate to create your first image.</p>
            </div>

            <div v-else>
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-bold text-white">Recent Generations</h3>
                    <Link :href="route('generations.index')" class="text-xs text-sky-400 hover:text-sky-300 transition-colors">View all →</Link>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3">
                    <div v-for="gen in recentGenerations" :key="gen.id"
                        class="group relative bg-[#0A0E1A] border border-white/5 hover:border-sky-500/20 rounded-2xl overflow-hidden transition-all hover:-translate-y-0.5 hover:shadow-lg hover:shadow-sky-500/8">
                        <div class="aspect-square bg-gradient-to-br from-gray-800 to-gray-900 relative overflow-hidden">
                            <img v-if="gen.result_url" :src="gen.result_url" :alt="gen.prompt" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" />
                            <div v-else class="w-full h-full flex flex-col items-center justify-center gap-2">
                                <svg v-if="gen.status === 'processing' || gen.status === 'pending'" class="w-7 h-7 text-sky-400 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                                <svg v-else-if="gen.status === 'failed'" class="w-7 h-7 text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                <span class="text-xs text-gray-600 capitalize">{{ gen.status }}</span>
                            </div>
                            <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity flex items-end p-2.5">
                                <p class="text-xs text-gray-200 line-clamp-2">{{ gen.prompt }}</p>
                            </div>
                        </div>
                        <div class="px-3 py-2.5">
                            <span :class="['text-[10px] px-2 py-0.5 rounded-full font-medium capitalize', statusColor(gen.status)]">{{ gen.status }}</span>
                        </div>
                        <Link :href="route('generations.show', gen.id)" class="absolute inset-0" />
                    </div>
                </div>
            </div>

        </div>
    </AuthenticatedLayout>
</template>
