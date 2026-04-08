<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import InputError from '@/Components/InputError.vue'

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
})

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    })
}
</script>

<template>
    <Head title="Create Account — DonDraper" />

    <div class="min-h-screen bg-gray-950 text-white flex">
        <!-- Left Panel -->
        <div class="hidden lg:flex flex-1 relative overflow-hidden bg-[#070B14]">
            <div class="absolute inset-0 bg-gradient-to-br from-cyan-900/20 via-transparent to-sky-900/15"></div>
            <div class="absolute inset-0 bg-[linear-gradient(rgba(255,255,255,0.015)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,0.015)_1px,transparent_1px)] bg-[size:52px_52px]"></div>
            <div class="absolute top-20 left-10 w-[500px] h-[500px] rounded-full bg-cyan-600/10 blur-[120px]"></div>
            <div class="absolute bottom-10 right-10 w-[400px] h-[400px] rounded-full bg-sky-600/10 blur-[100px]"></div>

            <div class="relative flex flex-col justify-center px-14 max-w-md">
                <Link :href="route('home')" class="flex items-center gap-3 mb-16">
                    <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-sky-500 to-cyan-600 flex items-center justify-center font-black">D</div>
                    <span class="text-lg font-bold">Don<span class="text-transparent bg-clip-text bg-gradient-to-r from-sky-400 to-cyan-400">Draper</span></span>
                </Link>

                <h2 class="text-4xl font-black mb-5 leading-tight">
                    Start creating<br>
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-sky-400 to-cyan-400">for free today.</span>
                </h2>
                <p class="text-gray-400 mb-10 leading-relaxed">Join 150,000+ creators already using DonDraper to build stunning visuals.</p>

                <div class="bg-sky-500/8 border border-sky-500/20 rounded-2xl p-5 mb-8">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-8 h-8 rounded-xl bg-sky-500/15 flex items-center justify-center">
                            <svg class="w-4 h-4 text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        </div>
                        <div class="text-xl font-black text-transparent bg-clip-text bg-gradient-to-r from-sky-400 to-cyan-400">10 Free Credits</div>
                    </div>
                    <p class="text-sm text-gray-400">Get 10 image generations on us when you create your account. No credit card required.</p>
                </div>

                <div class="space-y-3">
                    <div v-for="item in [
                        { text: 'No credit card required' },
                        { text: 'Access to all AI models' },
                        { text: 'Your creations, your rights' },
                    ]" :key="item.text" class="flex items-center gap-3 text-sm text-gray-300">
                        <svg class="w-4 h-4 text-emerald-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        {{ item.text }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Panel -->
        <div class="flex-1 lg:max-w-[420px] flex flex-col justify-center px-8 py-16 overflow-y-auto bg-gray-950">
            <div class="lg:hidden flex items-center gap-3 mb-10">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-sky-500 to-cyan-600 flex items-center justify-center font-black">D</div>
                <span class="text-lg font-bold">Don<span class="text-transparent bg-clip-text bg-gradient-to-r from-sky-400 to-cyan-400">Draper</span></span>
            </div>

            <h1 class="text-2xl font-black mb-1.5 text-white">Create Account</h1>
            <p class="text-gray-400 text-sm mb-8">
                Already have an account?
                <Link :href="route('login')" class="text-sky-400 hover:text-sky-300 font-medium transition-colors">Sign in</Link>
            </p>

            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <label for="name" class="block text-xs font-semibold text-gray-400 mb-2 uppercase tracking-wider">Full name</label>
                    <input
                        id="name"
                        v-model="form.name"
                        type="text"
                        required
                        autofocus
                        autocomplete="name"
                        placeholder="Don Draper"
                        class="w-full bg-white/4 border border-white/8 focus:border-sky-500/60 focus:ring-2 focus:ring-sky-500/12 rounded-xl px-4 py-3 text-white placeholder-gray-600 outline-none transition-all text-sm"
                    />
                    <InputError :message="form.errors.name" class="mt-2 text-sm text-rose-400" />
                </div>

                <div>
                    <label for="email" class="block text-xs font-semibold text-gray-400 mb-2 uppercase tracking-wider">Email address</label>
                    <input
                        id="email"
                        v-model="form.email"
                        type="email"
                        required
                        autocomplete="username"
                        placeholder="you@example.com"
                        class="w-full bg-white/4 border border-white/8 focus:border-sky-500/60 focus:ring-2 focus:ring-sky-500/12 rounded-xl px-4 py-3 text-white placeholder-gray-600 outline-none transition-all text-sm"
                    />
                    <InputError :message="form.errors.email" class="mt-2 text-sm text-rose-400" />
                </div>

                <div>
                    <label for="password" class="block text-xs font-semibold text-gray-400 mb-2 uppercase tracking-wider">Password</label>
                    <input
                        id="password"
                        v-model="form.password"
                        type="password"
                        required
                        autocomplete="new-password"
                        placeholder="Min. 8 characters"
                        class="w-full bg-white/4 border border-white/8 focus:border-sky-500/60 focus:ring-2 focus:ring-sky-500/12 rounded-xl px-4 py-3 text-white placeholder-gray-600 outline-none transition-all text-sm"
                    />
                    <InputError :message="form.errors.password" class="mt-2 text-sm text-rose-400" />
                </div>

                <div>
                    <label for="password_confirmation" class="block text-xs font-semibold text-gray-400 mb-2 uppercase tracking-wider">Confirm password</label>
                    <input
                        id="password_confirmation"
                        v-model="form.password_confirmation"
                        type="password"
                        required
                        autocomplete="new-password"
                        placeholder="••••••••"
                        class="w-full bg-white/4 border border-white/8 focus:border-sky-500/60 focus:ring-2 focus:ring-sky-500/12 rounded-xl px-4 py-3 text-white placeholder-gray-600 outline-none transition-all text-sm"
                    />
                    <InputError :message="form.errors.password_confirmation" class="mt-2 text-sm text-rose-400" />
                </div>

                <button
                    type="submit"
                    :disabled="form.processing"
                    class="w-full flex items-center justify-center gap-2 bg-gradient-to-r from-sky-500 to-cyan-600 hover:from-sky-400 hover:to-cyan-500 disabled:opacity-50 disabled:cursor-not-allowed text-white font-bold py-3.5 rounded-xl transition-all shadow-lg shadow-sky-500/20 hover:shadow-sky-500/35 hover:scale-[1.02] mt-2">
                    <svg v-if="form.processing" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    {{ form.processing ? 'Creating account...' : 'Create Account — Free' }}
                </button>
            </form>

            <p class="mt-8 text-center text-xs text-gray-600">
                By creating an account, you agree to our
                <a href="#" class="text-gray-500 hover:text-gray-300 transition-colors">Terms</a> and
                <a href="#" class="text-gray-500 hover:text-gray-300 transition-colors">Privacy Policy</a>.
            </p>
        </div>
    </div>
</template>
