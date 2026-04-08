<script setup>
import { Link, useForm, usePage } from '@inertiajs/vue3'
import { ref, computed } from 'vue'

defineProps({
    mustVerifyEmail: Boolean,
    status: String,
})

const user = usePage().props.auth.user
const avatarPreview = ref(null)

const form = useForm({
    name: user.name,
    email: user.email,
    avatar: null,
})

const currentAvatar = computed(() => {
    if (avatarPreview.value) return avatarPreview.value
    if (user.avatar) return `/storage/${user.avatar}`
    return null
})

const handleAvatar = (e) => {
    const file = e.target.files[0]
    if (!file) return
    form.avatar = file
    const reader = new FileReader()
    reader.onload = (ev) => { avatarPreview.value = ev.target.result }
    reader.readAsDataURL(file)
}

const submit = () => {
    form.post(route('profile.update'), {
        _method: 'patch',
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            form.avatar = null
        },
    })
}
</script>

<template>
    <section>
        <h2 class="text-lg font-bold text-white mb-1">Profile Information</h2>
        <p class="text-sm text-gray-500 mb-6">Update your profile picture, name, and email address.</p>

        <form @submit.prevent="submit" class="space-y-5">
            <!-- Avatar -->
            <div class="flex items-center gap-5">
                <div class="relative group">
                    <div v-if="currentAvatar"
                        class="w-20 h-20 rounded-2xl overflow-hidden border-2 border-white/10">
                        <img :src="currentAvatar" class="w-full h-full object-cover" />
                    </div>
                    <div v-else
                        class="w-20 h-20 rounded-2xl bg-gradient-to-br from-sky-500 to-cyan-600 flex items-center justify-center text-2xl font-black text-white border-2 border-white/10">
                        {{ user.name?.charAt(0).toUpperCase() }}
                    </div>
                    <label class="absolute inset-0 flex items-center justify-center bg-black/50 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity cursor-pointer">
                        <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <input type="file" accept="image/*" class="hidden" @change="handleAvatar" />
                    </label>
                </div>
                <div>
                    <p class="text-sm font-medium text-white">Profile Picture</p>
                    <p class="text-xs text-gray-500 mt-0.5">JPG, PNG or WEBP. Max 2MB.</p>
                </div>
            </div>

            <!-- Name -->
            <div>
                <label for="name" class="block text-xs font-semibold text-gray-400 mb-2 uppercase tracking-wider">Name</label>
                <input id="name" v-model="form.name" type="text" required autocomplete="name"
                    style="background-color: rgba(255,255,255,0.04); color: #fff;"
                    class="profile-input w-full border border-white/8 focus:border-sky-500/50 focus:ring-2 focus:ring-sky-500/10 rounded-xl px-4 py-3 placeholder-gray-600 outline-none transition-all text-sm" />
                <p v-if="form.errors.name" class="mt-1.5 text-xs text-rose-400">{{ form.errors.name }}</p>
            </div>

            <!-- Email -->
            <div>
                <label for="email" class="block text-xs font-semibold text-gray-400 mb-2 uppercase tracking-wider">Email</label>
                <input id="email" v-model="form.email" type="email" required autocomplete="username"
                    style="background-color: rgba(255,255,255,0.04); color: #fff;"
                    class="profile-input w-full border border-white/8 focus:border-sky-500/50 focus:ring-2 focus:ring-sky-500/10 rounded-xl px-4 py-3 placeholder-gray-600 outline-none transition-all text-sm" />
                <p v-if="form.errors.email" class="mt-1.5 text-xs text-rose-400">{{ form.errors.email }}</p>
            </div>

            <div v-if="mustVerifyEmail && !user.email_verified_at">
                <p class="text-sm text-gray-400">
                    Your email address is unverified.
                    <Link :href="route('verification.send')" method="post" as="button"
                        class="text-sky-400 hover:text-sky-300 underline transition-colors">
                        Click here to re-send the verification email.
                    </Link>
                </p>
                <div v-if="status === 'verification-link-sent'" class="mt-2 text-sm text-emerald-400">
                    A new verification link has been sent to your email address.
                </div>
            </div>

            <div class="flex items-center gap-3 pt-1">
                <button type="submit" :disabled="form.processing"
                    class="bg-gradient-to-r from-sky-500 to-cyan-600 hover:from-sky-400 hover:to-cyan-500 disabled:opacity-50 text-white font-semibold px-5 py-2.5 rounded-xl text-sm transition-all shadow-lg shadow-sky-500/15">
                    Save Changes
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
