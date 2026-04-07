<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import { ref, computed, watch } from 'vue'
import axios from 'axios'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
    imageAttributes: Object,
    videoAttributes: Object,
    credits: Number,
})

const form = useForm({
    type: 'image',
    prompt: '',
    negative_prompt: '',
    product_type: '',
    product_image: null,
    person_1_name: '',
    person_1_image: null,
    person_2_name: '',
    person_2_image: null,
    attributes: {},
})

const productImagePreview = ref(null)

const onProductImageChange = (e) => {
    const file = e.target.files[0]
    if (!file) return
    form.product_image = file
    const reader = new FileReader()
    reader.onload = (ev) => { productImagePreview.value = ev.target.result }
    reader.readAsDataURL(file)
}

const clearProductImage = () => {
    form.product_image = null
    productImagePreview.value = null
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

// Initialize defaults from both attribute sets
const initDefaults = () => {
    const allAttrs = [
        ...Object.values(props.imageAttributes).flat(),
        ...Object.values(props.videoAttributes).flat(),
    ]
    allAttrs.forEach(attr => {
        if (attr.default_value !== null && attr.default_value !== undefined) {
            form.attributes[attr.key] = attr.default_value
        }
    })
}
initDefaults()

const activeTab = ref('basic')
const imageTabs = [
    { key: 'basic',    label: 'Basic',    icon: '🎯' },
    { key: 'style',    label: 'Style',    icon: '🎨' },
    { key: 'quality',  label: 'Quality',  icon: '💎' },
    { key: 'advanced', label: 'Advanced', icon: '⚙️' },
]
const videoTabs = [
    { key: 'basic',   label: 'Basic',  icon: '🎯' },
    { key: 'style',   label: 'Style',  icon: '🎨' },
    { key: 'quality', label: 'Quality', icon: '💎' },
]

const tabs = computed(() => form.type === 'video' ? videoTabs : imageTabs)

const currentTabAttributes = computed(() => {
    const source = form.type === 'video' ? props.videoAttributes : props.imageAttributes
    return source[activeTab.value] || []
})

const creditCost = computed(() => form.type === 'video' ? 5 : 1)
const estimatedTime = computed(() => form.type === 'video' ? '2–10 min' : '~30 sec')

const promptSuggestions = [
    'A majestic dragon soaring over a neon-lit cyberpunk city at night',
    'Portrait of a wise elderly wizard in a mystical forest, detailed oil painting',
    'Serene Japanese zen garden at golden hour, ultra photorealistic',
    'Abstract fluid art in deep ocean blues and luminescent teals',
    'Futuristic space station orbiting a ringed gas giant, cinematic',
    'A cozy coffee shop on a rainy day, warm impressionist style',
]

const useSuggestion = (s) => { form.prompt = s }

const characterCount = computed(() => form.prompt.length)
const maxChars = 2000

const submit = () => {
    form.post(route('generations.store'), {
        forceFormData: true,
    })
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
                    <span class="font-bold text-violet-400">{{ credits }}</span>
                </div>
            </div>
        </template>

        <div class="py-8 px-6 max-w-7xl mx-auto">
            <form @submit.prevent="submit">
                <div class="grid lg:grid-cols-5 gap-8">

                    <!-- Left: Prompt & Settings -->
                    <div class="lg:col-span-3 space-y-6">

                        <!-- Type Selector -->
                        <div class="bg-gray-900/50 border border-white/5 rounded-2xl p-6">
                            <label class="block text-sm font-semibold text-gray-300 mb-3">Generation Type</label>
                            <div class="grid grid-cols-2 gap-3">
                                <button type="button" @click="form.type = 'image'"
                                    :class="['flex items-center gap-3 p-4 rounded-xl border-2 transition-all', form.type === 'image' ? 'border-violet-500 bg-violet-500/10 text-white' : 'border-white/10 bg-white/3 text-gray-400 hover:border-white/20']">
                                    <span class="text-2xl">🎨</span>
                                    <div class="text-left">
                                        <div class="font-semibold text-sm">Image</div>
                                        <div class="text-xs opacity-60">1 credit</div>
                                    </div>
                                </button>
                                <button type="button" @click="form.type = 'video'; activeTab = 'basic'"
                                    :class="['flex items-center gap-3 p-4 rounded-xl border-2 transition-all', form.type === 'video' ? 'border-fuchsia-500 bg-fuchsia-500/10 text-white' : 'border-white/10 bg-white/3 text-gray-400 hover:border-white/20']">
                                    <span class="text-2xl">🎬</span>
                                    <div class="text-left">
                                        <div class="font-semibold text-sm">Video</div>
                                        <div class="text-xs opacity-60">5 credits · 2–10 min</div>
                                    </div>
                                </button>
                            </div>
                        </div>

                        <!-- Prompt -->
                        <div class="bg-gray-900/50 border border-white/5 rounded-2xl p-6">
                            <div class="flex items-center justify-between mb-3">
                                <label class="text-sm font-semibold text-gray-300">Prompt</label>
                                <span :class="['text-xs', characterCount > maxChars * 0.9 ? 'text-rose-400' : 'text-gray-500']">{{ characterCount }}/{{ maxChars }}</span>
                            </div>
                            <textarea
                                v-model="form.prompt"
                                rows="5"
                                maxlength="2000"
                                placeholder="Describe what you want to create in detail. The more specific, the better the result..."
                                class="w-full bg-white/5 border border-white/10 focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 rounded-xl px-4 py-3 text-white placeholder-gray-600 outline-none transition-all resize-none text-sm"
                                required
                            ></textarea>
                            <div v-if="form.errors.prompt" class="mt-2 text-sm text-rose-400">{{ form.errors.prompt }}</div>

                            <!-- Enhance button -->
                            <div class="mt-3 flex items-center gap-3">
                                <button type="button" @click="enhancePrompt" :disabled="enhancing || !form.prompt.trim()"
                                    class="flex items-center gap-2 text-xs bg-violet-500/10 hover:bg-violet-500/20 border border-violet-500/30 text-violet-400 px-3 py-1.5 rounded-lg transition-all disabled:opacity-40">
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
                            <div class="mt-4">
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

                        <!-- Negative Prompt -->
                        <div class="bg-gray-900/50 border border-white/5 rounded-2xl p-6">
                            <label class="block text-sm font-semibold text-gray-300 mb-1">Negative Prompt <span class="text-gray-600 font-normal">(optional)</span></label>
                            <p class="text-xs text-gray-500 mb-3">Describe what you want to exclude from the image</p>
                            <textarea
                                v-model="form.negative_prompt"
                                rows="2"
                                placeholder="blurry, low quality, watermark, text, distorted..."
                                class="w-full bg-white/5 border border-white/10 focus:border-rose-500/50 focus:ring-2 focus:ring-rose-500/10 rounded-xl px-4 py-3 text-white placeholder-gray-600 outline-none transition-all resize-none text-sm"
                            ></textarea>
                        </div>

                        <!-- Reference People (optional) -->
                        <div class="bg-gray-900/50 border border-white/5 rounded-2xl p-6">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="text-lg">🧑‍🤝‍🧑</span>
                                <label class="text-sm font-semibold text-gray-300">Reference People <span class="text-gray-600 font-normal">(optional)</span></label>
                            </div>
                            <p class="text-xs text-gray-500 mb-5">Upload photos of the people you want to appear. GPT-4o Vision analyzes their exact appearance, then <strong class="text-gray-400">gpt-image-1</strong> uses the actual reference photos for accurate likeness — far better than DALL-E.</p>

                            <div class="space-y-5">
                                <!-- Person 1 -->
                                <div class="border border-white/5 rounded-xl p-4 space-y-3">
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Person 1</span>
                                        <button v-if="person1Preview" type="button" @click="clearPerson(1)" class="text-xs text-rose-400 hover:text-rose-300 transition-colors">Remove</button>
                                    </div>
                                    <input v-model="form.person_1_name" type="text" placeholder="Name or label (e.g. John, Model A)" maxlength="100"
                                        class="w-full bg-white/5 border border-white/10 focus:border-violet-500 rounded-xl px-4 py-2 text-white placeholder-gray-600 outline-none transition-all text-sm" />

                                    <div v-if="!person1Preview"
                                        class="relative border-2 border-dashed border-white/10 hover:border-violet-500/40 rounded-xl transition-all cursor-pointer group"
                                        @click="$refs.person1Input.click()">
                                        <div class="flex items-center gap-3 px-4 py-4">
                                            <div class="w-9 h-9 rounded-lg bg-white/5 group-hover:bg-violet-500/10 flex items-center justify-center flex-shrink-0 transition-all">
                                                <svg class="w-5 h-5 text-gray-500 group-hover:text-violet-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
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

                                <!-- Person 2 (expandable) -->
                                <div v-if="!showPerson2 && !person2Preview">
                                    <button type="button" @click="showPerson2 = true"
                                        class="flex items-center gap-2 text-sm text-gray-500 hover:text-violet-400 transition-colors">
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
                                        class="w-full bg-white/5 border border-white/10 focus:border-violet-500 rounded-xl px-4 py-2 text-white placeholder-gray-600 outline-none transition-all text-sm" />

                                    <div v-if="!person2Preview"
                                        class="relative border-2 border-dashed border-white/10 hover:border-violet-500/40 rounded-xl transition-all cursor-pointer group"
                                        @click="$refs.person2Input.click()">
                                        <div class="flex items-center gap-3 px-4 py-4">
                                            <div class="w-9 h-9 rounded-lg bg-white/5 group-hover:bg-violet-500/10 flex items-center justify-center flex-shrink-0 transition-all">
                                                <svg class="w-5 h-5 text-gray-500 group-hover:text-violet-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
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

                                <!-- Auto-switch to gpt-image-1 notice -->
                                <div v-if="person1Preview || person2Preview" class="flex items-start gap-2 bg-emerald-500/5 border border-emerald-500/20 rounded-xl p-3">
                                    <svg class="w-4 h-4 text-emerald-400 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <p class="text-xs text-emerald-300">Reference photos detected — will automatically use <strong>gpt-image-1</strong> with the actual face photos for best accuracy.</p>
                                </div>
                            </div>
                        </div>

                        <!-- Product Reference (optional) -->
                        <div class="bg-gray-900/50 border border-white/5 rounded-2xl p-6">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="text-lg">📦</span>
                                <label class="text-sm font-semibold text-gray-300">Product Reference <span class="text-gray-600 font-normal">(optional)</span></label>
                            </div>
                            <p class="text-xs text-gray-500 mb-5">Upload your product image and describe its type. GPT-4o will analyze the product and place it realistically inside the AI-generated scene.</p>

                            <div class="space-y-4">
                                <!-- Product Type -->
                                <div>
                                    <label class="block text-xs font-medium text-gray-400 mb-1.5">Product Type</label>
                                    <input
                                        v-model="form.product_type"
                                        type="text"
                                        placeholder="e.g. Coffee mug, Running shoe, Perfume bottle..."
                                        maxlength="100"
                                        class="w-full bg-white/5 border border-white/10 focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 rounded-xl px-4 py-2.5 text-white placeholder-gray-600 outline-none transition-all text-sm"
                                    />
                                </div>

                                <!-- Product Image Upload -->
                                <div>
                                    <label class="block text-xs font-medium text-gray-400 mb-1.5">Product Image</label>
                                    <div v-if="!productImagePreview"
                                        class="relative border-2 border-dashed border-white/10 hover:border-violet-500/40 rounded-xl transition-all cursor-pointer group"
                                        @click="$refs.productImageInput.click()">
                                        <div class="flex flex-col items-center justify-center py-8 px-4 text-center">
                                            <div class="w-10 h-10 rounded-xl bg-white/5 group-hover:bg-violet-500/10 flex items-center justify-center mb-3 transition-all">
                                                <svg class="w-5 h-5 text-gray-500 group-hover:text-violet-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                            </div>
                                            <p class="text-sm text-gray-500 group-hover:text-gray-400 transition-colors">Click to upload product photo</p>
                                            <p class="text-xs text-gray-600 mt-1">PNG, JPG, WEBP — max 5 MB</p>
                                        </div>
                                        <input ref="productImageInput" type="file" accept="image/jpeg,image/png,image/jpg,image/webp" class="hidden" @change="onProductImageChange" />
                                    </div>

                                    <!-- Preview -->
                                    <div v-else class="relative rounded-xl overflow-hidden border border-white/10 group">
                                        <img :src="productImagePreview" alt="Product preview" class="w-full h-40 object-contain bg-gray-800" />
                                        <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                            <button type="button" @click="clearProductImage" class="bg-rose-500 hover:bg-rose-600 text-white text-xs font-semibold px-4 py-2 rounded-lg transition-colors flex items-center gap-1.5">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                Remove
                                            </button>
                                        </div>
                                    </div>

                                    <div v-if="form.errors.product_image" class="mt-1.5 text-xs text-rose-400">{{ form.errors.product_image }}</div>
                                </div>

                                <!-- Cost notice -->
                                <div v-if="form.product_image || form.product_type" class="flex items-start gap-2 bg-violet-500/5 border border-violet-500/20 rounded-xl p-3">
                                    <svg class="w-4 h-4 text-violet-400 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <p class="text-xs text-violet-300">Product analysis uses GPT-4o Vision. This adds ~$0.01–0.02 per generation for more realistic results.</p>
                                </div>
                            </div>
                        </div>

                        <!-- Attributes Tabs -->
                        <div class="bg-gray-900/50 border border-white/5 rounded-2xl overflow-hidden">
                            <!-- Tab Headers -->
                            <div class="flex border-b border-white/5">
                                <button v-for="tab in tabs" :key="tab.key" type="button"
                                    @click="activeTab = tab.key"
                                    :class="['flex items-center gap-2 flex-1 py-3 px-4 text-sm font-medium transition-all', activeTab === tab.key ? 'text-white border-b-2 border-violet-500 bg-violet-500/5' : 'text-gray-500 hover:text-gray-300']">
                                    <span>{{ tab.icon }}</span>
                                    <span class="hidden sm:inline">{{ tab.label }}</span>
                                </button>
                            </div>

                            <!-- Tab Content -->
                            <div class="p-6 space-y-5">
                                <div v-for="attr in currentTabAttributes" :key="attr.key">
                                    <div class="flex items-center justify-between mb-2">
                                        <label :for="attr.key" class="text-sm font-medium text-gray-300">{{ attr.label }}</label>
                                        <span v-if="attr.type === 'range'" class="text-xs text-violet-400 font-mono bg-violet-500/10 px-2 py-0.5 rounded">
                                            {{ form.attributes[attr.key] || attr.default_value }}
                                        </span>
                                    </div>
                                    <p v-if="attr.description" class="text-xs text-gray-600 mb-2">{{ attr.description }}</p>

                                    <!-- Select -->
                                    <select v-if="attr.type === 'select'" :id="attr.key" v-model="form.attributes[attr.key]"
                                        class="w-full bg-white/5 border border-white/10 focus:border-violet-500 rounded-xl px-4 py-2.5 text-white text-sm outline-none transition-all appearance-none cursor-pointer">
                                        <option v-for="(label, value) in attr.options" :key="value" :value="value" class="bg-gray-800">{{ label }}</option>
                                    </select>

                                    <!-- Range -->
                                    <div v-else-if="attr.type === 'range'" class="space-y-1">
                                        <input type="range" :id="attr.key"
                                            v-model="form.attributes[attr.key]"
                                            :min="attr.min" :max="attr.max" :step="attr.step"
                                            class="w-full h-2 bg-white/10 rounded-full appearance-none cursor-pointer accent-violet-500" />
                                        <div class="flex justify-between text-xs text-gray-600">
                                            <span>{{ attr.min }}</span><span>{{ attr.max }}</span>
                                        </div>
                                    </div>

                                    <!-- Text -->
                                    <input v-else-if="attr.type === 'text'" type="text" :id="attr.key"
                                        v-model="form.attributes[attr.key]"
                                        :placeholder="attr.description || attr.label"
                                        class="w-full bg-white/5 border border-white/10 focus:border-violet-500 rounded-xl px-4 py-2.5 text-white text-sm outline-none transition-all" />

                                    <!-- Toggle -->
                                    <button v-else-if="attr.type === 'toggle'" type="button"
                                        @click="form.attributes[attr.key] = !form.attributes[attr.key]"
                                        :class="['relative w-12 h-6 rounded-full transition-all', form.attributes[attr.key] ? 'bg-violet-500' : 'bg-white/10']">
                                        <span :class="['absolute top-1 w-4 h-4 rounded-full bg-white transition-all', form.attributes[attr.key] ? 'left-7' : 'left-1']"></span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Preview & Submit -->
                    <div class="lg:col-span-2 space-y-6">

                        <!-- Preview Panel -->
                        <div class="bg-gray-900/50 border border-white/5 rounded-2xl p-6 sticky top-24">
                            <h3 class="text-sm font-semibold text-gray-300 mb-4">Generation Preview</h3>

                            <!-- Placeholder preview box -->
                            <div class="aspect-square rounded-2xl bg-gradient-to-br from-gray-800 to-gray-900 border border-white/5 flex flex-col items-center justify-center mb-6 relative overflow-hidden">
                                <div class="absolute inset-0 bg-[linear-gradient(rgba(139,92,246,0.03)_1px,transparent_1px),linear-gradient(90deg,rgba(139,92,246,0.03)_1px,transparent_1px)] bg-[size:20px_20px]"></div>
                                <div class="relative text-center px-6">
                                    <div class="text-5xl mb-3 opacity-30">🎨</div>
                                    <p class="text-xs text-gray-600">
                                        {{ form.prompt ? form.prompt.slice(0, 80) + (form.prompt.length > 80 ? '...' : '') : 'Your creation will appear here' }}
                                    </p>
                                </div>
                            </div>

                            <!-- Settings Summary -->
                            <div class="space-y-2 mb-6">
                                <div class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-3">Settings</div>
                                <div v-for="(val, key) in form.attributes" :key="key" v-show="val && val !== ''"
                                    class="flex items-center justify-between text-xs">
                                    <span class="text-gray-500 capitalize">{{ key.replace(/_/g, ' ') }}</span>
                                    <span class="text-gray-300 font-medium">{{ val }}</span>
                                </div>
                            </div>

                            <!-- Credits Warning -->
                            <div v-if="credits === 0" class="bg-rose-500/10 border border-rose-500/30 rounded-xl p-3 mb-4 text-sm text-rose-400">
                                You have no credits left. <Link :href="route('home') + '#pricing'" class="underline">Upgrade your plan</Link> to continue.
                            </div>

                            <div v-if="form.errors.credits" class="bg-rose-500/10 border border-rose-500/30 rounded-xl p-3 mb-4 text-sm text-rose-400">
                                {{ form.errors.credits }}
                            </div>

                            <!-- Video time notice -->
                            <div v-if="form.type === 'video'" class="bg-fuchsia-500/5 border border-fuchsia-500/20 rounded-xl p-3 mb-3 text-xs text-fuchsia-300 flex items-start gap-2">
                                <svg class="w-4 h-4 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Video generation takes 2–10 minutes. You'll see a live status update on the result page.
                            </div>

                            <button
                                type="submit"
                                :disabled="form.processing || !form.prompt || credits < creditCost"
                                class="w-full flex items-center justify-center gap-2 bg-gradient-to-r from-violet-500 to-fuchsia-600 hover:from-violet-400 hover:to-fuchsia-500 disabled:opacity-40 disabled:cursor-not-allowed text-white font-bold py-4 rounded-xl transition-all shadow-lg shadow-violet-500/25 hover:shadow-violet-500/40 hover:scale-[1.02]"
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
                                You have <span class="text-violet-400 font-medium">{{ credits }}</span> credits remaining
                            </p>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
