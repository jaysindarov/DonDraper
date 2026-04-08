<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { ref } from 'vue'

const props = defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
})

const prompt = ref('')

const galleryRow1Base = [
    { url: 'https://picsum.photos/id/1025/280/380', label: 'Portrait Study' },
    { url: 'https://picsum.photos/id/1015/280/380', label: 'Mountain Twilight' },
    { url: 'https://picsum.photos/id/1043/280/380', label: 'Forest Spirit' },
    { url: 'https://picsum.photos/id/1060/280/380', label: 'Ocean Dreams' },
    { url: 'https://picsum.photos/id/1080/280/380', label: 'Desert Wind' },
    { url: 'https://picsum.photos/id/1033/280/380', label: 'Misty Peaks' },
    { url: 'https://picsum.photos/id/1074/280/380', label: 'Golden Hour' },
    { url: 'https://picsum.photos/id/1035/280/380', label: 'Night Bloom' },
]
const galleryRow1 = [...galleryRow1Base, ...galleryRow1Base]

const galleryRow2Base = [
    { url: 'https://picsum.photos/id/1069/280/380', label: 'Neon City' },
    { url: 'https://picsum.photos/id/1071/280/380', label: 'Crystal Cave' },
    { url: 'https://picsum.photos/id/1048/280/380', label: 'Abstract Flux' },
    { url: 'https://picsum.photos/id/1053/280/380', label: 'Cosmic Bloom' },
    { url: 'https://picsum.photos/id/1084/280/380', label: 'Wild Meadow' },
    { url: 'https://picsum.photos/id/1039/280/380', label: 'Ancient Ruin' },
    { url: 'https://picsum.photos/id/1050/280/380', label: 'Surreal Scape' },
    { url: 'https://picsum.photos/id/1047/280/380', label: 'Electric Storm' },
]
const galleryRow2 = [...galleryRow2Base, ...galleryRow2Base]

const styleCards = [
    { url: 'https://picsum.photos/id/1025/200/260', label: 'Photorealistic', desc: 'Hyper-detailed' },
    { url: 'https://picsum.photos/id/1015/200/260', label: 'Oil Painting', desc: 'Classical art' },
    { url: 'https://picsum.photos/id/1043/200/260', label: 'Cyberpunk', desc: 'Neon future' },
    { url: 'https://picsum.photos/id/1060/200/260', label: 'Watercolor', desc: 'Soft strokes' },
    { url: 'https://picsum.photos/id/1080/200/260', label: 'Fantasy', desc: 'Epic worlds' },
    { url: 'https://picsum.photos/id/1033/200/260', label: 'Anime', desc: 'Japanese style' },
]

const discoverPrompts = [
    { url: 'https://picsum.photos/id/1025/160/120', text: 'A lone astronaut on a neon-lit alien beach at dusk' },
    { url: 'https://picsum.photos/id/1015/160/120', text: 'Ancient samurai warrior in cherry blossom snowfall' },
    { url: 'https://picsum.photos/id/1043/160/120', text: 'Cyberpunk city flooded, gondolas between neon towers' },
    { url: 'https://picsum.photos/id/1060/160/120', text: 'Wolf howling at aurora borealis over frozen tundra' },
    { url: 'https://picsum.photos/id/1080/160/120', text: 'Dragon coiled around a moonlit crystal mountain peak' },
    { url: 'https://picsum.photos/id/1033/160/120', text: 'Steampunk airship docking at floating sky harbor' },
    { url: 'https://picsum.photos/id/1069/160/120', text: 'Deep sea bioluminescent jellyfish city underwater' },
    { url: 'https://picsum.photos/id/1071/160/120', text: 'Victorian greenhouse overgrown with alien flora' },
    { url: 'https://picsum.photos/id/1048/160/120', text: 'Desert nomad silhouette against twin suns at sunset' },
]

