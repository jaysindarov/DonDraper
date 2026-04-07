<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { ref, computed } from 'vue'

const props = defineProps({
    tokens: Array,
})

const page = usePage()
const newToken = computed(() => page.props.flash?.token ?? null)
const copied = ref(false)

const form = useForm({ name: '' })

function createToken() {
    form.post(route('api-tokens.store'), {
        onSuccess: () => form.reset(),
    })
}

function revokeToken(id) {
    if (!confirm('Revoke this token?')) return
    form.delete(route('api-tokens.destroy', id))
}

function copyToken() {
    navigator.clipboard.writeText(newToken.value)
    copied.value = true
    setTimeout(() => copied.value = false, 2000)
}
</script>

<template>
    <Head title="API Tokens — DonDraper" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link :href="route('profile.edit')" class="text-gray-400 hover:text-white transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </Link>
                <h2 class="text-xl font-bold text-white">API Tokens</h2>
            </div>
        </template>

        <div class="py-8 px-6 max-w-3xl mx-auto space-y-6">
            <!-- New token banner -->
            <div v-if="newToken" class="bg-emerald-500/10 border border-emerald-500/30 rounded-2xl p-5">
                <p class="text-sm text-emerald-400 font-medium mb-2">Token created — copy it now, you won't see it again.</p>
                <div class="flex gap-3 items-center">
                    <code class="flex-1 text-xs bg-black/30 text-emerald-300 px-3 py-2 rounded-lg font-mono break-all">
                        {{ newToken }}
                    </code>
                    <button @click="copyToken"
                        class="text-xs bg-emerald-500/20 hover:bg-emerald-500/30 text-emerald-400 border border-emerald-500/30 px-3 py-2 rounded-lg transition-all whitespace-nowrap">
                        {{ copied ? 'Copied!' : 'Copy' }}
                    </button>
                </div>
            </div>

            <!-- Create token -->
            <div class="bg-gray-900/50 border border-white/5 rounded-2xl p-5 space-y-4">
                <h3 class="text-sm font-semibold text-white">Create New Token</h3>
                <form @submit.prevent="createToken" class="flex gap-3">
                    <input v-model="form.name" type="text" placeholder="Token name (e.g. My App)"
                        class="flex-1 bg-gray-800/50 border border-white/10 rounded-xl px-4 py-2.5 text-white text-sm placeholder-gray-600 focus:outline-none focus:border-violet-500/50" />
                    <button type="submit" :disabled="form.processing"
                        class="bg-violet-600 hover:bg-violet-500 disabled:opacity-50 text-white text-sm font-medium px-5 py-2.5 rounded-xl transition-all">
                        Create
                    </button>
                </form>
                <p v-if="form.errors.name" class="text-rose-400 text-sm">{{ form.errors.name }}</p>
            </div>

            <!-- Tokens list -->
            <div v-if="tokens.length" class="bg-gray-900/50 border border-white/5 rounded-2xl overflow-hidden">
                <div class="p-5 border-b border-white/5">
                    <h3 class="text-sm font-semibold text-white">Active Tokens</h3>
                </div>
                <div class="divide-y divide-white/5">
                    <div v-for="token in tokens" :key="token.id"
                        class="flex items-center justify-between px-5 py-3">
                        <div>
                            <p class="text-sm text-white">{{ token.name }}</p>
                            <p class="text-xs text-gray-500">
                                Last used: {{ token.last_used_at
                                    ? new Date(token.last_used_at).toLocaleDateString()
                                    : 'Never' }}
                                · Created {{ new Date(token.created_at).toLocaleDateString() }}
                            </p>
                        </div>
                        <button @click="revokeToken(token.id)"
                            class="text-xs text-rose-400 hover:text-rose-300 transition-colors">
                            Revoke
                        </button>
                    </div>
                </div>
            </div>

            <div v-else class="text-center py-8 text-gray-500 text-sm">
                No tokens yet. Create one to access the API.
            </div>

            <!-- API docs hint -->
            <div class="bg-gray-900/30 border border-white/5 rounded-2xl p-5 text-sm text-gray-500">
                <p class="text-gray-400 font-medium mb-2">Using the API</p>
                <p>Pass your token in the <code class="text-violet-400">Authorization</code> header:</p>
                <code class="block mt-2 bg-black/30 text-violet-300 px-3 py-2 rounded-lg font-mono text-xs">
                    Authorization: Bearer YOUR_TOKEN
                </code>
                <p class="mt-2">Base URL: <code class="text-violet-400">/api/v1</code> — requires Pro or Enterprise plan.</p>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
