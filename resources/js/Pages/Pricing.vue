<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref } from 'vue';

const isAnnual = ref(true);

const plans = [
    {
        name: 'FREE',
        description: 'Perfect for individuals and small teams getting started',
        monthlyPrice: 0,
        annualPrice: 0,
        features: [
            '20 meetings/month',
            '10 attendees per meeting',
            '50 contacts',
            '1 active survey',
            '50 survey responses/month',
            '1 user included',
            '100 MB storage',
            'Month calendar view',
            'All 4 meeting types',
            'Basic meeting reminders',
            'Push notifications',
            'Email support (48h response)',
            '1 year data retention'
        ],
        cta: 'Get Started Free',
        popular: false
    },
    {
        name: 'PRO',
        description: 'For growing teams who need powerful meeting tools',
        monthlyPrice: 999,
        annualPrice: 833, // ₹9,999/year = ₹833/month (17% off)
        features: [
            'Unlimited meetings',
            'Unlimited attendees per meeting',
            'Unlimited contacts',
            'Unlimited active surveys',
            'Unlimited survey responses',
            '5 users included',
            '10 GB storage',
            'All calendar views (Day/Week/Month)',
            'Custom meeting reminders',
            'CSV import/export',
            'Advanced survey analytics',
            'Contact groups & favorites',
            'Advanced filters & search',
            'Remove OngoingMeet branding',
            'Priority email support (24h)',
            'In-app chat support',
            'Unlimited data retention'
        ],
        cta: 'Start Free Trial',
        popular: true
    }
];

const faqs = [
    {
        question: 'Can I try PRO before buying?',
        answer: 'Yes! PRO plan comes with a free trial. No credit card required to start. Experience all features including unlimited meetings, surveys, and analytics.'
    },
    {
        question: 'What payment methods do you accept?',
        answer: 'We accept all major payment methods through Razorpay including UPI, Credit/Debit Cards, Net Banking, Wallets, and more. All payments are secure and processed in Indian Rupees (₹).'
    },
    {
        question: 'Can I change plans later?',
        answer: 'You can upgrade from FREE to PRO at any time. Changes take effect immediately, and we\'ll prorate any difference. Your data is always preserved when switching plans. You can also add additional users as an add-on to PRO plan.'
    },
    {
        question: 'How many team members can I add?',
        answer: 'FREE plan supports 1 user only. PRO plan includes 5 users. You can add more users as an add-on for ₹149/user/month. All users in your organization share the same limits and features.'
    },
    {
        question: 'What happens if I exceed my FREE plan limits?',
        answer: 'You\'ll receive notifications when approaching limits. To continue using features beyond FREE limits, upgrade to PRO for unlimited access. Your existing data is preserved during upgrade.'
    },
    {
        question: 'Do you offer annual billing discounts?',
        answer: 'Yes! Save 17% when you choose annual billing for PRO plan. Instead of ₹11,988/year (₹999/month), pay only ₹9,999/year - that\'s ₹1,989 in savings!'
    },
    {
        question: 'Can I add more users to PRO plan?',
        answer: 'Yes! PRO plan includes 5 users. Additional users can be added as an add-on for ₹149/user/month. Perfect for growing teams who need more than 5 members.'
    },
    {
        question: 'Is GST included in the price?',
        answer: 'All prices shown are exclusive of GST. GST will be added as applicable based on your organization\'s GST status and location. You can manage your GST details in your organization settings.'
    }
];

const comparisons = [
    { feature: 'Meetings per month', free: '20', pro: 'Unlimited' },
    { feature: 'Attendees per meeting', free: '10', pro: 'Unlimited' },
    { feature: 'Contacts', free: '50', pro: 'Unlimited' },
    { feature: 'Active surveys', free: '1', pro: 'Unlimited' },
    { feature: 'Survey responses/month', free: '50', pro: 'Unlimited' },
    { feature: 'Users included', free: '1', pro: '5' },
    { feature: 'Storage', free: '100 MB', pro: '10 GB' },
    { feature: 'Calendar views', free: 'Month only', pro: 'Day/Week/Month' },
    { feature: 'Meeting types', free: 'All 4 types', pro: 'All 4 types' },
    { feature: 'Meeting reminders', free: 'Basic (24h, 1h)', pro: 'Custom timing' },
    { feature: 'Push notifications', free: true, pro: true },
    { feature: 'CSV import/export', free: false, pro: true },
    { feature: 'Survey analytics', free: 'Basic', pro: 'Advanced (charts, trends)' },
    { feature: 'Contact groups & favorites', free: false, pro: true },
    { feature: 'Advanced filters & search', free: false, pro: true },
    { feature: 'Remove branding', free: false, pro: true },
    { feature: 'Email support', free: '48h response', pro: '24h priority' },
    { feature: 'In-app chat support', free: false, pro: true },
    { feature: 'Data retention', free: '1 year', pro: 'Unlimited' },
    { feature: 'Additional users (add-on)', free: 'N/A', pro: '₹149/user/month' }
];
</script>

