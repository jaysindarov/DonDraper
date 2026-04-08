<script setup>
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
    currentPlan: String,
    credits: Number,
    plans: Object,
    creditPacks: Array,
    subscription: Object,
    onGracePeriod: Boolean,
})

const planOrder = { free: 0, pro: 1, enterprise: 2 }
const currentRank = computed(() => planOrder[props.currentPlan] ?? 0)

function isCurrentPlan(key) { return props.currentPlan === key }
function isUpgrade(key) { return (planOrder[key] ?? 0) > currentRank.value }

function checkout(plan) { router.get(route('billing.checkout', { plan })) }
function openPortal() { router.get(route('billing.portal')) }
</script>

<template>
    <Head title="Billing & Plans — DonDraper" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-base font-bold text-white">Billing & Plans</h2>
        </template>

        <div class="py-7 px-6 max-w-5xl mx-auto space-y-8">

            <!-- Current plan status -->
            <div class="bg-[#0A0E1A] border border-white/6 rounded-2xl p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-5">
                <div>
                    <p class="text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-2">Current Plan</p>
                    <div class="flex items-center gap-3 mb-1">
                        <span class="text-2xl font-black text-white capitalize">{{ plans[currentPlan]?.name ?? currentPlan }}</span>
                        <span v-if="onGracePeriod" class="text-xs text-amber-400 bg-amber-500/10 border border-amber-500/20 px-2 py-0.5 rounded-full">
                            Cancelling at period end
                        </span>
                        <span v-else-if="subscription?.stripe_status === 'active'" class="text-xs text-emerald-400 bg-emerald-500/10 border border-emerald-500/20 px-2 py-0.5 rounded-full">
                            Active
                        </span>
                    </div>
                    <p class="text-sm text-gray-400">
                        <span class="font-bold text-sky-400">{{ credits }}</span> credits available
                    </p>
                </div>

                <div class="flex items-center gap-3 flex-shrink-0">
                    <button v-if="subscription" @click="openPortal"
                        class="text-sm text-gray-400 hover:text-white border border-white/8 hover:border-white/20 px-4 py-2.5 rounded-xl transition-all">
                        Manage subscription
                    </button>
                    <Link v-if="currentPlan !== 'enterprise'" :href="route('billing.checkout', { plan: currentPlan === 'free' ? 'pro' : 'enterprise' })"
                        class="text-sm bg-gradient-to-r from-sky-500 to-cyan-600 hover:from-sky-400 hover:to-cyan-500 text-white font-semibold px-4 py-2.5 rounded-xl transition-all shadow-lg shadow-sky-500/20">
                        Upgrade
                    </Link>
                </div>
            </div>

            <!-- Plan cards -->
            <div>
                <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-4">Plans</h3>
                <div class="grid md:grid-cols-3 gap-4">
                    <div v-for="(plan, key) in plans" :key="key"
                        :class="['relative rounded-2xl border-2 p-6 flex flex-col transition-all',
                            isCurrentPlan(key)
                                ? 'border-sky-500/40 bg-sky-500/5'
                                : key === 'pro' ? 'border-white/8 bg-[#0A0E1A]' : 'border-white/5 bg-[#0A0E1A]/60']">

                        <div v-if="key === 'pro' && !isCurrentPlan('pro')"
                            class="absolute -top-3.5 left-1/2 -translate-x-1/2 text-[10px] font-bold text-sky-300 bg-gray-950 border border-sky-500/35 px-3 py-1 rounded-full uppercase tracking-wider">
                            Recommended
                        </div>

                        <div class="mb-5">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-bold text-white">{{ plan.name }}</span>
                                <span v-if="isCurrentPlan(key)" class="text-xs text-sky-400 bg-sky-500/10 px-2 py-0.5 rounded-full">Current</span>
                            </div>
                            <div class="flex items-baseline gap-1">
                                <span class="text-3xl font-black text-white">${{ plan.price }}</span>
                                <span v-if="plan.price > 0" class="text-sm text-gray-500">/month</span>
                                <span v-else class="text-sm text-gray-500">free</span>
                            </div>
                            <p class="text-xs text-sky-400 mt-1">
                                {{ plan.credits_per_month >= 999999 ? 'Unlimited' : plan.credits_per_month.toLocaleString() }} credits/month
                            </p>
                        </div>

                        <ul class="space-y-2.5 flex-1 mb-6">
                            <li v-for="feature in plan.features" :key="feature" class="flex items-center gap-2 text-xs text-gray-400">
                                <svg class="w-3.5 h-3.5 text-emerald-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                </svg>
                                {{ feature }}
                            </li>
                        </ul>

                        <div>
                            <span v-if="isCurrentPlan(key)"
                                class="block text-center text-xs text-gray-500 py-2.5 border border-white/5 rounded-xl">
                                Your current plan
                            </span>
                            <button v-else-if="key === 'free'" disabled
                                class="w-full text-center text-xs text-gray-600 py-2.5 border border-white/5 rounded-xl cursor-not-allowed">
                                Downgrade to free
                            </button>
                            <button v-else-if="key === 'enterprise' && currentPlan !== 'pro'"
                                @click="checkout(key)"
                                class="w-full text-sm bg-white/4 hover:bg-white/8 border border-white/8 text-gray-300 py-2.5 rounded-xl transition-all">
                                Contact Sales
                            </button>
                            <button v-else @click="checkout(key)"
                                :class="['w-full text-sm font-semibold py-2.5 rounded-xl transition-all',
                                    isUpgrade(key)
                                        ? 'bg-gradient-to-r from-sky-500 to-cyan-600 hover:from-sky-400 hover:to-cyan-500 text-white shadow-lg shadow-sky-500/15'
                                        : 'bg-white/4 hover:bg-white/8 border border-white/8 text-gray-300']">
                                {{ isUpgrade(key) ? `Upgrade to ${plan.name}` : `Switch to ${plan.name}` }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Credit top-up packs -->
            <div>
                <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Credit Top-ups</h3>
                <p class="text-xs text-gray-600 mb-4">One-time purchases — credits never expire.</p>
                <div class="grid sm:grid-cols-3 gap-4">
                    <div v-for="(pack, i) in creditPacks" :key="i"
                        :class="['relative rounded-2xl border p-5 flex flex-col gap-3 transition-all',
                            pack.popular ? 'border-sky-500/35 bg-sky-500/5' : 'border-white/5 bg-[#0A0E1A]']">

                        <div v-if="pack.popular"
                            class="absolute -top-3.5 left-1/2 -translate-x-1/2 text-[10px] font-bold text-sky-300 bg-gray-950 border border-sky-500/35 px-3 py-1 rounded-full uppercase tracking-wider">
                            Popular
                        </div>

                        <div>
                            <p class="text-sm font-semibold text-white">{{ pack.name }}</p>
                            <div class="flex items-baseline gap-1 mt-1">
                                <span class="text-2xl font-black text-white">${{ pack.price }}</span>
                                <span class="text-xs text-gray-500">one-time</span>
                            </div>
                            <p class="text-xs text-sky-400 mt-0.5">{{ pack.credits }} credits</p>
                        </div>

                        <Link :href="route('billing.credits', { pack: i })"
                            :class="['block text-center text-sm font-semibold py-2.5 rounded-xl transition-all',
                                pack.popular
                                    ? 'bg-gradient-to-r from-sky-500 to-cyan-600 hover:from-sky-400 hover:to-cyan-500 text-white shadow-lg shadow-sky-500/15'
                                    : 'bg-white/4 hover:bg-white/8 border border-white/8 text-gray-300']">
                            Buy {{ pack.credits }} credits
                        </Link>
                    </div>
                </div>
            </div>

            <p class="text-xs text-gray-600 text-center pb-2">
                Payments are processed securely by Stripe. Cancel or manage your subscription at any time.
            </p>
        </div>
    </AuthenticatedLayout>
</template>
