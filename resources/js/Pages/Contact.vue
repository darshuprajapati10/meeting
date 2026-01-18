<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, reactive, computed } from 'vue';

const form = reactive({
    name: '',
    email: '',
    subject: '',
    message: ''
});

const errors = reactive({
    name: '',
    email: '',
    subject: '',
    message: ''
});

const isSubmitting = ref(false);
const isSuccess = ref(false);
const touched = reactive({
    name: false,
    email: false,
    subject: false,
    message: false
});

// Validation rules
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
            } else if (form.name.trim().length < 2) {
                errors.name = 'Name must be at least 2 characters';
            }
            break;
        case 'email':
            if (!form.email.trim()) {
                errors.email = 'Email is required';
            } else if (!validateEmail(form.email)) {
                errors.email = 'Please enter a valid email address';
            }
            break;
        case 'subject':
            if (!form.subject.trim()) {
                errors.subject = 'Subject is required';
            } else if (form.subject.trim().length < 3) {
                errors.subject = 'Subject must be at least 3 characters';
            }
            break;
        case 'message':
            if (!form.message.trim()) {
                errors.message = 'Message is required';
            } else if (form.message.trim().length < 10) {
                errors.message = 'Message must be at least 10 characters';
            }
            break;
    }
};

const isFormValid = computed(() => {
    return form.name.trim().length >= 2 &&
           validateEmail(form.email) &&
           form.subject.trim().length >= 3 &&
           form.message.trim().length >= 10;
});

const validateAllFields = () => {
    validateField('name');
    validateField('email');
    validateField('subject');
    validateField('message');
};

const submitForm = async () => {
    validateAllFields();

    if (!isFormValid.value) {
        return;
    }

    isSubmitting.value = true;

    // Simulate API call delay
    await new Promise(resolve => setTimeout(resolve, 1500));

    isSubmitting.value = false;
    isSuccess.value = true;

    // Reset form
    form.name = '';
    form.email = '';
    form.subject = '';
    form.message = '';
    Object.keys(touched).forEach(key => touched[key] = false);
};

const resetSuccess = () => {
    isSuccess.value = false;
};

const contactReasons = [
    { value: 'general', label: 'General Inquiry' },
    { value: 'demo', label: 'Request a Demo' },
    { value: 'sales', label: 'Sales Question' },
    { value: 'support', label: 'Technical Support' },
    { value: 'subscription', label: 'PRO Plan & Subscription' },
    { value: 'partnership', label: 'Partnership Opportunity' }
];

const faqs = [
    {
        question: 'How do I create a meeting?',
        answer: 'Navigate to Meetings, click "Schedule Meeting", fill in the details (title, date, time, duration), add attendees from your contacts, and save.'
    },
    {
        question: 'Can I import my existing contacts?',
        answer: 'Yes! Go to Contacts, click Import, download our CSV template, fill in your data, and upload. We\'ll preview before importing and show you the results.'
    },
    {
        question: 'How do notifications work?',
        answer: 'YUJIX sends push notifications via Firebase and email reminders. You can customize timing and notification types in your settings.'
    },
    {
        question: 'What survey question types are available?',
        answer: 'We support 9 types: Short Answer, Email, Number, Paragraph, Dropdown, Date, Multiple Choice, Checkboxes, and Rating Scale.'
    }
];
</script>