<template>
    <Head>
        <title>Pricing - Free Meeting Management App for Indian Market | OngoingMeet</title>
        <meta name="description" content="OngoingMeet pricing: FREE plan for individuals, PRO plan for teams. Simple 2-tier pricing starting at ₹0/month. Pay via Razorpay. Start free today!" />
        <meta name="keywords" content="meeting app pricing India, free meeting scheduler, team meeting software cost, affordable meeting management India, Razorpay payment" />
    </Head>
    <AppLayout>
        <!-- Hero Section -->
        <section class="pt-32 pb-16 bg-gradient-radial bg-hero-pattern">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold text-navy mb-6 animate-fade-in-up">
                    Simple, Transparent
                    <span class="text-teal">Pricing</span>
                </h1>
                <p class="text-lg text-teal/80 max-w-2xl mx-auto mb-10 animate-fade-in-up delay-100">
                    Simple 2-tier pricing for Indian market. Start free and upgrade when you need more.
                </p>

                <!-- Billing Toggle -->
                <div class="flex items-center justify-center gap-4 animate-fade-in-up delay-200">
                    <span :class="['text-sm font-medium transition-colors', !isAnnual ? 'text-navy font-semibold' : 'text-teal/60']">Monthly</span>
                    <button
                        @click="isAnnual = !isAnnual"
                        :class="['relative inline-flex h-8 w-14 items-center rounded-full transition-colors duration-300 focus:outline-none', isAnnual ? 'bg-teal' : 'bg-gray-300']"
                        role="switch"
                        :aria-checked="isAnnual"
                    >
                        <span
                            :class="[
                                'inline-block h-6 w-6 transform rounded-full bg-white shadow-lg transition-transform duration-300',
                                isAnnual ? 'translate-x-7' : 'translate-x-1'
                            ]"
                        ></span>
                    </button>
                    <span :class="['text-sm font-medium transition-colors', isAnnual ? 'text-navy font-semibold' : 'text-teal/60']">
                        Annual
                        <span class="text-green-600 ml-1 font-semibold">(Save 17%)</span>
                    </span>
                </div>
            </div>
        </section>

        <!-- Pricing Cards -->
        <section class="py-16 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid md:grid-cols-2 gap-8 max-w-5xl mx-auto">
                    <div
                        v-for="(plan, index) in plans"
                        :key="plan.name"
                        :class="[
                            'relative rounded-2xl p-8 animate-fade-in-up',
                            plan.popular ? 'bg-gradient-navy text-white ring-4 ring-teal/30 scale-105' : 'glass'
                        ]"
                        :style="{ animationDelay: `${index * 100}ms` }"
                    >
                        <!-- Popular Badge -->
                        <div v-if="plan.popular" class="absolute -top-4 left-1/2 -translate-x-1/2">
                            <span class="bg-yellow-400 text-navy px-4 py-1 rounded-full text-sm font-semibold">
                                Most Popular
                            </span>
                        </div>

                        <div class="text-center mb-8">
                            <h3 :class="['text-xl font-bold mb-2', plan.popular ? 'text-white' : 'text-navy']">
                                {{ plan.name }}
                            </h3>
                            <p :class="['text-sm', plan.popular ? 'text-cream/80' : 'text-teal/70']">
                                {{ plan.description }}
                            </p>
                        </div>

                        <div class="text-center mb-8">
                            <div class="flex items-baseline justify-center gap-1">
                                <span :class="['text-4xl font-bold', plan.popular ? 'text-white' : 'text-navy']">
                                    ₹{{ isAnnual && plan.annualPrice > 0 ? plan.annualPrice : plan.monthlyPrice }}
                                </span>
                                <span :class="['text-sm', plan.popular ? 'text-cream/70' : 'text-teal/60']">/month</span>
                            </div>
                            <p v-if="isAnnual && plan.annualPrice > 0" :class="['text-sm mt-1', plan.popular ? 'text-cream/60' : 'text-teal/50']">
                                Billed annually (₹{{ plan.annualPrice * 12 }}/year)
                                <span class="block text-green-600 font-semibold mt-1">Save ₹1,989/year</span>
                            </p>
                            <p v-if="plan.annualPrice === 0" :class="['text-sm mt-1', plan.popular ? 'text-cream/60' : 'text-teal/50']">
                                Free forever
                            </p>
                        </div>

                        <ul class="space-y-3 mb-8">
                            <li v-for="feature in plan.features" :key="feature" class="flex items-start gap-3">
                                <svg :class="['w-5 h-5 flex-shrink-0 mt-0.5', plan.popular ? 'text-green-400' : 'text-green-500']" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                <span :class="['text-sm', plan.popular ? 'text-cream/90' : 'text-navy/80']">{{ feature }}</span>
                            </li>
                        </ul>

                        <Link
                            :href="plan.cta === 'Contact Sales' ? '/contact' : '/get-started'"
                            :class="[
                                'block w-full py-3 rounded-xl text-center font-semibold transition-all',
                                plan.popular
                                    ? 'bg-white text-navy hover:bg-cream'
                                    : 'btn-primary'
                            ]"
                        >
                            {{ plan.cta }}
                        </Link>
                    </div>
                </div>

                <!-- Add-on Pricing Note -->
                <div class="mt-12 max-w-2xl mx-auto text-center animate-fade-in-up delay-300">
                    <div class="glass rounded-xl p-6">
                        <h3 class="text-lg font-semibold text-navy mb-2">Need More Users?</h3>
                        <p class="text-sm text-teal/70 mb-4">
                            PRO plan includes 5 users. Add more team members for just <span class="font-semibold text-navy">₹149/user/month</span>
                        </p>
                        <p class="text-xs text-teal/60">
                            Perfect for growing teams. Add or remove users anytime. All users share the same PRO features and limits.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Feature Comparison Table -->
        <section class="py-20 bg-cream">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 class="text-3xl font-bold text-navy text-center mb-12 animate-fade-in-up">
                    Compare Plans
                </h2>

                <div class="glass rounded-2xl overflow-hidden animate-fade-in-up delay-100">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-lavender/30">
                                    <th class="text-left py-4 px-6 text-navy font-semibold">Feature</th>
                                    <th class="text-center py-4 px-6 text-navy font-semibold">FREE</th>
                                    <th class="text-center py-4 px-6 text-navy font-semibold bg-teal/10">PRO</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(row, index) in comparisons" :key="row.feature" :class="index % 2 === 0 ? 'bg-white/50' : ''">
                                    <td class="py-3 px-6 text-navy text-sm">{{ row.feature }}</td>
                                    <td class="py-3 px-6 text-center">
                                        <template v-if="typeof row.free === 'boolean'">
                                            <svg v-if="row.free" class="w-5 h-5 text-green-500 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                            <svg v-else class="w-5 h-5 text-gray-300 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </template>
                                        <span v-else class="text-sm text-teal/70">{{ row.free }}</span>
                                    </td>
                                    <td class="py-3 px-6 text-center bg-teal/5">
                                        <template v-if="typeof row.pro === 'boolean'">
                                            <svg v-if="row.pro" class="w-5 h-5 text-green-500 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                            <svg v-else class="w-5 h-5 text-gray-300 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </template>
                                        <span v-else class="text-sm text-navy font-medium">{{ row.pro }}</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>

        <!-- FAQ Section -->
        <section class="py-20 bg-white">
            <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 class="text-3xl font-bold text-navy text-center mb-12 animate-fade-in-up">
                    Frequently Asked Questions
                </h2>

                <div class="space-y-4">
                    <div
                        v-for="(faq, index) in faqs"
                        :key="faq.question"
                        class="glass rounded-xl p-6 animate-fade-in-up"
                        :style="{ animationDelay: `${index * 50}ms` }"
                    >
                        <h3 class="font-semibold text-navy mb-2">{{ faq.question }}</h3>
                        <p class="text-teal/70">{{ faq.answer }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="py-20 bg-gradient-navy">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <h2 class="text-3xl sm:text-4xl font-bold text-white mb-6 animate-fade-in-up">
                    Ready to Get Started?
                </h2>
                <p class="text-lg text-cream/80 mb-10 animate-fade-in-up delay-100">
                    Start with FREE plan today. No credit card required. Upgrade to PRO when you need more.
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center animate-fade-in-up delay-200">
                    <Link href="/get-started" class="bg-white text-navy px-8 py-4 text-base font-semibold rounded-xl hover:bg-cream transition-colors shadow-lg">
                        Start Free Trial
                    </Link>
                    <Link href="/contact" class="border-2 border-white/30 text-white px-8 py-4 text-base font-semibold rounded-xl hover:bg-white/10 transition-colors">
                        Contact Sales
                    </Link>
                </div>
            </div>
        </section>
    </AppLayout>
</template>
