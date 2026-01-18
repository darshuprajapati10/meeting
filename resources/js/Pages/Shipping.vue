<script setup>
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
</script>

<template>
    <Head>
        <title>Shipping Policy - YUJIX</title>
        <meta name="description" content="YUJIX shipping policy. Learn about our digital service delivery, no physical shipping required for software subscriptions." />
        <meta name="keywords" content="shipping policy, delivery, digital service, YUJIX shipping" />
    </Head>
    <AppLayout>
        <!-- Hero Section -->
        <section class="pt-32 pb-16 bg-gradient-radial bg-hero-pattern">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <h1 class="text-4xl sm:text-5xl font-bold text-navy mb-6 animate-fade-in-up">
                    Shipping <span class="text-teal">Policy</span>
                </h1>
                <p class="text-lg text-teal/80 max-w-2xl mx-auto animate-fade-in-up delay-100">
                    Information about delivery and access to YUJIX services.
                </p>
            </div>
        </section>

        <!-- Content Section -->
        <section class="py-20 bg-white">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="prose prose-lg max-w-none space-y-12">
                    <!-- Digital Service -->
                    <div class="animate-fade-in-up">
                        <div class="glass rounded-2xl p-8 mb-8">
                            <h2 class="text-3xl font-bold text-navy mb-6 flex items-center gap-3">
                                <svg class="w-8 h-8 text-teal" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                                Digital Service Delivery
                            </h2>
                            
                            <div class="space-y-6 text-teal/80">
                                <p class="leading-relaxed">
                                    YUJIX is a cloud-based software-as-a-service (SaaS) platform. We do not ship any 
                                    physical products. All our services are delivered digitally through the internet 
                                    immediately upon account creation and subscription activation.
                                </p>
                                
                                <div class="bg-cream rounded-xl p-6">
                                    <h3 class="text-lg font-semibold text-navy mb-3">Instant Access</h3>
                                    <p class="leading-relaxed">
                                        Once you create an account and complete your subscription, you will have 
                                        immediate access to all features included in your plan. There are no shipping 
                                        delays or waiting periods.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Access Methods -->
                    <div class="animate-fade-in-up delay-100">
                        <div class="glass rounded-2xl p-8 mb-8">
                            <h2 class="text-3xl font-bold text-navy mb-6">How to Access YUJIX</h2>
                            
                            <div class="space-y-6 text-teal/80">
                                <div>
                                    <h3 class="text-xl font-semibold text-navy mb-3">Web Platform</h3>
                                    <p class="leading-relaxed">
                                        Access YUJIX directly from any web browser at 
                                        <a href="https://yujix.com" class="text-teal hover:underline">yujix.com</a>. 
                                        No installation or download required. Simply log in with your account credentials.
                                    </p>
                                </div>

                                <div>
                                    <h3 class="text-xl font-semibold text-navy mb-3">Mobile Apps</h3>
                                    <p class="leading-relaxed mb-3">
                                        Download the YUJIX mobile app from:
                                    </p>
                                    <ul class="list-disc list-inside space-y-2 ml-4">
                                        <li><strong>iOS:</strong> Available on the Apple App Store</li>
                                        <li><strong>Android:</strong> Available on Google Play Store</li>
                                    </ul>
                                    <p class="mt-4 leading-relaxed">
                                        Mobile apps can be downloaded instantly and provide full access to all 
                                        YUJIX features on your smartphone or tablet.
                                    </p>
                                </div>

                                <div>
                                    <h3 class="text-xl font-semibold text-navy mb-3">System Requirements</h3>
                                    <div class="grid md:grid-cols-2 gap-4 mt-4">
                                        <div class="bg-cream rounded-xl p-4">
                                            <h4 class="font-semibold text-navy mb-2">Web Browser</h4>
                                            <ul class="text-sm space-y-1">
                                                <li>• Chrome (latest version)</li>
                                                <li>• Firefox (latest version)</li>
                                                <li>• Safari (latest version)</li>
                                                <li>• Edge (latest version)</li>
                                            </ul>
                                        </div>
                                        <div class="bg-cream rounded-xl p-4">
                                            <h4 class="font-semibold text-navy mb-2">Mobile</h4>
                                            <ul class="text-sm space-y-1">
                                                <li>• iOS 13.0 or later</li>
                                                <li>• Android 8.0 or later</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Account Activation -->
                    <div class="animate-fade-in-up delay-200">
                        <div class="glass rounded-2xl p-8 mb-8">
                            <h2 class="text-3xl font-bold text-navy mb-6">Account Activation</h2>
                            
                            <div class="space-y-6 text-teal/80">
                                <div>
                                    <h3 class="text-xl font-semibold text-navy mb-3">New Accounts</h3>
                                    <p class="leading-relaxed">
                                        After completing registration, your account is activated immediately. 
                                        You can start using all free features right away. Premium features 
                                        become available immediately upon successful subscription payment.
                                    </p>
                                </div>

                                <div>
                                    <h3 class="text-xl font-semibold text-navy mb-3">Subscription Upgrades</h3>
                                    <p class="leading-relaxed">
                                        When you upgrade your subscription, new features and increased limits 
                                        are activated instantly upon payment confirmation. No additional setup 
                                        or waiting period required.
                                    </p>
                                </div>

                                <div>
                                    <h3 class="text-xl font-semibold text-navy mb-3">Email Confirmation</h3>
                                    <p class="leading-relaxed">
                                        Upon account creation, you will receive a welcome email with login 
                                        credentials and access instructions. Check your spam folder if you 
                                        don't see it in your inbox.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Support -->
                    <div class="animate-fade-in-up delay-300">
                        <div class="glass rounded-2xl p-8 mb-8">
                            <h2 class="text-3xl font-bold text-navy mb-6">Need Help Accessing Your Account?</h2>
                            
                            <div class="space-y-4 text-teal/80">
                                <p class="leading-relaxed">
                                    If you experience any issues accessing your YUJIX account or have questions 
                                    about getting started, our support team is here to help.
                                </p>
                                
                                <div class="flex flex-col sm:flex-row gap-4">
                                    <a href="/contact" class="btn-primary inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl font-semibold">
                                        Contact Support
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                        </svg>
                                    </a>
                                    <a href="mailto:support@yujix.com" class="border-2 border-teal text-teal hover:bg-teal hover:text-white inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl font-semibold transition-colors">
                                        Email Us
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Physical Merchandise -->
                    <div class="animate-fade-in-up delay-400">
                        <div class="glass rounded-2xl p-8 bg-gradient-to-br from-teal/10 to-blue/10">
                            <h2 class="text-2xl font-bold text-navy mb-4">Physical Merchandise</h2>
                            <p class="text-teal/80 leading-relaxed">
                                Currently, YUJIX does not offer any physical products or merchandise. 
                                All our services are delivered digitally. If we begin offering physical 
                                products in the future, this policy will be updated with shipping information, 
                                delivery times, and shipping costs.
                            </p>
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
