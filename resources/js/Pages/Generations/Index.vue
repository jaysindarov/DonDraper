<script setup>
import { Head, Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
    generations: Object,
})

const statusColor = (status) => {
    const map = { completed: 'text-emerald-400 bg-emerald-400/10', processing: 'text-blue-400 bg-blue-400/10', pending: 'text-yellow-400 bg-yellow-400/10', failed: 'text-rose-400 bg-rose-400/10' }
    return map[status] || 'text-gray-400 bg-gray-400/10'
}
</script>

<template>
    <Head title="My Generations — DonDraper" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-bold text-white">My Generations</h2>
                <Link :href="route('generations.create')" class="flex items-center gap-2 bg-gradient-to-r from-violet-500 to-fuchsia-600 hover:from-violet-400 hover:to-fuchsia-500 text-white font-semibold px-5 py-2.5 rounded-xl transition-all shadow-lg shadow-violet-500/25 text-sm">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    New Generation
                </Link>
            </div>
        </template>

        <div class="py-8 px-6 max-w-7xl mx-auto">
            <div v-if="generations.data.length === 0" class="text-center py-32">
                <div class="text-6xl mb-4">🎨</div>
                <h3 class="text-2xl font-black mb-2">No generations yet</h3>
                <p class="text-gray-400 mb-6">Create your first AI-generated image or video.</p>
                <Link :href="route('generations.create')" class="inline-flex items-center gap-2 bg-gradient-to-r from-violet-500 to-fuchsia-600 text-white font-bold px-6 py-3 rounded-xl transition-all shadow-lg shadow-violet-500/25">
                    Get Started
                </Link>
            </div>

            <div v-else>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4 mb-8">
                    <Link v-for="gen in generations.data" :key="gen.id" :href="route('generations.show', gen.id)"
                        class="group relative bg-gray-900/50 border border-white/5 hover:border-violet-500/30 rounded-2xl overflow-hidden transition-all hover:-translate-y-1 hover:shadow-xl hover:shadow-violet-500/10">
                        <div class="aspect-square bg-gradient-to-br from-gray-800 to-gray-900 relative overflow-hidden">
                            <img v-if="gen.result_url" :src="gen.result_url" :alt="gen.prompt" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" />
                            <div v-else class="w-full h-full flex flex-col items-center justify-center gap-2">
                                <svg v-if="gen.status !== 'failed'" class="w-8 h-8 text-violet-400/50 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                                <span v-else class="text-3xl">❌</span>
                            </div>
                            <!-- Hover overlay -->
                            <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity flex items-end p-3">
                                <p class="text-xs text-gray-200 line-clamp-2">{{ gen.prompt }}</p>
                            </div>
                        </div>
                        <div class="p-3">
                            <span :class="['text-xs px-2 py-0.5 rounded-full font-medium capitalize', statusColor(gen.status)]">{{ gen.status }}</span>
                            <p class="text-xs text-gray-600 mt-1">{{ new Date(gen.created_at).toLocaleDateString() }}</p>
                        </div>
                    </Link>
                </div>

                <!-- Pagination -->
                <div v-if="generations.last_page > 1" class="flex justify-center gap-2">
                    <Link v-for="link in generations.links" :key="link.label"
                        :href="link.url || '#'"
                        :class="['px-4 py-2 rounded-xl text-sm font-medium transition-all', link.active ? 'bg-gradient-to-r from-violet-500 to-fuchsia-600 text-white shadow-lg shadow-violet-500/25' : 'bg-white/5 border border-white/10 text-gray-400 hover:text-white', !link.url ? 'opacity-40 cursor-not-allowed' : '']"
                        v-html="link.label">
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
