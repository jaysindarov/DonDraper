<script setup>
import { Head, Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
    stats: Object,
    recent_generations: Array,
})

const statCards = [
    { label: 'Total Users', value: props.stats.total_users, color: 'text-sky-400' },
    { label: 'Total Generations', value: props.stats.total_generations, color: 'text-blue-400' },
    { label: 'Completed', value: props.stats.completed, color: 'text-emerald-400' },
    { label: 'Failed', value: props.stats.failed, color: 'text-rose-400' },
    { label: 'In Progress', value: props.stats.pending, color: 'text-yellow-400' },
    { label: 'Credits Used', value: props.stats.total_credits_used, color: 'text-cyan-400' },
]

const statusColors = {
    completed: 'text-emerald-400',
    processing: 'text-blue-400',
    pending: 'text-yellow-400',
    failed: 'text-rose-400',
}
</script>

<template>
    <Head title="Admin Dashboard — DonDraper" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-bold text-white">Admin Dashboard</h2>
        </template>

        <div class="py-8 px-6 max-w-7xl mx-auto space-y-8">
            <!-- Stats -->
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                <div v-for="stat in statCards" :key="stat.label"
                    class="bg-gray-900/50 border border-white/5 rounded-2xl p-4 text-center">
                    <div :class="['text-2xl font-bold', stat.color]">{{ stat.value }}</div>
                    <div class="text-xs text-gray-500 mt-1">{{ stat.label }}</div>
                </div>
            </div>

            <!-- Nav -->
            <div class="flex gap-3">
                <Link :href="route('admin.users.index')"
                    class="bg-white/5 hover:bg-white/10 border border-white/10 text-white text-sm font-medium px-4 py-2 rounded-xl transition-all">
                    Manage Users
                </Link>
                <Link :href="route('admin.generations.index')"
                    class="bg-white/5 hover:bg-white/10 border border-white/10 text-white text-sm font-medium px-4 py-2 rounded-xl transition-all">
                    All Generations
                </Link>
            </div>

            <!-- Recent Generations -->
            <div class="bg-gray-900/50 border border-white/5 rounded-2xl overflow-hidden">
                <div class="p-5 border-b border-white/5">
                    <h3 class="text-sm font-semibold text-white">Recent Generations</h3>
                </div>
                <div class="divide-y divide-white/5">
                    <div v-for="gen in recent_generations" :key="gen.id"
                        class="flex items-center justify-between p-4 hover:bg-white/[0.02]">
                        <div class="flex items-center gap-3">
                            <img v-if="gen.result_url" :src="gen.result_url" class="w-10 h-10 rounded-lg object-cover bg-gray-800" />
                            <div v-else class="w-10 h-10 rounded-lg bg-gray-800 flex items-center justify-center text-gray-600 text-xs">–</div>
                            <div>
                                <p class="text-sm text-white truncate max-w-xs">{{ gen.prompt }}</p>
                                <p class="text-xs text-gray-500">{{ gen.user?.name }} · {{ gen.model }}</p>
                            </div>
                        </div>
                        <span :class="['text-xs font-medium', statusColors[gen.status] || 'text-gray-400']">
                            {{ gen.status }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
