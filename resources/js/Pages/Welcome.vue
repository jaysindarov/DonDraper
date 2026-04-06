<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { ref, onMounted } from 'vue'

const props = defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
})

const features = [
    { icon: '🎨', title: 'AI Image Generation', description: 'Create stunning visuals with DALL-E 3, Stable Diffusion, and Flux. Full control over style, lighting, mood, and more.' },
    { icon: '🎬', title: 'Video Generation', description: 'Bring your ideas to life with AI-powered video creation. From concept to cinematic masterpiece in seconds.' },
    { icon: '⚙️', title: 'Full Attribute Control', description: 'Fine-tune every detail: art style, color palette, camera angle, diffusion steps, CFG scale, and seeds.' },
    { icon: '⚡', title: 'Blazing Fast', description: 'Queue-based async generation means you never wait. Real-time status updates keep you in the loop.' },
    { icon: '🗂️', title: 'Personal Gallery', description: 'All your generations saved and organized. Filter, search, and revisit your creative history anytime.' },
    { icon: '🔒', title: 'Secure & Private', description: 'Your creations are yours alone. Enterprise-grade security with Laravel Sanctum authentication.' },
]

const testimonials = [
    { name: 'Alex Morgan', role: 'Creative Director', avatar: 'AM', text: 'DonDraper transformed our campaign workflow. We generate hundreds of concept images in the time it used to take one.', gradient: 'from-violet-500 to-purple-600' },
    { name: 'Sofia Chen', role: 'Indie Game Dev', avatar: 'SC', text: 'The level of control over image attributes is unmatched. I can produce exactly the aesthetic I need every time.', gradient: 'from-cyan-500 to-blue-600' },
    { name: 'Marcus Webb', role: 'Brand Strategist', avatar: 'MW', text: 'From prompt to polished visual in under 30 seconds. Our social content calendar has never looked this good.', gradient: 'from-rose-500 to-pink-600' },
]

const stats = [
    { value: '2M+', label: 'Images Created' },
    { value: '150K+', label: 'Happy Creators' },
    { value: '99.9%', label: 'Uptime SLA' },
    { value: '<30s', label: 'Avg Generation Time' },
]

const plans = [
    { name: 'Starter', price: 'Free', period: '', credits: '10 credits/mo', features: ['10 image generations', 'Standard quality', 'Basic styles', 'Community gallery'], cta: 'Get Started Free', highlighted: false },
    { name: 'Pro', price: '$19', period: '/month', credits: '500 credits/mo', features: ['500 image generations', 'HD & Ultra HD quality', 'All styles & models', 'Priority queue', 'Private gallery', 'API access'], cta: 'Start Pro Trial', highlighted: true },
    { name: 'Enterprise', price: '$79', period: '/month', credits: 'Unlimited', features: ['Unlimited generations', 'All Pro features', 'Custom models', 'Dedicated support', 'Team workspace', 'SLA guarantee'], cta: 'Contact Sales', highlighted: false },
]

const creditExamples = [
    { credits: 10, images: 10, label: 'Free Starter', badge: 'Free', color: 'from-gray-500 to-gray-600' },
    { credits: 20, images: 20, label: 'Top-up Pack', badge: 'Flexible', color: 'from-blue-500 to-cyan-500' },
    { credits: 100, images: 100, label: 'Power User', badge: 'Popular', color: 'from-violet-500 to-fuchsia-500' },
    { credits: 500, images: 500, label: 'Pro Monthly', badge: 'Pro', color: 'from-emerald-500 to-teal-500' },
]

const isScrolled = ref(false)
const activeStyle = ref(0)
const styleShowcase = [
    { name: 'Photorealistic', color: 'from-amber-400 to-orange-500', emoji: '📸' },
    { name: 'Oil Painting', color: 'from-red-400 to-rose-600', emoji: '🖼️' },
    { name: 'Cyberpunk', color: 'from-cyan-400 to-blue-600', emoji: '🤖' },
    { name: 'Watercolor', color: 'from-teal-400 to-emerald-500', emoji: '🎨' },
    { name: 'Anime', color: 'from-pink-400 to-purple-600', emoji: '✨' },
    { name: 'Fantasy', color: 'from-violet-400 to-indigo-600', emoji: '🔮' },
]

const steps = [
    { num: '01', title: 'Describe Your Vision', desc: 'Write a prompt or use our AI prompt enhancer. Add negative prompts to exclude unwanted elements.' },
    { num: '02', title: 'Tune the Parameters', desc: 'Select your art style, lighting, mood, resolution, guidance scale, and more — all in an intuitive panel.' },
    { num: '03', title: 'Generate & Download', desc: 'Hit generate and watch your vision come to life. Save to your gallery and download in full resolution.' },
]

