<script setup>
import { ref } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'

const showMobileMenu = ref(false)
const showUserMenu = ref(false)
const page = usePage()

const navLinks = [
    { name: 'Dashboard', route: 'dashboard', icon: 'M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2 2z' },
    { name: 'Generate', route: 'generations.create', icon: 'M12 4v16m8-8H4' },
    { name: 'My Gallery', route: 'generations.index', icon: 'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z' },
    { name: 'Community', route: 'gallery', icon: 'M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z' },
    { name: 'API Tokens', route: 'api-tokens.index', icon: 'M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z' },
]

const isAdmin = page.props.auth?.is_admin
</script>

<template>
    <div class="min-h-screen bg-gray-950 text-white">
        <!-- Sidebar + Main Layout -->
        <div class="flex">
            <!-- Sidebar -->
            <aside class="hidden lg:flex flex-col w-64 min-h-screen bg-gray-900/50 border-r border-white/5 fixed left-0 top-0 bottom-0">
                <!-- Logo -->
                <div class="p-6 border-b border-white/5">
                    <Link :href="route('dashboard')" class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-violet-500 to-fuchsia-600 flex items-center justify-center font-black shadow-lg shadow-violet-500/30">D</div>
                        <span class="font-bold text-lg">Don<span class="text-transparent bg-clip-text bg-gradient-to-r from-violet-400 to-fuchsia-400">Draper</span></span>
                    </Link>
                </div>

                <!-- Nav Links -->
                <nav class="flex-1 p-4 space-y-1">
                    <Link v-for="link in navLinks" :key="link.name"
                        :href="route(link.route)"
                        :class="['flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all', route().current(link.route) ? 'bg-violet-500/15 text-violet-300 border border-violet-500/20' : 'text-gray-400 hover:text-white hover:bg-white/5']">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" :d="link.icon" />
                        </svg>
                        {{ link.name }}
                    </Link>
                    <Link v-if="isAdmin" :href="route('admin.dashboard')"
                        :class="['flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all mt-2',
                            route().current('admin.*') ? 'bg-rose-500/15 text-rose-300 border border-rose-500/20' : 'text-gray-500 hover:text-rose-300 hover:bg-rose-500/10']">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                        Admin
                    </Link>
                </nav>

                <!-- User Menu -->
                <div class="p-4 border-t border-white/5">
                    <div class="relative">
                        <button @click="showUserMenu = !showUserMenu"
                            class="flex items-center gap-3 w-full px-3 py-2.5 rounded-xl hover:bg-white/5 transition-all">
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-violet-500 to-fuchsia-600 flex items-center justify-center text-xs font-bold flex-shrink-0">
                                {{ $page.props.auth.user.name.charAt(0).toUpperCase() }}
                            </div>
                            <div class="flex-1 text-left min-w-0">
                                <div class="text-sm font-medium text-white truncate">{{ $page.props.auth.user.name }}</div>
                                <div class="text-xs text-gray-500 truncate">{{ $page.props.auth.user.email }}</div>
                            </div>
                            <svg class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>

                        <div v-if="showUserMenu" class="absolute bottom-full left-0 right-0 mb-2 bg-gray-800 border border-white/10 rounded-xl overflow-hidden shadow-2xl">
                            <Link :href="route('profile.edit')" class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-300 hover:bg-white/5 hover:text-white transition-all">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                Profile Settings
                            </Link>
                            <Link :href="route('logout')" method="post" as="button"
                                class="flex items-center gap-2 w-full px-4 py-2.5 text-sm text-rose-400 hover:bg-rose-500/10 transition-all text-left">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                Sign Out
                            </Link>
                        </div>
                    </div>
                </div>
            </aside>

            <!-- Main Content -->
            <div class="flex-1 lg:ml-64 flex flex-col min-h-screen">
                <!-- Top Bar (mobile + header slot) -->
                <header class="sticky top-0 z-40 bg-gray-950/95 backdrop-blur-xl border-b border-white/5">
                    <!-- Mobile Nav -->
                    <div class="lg:hidden flex items-center justify-between px-4 py-3">
                        <Link :href="route('dashboard')" class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-violet-500 to-fuchsia-600 flex items-center justify-center font-black text-sm">D</div>
                            <span class="font-bold">Don<span class="text-transparent bg-clip-text bg-gradient-to-r from-violet-400 to-fuchsia-400">Draper</span></span>
                        </Link>
                        <button @click="showMobileMenu = !showMobileMenu" class="p-2 text-gray-400 hover:text-white">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path v-if="!showMobileMenu" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Mobile Menu -->
                    <div v-if="showMobileMenu" class="lg:hidden border-t border-white/5 p-4 space-y-1">
                        <Link v-for="link in navLinks" :key="link.name" :href="route(link.route)"
                            :class="['flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium', route().current(link.route) ? 'bg-violet-500/15 text-violet-300' : 'text-gray-400']">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" :d="link.icon" />
                            </svg>
                            {{ link.name }}
                        </Link>
                        <div class="border-t border-white/5 pt-3 mt-3">
                            <Link :href="route('logout')" method="post" as="button" class="flex items-center gap-2 px-3 py-2 text-sm text-rose-400 w-full text-left">
                                Sign Out
                            </Link>
                        </div>
                    </div>

                    <!-- Page Header Slot -->
                    <div v-if="$slots.header" class="hidden lg:block px-6 py-4">
                        <slot name="header" />
                    </div>
                    <div v-if="$slots.header" class="lg:hidden px-4 py-3 border-t border-white/5">
                        <slot name="header" />
                    </div>
                </header>

                <!-- Flash Messages -->
                <div v-if="$page.props.flash?.success" class="mx-6 mt-4 bg-emerald-500/10 border border-emerald-500/30 rounded-xl px-4 py-3 text-sm text-emerald-400 flex items-center gap-2">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    {{ $page.props.flash.success }}
                </div>

                <main class="flex-1">
                    <slot />
                </main>
            </div>
        </div>
    </div>
</template>
