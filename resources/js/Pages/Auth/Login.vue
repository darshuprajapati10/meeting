<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const form = useForm({
    email: '',
    password: '',
    remember: false
});

const showPassword = ref(false);

const submit = () => {
    form.post('/login', {
        preserveScroll: true,
        onSuccess: (page) => {
            // Success - redirect will happen automatically
            console.log('Login successful', page);
        },
        onError: (errors) => {
            // Errors are automatically populated in form.errors
            console.log('Login errors', errors);
        },
        onFinish: () => {
            form.processing = false;
        },
    });
};
</script>

<template>
    <Head title="Sign In" />
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
                    <h1 class="text-3xl font-bold text-navy mb-2">Welcome back</h1>
                    <p class="text-teal/70 mb-8">Sign in to your account to continue</p>
                </div>

                <!-- Google Sign In -->
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

                <!-- Form -->
                <form @submit.prevent="submit" class="space-y-5 animate-fade-in-up delay-300">
                    <!-- General Error Message -->
                    <div v-if="form.errors.email && !form.errors.password" class="p-4 bg-red-50 border border-red-200 rounded-xl">
                        <p class="text-sm text-red-600">{{ form.errors.email }}</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-navy mb-2">Email</label>
                        <input
                            v-model="form.email"
                            type="email"
                            placeholder="Enter your email"
                            required
                            class="w-full px-4 py-3 rounded-xl border border-lavender/50 focus:ring-2 focus:ring-teal/30 focus:border-teal"
                            :class="{ 'border-red-500': form.errors.email }"
                        />
                        <p v-if="form.errors.email" class="mt-1 text-sm text-red-600">{{ Array.isArray(form.errors.email) ? form.errors.email[0] : form.errors.email }}</p>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="text-sm font-medium text-navy">Password</label>
                            <Link href="/forgot-password" class="text-sm text-teal hover:text-teal-dark">
                                Forgot password?
                            </Link>
                        </div>
                        <div class="relative">
                            <input
                                v-model="form.password"
                                :type="showPassword ? 'text' : 'password'"
                                placeholder="Enter your password"
                                required
                                class="w-full px-4 py-3 rounded-xl border border-lavender/50 focus:ring-2 focus:ring-teal/30 focus:border-teal pr-12"
                                :class="{ 'border-red-500': form.errors.password }"
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
                        <p v-if="form.errors.password" class="mt-1 text-sm text-red-600">{{ Array.isArray(form.errors.password) ? form.errors.password[0] : form.errors.password }}</p>
                    </div>

                    <div class="flex items-center gap-2">
                        <input
                            v-model="form.remember"
                            type="checkbox"
                            id="remember"
                            class="w-4 h-4 rounded border-lavender/50 text-teal focus:ring-teal/30"
                        />
                        <label for="remember" class="text-sm text-teal/70">Remember me</label>
                    </div>

                    <button 
                        type="submit" 
                        :disabled="form.processing"
                        class="w-full btn-primary py-3 rounded-xl font-semibold disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <span v-if="form.processing">Signing in...</span>
                        <span v-else>Sign In</span>
                    </button>
                </form>

                <p class="mt-8 text-center text-sm text-teal/70 animate-fade-in-up delay-400">
                    Don't have an account?
                    <Link href="/signup" class="text-teal font-medium hover:text-teal-dark">
                        Sign up for free
                    </Link>
                </p>
            </div>
        </div>

        <!-- Right Side - Image/Illustration (hidden on mobile) -->
        <div class="hidden lg:flex flex-1 bg-gradient-navy items-center justify-center p-12">
            <div class="max-w-md text-center text-white animate-fade-in">
                <div class="w-24 h-24 bg-white/10 rounded-3xl flex items-center justify-center mx-auto mb-8">
                    <svg class="w-12 h-12 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <h2 class="text-2xl font-bold mb-4">Manage Your Meetings Effortlessly</h2>
                <p class="text-cream/70">
                    Join thousands of teams who use YUJIX to schedule, manage, and optimize their meetings.
                </p>
            </div>
        </div>
    </div>
</template>
