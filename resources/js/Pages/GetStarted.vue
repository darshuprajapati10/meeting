<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, reactive, computed } from 'vue';

const form = reactive({
    name: '',
    email: '',
    company: '',
    teamSize: '',
    interest: ''
});

const errors = reactive({
    name: '',
    email: '',
    company: '',
    teamSize: ''
});

const isSubmitting = ref(false);
const isSuccess = ref(false);
const touched = reactive({
    name: false,
    email: false,
    company: false,
    teamSize: false
});

const teamSizes = [
    { value: '1-5', label: '1-5 members' },
    { value: '6-20', label: '6-20 members' },
    { value: '21-50', label: '21-50 members' },
    { value: '51-100', label: '51-100 members' },
    { value: '100+', label: '100+ members' }
];

const interests = [
    { value: 'meetings', label: 'Meeting Scheduling' },
    { value: 'contacts', label: 'Contact Management' },
    { value: 'surveys', label: 'Survey Builder' },
    { value: 'all', label: 'All Features' }
];

const validateEmail = (email) => {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
};

const validateField = (field) => {
    touched[field] = true;
    errors[field] = '';

    switch (field) {
        case 'name':
            if (!form.name.trim()) {
                errors.name = 'Full name is required';
            }
            break;
        case 'email':
            if (!form.email.trim()) {
                errors.email = 'Work email is required';
            } else if (!validateEmail(form.email)) {
                errors.email = 'Please enter a valid email address';
            }
            break;
        case 'company':
            if (!form.company.trim()) {
                errors.company = 'Company name is required';
            }
            break;
        case 'teamSize':
            if (!form.teamSize) {
                errors.teamSize = 'Please select your team size';
            }
            break;
    }
};

const isFormValid = computed(() => {
    return form.name.trim() &&
           validateEmail(form.email) &&
           form.company.trim() &&
           form.teamSize;
});

const submitForm = async () => {
    validateField('name');
    validateField('email');
    validateField('company');
    validateField('teamSize');

    if (!isFormValid.value) {
        return;
    }

    isSubmitting.value = true;
    await new Promise(resolve => setTimeout(resolve, 1500));
    isSubmitting.value = false;
    isSuccess.value = true;

    form.name = '';
    form.email = '';
    form.company = '';
    form.teamSize = '';
    form.interest = '';
    Object.keys(touched).forEach(key => touched[key] = false);
};

const resetSuccess = () => {
    isSuccess.value = false;
};

const benefits = [
    {
        icon: 'calendar',
        title: 'Smart Calendar',
        description: 'Month, week, and day views with smart filters'
    },
    {
        icon: 'users',
        title: 'Unlimited Contacts',
        description: 'Import/export with CSV, groups & favorites'
    },
    {
        icon: 'clipboard',
        title: 'Survey Builder',
        description: '9 question types with multi-step support'
    },
    {
        icon: 'bell',
        title: 'Push Notifications',
        description: 'Real-time alerts via Firebase'
    }
];

const steps = [
    { number: '1', title: 'Sign Up', description: 'Create your free account in seconds' },
    { number: '2', title: 'Import Contacts', description: 'Add your team via CSV or manually' },
    { number: '3', title: 'Schedule Meetings', description: 'Start organizing your calendar' },
    { number: '4', title: 'Collect Feedback', description: 'Send surveys after meetings' }
];
</script>

