<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import { ref, computed, watch } from 'vue'
import axios from 'axios'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
    credits: Number,
    imageModels: Array,
    videoModels: Array,
})

const form = useForm({
    type: 'image',
    model: 'gpt-image-1',
    prompt: '',
    negative_prompt: '',
    product_type: '',
    product_images: [],
    person_1_name: '',
    person_1_image: null,
    person_2_name: '',
    person_2_image: null,
})

const currentModels = computed(() => form.type === 'video' ? props.videoModels : props.imageModels)

// When type changes, reset to the recommended model for that type
watch(() => form.type, (type) => {
    const models = type === 'video' ? props.videoModels : props.imageModels
    const recommended = models.find(m => m.recommended) ?? models[0]
    if (recommended) form.model = recommended.id
})

// Accordion — prompt open by default, others closed
const openSections = ref(new Set(['prompt']))

const toggle = (key) => {
    if (openSections.value.has(key)) {
        openSections.value.delete(key)
    } else {
        openSections.value.add(key)
    }
    // trigger reactivity
    openSections.value = new Set(openSections.value)
}

const isOpen = (key) => openSections.value.has(key)

// Product images
const MAX_PRODUCT_IMAGES = 4
const productPreviews = ref([])

const addProductImage = (e) => {
    const file = e.target.files[0]
    if (!file) return
    if (form.product_images.length >= MAX_PRODUCT_IMAGES) return
    form.product_images = [...form.product_images, file]
    const reader = new FileReader()
    reader.onload = (ev) => { productPreviews.value = [...productPreviews.value, ev.target.result] }
    reader.readAsDataURL(file)
    e.target.value = ''
}

const removeProductImage = (index) => {
    form.product_images = form.product_images.filter((_, i) => i !== index)
    productPreviews.value = productPreviews.value.filter((_, i) => i !== index)
}

// Reference people
const person1Preview = ref(null)
const person2Preview = ref(null)
const showPerson2 = ref(false)

const onPersonImageChange = (e, n) => {
    const file = e.target.files[0]
    if (!file) return
    if (n === 1) form.person_1_image = file
    else form.person_2_image = file
    const reader = new FileReader()
    reader.onload = (ev) => {
        if (n === 1) person1Preview.value = ev.target.result
        else person2Preview.value = ev.target.result
    }
    reader.readAsDataURL(file)
}

const clearPerson = (n) => {
    if (n === 1) { form.person_1_image = null; form.person_1_name = ''; person1Preview.value = null }
    else { form.person_2_image = null; form.person_2_name = ''; person2Preview.value = null; showPerson2.value = false }
}

const creditCost = computed(() => form.type === 'video' ? 5 : 1)
const estimatedTime = computed(() => form.type === 'video' ? '2–10 min' : '~30 sec')
const characterCount = computed(() => form.prompt.length)
const maxChars = 2000

const personCount = computed(() => (person1Preview.value ? 1 : 0) + (person2Preview.value ? 1 : 0))

const promptSuggestions = [
    'A majestic dragon soaring over a neon-lit cyberpunk city at night',
    'Portrait of a wise elderly wizard in a mystical forest, detailed oil painting',
    'Serene Japanese zen garden at golden hour, ultra photorealistic',
    'Abstract fluid art in deep ocean blues and luminescent teals',
    'Futuristic space station orbiting a ringed gas giant, cinematic',
    'A cozy coffee shop on a rainy day, warm impressionist style',
]

const useSuggestion = (s) => { form.prompt = s }

const submit = () => {
    form.post(route('generations.store'), { forceFormData: true })
}

// Prompt enhancer
const enhancing = ref(false)
const enhanceError = ref(null)

async function enhancePrompt() {
    if (!form.prompt.trim()) return
    enhancing.value = true
    enhanceError.value = null
    try {
        const { data } = await axios.post(route('prompt.enhance'), {
            prompt: form.prompt,
            type: form.type,
        })
        if (data.enhanced) form.prompt = data.enhanced
    } catch {
        enhanceError.value = 'Could not enhance prompt. Try again.'
    } finally {
        enhancing.value = false
    }
}
</script>