onMounted(() => {
    window.addEventListener('scroll', () => { isScrolled.value = window.scrollY > 20 })
    setInterval(() => { activeStyle.value = (activeStyle.value + 1) % styleShowcase.length }, 2000)
})
</script>

<template>
    <Head title="DonDraper — AI Image & Video Generation" />
    <div class="min-h-screen bg-gray-950 text-white overflow-x-hidden">

        <!-- Navigation -->
        <nav :class="['fixed top-0 w-full z-50 transition-all duration-300', isScrolled ? 'bg-gray-950/95 backdrop-blur-xl border-b border-white/10 shadow-2xl' : 'bg-transparent']">
            <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-violet-500 to-fuchsia-600 flex items-center justify-center font-black text-lg shadow-lg shadow-violet-500/30">D</div>
                    <span class="text-xl font-bold tracking-tight">Don<span class="text-transparent bg-clip-text bg-gradient-to-r from-violet-400 to-fuchsia-400">Draper</span></span>
                </div>
                <div class="hidden md:flex items-center gap-8 text-sm font-medium text-gray-400">
                    <a href="#features" class="hover:text-white transition-colors">Features</a>
                    <a href="#showcase" class="hover:text-white transition-colors">Showcase</a>
                    <a href="#pricing" class="hover:text-white transition-colors">Pricing</a>
                    <a href="#testimonials" class="hover:text-white transition-colors">Reviews</a>
                </div>
                <div class="flex items-center gap-3">
                    <template v-if="canLogin">
                        <Link :href="route('login')" class="text-sm text-gray-400 hover:text-white transition-colors px-4 py-2">Sign In</Link>
                        <Link v-if="canRegister" :href="route('register')" class="text-sm font-semibold bg-gradient-to-r from-violet-500 to-fuchsia-600 hover:from-violet-600 hover:to-fuchsia-700 px-5 py-2.5 rounded-xl transition-all shadow-lg shadow-violet-500/25 hover:shadow-violet-500/40 hover:scale-105">
                            Start Free
                        </Link>
                    </template>
                </div>
            </div>
        </nav>

        <!-- Hero -->
        <section class="relative min-h-screen flex items-center justify-center pt-20 overflow-hidden">
            <div class="absolute inset-0 overflow-hidden">
                <div class="absolute -top-1/2 -left-1/4 w-[900px] h-[900px] rounded-full bg-violet-600/10 blur-[120px] animate-pulse"></div>
                <div class="absolute -bottom-1/4 -right-1/4 w-[800px] h-[800px] rounded-full bg-fuchsia-600/10 blur-[120px] animate-pulse" style="animation-delay:1s"></div>
                <div class="absolute top-1/4 right-1/3 w-[500px] h-[500px] rounded-full bg-cyan-600/8 blur-[100px] animate-pulse" style="animation-delay:2s"></div>
                <div class="absolute inset-0 bg-[linear-gradient(rgba(255,255,255,0.02)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,0.02)_1px,transparent_1px)] bg-[size:60px_60px]"></div>
            </div>
            <div class="relative max-w-7xl mx-auto px-6 text-center">
                <div class="inline-flex items-center gap-2 bg-violet-500/10 border border-violet-500/30 rounded-full px-4 py-2 text-sm text-violet-300 mb-8 backdrop-blur-sm">
                    <span class="w-2 h-2 bg-violet-400 rounded-full animate-pulse"></span>
                    Powered by DALL-E 3, Stable Diffusion &amp; Flux
                </div>
                <h1 class="text-6xl md:text-8xl font-black tracking-tight leading-none mb-6">
                    <span class="block text-white">Create Anything.</span>
                    <span class="block text-transparent bg-clip-text bg-gradient-to-r from-violet-400 via-fuchsia-400 to-cyan-400 mt-2">Imagine Everything.</span>
                </h1>
                <p class="text-xl md:text-2xl text-gray-400 max-w-3xl mx-auto mb-10 leading-relaxed">
                    The most powerful AI creative platform. Generate photorealistic images, stunning artwork, and cinematic videos with full control over every parameter.
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center mb-16">
                    <Link v-if="canRegister" :href="route('register')" class="group flex items-center justify-center gap-3 bg-gradient-to-r from-violet-500 to-fuchsia-600 hover:from-violet-400 hover:to-fuchsia-500 text-white font-bold text-lg px-8 py-4 rounded-2xl transition-all shadow-xl shadow-violet-500/30 hover:shadow-violet-500/50 hover:scale-105">
                        <span>Start Creating Free</span>
                        <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                    </Link>
                    <a href="#features" class="flex items-center justify-center gap-2 bg-white/5 hover:bg-white/10 border border-white/10 hover:border-white/20 text-white font-semibold text-lg px-8 py-4 rounded-2xl transition-all">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        Explore Features
                    </a>
                </div>
                <div class="flex flex-wrap justify-center gap-3 mb-16" id="showcase">
                    <div v-for="(style, i) in styleShowcase" :key="i"
                        :class="['flex items-center gap-2 px-4 py-2 rounded-xl border transition-all duration-500 cursor-default', i === activeStyle ? 'bg-gradient-to-r ' + style.color + ' border-transparent shadow-lg scale-110' : 'bg-white/5 border-white/10 hover:border-white/20']">
                        <span>{{ style.emoji }}</span>
                        <span class="text-sm font-medium">{{ style.name }}</span>
                    </div>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-6 max-w-3xl mx-auto">
                    <div v-for="stat in stats" :key="stat.label" class="bg-white/5 border border-white/10 rounded-2xl p-4 backdrop-blur-sm">
                        <div class="text-2xl md:text-3xl font-black text-transparent bg-clip-text bg-gradient-to-r from-violet-400 to-fuchsia-400">{{ stat.value }}</div>
                        <div class="text-xs text-gray-500 mt-1">{{ stat.label }}</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features -->
        <section id="features" class="relative py-32 px-6">
            <div class="max-w-7xl mx-auto">
                <div class="text-center mb-20">
                    <h2 class="text-5xl font-black mb-4"><span class="text-transparent bg-clip-text bg-gradient-to-r from-violet-400 to-fuchsia-400">Everything</span> You Need</h2>
                    <p class="text-xl text-gray-400 max-w-2xl mx-auto">A complete creative platform built for professionals, artists, and dreamers.</p>
                </div>
                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div v-for="feature in features" :key="feature.title" class="group relative bg-gray-900/50 border border-white/5 hover:border-violet-500/30 rounded-3xl p-8 transition-all hover:-translate-y-1 hover:shadow-2xl hover:shadow-violet-500/10 backdrop-blur-sm">
                        <div class="absolute inset-0 rounded-3xl bg-gradient-to-br from-violet-600/5 to-fuchsia-600/5 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                        <div class="relative">
                            <div class="text-4xl mb-5">{{ feature.icon }}</div>
                            <h3 class="text-xl font-bold mb-3">{{ feature.title }}</h3>
                            <p class="text-gray-400 leading-relaxed">{{ feature.description }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- How It Works -->
        <section class="py-32 px-6 bg-gradient-to-b from-transparent to-gray-900/50">
            <div class="max-w-5xl mx-auto">
                <div class="text-center mb-20">
                    <h2 class="text-5xl font-black mb-4">How It <span class="text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 to-blue-400">Works</span></h2>
                    <p class="text-xl text-gray-400">Three steps to your next masterpiece</p>
                </div>
                <div class="grid md:grid-cols-3 gap-8">
                    <div v-for="(step, i) in steps" :key="i" class="relative text-center">
                        <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-violet-500/20 to-fuchsia-500/20 border border-violet-500/30 flex items-center justify-center mx-auto mb-6">
                            <span class="text-2xl font-black text-transparent bg-clip-text bg-gradient-to-r from-violet-400 to-fuchsia-400">{{ step.num }}</span>
                        </div>
                        <h3 class="text-xl font-bold mb-3">{{ step.title }}</h3>
                        <p class="text-gray-400 leading-relaxed">{{ step.desc }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Testimonials -->
        <section id="testimonials" class="py-32 px-6">
            <div class="max-w-7xl mx-auto">
                <div class="text-center mb-20">
                    <h2 class="text-5xl font-black mb-4">Loved by <span class="text-transparent bg-clip-text bg-gradient-to-r from-rose-400 to-pink-400">Creators</span></h2>
                    <p class="text-xl text-gray-400">Join 150,000+ artists, designers, and marketers</p>
                </div>
                <div class="grid md:grid-cols-3 gap-6">
                    <div v-for="t in testimonials" :key="t.name" class="bg-gray-900/50 border border-white/5 rounded-3xl p-8 backdrop-blur-sm hover:border-white/10 transition-all hover:-translate-y-1">
                        <div class="flex items-center gap-4 mb-6">
                            <div :class="`w-12 h-12 rounded-full bg-gradient-to-br ${t.gradient} flex items-center justify-center font-bold text-sm`">{{ t.avatar }}</div>
                            <div>
                                <div class="font-bold">{{ t.name }}</div>
                                <div class="text-sm text-gray-500">{{ t.role }}</div>
                            </div>
                            <div class="ml-auto text-yellow-400">★★★★★</div>
                        </div>
                        <p class="text-gray-300 leading-relaxed italic">"{{ t.text }}"</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Pricing -->
        <section id="pricing" class="py-32 px-6 bg-gradient-to-b from-transparent to-gray-900/50">
            <div class="max-w-6xl mx-auto">
                <div class="text-center mb-20">
                    <h2 class="text-5xl font-black mb-4">Simple <span class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-400 to-cyan-400">Pricing</span></h2>
                    <p class="text-xl text-gray-400">Start free, scale when you need to</p>
                </div>
                <div class="grid md:grid-cols-3 gap-8">
                    <div v-for="plan in plans" :key="plan.name"
                        :class="['relative rounded-3xl p-8 transition-all hover:-translate-y-1', plan.highlighted ? 'bg-gradient-to-b from-violet-600/20 to-fuchsia-600/10 border-2 border-violet-500/50 shadow-2xl shadow-violet-500/20' : 'bg-gray-900/50 border border-white/5 hover:border-white/10']">
                        <div v-if="plan.highlighted" class="absolute -top-4 left-1/2 -translate-x-1/2 bg-gradient-to-r from-violet-500 to-fuchsia-600 text-xs font-bold px-4 py-1.5 rounded-full">MOST POPULAR</div>
                        <h3 class="text-xl font-bold mb-2">{{ plan.name }}</h3>
                        <div class="flex items-baseline gap-1 mb-1">
                            <span class="text-5xl font-black">{{ plan.price }}</span>
                            <span class="text-gray-400">{{ plan.period }}</span>
                        </div>
                        <div class="text-sm text-violet-400 mb-8">{{ plan.credits }}</div>
                        <ul class="space-y-3 mb-8">
                            <li v-for="f in plan.features" :key="f" class="flex items-center gap-3 text-sm text-gray-300">
                                <svg class="w-5 h-5 text-emerald-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                {{ f }}
                            </li>
                        </ul>
                        <Link :href="route('register')" :class="['block text-center font-bold py-3 rounded-2xl transition-all', plan.highlighted ? 'bg-gradient-to-r from-violet-500 to-fuchsia-600 hover:from-violet-400 hover:to-fuchsia-500 shadow-lg shadow-violet-500/30' : 'bg-white/5 hover:bg-white/10 border border-white/10']">
                            {{ plan.cta }}
                        </Link>
                    </div>
                </div>
            </div>
        </section>

        <!-- How Credits Work -->
        <section class="py-32 px-6">
            <div class="max-w-5xl mx-auto">
                <div class="text-center mb-16">
                    <h2 class="text-5xl font-black mb-4">How <span class="text-transparent bg-clip-text bg-gradient-to-r from-amber-400 to-orange-400">Credits</span> Work</h2>
                    <p class="text-xl text-gray-400 max-w-2xl mx-auto">Simple, transparent pricing. 1 credit = 1 image. No hidden fees, no complicated tiers.</p>
                </div>

                <!-- Credit rule -->
                <div class="flex items-center justify-center gap-6 mb-14 flex-wrap">
                    <div class="flex items-center gap-4 bg-gray-900/70 border border-white/10 rounded-2xl px-8 py-5 backdrop-blur-sm">
                        <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-amber-400/20 to-orange-500/20 border border-amber-400/30 flex items-center justify-center text-2xl">🪙</div>
                        <div>
                            <div class="text-3xl font-black text-transparent bg-clip-text bg-gradient-to-r from-amber-400 to-orange-400">1 Credit</div>
                            <div class="text-gray-400 text-sm mt-0.5">per generation</div>
                        </div>
                        <div class="text-3xl text-gray-600 mx-2">=</div>
                        <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-violet-400/20 to-fuchsia-500/20 border border-violet-400/30 flex items-center justify-center text-2xl">🖼️</div>
                        <div>
                            <div class="text-3xl font-black text-transparent bg-clip-text bg-gradient-to-r from-violet-400 to-fuchsia-400">1 Image</div>
                            <div class="text-gray-400 text-sm mt-0.5">any style, any model</div>
                        </div>
                    </div>
                </div>

                <!-- Credit examples grid -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-14">
                    <div v-for="ex in creditExamples" :key="ex.credits"
                        class="relative bg-gray-900/60 border border-white/5 hover:border-white/15 rounded-2xl p-6 text-center transition-all hover:-translate-y-1 backdrop-blur-sm group">
                        <div :class="`absolute top-3 right-3 text-xs font-bold px-2 py-0.5 rounded-full bg-gradient-to-r ${ex.color} text-white`">{{ ex.badge }}</div>
                        <div :class="`text-4xl font-black text-transparent bg-clip-text bg-gradient-to-r ${ex.color} mb-1`">{{ ex.credits }}</div>
                        <div class="text-xs text-gray-500 uppercase tracking-wider mb-3">credits</div>
                        <div class="w-full h-px bg-white/5 mb-3"></div>
                        <div class="text-2xl font-bold text-white mb-1">{{ ex.images }}</div>
                        <div class="text-xs text-gray-400">images generated</div>
                        <div class="text-xs text-gray-600 mt-2">{{ ex.label }}</div>
                    </div>
                </div>

                <!-- Notes -->
                <div class="grid md:grid-cols-3 gap-4">
                    <div class="flex items-start gap-3 bg-gray-900/40 border border-white/5 rounded-xl p-5">
                        <span class="text-xl mt-0.5">🎁</span>
                        <div>
                            <div class="font-semibold text-sm mb-1">Free on Signup</div>
                            <div class="text-xs text-gray-400">Every new account gets 10 credits instantly — no credit card required.</div>
                        </div>
                    </div>
                    <div class="flex items-start gap-3 bg-gray-900/40 border border-white/5 rounded-xl p-5">
                        <span class="text-xl mt-0.5">⚡</span>
                        <div>
                            <div class="font-semibold text-sm mb-1">Deducted on Submit</div>
                            <div class="text-xs text-gray-400">1 credit is deducted when you submit a generation request, before the AI processes it.</div>
                        </div>
                    </div>
                    <div class="flex items-start gap-3 bg-gray-900/40 border border-white/5 rounded-xl p-5">
                        <span class="text-xl mt-0.5">🔄</span>
                        <div>
                            <div class="font-semibold text-sm mb-1">Monthly Refresh</div>
                            <div class="text-xs text-gray-400">Pro and Enterprise plans refresh credits every billing cycle. Unused credits don't roll over.</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA -->
        <section class="py-32 px-6">
            <div class="max-w-4xl mx-auto text-center">
                <div class="relative bg-gradient-to-br from-violet-600/20 to-fuchsia-600/20 border border-violet-500/30 rounded-3xl p-16 overflow-hidden">
                    <div class="absolute inset-0 bg-[linear-gradient(rgba(139,92,246,0.05)_1px,transparent_1px),linear-gradient(90deg,rgba(139,92,246,0.05)_1px,transparent_1px)] bg-[size:30px_30px]"></div>
                    <div class="relative">
                        <h2 class="text-5xl md:text-6xl font-black mb-6">Ready to <span class="text-transparent bg-clip-text bg-gradient-to-r from-violet-400 to-fuchsia-400">Create?</span></h2>
                        <p class="text-xl text-gray-400 mb-10">Join thousands of creators. Start with 10 free credits — no credit card required.</p>
                        <Link v-if="canRegister" :href="route('register')" class="inline-flex items-center gap-3 bg-gradient-to-r from-violet-500 to-fuchsia-600 hover:from-violet-400 hover:to-fuchsia-500 text-white font-bold text-xl px-10 py-5 rounded-2xl transition-all shadow-2xl shadow-violet-500/40 hover:shadow-violet-500/60 hover:scale-105">
                            Get Started — It's Free
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                        </Link>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="border-t border-white/5 py-16 px-6">
            <div class="max-w-7xl mx-auto flex flex-col md:flex-row items-center justify-between gap-8">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-violet-500 to-fuchsia-600 flex items-center justify-center font-black">D</div>
                    <span class="font-bold">Don<span class="text-transparent bg-clip-text bg-gradient-to-r from-violet-400 to-fuchsia-400">Draper</span></span>
                </div>
                <div class="flex gap-8 text-sm text-gray-500">
                    <a href="#" class="hover:text-white transition-colors">Privacy</a>
                    <a href="#" class="hover:text-white transition-colors">Terms</a>
                    <a href="#" class="hover:text-white transition-colors">API Docs</a>
                    <a href="#" class="hover:text-white transition-colors">Contact</a>
                </div>
                <div class="text-sm text-gray-600">© 2026 DonDraper. Built with Laravel.</div>
            </div>
        </footer>
    </div>
</template>
