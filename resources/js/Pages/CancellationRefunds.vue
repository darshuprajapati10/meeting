<script setup>
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
</script>

<template>
    <Head>
        <title>Cancellation & Refunds Policy - YUJIX</title>
        <meta name="description" content="Learn about YUJIX cancellation and refunds policy. Understand our terms for subscription cancellations and refund eligibility." />
        <meta name="keywords" content="cancellation policy, refunds, subscription cancel, YUJIX refund policy" />
    </Head>
    <AppLayout>
        <!-- Hero Section -->
        <section class="pt-32 pb-16 bg-gradient-radial bg-hero-pattern">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <h1 class="text-4xl sm:text-5xl font-bold text-navy mb-6 animate-fade-in-up">
                    Cancellation & <span class="text-teal">Refunds</span>
                </h1>
                <p class="text-lg text-teal/80 max-w-2xl mx-auto animate-fade-in-up delay-100">
                    Our clear and fair cancellation and refund policy to ensure your satisfaction.
                </p>
            </div>
        </section>

        <!-- Content Section -->
        <section class="py-20 bg-white">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="prose prose-lg max-w-none space-y-12">
                    <!-- Cancellation Policy -->
                    <div class="animate-fade-in-up">
                        <div class="glass rounded-2xl p-8 mb-8">
                            <h2 class="text-3xl font-bold text-navy mb-6 flex items-center gap-3">
                                <svg class="w-8 h-8 text-teal" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                Cancellation Policy
                            </h2>
                            
                            <div class="space-y-6 text-teal/80">
                                <div>
                                    <h3 class="text-xl font-semibold text-navy mb-3">Subscription Cancellation</h3>
                                    <p class="leading-relaxed">
                                        You can cancel your YUJIX subscription at any time from your account settings. 
                                        When you cancel, your subscription will remain active until the end of the current billing period. 
                                        You will continue to have access to all premium features until your subscription expires.
                                    </p>
                                </div>

                                <div>
                                    <h3 class="text-xl font-semibold text-navy mb-3">How to Cancel</h3>
                                    <ol class="list-decimal list-inside space-y-2 ml-4">
                                        <li>Log in to your YUJIX account</li>
                                        <li>Navigate to Account Settings</li>
                                        <li>Click on Subscription or Billing</li>
                                        <li>Select "Cancel Subscription"</li>
                                        <li>Confirm your cancellation</li>
                                    </ol>
                                    <p class="mt-4 leading-relaxed">
                                        You will receive a confirmation email once your cancellation is processed. 
                                        Your subscription will remain active until the end of your current billing cycle.
                                    </p>
                                </div>

                                <div>
                                    <h3 class="text-xl font-semibold text-navy mb-3">Immediate Cancellation</h3>
                                    <p class="leading-relaxed">
                                        If you need to cancel immediately and request a refund, please contact our 
                                        support team at <a href="mailto:support@yujix.com" class="text-teal hover:underline">support@yujix.com</a> 
                                        or through our <a href="/contact" class="text-teal hover:underline">contact form</a>.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Refund Policy -->
                    <div class="animate-fade-in-up delay-100">
                        <div class="glass rounded-2xl p-8 mb-8">
                            <h2 class="text-3xl font-bold text-navy mb-6 flex items-center gap-3">
                                <svg class="w-8 h-8 text-teal" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                Refund Policy
                            </h2>
                            
                            <div class="space-y-6 text-teal/80">
                                <div>
                                    <h3 class="text-xl font-semibold text-navy mb-3">30-Day Money-Back Guarantee</h3>
                                    <p class="leading-relaxed">
                                        We offer a 30-day money-back guarantee for all new subscriptions. 
                                        If you're not satisfied with YUJIX within the first 30 days of your subscription, 
                                        contact us for a full refund, no questions asked.
                                    </p>
                                </div>

                                <div>
                                    <h3 class="text-xl font-semibold text-navy mb-3">Eligibility for Refunds</h3>
                                    <p class="leading-relaxed mb-3">You may be eligible for a refund if:</p>
                                    <ul class="list-disc list-inside space-y-2 ml-4">
                                        <li>You cancel within 30 days of your initial subscription</li>
                                        <li>You cancel within 30 days of upgrading to a higher tier</li>
                                        <li>Technical issues prevent you from using the service and cannot be resolved</li>
                                        <li>Duplicate charges due to billing errors</li>
                                    </ul>
                                </div>

                                <div>
                                    <h3 class="text-xl font-semibold text-navy mb-3">Refund Process</h3>
                                    <p class="leading-relaxed">
                                        To request a refund, please contact our support team with your account details 
                                        and reason for cancellation. Refunds will be processed within 5-10 business days 
                                        to the original payment method.
                                    </p>
                                </div>

                                <div>
                                    <h3 class="text-xl font-semibold text-navy mb-3">Non-Refundable Items</h3>
                                    <ul class="list-disc list-inside space-y-2 ml-4">
                                        <li>Subscriptions cancelled after 30 days (unless due to technical issues)</li>
                                        <li>Partially used subscription periods</li>
                                        <li>One-time purchases of add-ons or premium features</li>
                                        <li>Annual subscriptions cancelled after the refund period</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pro-rated Refunds -->
                    <div class="animate-fade-in-up delay-200">
                        <div class="glass rounded-2xl p-8 mb-8">
                            <h2 class="text-3xl font-bold text-navy mb-6">Pro-rated Refunds</h2>
                            
                            <div class="space-y-6 text-teal/80">
                                <p class="leading-relaxed">
                                    For annual subscriptions cancelled after the 30-day guarantee period, 
                                    we may offer pro-rated refunds on a case-by-case basis. Contact our 
                                    support team to discuss your situation.
                                </p>
                                
                                <p class="leading-relaxed">
                                    Monthly subscriptions are not eligible for pro-rated refunds after 
                                    the 30-day guarantee period. Your subscription will remain active 
                                    until the end of the current billing cycle.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Questions -->
                    <div class="animate-fade-in-up delay-300">
                        <div class="glass rounded-2xl p-8 bg-gradient-to-br from-teal/10 to-blue/10">
                            <h2 class="text-2xl font-bold text-navy mb-4">Questions About Cancellations or Refunds?</h2>
                            <p class="text-teal/80 mb-6">
                                If you have any questions about our cancellation or refund policy, 
                                please don't hesitate to reach out to our support team.
                            </p>
                            <a href="/contact" class="btn-primary inline-flex items-center gap-2 px-6 py-3 rounded-xl font-semibold">
                                Contact Support
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </a>
                        </div>
                    </div>

                    <!-- Last Updated -->
                    <div class="text-center text-sm text-teal/50 pt-8">
                        <p>Last updated: {{ new Date().toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' }) }}</p>
                    </div>
                </div>
            </div>
        </section>
    </AppLayout>
</template>