<template>
    <Head title="Create Generation — DonDraper" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <Link :href="route('dashboard')" class="text-gray-400 hover:text-white transition-colors">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    </Link>
                    <h2 class="text-xl font-bold text-white">New Generation</h2>
                </div>
                <div class="flex items-center gap-2 text-sm">
                    <span class="text-gray-400">Credits:</span>
                    <span class="font-bold text-sky-400">{{ credits }}</span>
                </div>
            </div>
        </template>

        <div class="py-8 px-6 max-w-7xl mx-auto">
            <form @submit.prevent="submit">
                <div class="grid lg:grid-cols-5 gap-8">

                    <!-- Left: Steps -->
                    <div class="lg:col-span-3 space-y-3">

                        <!-- Type Selector -->
                        <div class="bg-[#0A0E1A] border border-white/6 rounded-2xl p-5">
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Generation Type</label>
                            <div class="grid grid-cols-2 gap-3">
                                <button type="button" @click="form.type = 'image'"
                                    :class="['flex items-center gap-3 p-4 rounded-xl border-2 transition-all', form.type === 'image' ? 'border-sky-500 bg-sky-500/10 text-white' : 'border-white/10 bg-white/3 text-gray-400 hover:border-white/20']">
                                    <span class="text-2xl">🎨</span>
                                    <div class="text-left">
                                        <div class="font-semibold text-sm">Image</div>
                                        <div class="text-xs opacity-60">1 credit</div>
                                    </div>
                                </button>
                                <button type="button" @click="form.type = 'video'"
                                    :class="['flex items-center gap-3 p-4 rounded-xl border-2 transition-all', form.type === 'video' ? 'border-cyan-500 bg-cyan-500/10 text-white' : 'border-white/10 bg-white/3 text-gray-400 hover:border-white/20']">
                                    <span class="text-2xl">🎬</span>
                                    <div class="text-left">
                                        <div class="font-semibold text-sm">Video</div>
                                        <div class="text-xs opacity-60">5 credits · 2–10 min</div>
                                    </div>
                                </button>
                            </div>
                        </div>

                        <!-- ── Accordion ──────────────────────────────────────── -->

                        <!-- Step 1: Prompt -->
                        <div class="bg-[#0A0E1A] border border-white/6 rounded-2xl overflow-hidden">
                            <button type="button" @click="toggle('prompt')"
                                class="w-full flex items-center justify-between px-5 py-4 hover:bg-white/3 transition-colors">
                                <div class="flex items-center gap-3">
                                    <span class="text-base">✏️</span>
                                    <span class="text-sm font-semibold text-white">Prompt</span>
                                    <span class="text-[10px] font-medium text-rose-400 bg-rose-500/10 border border-rose-500/20 px-1.5 py-0.5 rounded">Required</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span v-if="!isOpen('prompt') && form.prompt"
                                        class="text-xs text-sky-400 bg-sky-500/10 px-2 py-0.5 rounded-full">
                                        {{ characterCount }} chars
                                    </span>
                                    <svg :class="['w-4 h-4 text-gray-500 transition-transform duration-200', isOpen('prompt') ? 'rotate-180' : '']"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </div>
                            </button>

                            <div :class="['grid transition-all duration-200 ease-in-out', isOpen('prompt') ? 'grid-rows-[1fr]' : 'grid-rows-[0fr]']">
                                <div class="overflow-hidden">
                                    <div class="px-5 pb-5 pt-1 space-y-4">
                                        <div>
                                            <div class="flex items-center justify-between mb-2">
                                                <label class="text-xs font-medium text-gray-400">Your prompt</label>
                                                <span :class="['text-xs', characterCount > maxChars * 0.9 ? 'text-rose-400' : 'text-gray-600']">{{ characterCount }}/{{ maxChars }}</span>
                                            </div>
                                            <textarea
                                                v-model="form.prompt"
                                                rows="5"
                                                maxlength="2000"
                                                placeholder="Describe what you want to create in detail. The more specific, the better the result..."
                                                class="w-full bg-white/5 border border-white/10 focus:border-sky-500 focus:ring-2 focus:ring-sky-500/15 rounded-xl px-4 py-3 text-white placeholder-gray-600 outline-none transition-all resize-none text-sm"
                                                required
                                            ></textarea>
                                            <div v-if="form.errors.prompt" class="mt-2 text-sm text-rose-400">{{ form.errors.prompt }}</div>
                                        </div>

                                        <!-- Enhance -->
                                        <div class="flex items-center gap-3">
                                            <button type="button" @click="enhancePrompt" :disabled="enhancing || !form.prompt.trim()"
                                                class="flex items-center gap-2 text-xs bg-sky-500/10 hover:bg-sky-500/20 border border-sky-500/30 text-sky-400 px-3 py-1.5 rounded-lg transition-all disabled:opacity-40">
                                                <svg v-if="enhancing" class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                                </svg>
                                                <svg v-else class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                                {{ enhancing ? 'Enhancing...' : 'Enhance with AI' }}
                                            </button>
                                            <span v-if="enhanceError" class="text-xs text-rose-400">{{ enhanceError }}</span>
                                        </div>

                                        <!-- Suggestions -->
                                        <div>
                                            <div class="text-xs text-gray-500 mb-2">Suggestions:</div>
                                            <div class="flex flex-wrap gap-2">
                                                <button v-for="s in promptSuggestions" :key="s" type="button"
                                                    @click="useSuggestion(s)"
                                                    class="text-xs bg-white/5 hover:bg-white/10 border border-white/10 hover:border-white/20 rounded-lg px-3 py-1.5 text-gray-400 hover:text-white transition-all text-left">
                                                    {{ s.slice(0, 40) }}...
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Step 2: Negative Prompt -->
                        <div class="bg-[#0A0E1A] border border-white/6 rounded-2xl overflow-hidden">
                            <button type="button" @click="toggle('negative')"
                                class="w-full flex items-center justify-between px-5 py-4 hover:bg-white/3 transition-colors">
                                <div class="flex items-center gap-3">
                                    <span class="text-base">🚫</span>
                                    <span class="text-sm font-semibold text-white">Negative Prompt</span>
                                    <span class="text-[10px] font-medium text-gray-500 bg-white/5 border border-white/10 px-1.5 py-0.5 rounded">Optional</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span v-if="!isOpen('negative') && form.negative_prompt"
                                        class="w-2 h-2 rounded-full bg-emerald-400 flex-shrink-0">
                                    </span>
                                    <svg :class="['w-4 h-4 text-gray-500 transition-transform duration-200', isOpen('negative') ? 'rotate-180' : '']"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </div>
                            </button>

                            <div :class="['grid transition-all duration-200 ease-in-out', isOpen('negative') ? 'grid-rows-[1fr]' : 'grid-rows-[0fr]']">
                                <div class="overflow-hidden">
                                    <div class="px-5 pb-5 pt-1 space-y-2">
                                        <p class="text-xs text-gray-500">Describe what you want to exclude from the result.</p>
                                        <textarea
                                            v-model="form.negative_prompt"
                                            rows="2"
                                            placeholder="blurry, low quality, watermark, text, distorted..."
                                            class="w-full bg-white/5 border border-white/10 focus:border-rose-500/50 focus:ring-2 focus:ring-rose-500/10 rounded-xl px-4 py-3 text-white placeholder-gray-600 outline-none transition-all resize-none text-sm"
                                        ></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Step 3: Reference People -->
                        <div class="bg-[#0A0E1A] border border-white/6 rounded-2xl overflow-hidden">
                            <button type="button" @click="toggle('people')"
                                class="w-full flex items-center justify-between px-5 py-4 hover:bg-white/3 transition-colors">
                                <div class="flex items-center gap-3">
                                    <span class="text-base">🧑‍🤝‍🧑</span>
                                    <span class="text-sm font-semibold text-white">Reference People</span>
                                    <span class="text-[10px] font-medium text-gray-500 bg-white/5 border border-white/10 px-1.5 py-0.5 rounded">Optional</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span v-if="!isOpen('people') && personCount > 0"
                                        class="text-xs text-emerald-400 bg-emerald-500/10 px-2 py-0.5 rounded-full">
                                        {{ personCount }} {{ personCount === 1 ? 'person' : 'people' }}
                                    </span>
                                    <svg :class="['w-4 h-4 text-gray-500 transition-transform duration-200', isOpen('people') ? 'rotate-180' : '']"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </div>
                            </button>

                            <div :class="['grid transition-all duration-200 ease-in-out', isOpen('people') ? 'grid-rows-[1fr]' : 'grid-rows-[0fr]']">
                                <div class="overflow-hidden">
                                    <div class="px-5 pb-5 pt-1 space-y-5">
                                        <p class="text-xs text-gray-500">Upload photos of the people you want to appear. GPT-4o Vision analyzes their exact appearance and passes it to the selected model for accurate likeness.</p>

                                        <!-- Person 1 -->
                                        <div class="border border-white/5 rounded-xl p-4 space-y-3">
                                            <div class="flex items-center justify-between">
                                                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Person 1</span>
                                                <button v-if="person1Preview" type="button" @click="clearPerson(1)" class="text-xs text-rose-400 hover:text-rose-300 transition-colors">Remove</button>
                                            </div>
                                            <input v-model="form.person_1_name" type="text" placeholder="Name or label (e.g. John, Model A)" maxlength="100"
                                                class="w-full bg-white/5 border border-white/10 focus:border-sky-500 rounded-xl px-4 py-2 text-white placeholder-gray-600 outline-none transition-all text-sm" />

                                            <div v-if="!person1Preview"
                                                class="relative border-2 border-dashed border-white/10 hover:border-sky-500/40 rounded-xl transition-all cursor-pointer group"
                                                @click="$refs.person1Input.click()">
                                                <div class="flex items-center gap-3 px-4 py-4">
                                                    <div class="w-9 h-9 rounded-lg bg-white/5 group-hover:bg-sky-500/10 flex items-center justify-center flex-shrink-0 transition-all">
                                                        <svg class="w-5 h-5 text-gray-500 group-hover:text-sky-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                                    </div>
                                                    <div>
                                                        <p class="text-sm text-gray-500 group-hover:text-gray-400 transition-colors">Upload a clear photo of their face</p>
                                                        <p class="text-xs text-gray-600 mt-0.5">PNG, JPG, WEBP — max 5 MB</p>
                                                    </div>
                                                </div>
                                                <input ref="person1Input" type="file" accept="image/jpeg,image/png,image/jpg,image/webp" class="hidden" @change="onPersonImageChange($event, 1)" />
                                            </div>

                                            <div v-else class="relative rounded-xl overflow-hidden border border-white/10 group">
                                                <img :src="person1Preview" alt="Person 1" class="w-full h-36 object-cover" />
                                                <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                                    <button type="button" @click="$refs.person1Input.click()" class="bg-white/10 hover:bg-white/20 text-white text-xs px-3 py-1.5 rounded-lg transition-colors">Change photo</button>
                                                </div>
                                                <input ref="person1Input" type="file" accept="image/jpeg,image/png,image/jpg,image/webp" class="hidden" @change="onPersonImageChange($event, 1)" />
                                            </div>
                                            <div v-if="form.errors.person_1_image" class="text-xs text-rose-400">{{ form.errors.person_1_image }}</div>
                                        </div>

                                        <!-- Person 2 -->
                                        <div v-if="!showPerson2 && !person2Preview">
                                            <button type="button" @click="showPerson2 = true"
                                                class="flex items-center gap-2 text-sm text-gray-500 hover:text-sky-400 transition-colors">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                                Add a 2nd person
                                            </button>
                                        </div>

                                        <div v-else class="border border-white/5 rounded-xl p-4 space-y-3">
                                            <div class="flex items-center justify-between">
                                                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Person 2</span>
                                                <button type="button" @click="clearPerson(2)" class="text-xs text-rose-400 hover:text-rose-300 transition-colors">Remove</button>
                                            </div>
                                            <input v-model="form.person_2_name" type="text" placeholder="Name or label (e.g. Jane, Model B)" maxlength="100"
                                                class="w-full bg-white/5 border border-white/10 focus:border-sky-500 rounded-xl px-4 py-2 text-white placeholder-gray-600 outline-none transition-all text-sm" />

                                            <div v-if="!person2Preview"
                                                class="relative border-2 border-dashed border-white/10 hover:border-sky-500/40 rounded-xl transition-all cursor-pointer group"
                                                @click="$refs.person2Input.click()">
                                                <div class="flex items-center gap-3 px-4 py-4">
                                                    <div class="w-9 h-9 rounded-lg bg-white/5 group-hover:bg-sky-500/10 flex items-center justify-center flex-shrink-0 transition-all">
                                                        <svg class="w-5 h-5 text-gray-500 group-hover:text-sky-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                                    </div>
                                                    <div>
                                                        <p class="text-sm text-gray-500 group-hover:text-gray-400 transition-colors">Upload a clear photo of their face</p>
                                                        <p class="text-xs text-gray-600 mt-0.5">PNG, JPG, WEBP — max 5 MB</p>
                                                    </div>
                                                </div>
                                                <input ref="person2Input" type="file" accept="image/jpeg,image/png,image/jpg,image/webp" class="hidden" @change="onPersonImageChange($event, 2)" />
                                            </div>

                                            <div v-else class="relative rounded-xl overflow-hidden border border-white/10 group">
                                                <img :src="person2Preview" alt="Person 2" class="w-full h-36 object-cover" />
                                                <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                                    <button type="button" @click="$refs.person2Input.click()" class="bg-white/10 hover:bg-white/20 text-white text-xs px-3 py-1.5 rounded-lg transition-colors">Change photo</button>
                                                </div>
                                                <input ref="person2Input" type="file" accept="image/jpeg,image/png,image/jpg,image/webp" class="hidden" @change="onPersonImageChange($event, 2)" />
                                            </div>
                                            <div v-if="form.errors.person_2_image" class="text-xs text-rose-400">{{ form.errors.person_2_image }}</div>
                                        </div>

                                        <!-- Reference photos notice -->
                                        <div v-if="person1Preview || person2Preview" class="flex items-start gap-2 bg-emerald-500/5 border border-emerald-500/20 rounded-xl p-3">
                                            <svg class="w-4 h-4 text-emerald-400 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            <p class="text-xs text-emerald-300">Reference photos detected — will use <strong>{{ currentModels.find(m => m.id === form.model)?.label ?? form.model }}</strong> with the actual face photos for best accuracy.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Step 4: Product Reference -->
                        <div class="bg-[#0A0E1A] border border-white/6 rounded-2xl overflow-hidden">
                            <button type="button" @click="toggle('product')"
                                class="w-full flex items-center justify-between px-5 py-4 hover:bg-white/3 transition-colors">
                                <div class="flex items-center gap-3">
                                    <span class="text-base">📦</span>
                                    <span class="text-sm font-semibold text-white">Product Reference</span>
                                    <span class="text-[10px] font-medium text-gray-500 bg-white/5 border border-white/10 px-1.5 py-0.5 rounded">Optional</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span v-if="!isOpen('product') && (form.product_images.length > 0 || form.product_type)"
                                        class="text-xs text-sky-400 bg-sky-500/10 px-2 py-0.5 rounded-full">
                                        {{ form.product_images.length > 0 ? `${form.product_images.length} image${form.product_images.length > 1 ? 's' : ''}` : form.product_type }}
                                    </span>
                                    <svg :class="['w-4 h-4 text-gray-500 transition-transform duration-200', isOpen('product') ? 'rotate-180' : '']"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </div>
                            </button>

                            <div :class="['grid transition-all duration-200 ease-in-out', isOpen('product') ? 'grid-rows-[1fr]' : 'grid-rows-[0fr]']">
                                <div class="overflow-hidden">
                                    <div class="px-5 pb-5 pt-1 space-y-4">
                                        <p class="text-xs text-gray-500">Upload your product image and describe its type. GPT-4o will analyze the product and place it realistically inside the AI-generated scene.</p>

                                        <!-- Product Type -->
                                        <div>
                                            <label class="block text-xs font-medium text-gray-400 mb-1.5">Product Type</label>
                                            <input
                                                v-model="form.product_type"
                                                type="text"
                                                placeholder="e.g. Coffee mug, Running shoe, Perfume bottle..."
                                                maxlength="100"
                                                class="w-full bg-white/5 border border-white/10 focus:border-sky-500 focus:ring-2 focus:ring-sky-500/15 rounded-xl px-4 py-2.5 text-white placeholder-gray-600 outline-none transition-all text-sm"
                                            />
                                        </div>

                                        <!-- Product Images -->
                                        <div>
                                            <div class="flex items-center justify-between mb-2">
                                                <label class="text-xs font-medium text-gray-400">Product Images</label>
                                                <span class="text-xs text-gray-600">{{ form.product_images.length }}/{{ MAX_PRODUCT_IMAGES }} angles</span>
                                            </div>

                                            <div class="grid grid-cols-4 gap-2">
                                                <div v-for="(preview, i) in productPreviews" :key="i"
                                                    class="relative aspect-square rounded-xl overflow-hidden border border-white/10 group">
                                                    <img :src="preview" :alt="`Product angle ${i + 1}`" class="w-full h-full object-cover bg-gray-800" />
                                                    <div class="absolute bottom-1 left-1 text-[10px] font-semibold bg-black/60 text-gray-300 px-1.5 py-0.5 rounded">
                                                        {{ ['Front','Side','Back','Detail'][i] ?? `#${i+1}` }}
                                                    </div>
                                                    <button type="button" @click="removeProductImage(i)"
                                                        class="absolute top-1 right-1 w-5 h-5 bg-rose-500 hover:bg-rose-400 rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity shadow">
                                                        <svg class="w-3 h-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/>
                                                        </svg>
                                                    </button>
                                                </div>

                                                <div v-if="form.product_images.length < MAX_PRODUCT_IMAGES"
                                                    class="aspect-square rounded-xl border-2 border-dashed border-white/10 hover:border-sky-500/40 flex flex-col items-center justify-center gap-1 cursor-pointer group transition-all"
                                                    @click="$refs.productImageInput.click()">
                                                    <svg class="w-5 h-5 text-gray-600 group-hover:text-sky-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                                    </svg>
                                                    <span class="text-[10px] text-gray-600 group-hover:text-sky-400 transition-colors font-medium">
                                                        {{ form.product_images.length === 0 ? 'Add photo' : 'Add angle' }}
                                                    </span>
                                                </div>
                                            </div>

                                            <input ref="productImageInput" type="file"
                                                accept="image/jpeg,image/png,image/jpg,image/webp"
                                                class="hidden" @change="addProductImage" />

                                            <p class="text-xs text-gray-600 mt-2">Add up to 4 angles for maximum accuracy</p>
                                            <div v-if="form.errors['product_images.0']" class="mt-1 text-xs text-rose-400">{{ form.errors['product_images.0'] }}</div>
                                        </div>

                                        <!-- Notice -->
                                        <div v-if="form.product_images.length > 0 || form.product_type" class="flex items-start gap-2 bg-sky-500/5 border border-sky-500/20 rounded-xl p-3">
                                            <svg class="w-4 h-4 text-sky-400 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            <p class="text-xs text-sky-300">
                                                Each product image is analyzed by GPT-4o Vision.
                                                <span v-if="form.product_images.length > 1"> {{ form.product_images.length }} angles uploaded — higher accuracy.</span>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>


                    </div>

                    <!-- Right: Preview & Submit -->
                    <div class="lg:col-span-2 space-y-6">
                        <div class="bg-[#0A0E1A] border border-white/6 rounded-2xl p-6 sticky top-24">
                            <!-- Model Picker -->
                            <div class="mb-6">
                                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">AI Model</label>
                                <div class="space-y-2">
                                    <button v-for="m in currentModels" :key="m.id"
                                        type="button"
                                        @click="form.model = m.id"
                                        :class="['w-full flex items-center gap-3 px-4 py-3 rounded-xl border-2 transition-all text-left',
                                            form.model === m.id
                                                ? 'border-sky-500 bg-sky-500/10'
                                                : 'border-white/8 bg-white/3 hover:border-white/15']">
                                        <div :class="['w-4 h-4 rounded-full border-2 flex-shrink-0 flex items-center justify-center transition-all',
                                            form.model === m.id ? 'border-sky-500' : 'border-white/20']">
                                            <div v-if="form.model === m.id" class="w-2 h-2 rounded-full bg-sky-500"></div>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <span :class="['text-sm font-semibold', form.model === m.id ? 'text-white' : 'text-gray-300']">{{ m.label }}</span>
                                                <span class="text-xs text-gray-600">{{ m.provider }}</span>
                                                <span v-if="m.recommended" class="text-[10px] font-semibold text-sky-400 bg-sky-500/10 border border-sky-500/20 px-1.5 py-0.5 rounded">
                                                    Recommended
                                                </span>
                                            </div>
                                            <p class="text-xs text-gray-500 mt-0.5">{{ m.description }}</p>
                                        </div>
                                    </button>
                                </div>
                            </div>

                            <div v-if="credits === 0" class="bg-rose-500/10 border border-rose-500/30 rounded-xl p-3 mb-4 text-sm text-rose-400">
                                You have no credits left. <Link :href="route('home') + '#pricing'" class="underline">Upgrade your plan</Link> to continue.
                            </div>

                            <div v-if="form.errors.credits" class="bg-rose-500/10 border border-rose-500/30 rounded-xl p-3 mb-4 text-sm text-rose-400">
                                {{ form.errors.credits }}
                            </div>

                            <div v-if="form.type === 'video'" class="bg-cyan-500/5 border border-cyan-500/20 rounded-xl p-3 mb-3 text-xs text-cyan-300 flex items-start gap-2">
                                <svg class="w-4 h-4 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Video generation takes 2–10 minutes. You'll see a live status update on the result page.
                            </div>

                            <button
                                type="submit"
                                :disabled="form.processing || !form.prompt || credits < creditCost"
                                class="w-full flex items-center justify-center gap-2 bg-gradient-to-r from-sky-500 to-cyan-600 hover:from-sky-400 hover:to-cyan-500 disabled:opacity-40 disabled:cursor-not-allowed text-white font-bold py-4 rounded-xl transition-all shadow-lg shadow-sky-500/25 hover:shadow-sky-500/40 hover:scale-[1.02]"
                            >
                                <svg v-if="form.processing" class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                                <svg v-else-if="form.type === 'video'" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.069A1 1 0 0121 8.87v6.26a1 1 0 01-1.447.894L15 14M3 8a2 2 0 012-2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V8z"/></svg>
                                <svg v-else class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3l14 9-14 9V3z"/></svg>
                                {{ form.processing ? 'Submitting...' : `Generate (${creditCost} credits · ${estimatedTime})` }}
                            </button>

                            <p class="text-center text-xs text-gray-600 mt-3">
                                You have <span class="text-sky-400 font-medium">{{ credits }}</span> credits remaining
                            </p>
                        </div>
                    </div>

                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
