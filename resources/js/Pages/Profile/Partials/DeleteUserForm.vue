<script setup>
import { useForm } from '@inertiajs/vue3'
import { ref, nextTick } from 'vue'

const confirmingDeletion = ref(false)
const passwordInput = ref(null)

const form = useForm({ password: '' })

const confirmDeletion = () => {
    confirmingDeletion.value = true
    nextTick(() => passwordInput.value?.focus())
}

const deleteUser = () => {
    form.delete(route('profile.destroy'), {
        preserveScroll: true,
        onSuccess: () => { confirmingDeletion.value = false },
        onError: () => passwordInput.value?.focus(),
        onFinish: () => form.reset(),
    })
}

const closeModal = () => {
    confirmingDeletion.value = false
    form.clearErrors()
    form.reset()
}
</script>

<template>
    <section>
        <h2 class="text-lg font-bold text-white mb-1">Delete Account</h2>
        <p class="text-sm text-gray-500 mb-6">
            Once your account is deleted, all of its resources and data will be permanently deleted.
            Before deleting your account, please download any data or information that you wish to retain.
        </p>

        <button @click="confirmDeletion"
            class="bg-rose-500/15 hover:bg-rose-500/25 border border-rose-500/25 text-rose-400 font-semibold px-5 py-2.5 rounded-xl text-sm transition-all">
            Delete Account
        </button>

        <!-- Confirmation modal -->
        <Teleport to="body">
            <div v-if="confirmingDeletion"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm p-4"
                @click.self="closeModal">
                <div class="bg-[#0D1221] border border-white/10 rounded-2xl shadow-2xl w-full max-w-md p-6">
                    <h3 class="text-lg font-bold text-white mb-2">Are you sure?</h3>
                    <p class="text-sm text-gray-400 mb-5">
                        This action cannot be undone. All your generations, credits, and data will be permanently deleted.
                        Enter your password to confirm.
                    </p>

                    <div class="mb-5">
                        <input ref="passwordInput" v-model="form.password" type="password" placeholder="Password"
                            @keyup.enter="deleteUser"
                            class="w-full bg-white/4 border border-white/8 focus:border-rose-500/50 focus:ring-2 focus:ring-rose-500/10 rounded-xl px-4 py-3 text-white placeholder-gray-600 outline-none transition-all text-sm" />
                        <p v-if="form.errors.password" class="mt-1.5 text-xs text-rose-400">{{ form.errors.password }}</p>
                    </div>

                    <div class="flex justify-end gap-2">
                        <button @click="closeModal"
                            class="px-4 py-2.5 bg-white/5 hover:bg-white/8 border border-white/10 text-gray-300 font-medium rounded-xl text-sm transition-all">
                            Cancel
                        </button>
                        <button @click="deleteUser" :disabled="form.processing"
                            class="px-4 py-2.5 bg-rose-600 hover:bg-rose-500 disabled:opacity-50 text-white font-semibold rounded-xl text-sm transition-all">
                            Delete Account
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>
    </section>
</template>
