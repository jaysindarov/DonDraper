<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import InputError from '@/Components/InputError.vue'

defineProps({ status: String })

const form = useForm({ email: '' })
const submit = () => form.post(route('password.email'))
</script>

<template>
    <Head title="Forgot Password — DonDraper" />

    <div class="min-h-screen bg-gray-950 text-white flex items-center justify-center px-6">
        <div class="w-full max-w-sm">
            <Link :href="route('home')" class="flex items-center gap-2.5 mb-10 justify-center">
                <div class="w-8 h-8 rounded-xl bg-gradient-to-br from-sky-500 to-cyan-600 flex items-center justify-center font-black text-sm">D</div>
                <span class="font-bold">Don<span class="text-transparent bg-clip-text bg-gradient-to-r from-sky-400 to-cyan-400">Draper</span></span>
            </Link>

            <h1 class="text-2xl font-black mb-2 text-center">Forgot Password</h1>
            <p class="text-gray-400 text-sm text-center mb-8">Enter your email and we'll send you a reset link.</p>

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

                <button type="submit" :disabled="form.processing"
                    class="w-full flex items-center justify-center gap-2 bg-gradient-to-r from-sky-500 to-cyan-600 hover:from-sky-400 hover:to-cyan-500 disabled:opacity-50 text-white font-bold py-3.5 rounded-xl transition-all shadow-lg shadow-sky-500/20">
                    <svg v-if="form.processing" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    {{ form.processing ? 'Sending...' : 'Send Reset Link' }}
                </button>
            </form>

            <p class="mt-8 text-center text-sm text-gray-500">
                Remember it?
                <Link :href="route('login')" class="text-sky-400 hover:text-sky-300 font-medium transition-colors">Sign in</Link>
            </p>
        </div>
    </div>
</template>
