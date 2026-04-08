<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { ref } from 'vue'

const props = defineProps({
    users: Object,
    search: String,
})

const searchQuery = ref(props.search)

function doSearch() {
    router.get(route('admin.users.index'), { search: searchQuery.value }, { preserveState: true })
}
</script>

<template>
    <Head title="Admin — Users" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link :href="route('admin.dashboard')" class="text-gray-400 hover:text-white transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </Link>
                <h2 class="text-xl font-bold text-white">Users</h2>
            </div>
        </template>

        <div class="py-8 px-6 max-w-7xl mx-auto space-y-6">
            <!-- Search -->
            <form @submit.prevent="doSearch" class="flex gap-3">
                <input v-model="searchQuery" type="text" placeholder="Search name or email..."
                    class="flex-1 bg-gray-900/50 border border-white/10 rounded-xl px-4 py-2.5 text-white placeholder-gray-600 text-sm focus:outline-none focus:border-sky-500/50" />
                <button type="submit"
                    class="bg-sky-600 hover:bg-sky-500 text-white text-sm font-medium px-5 py-2.5 rounded-xl transition-all">
                    Search
                </button>
            </form>

            <!-- Table -->
            <div class="bg-gray-900/50 border border-white/5 rounded-2xl overflow-hidden">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-white/5">
                            <th class="text-left text-gray-500 font-medium px-5 py-3">Name</th>
                            <th class="text-left text-gray-500 font-medium px-5 py-3">Email</th>
                            <th class="text-left text-gray-500 font-medium px-5 py-3">Plan</th>
                            <th class="text-left text-gray-500 font-medium px-5 py-3">Credits</th>
                            <th class="text-left text-gray-500 font-medium px-5 py-3">Gens</th>
                            <th class="text-left text-gray-500 font-medium px-5 py-3">Admin</th>
                            <th class="px-5 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        <tr v-for="user in users.data" :key="user.id" class="hover:bg-white/[0.02]">
                            <td class="px-5 py-3 text-white">{{ user.name }}</td>
                            <td class="px-5 py-3 text-gray-400">{{ user.email }}</td>
                            <td class="px-5 py-3">
                                <span :class="['text-xs px-2 py-0.5 rounded-full font-medium',
                                    user.plan === 'enterprise' ? 'bg-cyan-500/20 text-cyan-300' :
                                    user.plan === 'pro' ? 'bg-sky-500/20 text-sky-300' :
                                    'bg-gray-700/50 text-gray-400']">
                                    {{ user.plan ?? 'free' }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-sky-400 font-medium">{{ user.credits }}</td>
                            <td class="px-5 py-3 text-gray-400">{{ user.generations_count }}</td>
                            <td class="px-5 py-3">
                                <span v-if="user.is_admin" class="text-xs text-emerald-400">✓</span>
                                <span v-else class="text-xs text-gray-600">–</span>
                            </td>
                            <td class="px-5 py-3 text-right">
                                <Link :href="route('admin.users.edit', user.id)"
                                    class="text-xs text-sky-400 hover:text-sky-300 transition-colors">
                                    Edit
                                </Link>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="flex justify-center gap-2">
                <Link v-for="link in users.links" :key="link.label"
                    :href="link.url ?? '#'"
                    :class="['text-xs px-3 py-1.5 rounded-lg transition-all',
                        link.active ? 'bg-sky-600 text-white' : 'bg-white/5 text-gray-400 hover:bg-white/10',
                        !link.url ? 'opacity-40 pointer-events-none' : '']"
                    v-html="link.label" />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
