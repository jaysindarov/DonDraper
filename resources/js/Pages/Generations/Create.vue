<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import { ref, computed, watch } from 'vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
    attributes: Object,
    credits: Number,
})

const form = useForm({
    type: 'image',
    prompt: '',
    negative_prompt: '',
    attributes: {},
})

// Initialize defaults from attributes
const initDefaults = () => {
    Object.values(props.attributes).flat().forEach(attr => {
        if (attr.default_value !== null && attr.default_value !== undefined) {
            form.attributes[attr.key] = attr.default_value
        }
    })
}
initDefaults()

const activeTab = ref('basic')
const tabs = [
    { key: 'basic', label: 'Basic', icon: '🎯' },
    { key: 'style', label: 'Style', icon: '🎨' },
    { key: 'quality', label: 'Quality', icon: '💎' },
    { key: 'advanced', label: 'Advanced', icon: '⚙️' },
]

const currentTabAttributes = computed(() => props.attributes[activeTab.value] || [])

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
    form.post(route('generations.store'))
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
                                <button type="button" @click="form.type = 'video'"
                                    :class="['flex items-center gap-3 p-4 rounded-xl border-2 transition-all', form.type === 'video' ? 'border-fuchsia-500 bg-fuchsia-500/10 text-white' : 'border-white/10 bg-white/3 text-gray-400 hover:border-white/20']">
                                    <span class="text-2xl">🎬</span>
                                    <div class="text-left">
                                        <div class="font-semibold text-sm">Video</div>
                                        <div class="text-xs opacity-60">5 credits</div>
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

                            <button
                                type="submit"
                                :disabled="form.processing || !form.prompt || credits === 0"
                                class="w-full flex items-center justify-center gap-2 bg-gradient-to-r from-violet-500 to-fuchsia-600 hover:from-violet-400 hover:to-fuchsia-500 disabled:opacity-40 disabled:cursor-not-allowed text-white font-bold py-4 rounded-xl transition-all shadow-lg shadow-violet-500/25 hover:shadow-violet-500/40 hover:scale-[1.02]"
                            >
                                <svg v-if="form.processing" class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                                <svg v-else class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3l14 9-14 9V3z"/></svg>
                                {{ form.processing ? 'Generating...' : 'Generate (1 credit)' }}
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
