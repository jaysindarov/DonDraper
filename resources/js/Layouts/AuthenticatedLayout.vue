<script setup>
import { ref, computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'

const showMobileMenu = ref(false)
const showUserMenu = ref(false)
const page = usePage()

const navLinks = [
    { name: 'Dashboard', route: 'dashboard' },
    { name: 'My Gallery', route: 'generations.index' },
    { name: 'Community', route: 'gallery' },
]

const isAdmin = page.props.auth?.is_admin
const apiTokensEnabled = page.props.features?.api_tokens

// Per-user seeded color — consistent per user, unique between users
const USER_GRADIENTS = [
    { from: 'from-sky-500', to: 'to-cyan-600', ring: 'ring-sky-500/40', glow: 'shadow-sky-500/40' },
    { from: 'from-violet-500', to: 'to-purple-600', ring: 'ring-violet-500/40', glow: 'shadow-violet-500/40' },
    { from: 'from-rose-500', to: 'to-pink-600', ring: 'ring-rose-500/40', glow: 'shadow-rose-500/40' },
    { from: 'from-amber-500', to: 'to-orange-500', ring: 'ring-amber-500/40', glow: 'shadow-amber-500/40' },
    { from: 'from-emerald-500', to: 'to-teal-600', ring: 'ring-emerald-500/40', glow: 'shadow-emerald-500/40' },
    { from: 'from-indigo-500', to: 'to-blue-600', ring: 'ring-indigo-500/40', glow: 'shadow-indigo-500/40' },
    { from: 'from-fuchsia-500', to: 'to-violet-600', ring: 'ring-fuchsia-500/40', glow: 'shadow-fuchsia-500/40' },
    { from: 'from-cyan-500', to: 'to-sky-600', ring: 'ring-cyan-500/40', glow: 'shadow-cyan-500/40' },
]

const userGradient = computed(() => {
    const name = page.props.auth?.user?.name ?? ''
    if (!name) return USER_GRADIENTS[0]
    let hash = 0
    for (let i = 0; i < name.length; i++) {
        hash = ((hash << 5) - hash) + name.charCodeAt(i)
        hash |= 0
    }
    return USER_GRADIENTS[Math.abs(hash) % USER_GRADIENTS.length]
})

const userInitial = computed(() => page.props.auth?.user?.name?.charAt(0).toUpperCase() ?? '?')
const userAvatar = computed(() => page.props.auth?.user?.avatar ? `/storage/${page.props.auth.user.avatar}` : null)
const credits = computed(() => page.props.auth?.credits ?? 0)
</script>

<template>
    <div class="min-h-screen bg-gray-950 text-white">

        <!-- ── Top Navigation ── -->
        <header class="sticky top-0 z-50 bg-gray-950/95 backdrop-blur-xl border-b border-white/5">
            <div class="max-w-screen-xl mx-auto px-5 flex items-center justify-between h-13" style="height:52px;">

                <!-- Logo -->
                <Link :href="route('dashboard')" class="flex items-center gap-2.5 flex-shrink-0">
                    <div :class="`w-8 h-8 rounded-xl bg-gradient-to-br ${userGradient.from} ${userGradient.to} flex items-center justify-center font-black text-sm shadow-lg ${userGradient.glow}`">D</div>
                    <span class="font-bold text-base hidden sm:block">Don<span class="text-transparent bg-clip-text bg-gradient-to-r from-sky-400 to-cyan-400">Draper</span></span>
                </Link>

                <!-- Desktop Nav Links -->
                <nav class="hidden md:flex items-center gap-0.5">
                    <Link v-for="link in navLinks" :key="link.name"
                        :href="route(link.route)"
                        :class="['px-3.5 py-2 rounded-xl text-sm font-medium transition-all',
                            route().current(link.route)
                                ? 'text-white bg-white/8'
                                : 'text-gray-400 hover:text-white hover:bg-white/5']">
                        {{ link.name }}
                    </Link>
                    <Link v-if="apiTokensEnabled" :href="route('api-tokens.index')"
                        :class="['px-3.5 py-2 rounded-xl text-sm font-medium transition-all',
                            route().current('api-tokens.*') ? 'text-white bg-white/8' : 'text-gray-400 hover:text-white hover:bg-white/5']">
                        API
                    </Link>
                    <Link v-if="isAdmin" :href="route('admin.dashboard')"
                        :class="['px-3.5 py-2 rounded-xl text-sm font-medium transition-all',
                            route().current('admin.*') ? 'text-rose-300 bg-rose-500/10' : 'text-gray-500 hover:text-rose-300 hover:bg-rose-500/8']">
                        Admin
                    </Link>
                </nav>

                <!-- Right: Credits + Avatar -->
                <div class="flex items-center gap-2.5">
                    <!-- Credits -->
                    <div class="hidden sm:flex items-center gap-1.5 bg-white/5 border border-white/8 rounded-xl px-3 py-1.5">
                        <svg class="w-3.5 h-3.5 text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        <span class="text-sm font-bold text-white">{{ credits }}</span>
                        <span class="text-xs text-gray-500">cr</span>
                    </div>

                    <!-- Avatar Dropdown -->
                    <div class="relative">
                        <button @click="showUserMenu = !showUserMenu"
                            :class="`w-8 h-8 rounded-full ${!userAvatar ? 'bg-gradient-to-br ' + userGradient.from + ' ' + userGradient.to : ''} flex items-center justify-center text-xs font-bold ring-2 ring-offset-2 ring-offset-gray-950 ${userGradient.ring} transition-all hover:scale-105 shadow-lg ${userGradient.glow} overflow-hidden`">
                            <img v-if="userAvatar" :src="userAvatar" class="w-full h-full object-cover" />
                            <span v-else>{{ userInitial }}</span>
                        </button>

                        <Transition enter-from-class="opacity-0 scale-95 translate-y-1" enter-active-class="transition duration-150 ease-out" leave-to-class="opacity-0 scale-95 translate-y-1" leave-active-class="transition duration-100 ease-in">
                            <div v-if="showUserMenu"
                                class="absolute right-0 top-full mt-2 w-56 bg-[#0F1525] border border-white/8 rounded-2xl shadow-2xl shadow-black/60 overflow-hidden origin-top-right">
                                <!-- User info -->
                                <div class="px-4 py-3.5 border-b border-white/5">
                                    <div class="text-sm font-semibold text-white truncate">{{ $page.props.auth.user.name }}</div>
                                    <div class="text-xs text-gray-500 truncate mt-0.5">{{ $page.props.auth.user.email }}</div>
                                    <div class="flex items-center gap-1.5 mt-2">
                                        <svg class="w-3 h-3 text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                        <span class="text-xs text-sky-400 font-medium">{{ credits }} credits</span>
                                    </div>
                                </div>
                                <div class="p-1.5">
                                    <Link :href="route('profile.edit')"
                                        class="flex items-center gap-2.5 px-3 py-2 text-sm text-gray-300 hover:bg-white/5 hover:text-white rounded-xl transition-all">
                                        <svg class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                        Profile Settings
                                    </Link>
                                    <div class="border-t border-white/5 my-1"></div>
                                    <Link :href="route('logout')" method="post" as="button"
                                        class="flex items-center gap-2.5 w-full px-3 py-2 text-sm text-rose-400 hover:bg-rose-500/8 rounded-xl transition-all text-left">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                        Sign Out
                                    </Link>
                                </div>
                            </div>
                        </Transition>
                    </div>

                    <!-- Mobile menu toggle -->
                    <button @click="showMobileMenu = !showMobileMenu" class="md:hidden p-2 text-gray-400 hover:text-white transition-colors">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path v-if="!showMobileMenu" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Mobile Menu -->
            <Transition enter-from-class="opacity-0 -translate-y-2" enter-active-class="transition duration-150" leave-to-class="opacity-0 -translate-y-2" leave-active-class="transition duration-100">
                <div v-if="showMobileMenu" class="md:hidden border-t border-white/5 bg-[#0A0E1A] p-3 space-y-0.5">
                    <Link v-for="link in navLinks" :key="link.name" :href="route(link.route)"
                        :class="['flex items-center px-3 py-2.5 rounded-xl text-sm font-medium transition-all',
                            route().current(link.route) ? 'text-white bg-white/8' : 'text-gray-400 hover:text-white hover:bg-white/5']">
                        {{ link.name }}
                    </Link>
                    <div class="border-t border-white/5 pt-2 mt-2 flex items-center justify-between px-3 py-2">
                        <span class="text-xs text-gray-500">Credits: <span class="text-sky-400 font-bold">{{ credits }}</span></span>
                        <Link :href="route('logout')" method="post" as="button" class="text-xs text-rose-400 hover:text-rose-300 transition-colors">Sign Out</Link>
                    </div>
                </div>
            </Transition>

            <!-- Page sub-header (optional slot) -->
            <div v-if="$slots.header" class="border-t border-white/4 px-5 py-3 max-w-screen-xl mx-auto">
                <slot name="header" />
            </div>
        </header>

        <!-- Flash Message -->
        <div v-if="$page.props.flash?.success"
            class="max-w-screen-xl mx-auto px-5 pt-4">
            <div class="bg-emerald-500/8 border border-emerald-500/25 rounded-xl px-4 py-3 text-sm text-emerald-400 flex items-center gap-2">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                {{ $page.props.flash.success }}
            </div>
        </div>

        <!-- Main Content -->
        <main class="flex-1">
            <slot />
        </main>
    </div>
</template>
