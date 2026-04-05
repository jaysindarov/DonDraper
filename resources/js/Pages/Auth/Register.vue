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
        <div class="hidden lg:flex flex-1 relative overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-br from-fuchsia-900/40 via-gray-950 to-violet-900/30"></div>
            <div class="absolute inset-0 bg-[linear-gradient(rgba(255,255,255,0.02)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,0.02)_1px,transparent_1px)] bg-[size:50px_50px]"></div>
            <div class="absolute top-20 left-10 w-[500px] h-[500px] rounded-full bg-fuchsia-600/15 blur-[100px]"></div>
            <div class="absolute bottom-10 right-10 w-[400px] h-[400px] rounded-full bg-violet-600/15 blur-[100px]"></div>

            <div class="relative flex flex-col justify-center px-16 max-w-lg">
                <Link :href="route('home')" class="flex items-center gap-3 mb-16">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-violet-500 to-fuchsia-600 flex items-center justify-center font-black text-lg">D</div>
                    <span class="text-xl font-bold">Don<span class="text-transparent bg-clip-text bg-gradient-to-r from-violet-400 to-fuchsia-400">Draper</span></span>
                </Link>

                <h2 class="text-4xl font-black mb-6 leading-tight">
                    Start creating<br>
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-fuchsia-400 to-violet-400">for free today.</span>
                </h2>
                <p class="text-gray-400 text-lg mb-12">Join 150,000+ creators already using DonDraper to build stunning visuals.</p>

                <div class="bg-white/5 border border-white/10 rounded-2xl p-6 mb-6">
                    <div class="text-2xl font-black text-transparent bg-clip-text bg-gradient-to-r from-violet-400 to-fuchsia-400 mb-1">10 Free Credits</div>
                    <p class="text-sm text-gray-400">Get 10 image generations on us when you create your account. No credit card required.</p>
                </div>

                <div class="space-y-3">
                    <div v-for="item in [
                        { icon: '✅', text: 'No credit card required' },
                        { icon: '✅', text: 'Access to all AI models' },
                        { icon: '✅', text: 'Your creations, your rights' },
                    ]" :key="item.text" class="flex items-center gap-3 text-sm text-gray-300">
                        <span>{{ item.icon }}</span>
                        {{ item.text }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Panel - Register Form -->
        <div class="flex-1 lg:max-w-md flex flex-col justify-center px-8 py-16 overflow-y-auto">
            <div class="lg:hidden flex items-center gap-3 mb-10">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-violet-500 to-fuchsia-600 flex items-center justify-center font-black text-lg">D</div>
                <span class="text-xl font-bold">Don<span class="text-transparent bg-clip-text bg-gradient-to-r from-violet-400 to-fuchsia-400">Draper</span></span>
            </div>

            <h1 class="text-3xl font-black mb-2">Create Account</h1>
            <p class="text-gray-400 mb-8">Already have an account? <Link :href="route('login')" class="text-violet-400 hover:text-violet-300 font-medium">Sign in</Link></p>

            <form @submit.prevent="submit" class="space-y-5">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-300 mb-2">Full name</label>
                    <input
                        id="name"
                        v-model="form.name"
                        type="text"
                        required
                        autofocus
                        autocomplete="name"
                        placeholder="Don Draper"
                        class="w-full bg-white/5 border border-white/10 focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 rounded-xl px-4 py-3 text-white placeholder-gray-600 outline-none transition-all"
                    />
                    <InputError :message="form.errors.name" class="mt-2 text-sm text-rose-400" />
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-300 mb-2">Email address</label>
                    <input
                        id="email"
                        v-model="form.email"
                        type="email"
                        required
                        autocomplete="username"
                        placeholder="you@example.com"
                        class="w-full bg-white/5 border border-white/10 focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 rounded-xl px-4 py-3 text-white placeholder-gray-600 outline-none transition-all"
                    />
                    <InputError :message="form.errors.email" class="mt-2 text-sm text-rose-400" />
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-300 mb-2">Password</label>
                    <input
                        id="password"
                        v-model="form.password"
                        type="password"
                        required
                        autocomplete="new-password"
                        placeholder="Min. 8 characters"
                        class="w-full bg-white/5 border border-white/10 focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 rounded-xl px-4 py-3 text-white placeholder-gray-600 outline-none transition-all"
                    />
                    <InputError :message="form.errors.password" class="mt-2 text-sm text-rose-400" />
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-300 mb-2">Confirm password</label>
                    <input
                        id="password_confirmation"
                        v-model="form.password_confirmation"
                        type="password"
                        required
                        autocomplete="new-password"
                        placeholder="••••••••"
                        class="w-full bg-white/5 border border-white/10 focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 rounded-xl px-4 py-3 text-white placeholder-gray-600 outline-none transition-all"
                    />
                    <InputError :message="form.errors.password_confirmation" class="mt-2 text-sm text-rose-400" />
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
                    {{ form.processing ? 'Creating account...' : 'Create Account — Free' }}
                </button>
            </form>

            <p class="mt-8 text-center text-sm text-gray-600">
                By creating an account, you agree to our
                <a href="#" class="text-gray-400 hover:text-white">Terms</a> and
                <a href="#" class="text-gray-400 hover:text-white">Privacy Policy</a>.
            </p>
        </div>
    </div>
</template>
