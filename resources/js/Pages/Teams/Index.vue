<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { ref } from 'vue'

const props = defineProps({
    teams: Array,
    currentTeam: Object,
})

const showCreate = ref(false)
const form = useForm({ name: '' })

function create() {
    form.post(route('teams.store'), {
        onSuccess: () => { form.reset(); showCreate.value = false },
    })
}
</script>

<template>
    <Head title="Teams — DonDraper" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-bold text-white">Team Workspaces</h2>
        </template>

        <div class="py-8 px-6 max-w-4xl mx-auto space-y-6">
            <!-- Current team banner -->
            <div v-if="currentTeam" class="bg-violet-500/10 border border-violet-500/20 rounded-2xl p-4 flex items-center justify-between">
                <div>
                    <p class="text-xs text-violet-400 font-medium">Active workspace</p>
                    <p class="text-white font-semibold mt-0.5">{{ currentTeam.name }}</p>
                </div>
                <Link :href="route('teams.show', currentTeam.id)"
                    class="text-sm text-violet-400 hover:text-violet-300 transition-colors">
                    View →
                </Link>
            </div>

            <!-- Teams list -->
            <div class="space-y-3">
                <div v-for="team in teams" :key="team.id"
                    class="bg-gray-900/50 border border-white/5 rounded-2xl p-5 flex items-center justify-between">
                    <div>
                        <p class="text-white font-semibold">{{ team.name }}</p>
                        <p class="text-gray-500 text-sm">{{ team.members_count }} members · {{ team.pivot?.role }}</p>
                    </div>
                    <div class="flex gap-2">
                        <form :action="route('teams.switch', team.id)" method="POST" @submit.prevent="$inertia.post(route('teams.switch', team.id))">
                            <button type="submit"
                                :class="['text-xs px-3 py-1.5 rounded-lg border transition-all',
                                    currentTeam?.id === team.id
                                        ? 'border-violet-500/30 text-violet-400 bg-violet-500/10'
                                        : 'border-white/10 text-gray-400 hover:text-white hover:border-white/20']">
                                {{ currentTeam?.id === team.id ? 'Active' : 'Switch' }}
                            </button>
                        </form>
                        <Link :href="route('teams.show', team.id)"
                            class="text-xs px-3 py-1.5 rounded-lg border border-white/10 text-gray-400 hover:text-white hover:border-white/20 transition-all">
                            Manage
                        </Link>
                    </div>
                </div>

                <div v-if="!teams.length" class="text-center py-12 text-gray-500">
                    No teams yet. Create one to collaborate with your team.
                </div>
            </div>

            <!-- Create team -->
            <div>
                <button v-if="!showCreate" @click="showCreate = true"
                    class="flex items-center gap-2 bg-white/5 hover:bg-white/10 border border-white/10 text-white text-sm font-medium px-4 py-2.5 rounded-xl transition-all">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    New Team
                </button>

                <div v-else class="bg-gray-900/50 border border-white/5 rounded-2xl p-5 space-y-4">
                    <h3 class="text-white font-semibold">Create Team</h3>
                    <form @submit.prevent="create" class="flex gap-3">
                        <input v-model="form.name" type="text" placeholder="Team name..."
                            class="flex-1 bg-gray-800/50 border border-white/10 rounded-xl px-4 py-2.5 text-white text-sm placeholder-gray-600 focus:outline-none focus:border-violet-500/50" />
                        <button type="submit" :disabled="form.processing"
                            class="bg-violet-600 hover:bg-violet-500 disabled:opacity-50 text-white text-sm font-medium px-5 py-2.5 rounded-xl transition-all">
                            Create
                        </button>
                        <button type="button" @click="showCreate = false"
                            class="text-gray-500 hover:text-white text-sm px-4 py-2.5 rounded-xl transition-all">
                            Cancel
                        </button>
                    </form>
                    <p v-if="form.errors.plan" class="text-rose-400 text-sm">{{ form.errors.plan }}</p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
