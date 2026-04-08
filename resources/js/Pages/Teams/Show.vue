<script setup>
import { Head, Link, useForm, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { ref } from 'vue'

const props = defineProps({
    team: Object,
    invitations: Array,
})

const inviteForm = useForm({ email: '' })

function invite() {
    inviteForm.post(route('teams.invite', props.team.id), {
        onSuccess: () => inviteForm.reset(),
    })
}

function deleteTeam() {
    if (!confirm(`Delete "${props.team.name}"? This cannot be undone.`)) return
    router.delete(route('teams.destroy', props.team.id))
}
</script>

<template>
    <Head :title="`${team.name} — DonDraper`" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link :href="route('teams.index')" class="text-gray-400 hover:text-white transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </Link>
                <h2 class="text-xl font-bold text-white">{{ team.name }}</h2>
            </div>
        </template>

        <div class="py-8 px-6 max-w-4xl mx-auto space-y-6">
            <!-- Members -->
            <div class="bg-gray-900/50 border border-white/5 rounded-2xl overflow-hidden">
                <div class="p-5 border-b border-white/5">
                    <h3 class="text-sm font-semibold text-white">Members ({{ team.members?.length }})</h3>
                </div>
                <div class="divide-y divide-white/5">
                    <div v-for="member in team.members" :key="member.id"
                        class="flex items-center justify-between px-5 py-3">
                        <div>
                            <p class="text-sm text-white">{{ member.name }}</p>
                            <p class="text-xs text-gray-500">{{ member.email }}</p>
                        </div>
                        <span class="text-xs text-gray-400 capitalize">{{ member.pivot?.role }}</span>
                    </div>
                </div>
            </div>

            <!-- Invite -->
            <div class="bg-gray-900/50 border border-white/5 rounded-2xl p-5 space-y-4">
                <h3 class="text-sm font-semibold text-white">Invite Member</h3>
                <form @submit.prevent="invite" class="flex gap-3">
                    <input v-model="inviteForm.email" type="email" placeholder="colleague@company.com"
                        class="flex-1 bg-gray-800/50 border border-white/10 rounded-xl px-4 py-2.5 text-white text-sm placeholder-gray-600 focus:outline-none focus:border-sky-500/50" />
                    <button type="submit" :disabled="inviteForm.processing"
                        class="bg-sky-600 hover:bg-sky-500 disabled:opacity-50 text-white text-sm font-medium px-5 py-2.5 rounded-xl transition-all">
                        Send Invite
                    </button>
                </form>
                <p v-if="inviteForm.errors.email" class="text-rose-400 text-sm">{{ inviteForm.errors.email }}</p>
            </div>

            <!-- Pending invitations -->
            <div v-if="invitations?.length" class="bg-gray-900/50 border border-white/5 rounded-2xl overflow-hidden">
                <div class="p-5 border-b border-white/5">
                    <h3 class="text-sm font-semibold text-white">Pending Invitations</h3>
                </div>
                <div class="divide-y divide-white/5">
                    <div v-for="inv in invitations" :key="inv.id" class="flex items-center justify-between px-5 py-3">
                        <p class="text-sm text-gray-400">{{ inv.email }}</p>
                        <p class="text-xs text-gray-600">Expires {{ new Date(inv.expires_at).toLocaleDateString() }}</p>
                    </div>
                </div>
            </div>

            <!-- Danger zone -->
            <div class="bg-rose-900/10 border border-rose-500/20 rounded-2xl p-5">
                <h3 class="text-sm font-semibold text-rose-400 mb-3">Danger Zone</h3>
                <button @click="deleteTeam"
                    class="text-sm bg-rose-500/10 hover:bg-rose-500/20 border border-rose-500/30 text-rose-400 px-4 py-2 rounded-xl transition-all">
                    Delete Team
                </button>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
