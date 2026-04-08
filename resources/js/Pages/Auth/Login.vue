<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import InputError from '@/Components/InputError.vue'

defineProps({
    canResetPassword: Boolean,
    status: String,
})

const form = useForm({
    email: '',
    password: '',
    remember: false,
})

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    })
}
</script>

<template>
    <Head title="Sign In — DonDraper" />

    <div class="min-h-screen bg-gray-950 text-white flex">
        <!-- Left Panel -->
        <div class="hidden lg:flex flex-1 relative overflow-hidden bg-[#070B14]">
            <div class="absolute inset-0 bg-gradient-to-br from-sky-900/20 via-transparent to-cyan-900/15"></div>
            <div class="absolute inset-0 bg-[linear-gradient(rgba(255,255,255,0.015)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,0.015)_1px,transparent_1px)] bg-[size:52px_52px]"></div>
            <div class="absolute -top-32 -left-32 w-[600px] h-[600px] rounded-full bg-sky-600/10 blur-[120px]"></div>
            <div class="absolute -bottom-32 right-0 w-[500px] h-[500px] rounded-full bg-cyan-600/10 blur-[100px]"></div>

            <div class="relative flex flex-col justify-center px-14 max-w-md">
                <Link :href="route('home')" class="flex items-center gap-3 mb-16">
                    <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-sky-500 to-cyan-600 flex items-center justify-center font-black">D</div>
                    <span class="text-lg font-bold">Don<span class="text-transparent bg-clip-text bg-gradient-to-r from-sky-400 to-cyan-400">Draper</span></span>
                </Link>

                <h2 class="text-4xl font-black mb-5 leading-tight">
                    Welcome back,<br>
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-sky-400 to-cyan-400">Creator.</span>
                </h2>
                <p class="text-gray-400 mb-12 leading-relaxed">Sign in to continue building your AI-powered creative portfolio.</p>

                <div class="space-y-4">
                    <div v-for="item in [
                        { icon: '🎨', text: 'Generate images with DALL-E 3 & Stable Diffusion' },
                        { icon: '⚙️', text: 'Full control over 15+ generation attributes' },
                        { icon: '🗂️', text: 'Access your entire creation gallery' },
                    ]" :key="item.text" class="flex items-center gap-4">
                        <span class="text-xl flex-shrink-0">{{ item.icon }}</span>
                        <span class="text-gray-300 text-sm">{{ item.text }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Panel -->
        <div class="flex-1 lg:max-w-[420px] flex flex-col justify-center px-8 py-16 bg-gray-950">
            <!-- Mobile logo -->
            <div class="lg:hidden flex items-center gap-3 mb-10">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-sky-500 to-cyan-600 flex items-center justify-center font-black">D</div>
                <span class="text-lg font-bold">Don<span class="text-transparent bg-clip-text bg-gradient-to-r from-sky-400 to-cyan-400">Draper</span></span>
            </div>

            <h1 class="text-2xl font-black mb-1.5 text-white">Sign In</h1>
            <p class="text-gray-400 text-sm mb-8">
                Don't have an account?
                <Link :href="route('register')" class="text-sky-400 hover:text-sky-300 font-medium transition-colors">Create one free</Link>
            </p>

            <div v-if="status" class="mb-6 bg-emerald-500/8 border border-emerald-500/25 rounded-xl px-4 py-3 text-sm text-emerald-400">
                {{ status }}
            </div>

            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <label for="email" class="block text-xs font-semibold text-gray-400 mb-2 uppercase tracking-wider">Email address</label>
                    <input
                        id="email"
                        v-model="form.email"
                        type="email"
                        required
                        autofocus
                        autocomplete="username"
                        placeholder="you@example.com"
                        class="w-full bg-white/4 border border-white/8 focus:border-sky-500/60 focus:ring-2 focus:ring-sky-500/12 rounded-xl px-4 py-3 text-white placeholder-gray-600 outline-none transition-all text-sm"
                    />
                    <InputError :message="form.errors.email" class="mt-2 text-sm text-rose-400" />
                </div>

                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label for="password" class="block text-xs font-semibold text-gray-400 uppercase tracking-wider">Password</label>
                        <Link v-if="canResetPassword" :href="route('password.request')" class="text-xs text-sky-400 hover:text-sky-300 transition-colors">Forgot?</Link>
                    </div>
                    <input
                        id="password"
                        v-model="form.password"
                        type="password"
                        required
                        autocomplete="current-password"
                        placeholder="••••••••"
                        class="w-full bg-white/4 border border-white/8 focus:border-sky-500/60 focus:ring-2 focus:ring-sky-500/12 rounded-xl px-4 py-3 text-white placeholder-gray-600 outline-none transition-all text-sm"
                    />
                    <InputError :message="form.errors.password" class="mt-2 text-sm text-rose-400" />
                </div>

                <div class="flex items-center gap-3">
                    <input id="remember" v-model="form.remember" type="checkbox"
                        class="w-4 h-4 rounded border-white/20 bg-white/5 text-sky-500 focus:ring-sky-500/20 focus:ring-2" />
                    <label for="remember" class="text-sm text-gray-400">Remember me</label>
                </div>

                <button
                    type="submit"
                    :disabled="form.processing"
                    class="w-full flex items-center justify-center gap-2 bg-gradient-to-r from-sky-500 to-cyan-600 hover:from-sky-400 hover:to-cyan-500 disabled:opacity-50 disabled:cursor-not-allowed text-white font-bold py-3.5 rounded-xl transition-all shadow-lg shadow-sky-500/20 hover:shadow-sky-500/35 hover:scale-[1.02] mt-2">
                    <svg v-if="form.processing" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    {{ form.processing ? 'Signing in...' : 'Sign In' }}
                </button>
            </form>

            <p class="mt-8 text-center text-xs text-gray-600">
                By signing in, you agree to our
                <a href="#" class="text-gray-500 hover:text-gray-300 transition-colors">Terms</a> and
                <a href="#" class="text-gray-500 hover:text-gray-300 transition-colors">Privacy Policy</a>.
            </p>
        </div>
    </div>
</template>
