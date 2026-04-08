<script setup>
import { useForm } from '@inertiajs/vue3'
import { ref } from 'vue'

const passwordInput = ref(null)
const currentPasswordInput = ref(null)

const showCurrent = ref(false)
const showNew = ref(false)
const showConfirm = ref(false)

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
})

const updatePassword = () => {
    form.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
        onError: () => {
            if (form.errors.password) {
                form.reset('password', 'password_confirmation')
                passwordInput.value?.focus()
            }
            if (form.errors.current_password) {
                form.reset('current_password')
                currentPasswordInput.value?.focus()
            }
        },
    })
}
</script>

<template>
    <section>
        <h2 class="text-lg font-bold text-white mb-1">Update Password</h2>
        <p class="text-sm text-gray-500 mb-6">Ensure your account is using a long, random password to stay secure.</p>

        <form @submit.prevent="updatePassword" class="space-y-5">
            <div>
                <label for="current_password" class="block text-xs font-semibold text-gray-400 mb-2 uppercase tracking-wider">Current Password</label>
                <div class="relative">
                    <input id="current_password" ref="currentPasswordInput" v-model="form.current_password"
                        :type="showCurrent ? 'text' : 'password'" autocomplete="current-password"
                        style="background-color: rgba(255,255,255,0.04); color: #fff;"
                        class="profile-input w-full border border-white/8 focus:border-sky-500/50 focus:ring-2 focus:ring-sky-500/10 rounded-xl px-4 py-3 pr-11 placeholder-gray-600 outline-none transition-all text-sm" />
                    <button type="button" @click="showCurrent = !showCurrent"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-300 transition-colors">
                        <svg v-if="showCurrent" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L6.59 6.59m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>
                        <svg v-else class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    </button>
                </div>
                <p v-if="form.errors.current_password" class="mt-1.5 text-xs text-rose-400">{{ form.errors.current_password }}</p>
            </div>

            <div>
                <label for="password" class="block text-xs font-semibold text-gray-400 mb-2 uppercase tracking-wider">New Password</label>
                <div class="relative">
                    <input id="password" ref="passwordInput" v-model="form.password"
                        :type="showNew ? 'text' : 'password'" autocomplete="new-password"
                        style="background-color: rgba(255,255,255,0.04); color: #fff;"
                        class="profile-input w-full border border-white/8 focus:border-sky-500/50 focus:ring-2 focus:ring-sky-500/10 rounded-xl px-4 py-3 pr-11 placeholder-gray-600 outline-none transition-all text-sm" />
                    <button type="button" @click="showNew = !showNew"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-300 transition-colors">
                        <svg v-if="showNew" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L6.59 6.59m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>
                        <svg v-else class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    </button>
                </div>
                <p v-if="form.errors.password" class="mt-1.5 text-xs text-rose-400">{{ form.errors.password }}</p>
            </div>

            <div>
                <label for="password_confirmation" class="block text-xs font-semibold text-gray-400 mb-2 uppercase tracking-wider">Confirm Password</label>
                <div class="relative">
                    <input id="password_confirmation" v-model="form.password_confirmation"
                        :type="showConfirm ? 'text' : 'password'" autocomplete="new-password"
                        style="background-color: rgba(255,255,255,0.04); color: #fff;"
                        class="profile-input w-full border border-white/8 focus:border-sky-500/50 focus:ring-2 focus:ring-sky-500/10 rounded-xl px-4 py-3 pr-11 placeholder-gray-600 outline-none transition-all text-sm" />
                    <button type="button" @click="showConfirm = !showConfirm"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-300 transition-colors">
                        <svg v-if="showConfirm" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L6.59 6.59m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>
                        <svg v-else class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    </button>
                </div>
                <p v-if="form.errors.password_confirmation" class="mt-1.5 text-xs text-rose-400">{{ form.errors.password_confirmation }}</p>
            </div>

            <div class="flex items-center gap-3 pt-1">
                <button type="submit" :disabled="form.processing"
                    class="bg-gradient-to-r from-sky-500 to-cyan-600 hover:from-sky-400 hover:to-cyan-500 disabled:opacity-50 text-white font-semibold px-5 py-2.5 rounded-xl text-sm transition-all shadow-lg shadow-sky-500/15">
                    Update Password
                </button>
                <Transition enter-active-class="transition ease-in-out" enter-from-class="opacity-0" leave-active-class="transition ease-in-out" leave-to-class="opacity-0">
                    <span v-if="form.recentlySuccessful" class="text-sm text-emerald-400">Saved.</span>
                </Transition>
            </div>
        </form>
    </section>
</template>

<style scoped>
.profile-input,
.profile-input:autofill,
.profile-input:-webkit-autofill {
    background-color: rgba(255,255,255,0.04) !important;
    color: #fff !important;
    -webkit-text-fill-color: #fff !important;
    -webkit-box-shadow: 0 0 0 30px #0A0E1A inset !important;
    caret-color: #fff;
}
</style>
