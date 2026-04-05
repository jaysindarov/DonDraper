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
        <div class="hidden lg:flex flex-1 relative overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-br from-violet-900/50 via-gray-950 to-fuchsia-900/30"></div>
            <div class="absolute inset-0 bg-[linear-gradient(rgba(255,255,255,0.02)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,0.02)_1px,transparent_1px)] bg-[size:50px_50px]"></div>
            <div class="absolute -top-20 -left-20 w-[600px] h-[600px] rounded-full bg-violet-600/15 blur-[100px]"></div>
            <div class="absolute -bottom-20 -right-20 w-[500px] h-[500px] rounded-full bg-fuchsia-600/15 blur-[100px]"></div>

            <div class="relative flex flex-col justify-center px-16 max-w-lg">
                <Link :href="route('home')" class="flex items-center gap-3 mb-16">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-violet-500 to-fuchsia-600 flex items-center justify-center font-black text-lg">D</div>
                    <span class="text-xl font-bold">Don<span class="text-transparent bg-clip-text bg-gradient-to-r from-violet-400 to-fuchsia-400">Draper</span></span>
                </Link>

                <h2 class="text-4xl font-black mb-6 leading-tight">
                    Welcome back,<br>
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-violet-400 to-fuchsia-400">Creator.</span>
                </h2>
                <p class="text-gray-400 text-lg mb-12">Sign in to continue building your AI-powered creative portfolio.</p>

                <div class="space-y-4">
                    <div v-for="item in [
                        { icon: '🎨', text: 'Generate images with DALL-E 3 & Stable Diffusion' },
                        { icon: '⚙️', text: 'Full control over 15+ generation attributes' },
                        { icon: '🗂️', text: 'Access your entire creation gallery' },
                    ]" :key="item.text" class="flex items-center gap-4">
                        <span class="text-2xl">{{ item.icon }}</span>
                        <span class="text-gray-300 text-sm">{{ item.text }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Panel - Login Form -->
        <div class="flex-1 lg:max-w-md flex flex-col justify-center px-8 py-16">
            <!-- Mobile logo -->
            <div class="lg:hidden flex items-center gap-3 mb-10">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-violet-500 to-fuchsia-600 flex items-center justify-center font-black text-lg">D</div>
                <span class="text-xl font-bold">Don<span class="text-transparent bg-clip-text bg-gradient-to-r from-violet-400 to-fuchsia-400">Draper</span></span>
            </div>

            <h1 class="text-3xl font-black mb-2">Sign In</h1>
            <p class="text-gray-400 mb-8">Don't have an account? <Link :href="route('register')" class="text-violet-400 hover:text-violet-300 font-medium">Create one free</Link></p>

            <div v-if="status" class="mb-6 bg-emerald-500/10 border border-emerald-500/30 rounded-xl px-4 py-3 text-sm text-emerald-400">
                {{ status }}
            </div>

            <form @submit.prevent="submit" class="space-y-5">
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-300 mb-2">Email address</label>
                    <input
                        id="email"
                        v-model="form.email"
                        type="email"
                        required
                        autofocus
                        autocomplete="username"
                        placeholder="you@example.com"
                        class="w-full bg-white/5 border border-white/10 focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 rounded-xl px-4 py-3 text-white placeholder-gray-600 outline-none transition-all"
                    />
                    <InputError :message="form.errors.email" class="mt-2 text-sm text-rose-400" />
                </div>

                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label for="password" class="block text-sm font-medium text-gray-300">Password</label>
                        <Link v-if="canResetPassword" :href="route('password.request')" class="text-sm text-violet-400 hover:text-violet-300">Forgot?</Link>
                    </div>
                    <input
                        id="password"
                        v-model="form.password"
                        type="password"
                        required
                        autocomplete="current-password"
                        placeholder="••••••••"
                        class="w-full bg-white/5 border border-white/10 focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 rounded-xl px-4 py-3 text-white placeholder-gray-600 outline-none transition-all"
                    />
                    <InputError :message="form.errors.password" class="mt-2 text-sm text-rose-400" />
                </div>

                <div class="flex items-center gap-3">
                    <input id="remember" v-model="form.remember" type="checkbox"
                        class="w-4 h-4 rounded border-white/20 bg-white/5 text-violet-500 focus:ring-violet-500/20 focus:ring-2" />
                    <label for="remember" class="text-sm text-gray-400">Remember me</label>
                </div>

                <button
                    type="submit"
                    :disabled="form.processing"
                    class="w-full flex items-center justify-center gap-2 bg-gradient-to-r from-violet-500 to-fuchsia-600 hover:from-violet-400 hover:to-fuchsia-500 disabled:opacity-50 disabled:cursor-not-allowed text-white font-bold py-3.5 rounded-xl transition-all shadow-lg shadow-violet-500/25 hover:shadow-violet-500/40 hover:scale-[1.02]"
                >
                    <svg v-if="form.processing" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    {{ form.processing ? 'Signing in...' : 'Sign In' }}
                </button>
            </form>

            <p class="mt-8 text-center text-sm text-gray-600">
                By signing in, you agree to our
                <a href="#" class="text-gray-400 hover:text-white">Terms</a> and
                <a href="#" class="text-gray-400 hover:text-white">Privacy Policy</a>.
            </p>
        </div>
    </div>
</template>