const features = [
    { icon: 'M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z', title: 'AI Image Generation', desc: 'DALL-E 3, Stable Diffusion, Flux. Full style, lighting, and mood control.' },
    { icon: 'M15 10l4.553-2.277A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z', title: 'Video Generation', desc: 'Cinematic AI video from text or image — motion, style, and duration control.' },
    { icon: 'M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4', title: 'Full Attribute Control', desc: 'Fine-tune every parameter: style, CFG scale, steps, seeds, aspect ratio.' },
    { icon: 'M13 10V3L4 14h7v7l9-11h-7z', title: 'Lightning Fast Queue', desc: 'Async generation with real-time status. Submit and continue — results appear instantly.' },
    { icon: 'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z', title: 'Personal Gallery', desc: 'Every generation saved, searchable, filterable. Your creative history organized.' },
    { icon: 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z', title: 'Team Workspaces', desc: 'Collaborate with your team. Shared credits, galleries, and generation history.' },
]

const stats = [
    { value: '2M+', label: 'Images Created' },
    { value: '150K+', label: 'Happy Creators' },
    { value: '99.9%', label: 'Uptime SLA' },
    { value: '<30s', label: 'Avg Generation' },
]

const testimonials = [
    { name: 'Alex Morgan', role: 'Creative Director', img: 'https://picsum.photos/id/64/80/80', text: 'DonDraper transformed our entire campaign workflow. We generate hundreds of concept images in the time it used to take one designer to produce a single comp.' },
    { name: 'Sofia Chen', role: 'Indie Game Dev', img: 'https://picsum.photos/id/91/80/80', text: 'The level of control over image attributes is unmatched. I produce exactly the aesthetic I need for each game scene every single time.' },
    { name: 'Marcus Webb', role: 'Brand Strategist', img: 'https://picsum.photos/id/177/80/80', text: 'From prompt to polished visual in under 30 seconds. Our social content calendar has never looked this good or moved this fast.' },
    { name: 'Lena Park', role: 'UX Designer', img: 'https://picsum.photos/id/65/80/80', text: 'I use it for mood boards and client presentations. The quality rivals stock photography but costs a fraction and I own the result.' },
    { name: 'James Torres', role: 'Film Director', img: 'https://picsum.photos/id/158/80/80', text: 'Pre-vis storyboards in minutes. I can iterate on scene composition with clients in real-time now — it has changed how I pitch.' },
    { name: 'Yuki Tanaka', role: 'Manga Artist', img: 'https://picsum.photos/id/146/80/80', text: 'The anime style output is exceptional. It feels like having an assistant who understands exactly what visual language I am working in.' },
]

const plans = [
    { name: 'Starter', price: 'Free', period: '', credits: '10 credits/mo', features: ['10 image generations', 'Standard quality', 'Basic styles', 'Community gallery'], cta: 'Get Started Free', highlighted: false },
    { name: 'Pro', price: '$19', period: '/month', credits: '500 credits/mo', features: ['500 image generations', 'HD & Ultra HD quality', 'All styles & models', 'Priority queue', 'Private gallery', 'API access'], cta: 'Start Pro Trial', highlighted: true },
    { name: 'Enterprise', price: '$79', period: '/month', credits: 'Unlimited', features: ['Unlimited generations', 'All Pro features', 'Custom AI models', 'Dedicated support', 'Team workspace', 'SLA guarantee'], cta: 'Contact Sales', highlighted: false },
]

const faqs = [
    { q: 'What AI models does DonDraper use?', a: 'We support DALL-E 3, GPT-Image-1, Stable Diffusion, and Flux — selectable per generation.' },
    { q: 'How do credits work?', a: 'Each image costs 1 credit. Video costs 5. Credits reset monthly on paid plans. Unused credits do not roll over on Free.' },
    { q: 'Can I use generated images commercially?', a: 'Yes. You retain full commercial rights to all images generated on paid plans.' },
    { q: 'How fast is generation?', a: 'Most images complete in 10–30 seconds. Queue priority is higher on Pro and Enterprise plans.' },
    { q: 'Is there an API?', a: 'Yes. Pro and Enterprise plans include API access with full documentation and Sanctum token auth.' },
    { q: 'Can I upload reference images?', a: 'Yes. Our Reference People and Product Reference features let you upload faces and products for accurate scene placement.' },
    { q: 'What aspect ratios are supported?', a: 'Square (1:1), Portrait (2:3), Landscape (3:2), Widescreen (16:9), and Cinematic (21:9).' },
    { q: 'Is my content private?', a: 'By default yes. You choose what to share to the Community Gallery. Nothing is shared without your explicit action.' },
    { q: 'Do you support teams?', a: 'Yes. Enterprise plans include Team Workspaces with shared credits, galleries, and role-based permissions.' },
    { q: 'What happens if I run out of credits?', a: 'Generation is paused. You can purchase one-time top-up packs or upgrade your plan anytime from the billing page.' },
    { q: 'Can I cancel anytime?', a: 'Absolutely. No contracts, no cancellation fees. Cancel from your billing dashboard and access continues until the period ends.' },
    { q: 'Is there a free trial for Pro?', a: 'New users get 10 free credits to try the platform. Paid trial options are available — contact us for details.' },
]
</script>

<template>
    <Head title="DonDraper — AI Image & Video Generation" />

    <div class="min-h-screen bg-gray-950 text-white overflow-x-hidden">

        <!-- ── Aurora Background ── -->
        <div class="fixed inset-0 pointer-events-none overflow-hidden z-0">
            <div class="aurora-blob aurora-1"></div>
            <div class="aurora-blob aurora-2"></div>
            <div class="aurora-blob aurora-3"></div>
            <!-- Floating particles -->
            <div v-for="i in 5" :key="i" :class="`particle particle-${i}`"></div>
        </div>

        <!-- ── Navigation ── -->
        <nav class="relative z-50 border-b border-white/5 bg-gray-950/80 backdrop-blur-xl sticky top-0">
            <div class="max-w-screen-xl mx-auto px-5 flex items-center justify-between h-14">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-gradient-to-br from-sky-500 to-cyan-600 flex items-center justify-center font-black text-sm shadow-lg shadow-sky-500/30 logo-glow">D</div>
                    <span class="font-bold text-base">Don<span class="text-transparent bg-clip-text bg-gradient-to-r from-sky-400 to-cyan-400">Draper</span></span>
                </div>
                <div class="flex items-center gap-2">
                    <template v-if="canLogin">
                        <Link :href="route('login')" class="px-4 py-2 text-sm text-gray-400 hover:text-white transition-colors font-medium">Sign In</Link>
                        <Link v-if="canRegister" :href="route('register')"
                            class="px-4 py-2 bg-gradient-to-r from-sky-500 to-cyan-600 hover:from-sky-400 hover:to-cyan-500 text-white text-sm font-semibold rounded-xl transition-all shadow-lg shadow-sky-500/20 hover:scale-105">
                            Get Started Free
                        </Link>
                    </template>
                    <Link v-else :href="route('dashboard')" class="px-4 py-2 bg-gradient-to-r from-sky-500 to-cyan-600 text-white text-sm font-semibold rounded-xl">
                        Dashboard
                    </Link>
                </div>
            </div>
        </nav>

        <!-- ── Hero ── -->
        <section class="relative z-10 pt-24 pb-8 px-5 text-center">
            <div class="max-w-3xl mx-auto">
                <div class="inline-flex items-center gap-2 bg-sky-500/10 border border-sky-500/20 rounded-full px-4 py-1.5 text-xs text-sky-400 font-medium mb-8 fade-up delay-0">
                    <span class="w-1.5 h-1.5 rounded-full bg-sky-400 animate-pulse"></span>
                    Powered by DALL-E 3, Flux &amp; Stable Diffusion
                </div>
                <h1 class="text-5xl sm:text-6xl md:text-7xl font-black leading-tight mb-6 fade-up delay-1">
                    Create Stunning<br>
                    <span class="shimmer-text">AI Visuals</span><br>
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-sky-400 via-cyan-400 to-blue-500">In Seconds</span>
                </h1>
                <p class="text-lg text-gray-400 mb-10 leading-relaxed max-w-xl mx-auto fade-up delay-2">
                    Turn text into breathtaking images and videos. Full control over style, lighting, mood, and parameters. Professional quality, every time.
                </p>

                <!-- Inline Generator -->
                <div class="bg-[#0A0E1A]/80 backdrop-blur border border-white/8 rounded-2xl p-2 mb-4 max-w-2xl mx-auto fade-up delay-3 shadow-2xl shadow-black/40">
                    <div class="flex items-center gap-2">
                        <div class="flex-1 flex items-center gap-3 bg-white/4 rounded-xl px-4 py-3">
                            <svg class="w-4 h-4 text-sky-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                            <input v-model="prompt" type="text" placeholder="A lone astronaut surfing neon waves on a distant planet…"
                                class="flex-1 bg-transparent text-sm text-white placeholder-gray-600 outline-none" />
                        </div>
                        <Link :href="canLogin ? route('register') : route('generations.create')"
                            class="flex items-center gap-2 bg-gradient-to-r from-sky-500 to-cyan-600 hover:from-sky-400 hover:to-cyan-500 text-white font-bold px-5 py-3 rounded-xl text-sm transition-all hover:scale-105 shadow-lg shadow-sky-500/30 whitespace-nowrap">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            Generate
                        </Link>
                    </div>
                </div>
                <p class="text-xs text-gray-600 fade-up delay-4">Free to start · No credit card required · 10 free generations</p>
            </div>
        </section>

        <!-- ── Stats ── -->
        <section class="relative z-10 py-10 px-5">
            <div class="max-w-3xl mx-auto grid grid-cols-2 md:grid-cols-4 gap-4">
                <div v-for="stat in stats" :key="stat.label"
                    class="text-center bg-white/3 border border-white/6 rounded-2xl py-5 px-4">
                    <div class="text-2xl font-black text-transparent bg-clip-text bg-gradient-to-r from-sky-400 to-cyan-400 mb-1">{{ stat.value }}</div>
                    <div class="text-xs text-gray-500">{{ stat.label }}</div>
                </div>
            </div>
        </section>

        <!-- ── Gallery Marquee ── -->
        <section class="relative z-10 py-8 overflow-hidden">
            <!-- Row 1 — left -->
            <div class="marquee-track mb-3">
                <div class="marquee-inner marquee-left">
                    <div v-for="(img, i) in galleryRow1" :key="`r1-${i}`" class="gallery-card flex-shrink-0">
                        <img :src="img.url" :alt="img.label" class="w-full h-full object-cover" loading="lazy" />
                        <div class="gallery-overlay">
                            <span class="text-xs font-medium text-white/90">{{ img.label }}</span>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Row 2 — right -->
            <div class="marquee-track">
                <div class="marquee-inner marquee-right">
                    <div v-for="(img, i) in galleryRow2" :key="`r2-${i}`" class="gallery-card flex-shrink-0">
                        <img :src="img.url" :alt="img.label" class="w-full h-full object-cover" loading="lazy" />
                        <div class="gallery-overlay">
                            <span class="text-xs font-medium text-white/90">{{ img.label }}</span>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Fade edges -->
            <div class="absolute inset-y-0 left-0 w-32 bg-gradient-to-r from-gray-950 to-transparent z-10 pointer-events-none"></div>
            <div class="absolute inset-y-0 right-0 w-32 bg-gradient-to-l from-gray-950 to-transparent z-10 pointer-events-none"></div>
        </section>

        <!-- ── Try a Style ── -->
        <section class="relative z-10 py-16 px-5">
            <div class="max-w-screen-xl mx-auto">
                <div class="text-center mb-10">
                    <h2 class="text-3xl font-black mb-3">Try a Style</h2>
                    <p class="text-gray-400 text-sm">From photorealistic to anime — every aesthetic at your command.</p>
                </div>
                <div class="flex gap-4 overflow-x-auto pb-4 justify-center flex-wrap">
                    <Link v-for="style in styleCards" :key="style.label"
                        :href="canLogin ? route('register') : route('generations.create')"
                        class="style-card flex-shrink-0 w-36 group cursor-pointer">
                        <div class="relative rounded-2xl overflow-hidden h-48 mb-2">
                            <img :src="style.url" :alt="style.label" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500" loading="lazy" />
                            <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-transparent to-transparent"></div>
                            <div class="absolute bottom-2 left-0 right-0 text-center">
                                <span class="text-xs font-semibold text-white">{{ style.label }}</span>
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 text-center">{{ style.desc }}</p>
                    </Link>
                </div>
            </div>
        </section>

        <!-- ── Discover Something New ── -->
        <section class="relative z-10 py-16 px-5">
            <div class="max-w-screen-xl mx-auto">
                <div class="text-center mb-10">
                    <h2 class="text-3xl font-black mb-3">Discover Something New</h2>
                    <p class="text-gray-400 text-sm">Get inspired. Click any prompt to start creating.</p>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                    <Link v-for="item in discoverPrompts" :key="item.text"
                        :href="canLogin ? route('register') : route('generations.create')"
                        class="discover-card group flex items-center gap-3 bg-white/3 hover:bg-white/6 border border-white/6 hover:border-sky-500/20 rounded-2xl p-3 transition-all">
                        <div class="w-16 h-16 rounded-xl overflow-hidden flex-shrink-0">
                            <img :src="item.url" :alt="item.text" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300" loading="lazy" />
                        </div>
                        <p class="text-sm text-gray-300 group-hover:text-white leading-snug transition-colors">{{ item.text }}</p>
                    </Link>
                </div>
            </div>
        </section>

        <!-- ── Features ── -->
        <section class="relative z-10 py-20 px-5">
            <div class="max-w-screen-xl mx-auto">
                <div class="text-center mb-14">
                    <h2 class="text-4xl font-black mb-4">Everything You Need to Create</h2>
                    <p class="text-gray-400 max-w-xl mx-auto">Professional-grade tools built for creators, designers, and teams who demand the best.</p>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                    <div v-for="feat in features" :key="feat.title"
                        class="bg-[#0A0E1A] border border-white/6 hover:border-sky-500/20 rounded-2xl p-6 transition-all group hover:-translate-y-0.5">
                        <div class="w-10 h-10 rounded-xl bg-sky-500/10 border border-sky-500/15 flex items-center justify-center mb-4 group-hover:bg-sky-500/15 transition-colors">
                            <svg class="w-5 h-5 text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" :d="feat.icon" />
                            </svg>
                        </div>
                        <h3 class="font-bold text-white mb-2">{{ feat.title }}</h3>
                        <p class="text-sm text-gray-500 leading-relaxed">{{ feat.desc }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ── How It Works ── -->
        <section class="relative z-10 py-20 px-5">
            <div class="max-w-3xl mx-auto">
                <div class="text-center mb-14">
                    <h2 class="text-4xl font-black mb-4">Three Steps to Stunning</h2>
                    <p class="text-gray-400">From idea to finished image in under a minute.</p>
                </div>
                <div class="space-y-6">
                    <div v-for="(step, i) in [
                        { num: '01', title: 'Describe Your Vision', desc: 'Write a prompt or use our AI prompt enhancer. Add negative prompts to exclude unwanted elements.' },
                        { num: '02', title: 'Tune the Parameters', desc: 'Select art style, lighting, mood, resolution, guidance scale — all in an intuitive panel.' },
                        { num: '03', title: 'Generate & Download', desc: 'Hit generate and watch your vision materialize. Save to your gallery and download in full resolution.' },
                    ]" :key="step.num"
                        class="flex items-start gap-6 bg-[#0A0E1A] border border-white/6 rounded-2xl p-6">
                        <div class="text-4xl font-black text-transparent bg-clip-text bg-gradient-to-br from-sky-500 to-cyan-600 flex-shrink-0 leading-none">{{ step.num }}</div>
                        <div>
                            <h3 class="font-bold text-white mb-1">{{ step.title }}</h3>
                            <p class="text-sm text-gray-500 leading-relaxed">{{ step.desc }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ── Testimonials ── -->
        <section class="relative z-10 py-20 px-5">
            <div class="max-w-screen-xl mx-auto">
                <div class="text-center mb-14">
                    <h2 class="text-4xl font-black mb-4">Loved by Creators</h2>
                    <p class="text-gray-400">Join thousands of designers, developers, and artists already creating with DonDraper.</p>
                </div>
                <div class="columns-1 md:columns-2 lg:columns-3 gap-5 space-y-5">
                    <div v-for="t in testimonials" :key="t.name"
                        class="break-inside-avoid bg-[#0A0E1A] border border-white/6 rounded-2xl p-6 mb-5">
                        <div class="flex gap-0.5 mb-4">
                            <svg v-for="s in 5" :key="s" class="w-4 h-4 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        </div>
                        <p class="text-sm text-gray-300 leading-relaxed mb-5">"{{ t.text }}"</p>
                        <div class="flex items-center gap-3">
                            <img :src="t.img" :alt="t.name" class="w-9 h-9 rounded-full object-cover" loading="lazy" />
                            <div>
                                <div class="text-sm font-semibold text-white">{{ t.name }}</div>
                                <div class="text-xs text-gray-500">{{ t.role }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ── Pricing ── -->
        <section class="relative z-10 py-20 px-5">
            <div class="max-w-screen-xl mx-auto">
                <div class="text-center mb-14">
                    <h2 class="text-4xl font-black mb-4">Simple, Honest Pricing</h2>
                    <p class="text-gray-400">Start free. Upgrade when you're ready. Cancel anytime.</p>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-5 max-w-4xl mx-auto">
                    <div v-for="plan in plans" :key="plan.name"
                        :class="['rounded-2xl p-6 border transition-all relative', plan.highlighted
                            ? 'bg-gradient-to-b from-sky-500/10 to-cyan-600/5 border-sky-500/30 ring-1 ring-sky-500/20 shadow-xl shadow-sky-500/10'
                            : 'bg-[#0A0E1A] border-white/6']">
                        <div v-if="plan.highlighted" class="absolute -top-3 left-1/2 -translate-x-1/2">
                            <span class="bg-gradient-to-r from-sky-500 to-cyan-600 text-white text-xs font-bold px-3 py-1 rounded-full shadow-lg">Most Popular</span>
                        </div>
                        <div class="mb-5">
                            <div class="text-sm font-semibold text-gray-400 mb-1">{{ plan.name }}</div>
                            <div class="flex items-baseline gap-1">
                                <span class="text-4xl font-black" :class="plan.highlighted ? 'text-transparent bg-clip-text bg-gradient-to-r from-sky-400 to-cyan-400' : 'text-white'">{{ plan.price }}</span>
                                <span class="text-gray-500 text-sm">{{ plan.period }}</span>
                            </div>
                            <div class="text-xs text-gray-500 mt-1">{{ plan.credits }}</div>
                        </div>
                        <ul class="space-y-2.5 mb-6">
                            <li v-for="f in plan.features" :key="f" class="flex items-center gap-2 text-sm text-gray-300">
                                <svg class="w-4 h-4 flex-shrink-0" :class="plan.highlighted ? 'text-sky-400' : 'text-gray-600'" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                {{ f }}
                            </li>
                        </ul>
                        <Link :href="route('register')"
                            :class="['w-full flex items-center justify-center py-3 rounded-xl font-semibold text-sm transition-all', plan.highlighted
                                ? 'bg-gradient-to-r from-sky-500 to-cyan-600 hover:from-sky-400 hover:to-cyan-500 text-white shadow-lg shadow-sky-500/20 hover:scale-105'
                                : 'bg-white/5 hover:bg-white/8 text-gray-300 hover:text-white border border-white/8']">
                            {{ plan.cta }}
                        </Link>
                    </div>
                </div>
            </div>
        </section>

        <!-- ── FAQ ── -->
        <section class="relative z-10 py-20 px-5">
            <div class="max-w-screen-xl mx-auto">
                <div class="text-center mb-14">
                    <h2 class="text-4xl font-black mb-4">Frequently Asked Questions</h2>
                    <p class="text-gray-400">Everything you need to know before getting started.</p>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 max-w-4xl mx-auto">
                    <div v-for="(faq, i) in faqs" :key="i"
                        class="bg-[#0A0E1A] border border-white/6 rounded-2xl p-5">
                        <div class="flex items-start gap-3">
                            <span class="text-sky-500/50 font-black text-sm flex-shrink-0 pt-0.5">{{ String(i + 1).padStart(2, '0') }}</span>
                            <div>
                                <h4 class="font-semibold text-white text-sm mb-1.5">{{ faq.q }}</h4>
                                <p class="text-sm text-gray-500 leading-relaxed">{{ faq.a }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ── CTA ── -->
        <section class="relative z-10 py-24 px-5 text-center">
            <div class="max-w-2xl mx-auto">
                <div class="inline-block mb-8">
                    <div class="w-20 h-20 rounded-3xl bg-gradient-to-br from-sky-500 to-cyan-600 flex items-center justify-center font-black text-3xl shadow-2xl shadow-sky-500/30 mx-auto logo-glow">D</div>
                </div>
                <h2 class="text-5xl font-black mb-6">Start Creating<br><span class="text-transparent bg-clip-text bg-gradient-to-r from-sky-400 to-cyan-400">Right Now</span></h2>
                <p class="text-gray-400 mb-10 text-lg">10 free credits. No credit card. No limits on what you can imagine.</p>
                <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
                    <Link :href="route('register')"
                        class="flex items-center gap-2 bg-gradient-to-r from-sky-500 to-cyan-600 hover:from-sky-400 hover:to-cyan-500 text-white font-bold px-8 py-4 rounded-2xl text-base transition-all hover:scale-105 shadow-2xl shadow-sky-500/30">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        Get Started Free
                    </Link>
                    <Link :href="route('login')" class="px-8 py-4 bg-white/5 hover:bg-white/8 border border-white/10 text-gray-300 hover:text-white font-semibold rounded-2xl text-base transition-all">
                        Sign In
                    </Link>
                </div>
            </div>
        </section>

        <!-- ── Footer ── -->
        <footer class="relative z-10 border-t border-white/5 py-12 px-5">
            <div class="max-w-screen-xl mx-auto">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-8 mb-10">
                    <div class="col-span-2 md:col-span-1">
                        <div class="flex items-center gap-2 mb-3">
                            <div class="w-7 h-7 rounded-xl bg-gradient-to-br from-sky-500 to-cyan-600 flex items-center justify-center font-black text-xs">D</div>
                            <span class="font-bold text-sm">Don<span class="text-transparent bg-clip-text bg-gradient-to-r from-sky-400 to-cyan-400">Draper</span></span>
                        </div>
                        <p class="text-xs text-gray-600 leading-relaxed">Professional AI image & video generation for creators and teams.</p>
                    </div>
                    <div>
                        <div class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Product</div>
                        <ul class="space-y-2 text-xs text-gray-600">
                            <li><Link :href="route('gallery')" class="hover:text-gray-300 transition-colors">Community Gallery</Link></li>
                            <li><Link :href="route('register')" class="hover:text-gray-300 transition-colors">Generate Images</Link></li>
                            <li><Link :href="route('billing')" class="hover:text-gray-300 transition-colors">Pricing</Link></li>
                        </ul>
                    </div>
                    <div>
                        <div class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Account</div>
                        <ul class="space-y-2 text-xs text-gray-600">
                            <li><Link :href="route('login')" class="hover:text-gray-300 transition-colors">Sign In</Link></li>
                            <li><Link :href="route('register')" class="hover:text-gray-300 transition-colors">Register</Link></li>
                        </ul>
                    </div>
                    <div>
                        <div class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Legal</div>
                        <ul class="space-y-2 text-xs text-gray-600">
                            <li><span class="cursor-default">Privacy Policy</span></li>
                            <li><span class="cursor-default">Terms of Service</span></li>
                        </ul>
                    </div>
                </div>
                <div class="border-t border-white/5 pt-6 flex flex-col sm:flex-row items-center justify-between gap-3">
                    <p class="text-xs text-gray-700">© {{ new Date().getFullYear() }} DonDraper. All rights reserved.</p>
                    <p class="text-xs text-gray-700">Built with Laravel, Vue 3 &amp; Inertia.js</p>
                </div>
            </div>
        </footer>

    </div>
</template>

<style scoped>
/* Aurora background blobs */
.aurora-blob {
    position: absolute;
    border-radius: 50%;
    filter: blur(80px);
    opacity: 0.12;
    animation: aurora-pulse 8s ease-in-out infinite;
}
.aurora-1 {
    width: 600px; height: 600px;
    background: radial-gradient(circle, #0ea5e9, #06b6d4);
    top: -200px; left: -100px;
    animation-delay: 0s;
}
.aurora-2 {
    width: 500px; height: 500px;
    background: radial-gradient(circle, #6366f1, #8b5cf6);
    top: 100px; right: -150px;
    animation-delay: 3s;
}
.aurora-3 {
    width: 400px; height: 400px;
    background: radial-gradient(circle, #0284c7, #0891b2);
    bottom: 200px; left: 30%;
    animation-delay: 5s;
}
@keyframes aurora-pulse {
    0%, 100% { transform: scale(1) translate(0, 0); opacity: 0.12; }
    33% { transform: scale(1.15) translate(30px, -20px); opacity: 0.18; }
    66% { transform: scale(0.9) translate(-20px, 30px); opacity: 0.08; }
}

/* Floating particles */
.particle {
    position: absolute;
    width: 3px; height: 3px;
    background: #0ea5e9;
    border-radius: 50%;
    opacity: 0.4;
    animation: float-particle 12s ease-in-out infinite;
}
.particle-1 { top: 20%; left: 15%; animation-delay: 0s; animation-duration: 14s; }
.particle-2 { top: 60%; left: 80%; animation-delay: 2s; animation-duration: 11s; }
.particle-3 { top: 40%; left: 50%; animation-delay: 4s; animation-duration: 15s; }
.particle-4 { top: 75%; left: 25%; animation-delay: 6s; animation-duration: 13s; }
.particle-5 { top: 10%; left: 70%; animation-delay: 8s; animation-duration: 12s; }
@keyframes float-particle {
    0%, 100% { transform: translate(0, 0) scale(1); opacity: 0.4; }
    25% { transform: translate(20px, -30px) scale(1.5); opacity: 0.7; }
    50% { transform: translate(-15px, 20px) scale(0.8); opacity: 0.2; }
    75% { transform: translate(30px, 10px) scale(1.2); opacity: 0.6; }
}

/* Shimmer text */
.shimmer-text {
    background: linear-gradient(90deg, #f8fafc 0%, #0ea5e9 40%, #22d3ee 60%, #f8fafc 100%);
    background-size: 200% auto;
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    animation: shimmer 3s linear infinite;
}
@keyframes shimmer {
    0% { background-position: 200% center; }
    100% { background-position: -200% center; }
}

/* Fade up entrance animations */
.fade-up {
    animation: fadeInUp 0.7s ease-out both;
}
.delay-0 { animation-delay: 0s; }
.delay-1 { animation-delay: 0.1s; }
.delay-2 { animation-delay: 0.2s; }
.delay-3 { animation-delay: 0.35s; }
.delay-4 { animation-delay: 0.5s; }
@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Logo glow */
.logo-glow {
    animation: logo-glow 3s ease-in-out infinite;
}
@keyframes logo-glow {
    0%, 100% { box-shadow: 0 0 20px rgba(14, 165, 233, 0.3); }
    50% { box-shadow: 0 0 40px rgba(14, 165, 233, 0.6), 0 0 80px rgba(6, 182, 212, 0.2); }
}

/* Marquee */
.marquee-track {
    overflow: hidden;
    width: 100%;
}
.marquee-inner {
    display: flex;
    gap: 12px;
    width: max-content;
    will-change: transform;
}
.marquee-left {
    animation: marquee-left 50s linear infinite;
}
.marquee-right {
    animation: marquee-right 50s linear infinite;
}
@keyframes marquee-left {
    0% { transform: translateX(0); }
    100% { transform: translateX(-50%); }
}
@keyframes marquee-right {
    0% { transform: translateX(-50%); }
    100% { transform: translateX(0); }
}
.marquee-track:hover .marquee-inner {
    animation-play-state: paused;
}

/* Gallery cards */
.gallery-card {
    width: 180px;
    height: 240px;
    border-radius: 16px;
    overflow: hidden;
    position: relative;
    cursor: pointer;
}
.gallery-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(to top, rgba(0,0,0,0.6) 0%, transparent 50%);
    display: flex;
    align-items: flex-end;
    padding: 10px;
    opacity: 0;
    transition: opacity 0.2s;
}
.gallery-card:hover .gallery-overlay {
    opacity: 1;
}
</style>
