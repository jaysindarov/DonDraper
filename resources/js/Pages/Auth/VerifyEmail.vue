<script setup>
import { computed } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'

const props = defineProps({ status: String })

const form = useForm({})
const submit = () => form.post(route('verification.send'))
const verificationLinkSent = computed(() => props.status === 'verification-link-sent')
</script>

<template>
    <Head title="Verify Email — DonDraper" />

    <div class="min-h-screen bg-gray-950 text-white flex items-center justify-center px-6">
        <div class="w-full max-w-sm text-center">
            <div class="flex items-center gap-2.5 mb-10 justify-center">
                <div class="w-8 h-8 rounded-xl bg-gradient-to-br from-sky-500 to-cyan-600 flex items-center justify-center font-black text-sm">D</div>
                <span class="font-bold">Don<span class="text-transparent bg-clip-text bg-gradient-to-r from-sky-400 to-cyan-400">Draper</span></span>
            </div>

            <div class="w-14 h-14 rounded-2xl bg-sky-500/10 border border-sky-500/20 flex items-center justify-center mx-auto mb-5">
                <svg class="w-7 h-7 text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
            </div>

            <h1 class="text-2xl font-black mb-3">Verify Your Email</h1>
            <p class="text-gray-400 text-sm mb-8 leading-relaxed">
                Thanks for signing up! Please click the verification link we sent to your email before getting started.
            </p>

            <div v-if="verificationLinkSent" class="mb-6 bg-emerald-500/8 border border-emerald-500/25 rounded-xl px-4 py-3 text-sm text-emerald-400">
                A new verification link has been sent to your email address.
            </div>

            <form @submit.prevent="submit">
                <button type="submit" :disabled="form.processing"
                    class="w-full flex items-center justify-center gap-2 bg-gradient-to-r from-sky-500 to-cyan-600 hover:from-sky-400 hover:to-cyan-500 disabled:opacity-50 text-white font-bold py-3.5 rounded-xl transition-all shadow-lg shadow-sky-500/20 mb-4">
                    <svg v-if="form.processing" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    {{ form.processing ? 'Sending...' : 'Resend Verification Email' }}
                </button>
            </form>

            <Link :href="route('logout')" method="post" as="button"
                class="text-sm text-gray-500 hover:text-gray-300 transition-colors">
                Log Out
            </Link>
        </div>
    </div>
</template>
