<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { ref } from 'vue'

const props = defineProps({
    generations: Object,
    filters: Object,
})

const typeFilter = ref(props.filters?.type ?? '')

function applyFilter() {
    router.get(route('gallery'), { type: typeFilter.value || undefined }, { preserveState: true })
}
</script>

<template>
    <Head title="Community Gallery — DonDraper" />

    <div class="min-h-screen bg-gray-950 text-white">
        <!-- Header -->
        <header class="border-b border-white/5 px-6 py-4 flex items-center justify-between">
            <Link :href="route('home')" class="text-xl font-bold bg-gradient-to-r from-violet-400 to-fuchsia-400 bg-clip-text text-transparent">
                DonDraper
            </Link>
            <div class="flex items-center gap-4">
                <Link :href="route('login')" class="text-sm text-gray-400 hover:text-white transition-colors">Sign in</Link>
                <Link :href="route('register')" class="text-sm bg-violet-600 hover:bg-violet-500 text-white px-4 py-1.5 rounded-lg transition-all">
                    Get started
                </Link>
            </div>
        </header>

        <div class="max-w-7xl mx-auto px-6 py-12">
            <!-- Title + filters -->
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-white">Community Gallery</h1>
                    <p class="text-gray-500 mt-1">Publicly shared AI generations</p>
                </div>
                <select v-model="typeFilter" @change="applyFilter"
                    class="bg-gray-900/50 border border-white/10 rounded-xl px-4 py-2 text-sm text-white focus:outline-none focus:border-violet-500/50">
                    <option value="">All Types</option>
                    <option value="image">Images</option>
                    <option value="video">Videos</option>
                </select>
            </div>

            <!-- Grid -->
            <div v-if="generations.data.length" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                <div v-for="gen in generations.data" :key="gen.id"
                    class="group relative rounded-2xl overflow-hidden bg-gray-900 border border-white/5 hover:border-violet-500/30 transition-all">
                    <div :class="gen.type === 'video' ? 'aspect-video' : 'aspect-square'">
                        <video v-if="gen.type === 'video' && gen.result_url"
                            :src="gen.result_url" loop muted playsinline autoplay
                            class="w-full h-full object-cover" />
                        <img v-else-if="gen.result_url"
                            :src="gen.result_url" :alt="gen.prompt"
                            class="w-full h-full object-cover" />
                    </div>
                    <!-- Overlay -->
                    <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity p-3 flex flex-col justify-end">
                        <p class="text-xs text-white line-clamp-2">{{ gen.prompt }}</p>
                        <p class="text-xs text-gray-400 mt-1">by {{ gen.user?.name }}</p>
                    </div>
                    <div v-if="gen.type === 'video'"
                        class="absolute top-2 right-2 text-xs bg-black/60 text-white px-2 py-0.5 rounded-full">
                        Video
                    </div>
                </div>
            </div>

            <div v-else class="text-center py-24 text-gray-500">
                No public generations yet. Be the first to share!
            </div>

            <!-- Pagination -->
            <div v-if="generations.last_page > 1" class="flex justify-center gap-2 mt-10">
                <Link v-for="link in generations.links" :key="link.label"
                    :href="link.url ?? '#'"
                    :class="['text-xs px-3 py-1.5 rounded-lg transition-all',
                        link.active ? 'bg-violet-600 text-white' : 'bg-white/5 text-gray-400 hover:bg-white/10',
                        !link.url ? 'opacity-40 pointer-events-none' : '']"
                    v-html="link.label" />
            </div>
        </div>
    </div>
</template>
