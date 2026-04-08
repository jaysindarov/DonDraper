<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue'
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue'
import DeleteUserForm from './Partials/DeleteUserForm.vue'
import { Head } from '@inertiajs/vue3'

defineProps({
    mustVerifyEmail: Boolean,
    status: String,
})
</script>

<template>
    <Head title="Profile Settings — DonDraper" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-base font-bold text-white">Profile Settings</h2>
        </template>

        <div class="py-8 px-5 max-w-2xl mx-auto space-y-6">
            <div class="bg-[#0A0E1A] border border-white/8 rounded-2xl p-6">
                <UpdateProfileInformationForm
                    :must-verify-email="mustVerifyEmail"
                    :status="status"
                />
            </div>

            <div class="bg-[#0A0E1A] border border-white/8 rounded-2xl p-6">
                <UpdatePasswordForm />
            </div>

            <div class="bg-[#0A0E1A] border border-white/8 rounded-2xl p-6">
                <DeleteUserForm />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