<template>
    <Head>
        <title>Get Started Free - Download YUJIX Meeting Management App</title>
        <meta name="description" content="Start using YUJIX today! Download our meeting management app for iOS and Android. Schedule meetings, manage contacts, and collect feedback - all for free." />
        <meta name="keywords" content="download meeting app, get started YUJIX, free meeting scheduler download, iOS Android meeting app" />
    </Head>
    <AppLayout>
        <!-- Hero Section -->
        <section class="pt-32 pb-16 bg-gradient-radial bg-hero-pattern">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold text-navy mb-6 animate-fade-in-up">
                    Start Managing Meetings
                    <span class="text-teal">Effortlessly</span>
                </h1>
                <p class="text-lg text-teal/80 max-w-2xl mx-auto animate-fade-in-up delay-100">
                    Join 10,000+ teams using YUJIX to schedule meetings, organize contacts, and gather feedback with powerful surveys.
                </p>
            </div>
        </section>

        <!-- Main Content -->
        <section class="py-20 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid lg:grid-cols-2 gap-12 items-start">
                    <!-- Left Side - Benefits -->
                    <div class="animate-fade-in-up">
                        <h2 class="text-2xl font-bold text-navy mb-6">What You'll Get</h2>

                        <div class="space-y-4 mb-10">
                            <div
                                v-for="(benefit, index) in benefits"
                                :key="benefit.title"
                                class="flex items-start gap-4 p-4 bg-cream rounded-xl"
                            >
                                <div class="w-12 h-12 bg-gradient-navy rounded-xl flex items-center justify-center flex-shrink-0">
                                    <svg v-if="benefit.icon === 'calendar'" class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    <svg v-else-if="benefit.icon === 'users'" class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                    </svg>
                                    <svg v-else-if="benefit.icon === 'clipboard'" class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                    </svg>
                                    <svg v-else-if="benefit.icon === 'bell'" class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-navy">{{ benefit.title }}</h3>
                                    <p class="text-sm text-teal/70">{{ benefit.description }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Trial Info -->
                        <div class="glass rounded-2xl p-6">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                                    <svg class="w-5 h-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-navy">14-Day Free Trial</h3>
                                    <p class="text-sm text-teal/70">No credit card required</p>
                                </div>
                            </div>
                            <ul class="space-y-2 text-sm text-teal/80">
                                <li class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    Full access to all Professional features
                                </li>
                                <li class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    Unlimited meetings and contacts
                                </li>
                                <li class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    Cancel anytime, no questions asked
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- Right Side - Form -->
                    <div class="animate-fade-in-up delay-200">
                        <div class="glass rounded-2xl p-8">
                            <!-- Success Message -->
                            <div v-if="isSuccess" class="text-center py-8 animate-fade-in-up">
                                <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6 animate-bounce-in">
                                    <svg class="w-10 h-10 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                                <h2 class="text-2xl font-bold text-navy mb-3">You're All Set!</h2>
                                <p class="text-teal/70 mb-4">
                                    Welcome to YUJIX! Check your email for login instructions and get started with your 14-day free trial.
                                </p>
                                <div class="bg-cream rounded-xl p-4 mb-6">
                                    <p class="text-sm text-navy font-medium">What's Next?</p>
                                    <ul class="text-sm text-teal/70 mt-2 space-y-1">
                                        <li>1. Check your inbox for the welcome email</li>
                                        <li>2. Download the mobile app (iOS/Android)</li>
                                        <li>3. Import your contacts via CSV</li>
                                        <li>4. Schedule your first meeting!</li>
                                    </ul>
                                </div>
                                <button
                                    @click="resetSuccess"
                                    class="btn-outline px-6 py-3 rounded-xl font-semibold"
                                >
                                    Register Another Account
                                </button>
                            </div>

                            <!-- Registration Form -->
                            <div v-else>
                                <h2 class="text-2xl font-bold text-navy mb-2">Start Your Free Trial</h2>
                                <p class="text-teal/70 mb-6">Get started in less than 2 minutes</p>

                                <form @submit.prevent="submitForm" class="space-y-5">
                                    <!-- Name Field -->
                                    <div>
                                        <label class="block text-sm font-medium text-navy mb-2">
                                            Full Name <span class="text-red-500">*</span>
                                        </label>
                                        <input
                                            v-model="form.name"
                                            @blur="validateField('name')"
                                            @input="touched.name && validateField('name')"
                                            type="text"
                                            placeholder="John Smith"
                                            :class="[
                                                'w-full px-4 py-3 rounded-xl border transition-colors',
                                                errors.name && touched.name
                                                    ? 'border-red-400 focus:ring-2 focus:ring-red-200 focus:border-red-400'
                                                    : 'border-lavender/50 focus:ring-2 focus:ring-teal/30 focus:border-teal'
                                            ]"
                                        />
                                        <p v-if="errors.name && touched.name" class="mt-1.5 text-sm text-red-500 flex items-center gap-1">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            {{ errors.name }}
                                        </p>
                                    </div>

                                    <!-- Email Field -->
                                    <div>
                                        <label class="block text-sm font-medium text-navy mb-2">
                                            Work Email <span class="text-red-500">*</span>
                                        </label>
                                        <input
                                            v-model="form.email"
                                            @blur="validateField('email')"
                                            @input="touched.email && validateField('email')"
                                            type="email"
                                            placeholder="john@company.com"
                                            :class="[
                                                'w-full px-4 py-3 rounded-xl border transition-colors',
                                                errors.email && touched.email
                                                    ? 'border-red-400 focus:ring-2 focus:ring-red-200 focus:border-red-400'
                                                    : 'border-lavender/50 focus:ring-2 focus:ring-teal/30 focus:border-teal'
                                            ]"
                                        />
                                        <p v-if="errors.email && touched.email" class="mt-1.5 text-sm text-red-500 flex items-center gap-1">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            {{ errors.email }}
                                        </p>
                                    </div>

                                    <!-- Company Field -->
                                    <div>
                                        <label class="block text-sm font-medium text-navy mb-2">
                                            Company Name <span class="text-red-500">*</span>
                                        </label>
                                        <input
                                            v-model="form.company"
                                            @blur="validateField('company')"
                                            @input="touched.company && validateField('company')"
                                            type="text"
                                            placeholder="Acme Inc."
                                            :class="[
                                                'w-full px-4 py-3 rounded-xl border transition-colors',
                                                errors.company && touched.company
                                                    ? 'border-red-400 focus:ring-2 focus:ring-red-200 focus:border-red-400'
                                                    : 'border-lavender/50 focus:ring-2 focus:ring-teal/30 focus:border-teal'
                                            ]"
                                        />
                                        <p v-if="errors.company && touched.company" class="mt-1.5 text-sm text-red-500 flex items-center gap-1">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            {{ errors.company }}
                                        </p>
                                    </div>

                                    <!-- Team Size Field -->
                                    <div>
                                        <label class="block text-sm font-medium text-navy mb-2">
                                            Team Size <span class="text-red-500">*</span>
                                        </label>
                                        <select
                                            v-model="form.teamSize"
                                            @blur="validateField('teamSize')"
                                            @change="validateField('teamSize')"
                                            :class="[
                                                'w-full px-4 py-3 rounded-xl border transition-colors appearance-none bg-white',
                                                errors.teamSize && touched.teamSize
                                                    ? 'border-red-400 focus:ring-2 focus:ring-red-200 focus:border-red-400'
                                                    : 'border-lavender/50 focus:ring-2 focus:ring-teal/30 focus:border-teal'
                                            ]"
                                        >
                                            <option value="">Select team size</option>
                                            <option v-for="size in teamSizes" :key="size.value" :value="size.value">
                                                {{ size.label }}
                                            </option>
                                        </select>
                                        <p v-if="errors.teamSize && touched.teamSize" class="mt-1.5 text-sm text-red-500 flex items-center gap-1">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            {{ errors.teamSize }}
                                        </p>
                                    </div>

                                    <!-- Interest Field (Optional) -->
                                    <div>
                                        <label class="block text-sm font-medium text-navy mb-2">
                                            Primary Interest <span class="text-teal/50">(Optional)</span>
                                        </label>
                                        <div class="grid grid-cols-2 gap-2">
                                            <label
                                                v-for="interest in interests"
                                                :key="interest.value"
                                                :class="[
                                                    'flex items-center gap-2 p-3 rounded-xl border cursor-pointer transition-colors',
                                                    form.interest === interest.value
                                                        ? 'border-teal bg-teal/10'
                                                        : 'border-lavender/50 hover:border-teal/50'
                                                ]"
                                            >
                                                <input
                                                    type="radio"
                                                    v-model="form.interest"
                                                    :value="interest.value"
                                                    class="sr-only"
                                                />
                                                <span :class="['text-sm', form.interest === interest.value ? 'text-teal font-medium' : 'text-navy']">
                                                    {{ interest.label }}
                                                </span>
                                            </label>
                                        </div>
                                    </div>

                                    <!-- Submit Button -->
                                    <button
                                        type="submit"
                                        :disabled="isSubmitting"
                                        :class="[
                                            'w-full py-4 rounded-xl font-semibold text-lg transition-all flex items-center justify-center gap-2',
                                            isSubmitting
                                                ? 'bg-teal/70 cursor-not-allowed'
                                                : 'btn-primary hover:shadow-lg'
                                        ]"
                                    >
                                        <svg v-if="isSubmitting" class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        {{ isSubmitting ? 'Creating Account...' : 'Start Free Trial' }}
                                    </button>

                                    <p class="text-xs text-teal/50 text-center">
                                        By signing up, you agree to our
                                        <a href="#" class="text-teal hover:underline">Terms of Service</a>
                                        and
                                        <a href="#" class="text-teal hover:underline">Privacy Policy</a>
                                    </p>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- How It Works -->
        <section class="py-20 bg-cream">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-12">
                    <h2 class="text-3xl font-bold text-navy mb-4 animate-fade-in-up">Get Started in 4 Simple Steps</h2>
                    <p class="text-lg text-teal/70 animate-fade-in-up delay-100">
                        From signup to your first meeting in minutes
                    </p>
                </div>

                <div class="grid md:grid-cols-4 gap-6">
                    <div
                        v-for="(step, index) in steps"
                        :key="step.number"
                        class="relative text-center animate-fade-in-up"
                        :style="{ animationDelay: `${index * 100}ms` }"
                    >
                        <div class="w-16 h-16 bg-gradient-navy rounded-2xl flex items-center justify-center mx-auto mb-4 text-white text-2xl font-bold">
                            {{ step.number }}
                        </div>
                        <h3 class="font-semibold text-navy mb-2">{{ step.title }}</h3>
                        <p class="text-sm text-teal/70">{{ step.description }}</p>

                        <!-- Connector -->
                        <div v-if="index < 3" class="hidden md:block absolute top-8 left-[60%] w-[80%] h-0.5 bg-lavender/50"></div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Trust Section -->
        <section class="py-16 bg-white">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <p class="text-teal/60 mb-6">Trusted by teams at</p>
                <div class="flex flex-wrap items-center justify-center gap-8 opacity-60">
                    <div class="text-2xl font-bold text-navy">TechCorp</div>
                    <div class="text-2xl font-bold text-navy">StartupX</div>
                    <div class="text-2xl font-bold text-navy">GlobalCo</div>
                    <div class="text-2xl font-bold text-navy">InnovateLab</div>
                    <div class="text-2xl font-bold text-navy">DataSync</div>
                </div>
            </div>
        </section>
    </AppLayout>
</template>
