<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({ user: Object })

const form = useForm({
    credits:  props.user.credits,
    plan:     props.user.plan ?? 'free',
    is_admin: props.user.is_admin ?? false,
})

function submit() {
    form.patch(route('admin.users.update', props.user.id))
}
</script>

<template>
    <Head :title="`Admin — Edit ${user.name}`" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link :href="route('admin.users.index')" class="text-gray-400 hover:text-white transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </Link>
                <h2 class="text-xl font-bold text-white">Edit User</h2>
            </div>
        </template>

        <div class="py-8 px-6 max-w-lg mx-auto">
            <div class="bg-gray-900/50 border border-white/5 rounded-2xl p-6 space-y-5">
                <div>
                    <p class="text-white font-semibold">{{ user.name }}</p>
                    <p class="text-gray-500 text-sm">{{ user.email }}</p>
                </div>

                <form @submit.prevent="submit" class="space-y-4">
                    <div>
                        <label class="block text-sm text-gray-400 mb-1.5">Credits</label>
                        <input v-model="form.credits" type="number" min="0"
                            class="w-full bg-gray-800/50 border border-white/10 rounded-xl px-4 py-2.5 text-white text-sm focus:outline-none focus:border-sky-500/50" />
                    </div>

                    <div>
                        <label class="block text-sm text-gray-400 mb-1.5">Plan</label>
                        <select v-model="form.plan"
                            class="w-full bg-gray-800/50 border border-white/10 rounded-xl px-4 py-2.5 text-white text-sm focus:outline-none focus:border-sky-500/50">
                            <option value="free">Free</option>
                            <option value="pro">Pro</option>
                            <option value="enterprise">Enterprise</option>
                        </select>
                    </div>

                    <div class="flex items-center gap-3">
                        <input v-model="form.is_admin" type="checkbox" id="is_admin"
                            class="w-4 h-4 rounded border-white/20 bg-gray-800 text-sky-500 focus:ring-violet-500" />
                        <label for="is_admin" class="text-sm text-gray-400">Admin access</label>
                    </div>

                    <p v-if="form.errors.credits || form.errors.plan" class="text-rose-400 text-sm">
                        {{ form.errors.credits || form.errors.plan }}
                    </p>

                    <button type="submit" :disabled="form.processing"
                        class="w-full bg-gradient-to-r from-sky-500 to-cyan-600 hover:from-sky-400 hover:to-cyan-500 disabled:opacity-50 text-white font-semibold py-3 rounded-xl transition-all">
                        Save Changes
                    </button>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
