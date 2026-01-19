<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import axios from 'axios';

const form = ref({
    name: '',
    organization_name: '',
    email: '',
    password: '',
    password_confirmation: ''
});

const showPassword = ref(false);
const showConfirmPassword = ref(false);
const errors = ref({});
const isSubmitting = ref(false);

const submitForm = async () => {
    errors.value = {};
    isSubmitting.value = true;

    try {
        const response = await axios.post('/api/register', {
            name: form.value.name,
            email: form.value.email,
            password: form.value.password,
            password_confirmation: form.value.password_confirmation,
            organization_name: form.value.organization_name || form.value.name + "'s Organization"
        });

        // Redirect to login page after successful registration
        router.visit(`/login?registered=true&email=${encodeURIComponent(form.value.email)}`, {
            method: 'get'
        });
    } catch (error) {
        if (error.response?.data?.errors) {
            errors.value = error.response.data.errors;
        } else if (error.response?.data?.message) {
            errors.value = { general: error.response.data.message };
        } else {
            errors.value = { general: 'Registration failed. Please try again.' };
        }
    } finally {
        isSubmitting.value = false;
    }
};
</script>

<template>
    <Head title="Sign Up" />
    <div class="min-h-screen bg-gradient-radial bg-hero-pattern flex">
        <!-- Left Side - Form -->
        <div class="flex-1 flex items-center justify-center p-8">
            <div class="w-full max-w-md">
                <!-- Logo -->
                <Link href="/" class="flex items-center gap-2 mb-8 animate-fade-in-up">
                    <div class="w-10 h-10 bg-gradient-navy rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <span class="text-xl font-bold text-navy">YUJIX</span>
                </Link>

                <div class="animate-fade-in-up delay-100">
                    <h1 class="text-3xl font-bold text-navy mb-2">Create your account</h1>
                    <p class="text-teal/70 mb-8">Start your 14-day free trial</p>
                </div>

                <!-- Google Sign Up -->
                <button class="w-full flex items-center justify-center gap-3 px-4 py-3 border-2 border-lavender/50 rounded-xl hover:bg-lavender/10 transition-colors mb-6 animate-fade-in-up delay-200">
                    <svg class="w-5 h-5" viewBox="0 0 24 24">
                        <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                        <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                        <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                        <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                    </svg>
                    <span class="font-medium text-navy">Continue with Google</span>
                </button>

                <div class="flex items-center gap-4 mb-6 animate-fade-in-up delay-200">
                    <div class="flex-1 h-px bg-lavender/50"></div>
                    <span class="text-sm text-teal/60">or</span>
                    <div class="flex-1 h-px bg-lavender/50"></div>
                </div>

                <!-- Error Message -->
                <div v-if="errors.general" class="mb-4 p-3 bg-red-50 border border-red-200 rounded-xl text-red-600 text-sm animate-fade-in-up">
                    {{ errors.general }}
                </div>

                <!-- Form -->
                <form @submit.prevent="submitForm" class="space-y-4 animate-fade-in-up delay-300">
                    <div>
                        <label class="block text-sm font-medium text-navy mb-2">Full Name <span class="text-red-500">*</span></label>
                        <input
                            v-model="form.name"
                            type="text"
                            placeholder="Enter your name"
                            required
                            :class="[
                                'w-full px-4 py-3 rounded-xl border focus:ring-2 focus:ring-teal/30 focus:border-teal',
                                errors.name ? 'border-red-400' : 'border-lavender/50'
                            ]"
                        />
                        <p v-if="errors.name" class="mt-1 text-xs text-red-500">{{ errors.name[0] }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-navy mb-2">Organization</label>
                        <input
                            v-model="form.organization_name"
                            type="text"
                            placeholder="Your company or team name (optional)"
                            :class="[
                                'w-full px-4 py-3 rounded-xl border focus:ring-2 focus:ring-teal/30 focus:border-teal',
                                errors.organization_name ? 'border-red-400' : 'border-lavender/50'
                            ]"
                        />
                        <p v-if="errors.organization_name" class="mt-1 text-xs text-red-500">{{ errors.organization_name[0] }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-navy mb-2">Email <span class="text-red-500">*</span></label>
                        <input
                            v-model="form.email"
                            type="email"
                            placeholder="Enter your email"
                            required
                            :class="[
                                'w-full px-4 py-3 rounded-xl border focus:ring-2 focus:ring-teal/30 focus:border-teal',
                                errors.email ? 'border-red-400' : 'border-lavender/50'
                            ]"
                        />
                        <p v-if="errors.email" class="mt-1 text-xs text-red-500">{{ errors.email[0] }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-navy mb-2">Password <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <input
                                v-model="form.password"
                                :type="showPassword ? 'text' : 'password'"
                                placeholder="Create a password"
                                required
                                minlength="8"
                                :class="[
                                    'w-full px-4 py-3 rounded-xl border focus:ring-2 focus:ring-teal/30 focus:border-teal pr-12',
                                    errors.password ? 'border-red-400' : 'border-lavender/50'
                                ]"
                            />
                            <button
                                type="button"
                                @click="showPassword = !showPassword"
                                class="absolute right-4 top-1/2 -translate-y-1/2 text-teal/50 hover:text-teal"
                            >
                                <svg v-if="!showPassword" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <svg v-else class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                </svg>
                            </button>
                        </div>
                        <p class="text-xs text-teal/50 mt-1">Must be at least 8 characters</p>
                        <p v-if="errors.password" class="mt-1 text-xs text-red-500">{{ errors.password[0] }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-navy mb-2">Confirm Password <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <input
                                v-model="form.password_confirmation"
                                :type="showConfirmPassword ? 'text' : 'password'"
                                placeholder="Confirm your password"
                                required
                                :class="[
                                    'w-full px-4 py-3 rounded-xl border focus:ring-2 focus:ring-teal/30 focus:border-teal pr-12',
                                    errors.password_confirmation ? 'border-red-400' : 'border-lavender/50'
                                ]"
                            />
                            <button
                                type="button"
                                @click="showConfirmPassword = !showConfirmPassword"
                                class="absolute right-4 top-1/2 -translate-y-1/2 text-teal/50 hover:text-teal"
                            >
                                <svg v-if="!showConfirmPassword" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <svg v-else class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                </svg>
                            </button>
                        </div>
                        <p v-if="errors.password_confirmation" class="mt-1 text-xs text-red-500">{{ errors.password_confirmation[0] }}</p>
                    </div>

                    <button 
                        type="submit" 
                        :disabled="isSubmitting"
                        :class="[
                            'w-full py-3 rounded-xl font-semibold mt-6 transition-all flex items-center justify-center gap-2',
                            isSubmitting 
                                ? 'bg-teal/70 cursor-not-allowed' 
                                : 'btn-primary hover:shadow-lg'
                        ]"
                    >
                        <svg v-if="isSubmitting" class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        {{ isSubmitting ? 'Creating Account...' : 'Create Account' }}
                    </button>

                    <p class="text-xs text-teal/60 text-center">
                        By signing up, you agree to our
                        <Link href="/terms" class="text-teal hover:underline">Terms of Service</Link>
                        and
                        <Link href="/privacy" class="text-teal hover:underline">Privacy Policy</Link>
                    </p>
                </form>

                <p class="mt-8 text-center text-sm text-teal/70 animate-fade-in-up delay-400">
                    Already have an account?
                    <Link href="/login" class="text-teal font-medium hover:text-teal-dark">
                        Sign in
                    </Link>
                </p>
            </div>
        </div>

        <!-- Right Side - Image/Illustration (hidden on mobile) -->
        <div class="hidden lg:flex flex-1 bg-gradient-navy items-center justify-center p-12">
            <div class="max-w-md text-white animate-fade-in">
                <h2 class="text-2xl font-bold mb-6 text-center">Why Teams Love YUJIX</h2>
                <div class="space-y-6">
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 bg-white/10 rounded-lg flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-medium mb-1">Easy Scheduling</h3>
                            <p class="text-sm text-cream/70">Schedule meetings in seconds with smart calendar integration.</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 bg-white/10 rounded-lg flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-medium mb-1">Smart Notifications</h3>
                            <p class="text-sm text-cream/70">Never miss a meeting with customizable reminders.</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 bg-white/10 rounded-lg flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-medium mb-1">Powerful Analytics</h3>
                            <p class="text-sm text-cream/70">Gain insights into meeting patterns and team productivity.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
