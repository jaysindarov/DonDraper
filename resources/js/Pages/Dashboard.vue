<script setup>
import { Head, Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
    recentGenerations: Array,
    stats: Object,
})

const statusColor = (status) => {
    const map = { completed: 'text-emerald-400 bg-emerald-400/10', processing: 'text-blue-400 bg-blue-400/10', pending: 'text-yellow-400 bg-yellow-400/10', failed: 'text-rose-400 bg-rose-400/10' }
    return map[status] || 'text-gray-400 bg-gray-400/10'
}
</script>

<template>
    <Head title="Dashboard — DonDraper" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-bold text-white">Dashboard</h2>
                <Link :href="route('generations.create')" class="flex items-center gap-2 bg-gradient-to-r from-violet-500 to-fuchsia-600 hover:from-violet-400 hover:to-fuchsia-500 text-white font-semibold px-5 py-2.5 rounded-xl transition-all shadow-lg shadow-violet-500/25 text-sm">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    New Generation
                </Link>
            </div>
        </template>

        <div class="py-8 px-6 max-w-7xl mx-auto space-y-8">
            <!-- Stats -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                <div class="bg-gray-900/50 border border-white/5 rounded-2xl p-6">
                    <div class="text-3xl font-black text-transparent bg-clip-text bg-gradient-to-r from-violet-400 to-fuchsia-400 mb-1">{{ stats.total }}</div>
                    <div class="text-sm text-gray-400">Total Generations</div>
                </div>
                <div class="bg-gray-900/50 border border-white/5 rounded-2xl p-6">
                    <div class="text-3xl font-black text-transparent bg-clip-text bg-gradient-to-r from-emerald-400 to-cyan-400 mb-1">{{ stats.completed }}</div>
                    <div class="text-sm text-gray-400">Completed</div>
                </div>
                <div class="bg-gray-900/50 border border-white/5 rounded-2xl p-6">
                    <div class="flex items-center justify-between mb-1">
                        <div class="text-3xl font-black text-transparent bg-clip-text bg-gradient-to-r from-amber-400 to-orange-400">{{ stats.credits }}</div>
                        <span class="text-xs bg-violet-500/20 text-violet-400 border border-violet-500/30 px-2 py-0.5 rounded-full">credits left</span>
                    </div>
                    <div class="text-sm text-gray-400">Available Credits</div>
                    <Link :href="route('home') + '#pricing'" class="text-xs text-violet-400 hover:text-violet-300 mt-1 inline-block">Upgrade plan →</Link>
                </div>
            </div>

            <!-- Quick Create CTA -->
            <div v-if="stats.total === 0" class="bg-gradient-to-br from-violet-600/15 to-fuchsia-600/10 border border-violet-500/30 rounded-3xl p-10 text-center">
                <div class="text-5xl mb-4">🎨</div>
                <h3 class="text-2xl font-black mb-2">Create Your First Image</h3>
                <p class="text-gray-400 mb-6">You have {{ stats.credits }} free credits. Start generating stunning AI images now.</p>
                <Link :href="route('generations.create')" class="inline-flex items-center gap-2 bg-gradient-to-r from-violet-500 to-fuchsia-600 hover:from-violet-400 hover:to-fuchsia-500 text-white font-bold px-6 py-3 rounded-xl transition-all shadow-lg shadow-violet-500/25">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Start Generating
                </Link>
            </div>

            <!-- Recent Generations -->
            <div v-else>
                <div class="flex items-center justify-between mb-5">
                    <h3 class="text-lg font-bold">Recent Generations</h3>
                    <Link :href="route('generations.index')" class="text-sm text-violet-400 hover:text-violet-300">View all →</Link>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                    <div v-for="gen in recentGenerations" :key="gen.id"
                        class="group relative bg-gray-900/50 border border-white/5 hover:border-violet-500/30 rounded-2xl overflow-hidden transition-all hover:-translate-y-1">
                        <!-- Image -->
                        <div class="aspect-square bg-gradient-to-br from-gray-800 to-gray-900 relative overflow-hidden">
                            <img v-if="gen.result_url" :src="gen.result_url" :alt="gen.prompt" class="w-full h-full object-cover" />
                            <div v-else class="w-full h-full flex flex-col items-center justify-center gap-2">
                                <svg v-if="gen.status === 'processing' || gen.status === 'pending'" class="w-8 h-8 text-violet-400 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                                <svg v-else-if="gen.status === 'failed'" class="w-8 h-8 text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                <span class="text-xs text-gray-500 capitalize">{{ gen.status }}</span>
                            </div>
                        </div>
                        <!-- Info -->
                        <div class="p-3">
                            <p class="text-xs text-gray-300 truncate mb-2">{{ gen.prompt }}</p>
                            <span :class="['text-xs px-2 py-0.5 rounded-full font-medium capitalize', statusColor(gen.status)]">{{ gen.status }}</span>
                        </div>
                        <!-- Overlay on hover -->
                        <Link :href="route('generations.show', gen.id)" class="absolute inset-0 opacity-0 group-hover:opacity-100 bg-black/50 flex items-center justify-center transition-opacity">
                            <span class="text-sm font-medium text-white">View</span>
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