<template>
    <Head>
        <title>Contact Us - Get Help with YUJIX Meeting Management App</title>
        <meta name="description" content="Contact YUJIX support team. Get help with meeting scheduling, surveys, or any questions about our meeting management platform. We're here to help!" />
        <meta name="keywords" content="contact YUJIX, meeting app support, customer service, help desk" />
    </Head>
    <AppLayout>
        <!-- Hero Section -->
        <section class="pt-32 pb-16 bg-gradient-radial bg-hero-pattern">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <h1 class="text-4xl sm:text-5xl font-bold text-navy mb-6 animate-fade-in-up">
                    Get in <span class="text-teal">Touch</span>
                </h1>
                <p class="text-lg text-teal/80 max-w-2xl mx-auto animate-fade-in-up delay-100">
                    Have questions about YUJIX? Want to schedule a demo? Our team is here to help you get the most out of your meeting management.
                </p>
            </div>
        </section>

        <!-- Contact Section -->
        <section class="py-20 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid lg:grid-cols-2 gap-12">
                    <!-- Contact Info -->
                    <div class="animate-fade-in-up">
                        <h2 class="text-2xl font-bold text-navy mb-6">Contact Information</h2>
                        <p class="text-teal/70 mb-8">
                            Fill out the form and our team will get back to you within 24 hours. For urgent matters, reach us directly.
                        </p>

                        <div class="space-y-6">
                            <div class="flex items-start gap-4">
                                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center flex-shrink-0">
                                    <svg class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-navy">Email</h3>
                                    <p class="text-teal/70">info@yujix.com</p>
                                   
                                </div>
                            </div>

                            <div class="flex items-start gap-4">
                                <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center flex-shrink-0">
                                    <svg class="w-6 h-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-navy">Phone</h3>
                                    <p class="text-teal/70">+91 9265299142</p>
                                    <p class="text-sm text-teal/50">Mon-Fri, 10am-7pm EST</p>
                                </div>
                            </div>

                            <div class="flex items-start gap-4">
                                <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center flex-shrink-0">
                                    <svg class="w-6 h-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-navy">Office</h3>
                                    <p class="text-teal/70">403, LINK, 100 FT RCC ROAD</p>
                                    <p class="text-teal/70">Serenity Space Rd, near JLR Showroom, Upper, Gota</p>
                                    <p class="text-teal/70">Ahmedabad, Gujarat 382481</p>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Links -->
                        <div class="mt-10">
                            <h3 class="font-semibold text-navy mb-4">Quick Links</h3>
                            <div class="flex flex-wrap gap-3">
                                <Link href="/features" class="px-4 py-2 bg-cream rounded-lg text-sm text-navy hover:bg-lavender/30 transition-colors">
                                    View Features
                                </Link>
                                <Link href="/pricing" class="px-4 py-2 bg-cream rounded-lg text-sm text-navy hover:bg-lavender/30 transition-colors">
                                    Pricing Plans
                                </Link>
                                <Link href="/about" class="px-4 py-2 bg-cream rounded-lg text-sm text-navy hover:bg-lavender/30 transition-colors">
                                    About Us
                                </Link>
                            </div>
                        </div>

                        <!-- Social Links -->
                        <div class="mt-10">
                            <h3 class="font-semibold text-navy mb-4">Follow Us</h3>
                            <div class="flex gap-4">
                                <a href="#" class="w-10 h-10 bg-cream rounded-lg flex items-center justify-center text-teal hover:bg-lavender/30 transition-colors">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                                </a>
                                <a href="#" class="w-10 h-10 bg-cream rounded-lg flex items-center justify-center text-teal hover:bg-lavender/30 transition-colors">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/></svg>
                                </a>
                                <a href="#" class="w-10 h-10 bg-cream rounded-lg flex items-center justify-center text-teal hover:bg-lavender/30 transition-colors">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Contact Form -->
                    <div class="animate-fade-in-up delay-200">
                        <div class="glass rounded-2xl p-8">
                            <!-- Success Message -->
                            <div v-if="isSuccess" class="text-center py-8 animate-fade-in-up">
                                <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6 animate-bounce-in">
                                    <svg class="w-10 h-10 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                                <h2 class="text-2xl font-bold text-navy mb-3">Message Sent!</h2>
                                <p class="text-teal/70 mb-6">
                                    Thank you for reaching out. Our team will get back to you within 24 hours.
                                </p>
                                <button
                                    @click="resetSuccess"
                                    class="btn-primary px-6 py-3 rounded-xl font-semibold"
                                >
                                    Send Another Message
                                </button>
                            </div>

                            <!-- Contact Form -->
                            <div v-else>
                                <h2 class="text-2xl font-bold text-navy mb-6">Send us a message</h2>
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
                                            placeholder="Your name"
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
                                            Email <span class="text-red-500">*</span>
                                        </label>
                                        <input
                                            v-model="form.email"
                                            @blur="validateField('email')"
                                            @input="touched.email && validateField('email')"
                                            type="email"
                                            placeholder="your@email.com"
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

                                    <!-- Subject Field -->
                                    <div>
                                        <label class="block text-sm font-medium text-navy mb-2">
                                            Subject <span class="text-red-500">*</span>
                                        </label>
                                        <input
                                            v-model="form.subject"
                                            @blur="validateField('subject')"
                                            @input="touched.subject && validateField('subject')"
                                            type="text"
                                            placeholder="How can we help?"
                                            :class="[
                                                'w-full px-4 py-3 rounded-xl border transition-colors',
                                                errors.subject && touched.subject
                                                    ? 'border-red-400 focus:ring-2 focus:ring-red-200 focus:border-red-400'
                                                    : 'border-lavender/50 focus:ring-2 focus:ring-teal/30 focus:border-teal'
                                            ]"
                                        />
                                        <p v-if="errors.subject && touched.subject" class="mt-1.5 text-sm text-red-500 flex items-center gap-1">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            {{ errors.subject }}
                                        </p>
                                    </div>

                                    <!-- Message Field -->
                                    <div>
                                        <label class="block text-sm font-medium text-navy mb-2">
                                            Message <span class="text-red-500">*</span>
                                        </label>
                                        <textarea
                                            v-model="form.message"
                                            @blur="validateField('message')"
                                            @input="touched.message && validateField('message')"
                                            rows="5"
                                            placeholder="Tell us more about your question or how we can help..."
                                            :class="[
                                                'w-full px-4 py-3 rounded-xl border transition-colors resize-none',
                                                errors.message && touched.message
                                                    ? 'border-red-400 focus:ring-2 focus:ring-red-200 focus:border-red-400'
                                                    : 'border-lavender/50 focus:ring-2 focus:ring-teal/30 focus:border-teal'
                                            ]"
                                        ></textarea>
                                        <p v-if="errors.message && touched.message" class="mt-1.5 text-sm text-red-500 flex items-center gap-1">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            {{ errors.message }}
                                        </p>
                                        <p class="mt-1 text-xs text-teal/50">Minimum 10 characters</p>
                                    </div>

                                    <!-- Submit Button -->
                                    <button
                                        type="submit"
                                        :disabled="isSubmitting"
                                        :class="[
                                            'w-full py-3 rounded-xl font-semibold transition-all flex items-center justify-center gap-2',
                                            isSubmitting
                                                ? 'bg-teal/70 cursor-not-allowed'
                                                : 'btn-primary hover:shadow-lg'
                                        ]"
                                    >
                                        <svg v-if="isSubmitting" class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        {{ isSubmitting ? 'Sending...' : 'Send Message' }}
                                    </button>

                                    <p class="text-xs text-teal/50 text-center">
                                        By submitting this form, you agree to our
                                        <a href="#" class="text-teal hover:underline">Privacy Policy</a>
                                    </p>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- FAQ Section -->
        <section class="py-20 bg-cream">
            <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-12">
                    <h2 class="text-3xl font-bold text-navy mb-4 animate-fade-in-up">Quick Help</h2>
                    <p class="text-lg text-teal/70 animate-fade-in-up delay-100">
                        Common questions about YUJIX
                    </p>
                </div>

                <div class="space-y-4">
                    <div
                        v-for="(faq, index) in faqs"
                        :key="faq.question"
                        class="glass rounded-xl p-6 animate-fade-in-up"
                        :style="{ animationDelay: `${index * 50}ms` }"
                    >
                        <h3 class="font-semibold text-navy mb-2">{{ faq.question }}</h3>
                        <p class="text-teal/70 text-sm">{{ faq.answer }}</p>
                    </div>
                </div>
            </div>
        </section>
    </AppLayout>
</template>
