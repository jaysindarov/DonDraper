<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { ref } from 'vue'

const props = defineProps({
    generations: Object,
    filters: Object,
})

const statusFilter = ref(props.filters?.status ?? '')
const typeFilter   = ref(props.filters?.type ?? '')

function applyFilters() {
    router.get(route('admin.generations.index'), {
        status: statusFilter.value || undefined,
        type: typeFilter.value || undefined,
    }, { preserveState: true })
}

function deleteGen(id) {
    if (!confirm('Delete this generation?')) return
    router.delete(route('admin.generations.destroy', id))
}

const statusColors = {
    completed: 'text-emerald-400',
    processing: 'text-blue-400',
    pending: 'text-yellow-400',
    failed: 'text-rose-400',
}
</script>

<template>
    <Head title="Admin — Generations" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link :href="route('admin.dashboard')" class="text-gray-400 hover:text-white transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </Link>
                <h2 class="text-xl font-bold text-white">All Generations</h2>
            </div>
        </template>

        <div class="py-8 px-6 max-w-7xl mx-auto space-y-6">
            <!-- Filters -->
            <div class="flex gap-3">
                <select v-model="statusFilter" @change="applyFilters"
                    class="bg-gray-900/50 border border-white/10 rounded-xl px-4 py-2 text-sm text-white focus:outline-none focus:border-violet-500/50">
                    <option value="">All Statuses</option>
                    <option value="pending">Pending</option>
                    <option value="processing">Processing</option>
                    <option value="completed">Completed</option>
                    <option value="failed">Failed</option>
                </select>
                <select v-model="typeFilter" @change="applyFilters"
                    class="bg-gray-900/50 border border-white/10 rounded-xl px-4 py-2 text-sm text-white focus:outline-none focus:border-violet-500/50">
                    <option value="">All Types</option>
                    <option value="image">Image</option>
                    <option value="video">Video</option>
                </select>
            </div>

            <!-- Table -->
            <div class="bg-gray-900/50 border border-white/5 rounded-2xl overflow-hidden">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-white/5">
                            <th class="text-left text-gray-500 font-medium px-5 py-3">ID</th>
                            <th class="text-left text-gray-500 font-medium px-5 py-3">User</th>
                            <th class="text-left text-gray-500 font-medium px-5 py-3">Prompt</th>
                            <th class="text-left text-gray-500 font-medium px-5 py-3">Model</th>
                            <th class="text-left text-gray-500 font-medium px-5 py-3">Type</th>
                            <th class="text-left text-gray-500 font-medium px-5 py-3">Status</th>
                            <th class="px-5 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        <tr v-for="gen in generations.data" :key="gen.id" class="hover:bg-white/[0.02]">
                            <td class="px-5 py-3 text-gray-500">#{{ gen.id }}</td>
                            <td class="px-5 py-3 text-gray-400">{{ gen.user?.name }}</td>
                            <td class="px-5 py-3 text-white max-w-xs truncate">{{ gen.prompt }}</td>
                            <td class="px-5 py-3 text-gray-400">{{ gen.model }}</td>
                            <td class="px-5 py-3 text-gray-400 capitalize">{{ gen.type }}</td>
                            <td class="px-5 py-3">
                                <span :class="['text-xs font-medium', statusColors[gen.status] || 'text-gray-400']">
                                    {{ gen.status }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-right">
                                <button @click="deleteGen(gen.id)"
                                    class="text-xs text-rose-400 hover:text-rose-300 transition-colors">
                                    Delete
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="flex justify-center gap-2">
                <Link v-for="link in generations.links" :key="link.label"
                    :href="link.url ?? '#'"
                    :class="['text-xs px-3 py-1.5 rounded-lg transition-all',
                        link.active ? 'bg-violet-600 text-white' : 'bg-white/5 text-gray-400 hover:bg-white/10',
                        !link.url ? 'opacity-40 pointer-events-none' : '']"
                    v-html="link.label" />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
