<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const features = [
    {
        icon: 'users',
        title: 'Meeting Management',
        description: 'Schedule meetings with title, date, time, duration, and attendees. Track status from Created to Completed.',
        color: 'bg-green-500'
    },
    {
        icon: 'contact',
        title: 'Contact Organization',
        description: 'Manage professional contacts with groups, favorites, and smart filters. Import/export with CSV support.',
        color: 'bg-purple-500'
    },
    {
        icon: 'clipboard',
        title: 'Survey Builder',
        description: 'Create multi-step surveys with various question types including rating scales, dropdowns, and file attachments.',
        color: 'bg-orange-500'
    },
    {
        icon: 'bell',
        title: 'Smart Notifications',
        description: 'Never miss a meeting with customizable push notifications. Real-time alerts via Firebase.',
        color: 'bg-pink-500'
    },
    {
        icon: 'chart',
        title: 'Survey Analytics',
        description: 'Track survey responses, analyze feedback trends, and measure meeting effectiveness with visual reports.',
        color: 'bg-teal-500'
    }
];


const testimonials = [
    {
        quote: "YUJIX transformed how our team manages meetings. The calendar integration is seamless and the survey feature has been invaluable for collecting feedback after every session.",
        author: "Sarah Chen",
        role: "Product Manager",
        company: "TechCorp",
        avatar: "SC"
    },
    {
        quote: "Finally, a meeting platform that doesn't feel like work. Clean interface, powerful contact management, and the mobile app works flawlessly across all devices.",
        author: "Michael Rodriguez",
        role: "CEO",
        company: "StartupX",
        avatar: "MR"
    },
    {
        quote: "The notification system alone saved us hours of missed meetings. The CSV import made migrating our contacts effortless. Highly recommend for any remote team.",
        author: "Emily Watson",
        role: "HR Director",
        company: "GlobalCo",
        avatar: "EW"
    }
];

const workflows = [
    {
        step: '01',
        title: 'Schedule Meeting',
        description: 'Create meetings with title, date, time, duration, and select attendees from your contacts.'
    },
    {
        step: '02',
        title: 'Invite Attendees',
        description: 'Add participants from your organized contact list with groups and favorites support.'
    },
    {
        step: '03',
        title: 'Get Notified',
        description: 'Receive push and email reminders before your meetings so you never miss one.'
    },
    {
        step: '04',
        title: 'Collect Feedback',
        description: 'Send post-meeting surveys to gather insights and improve future sessions.'
    }
];
</script>

<template>
    <Head>
        <title>YUJIX - Best Meeting Management App for Teams | Schedule Meetings & Surveys</title>
        <meta name="description" content="YUJIX is the #1 meeting management platform. Schedule team meetings, manage contacts, send reminders, and collect feedback with post-meeting surveys. Free to start!" />
        <meta name="keywords" content="meeting management app, team meeting scheduler, meeting reminder app, post-meeting surveys, contact management, meeting organizer" />
    </Head>
    <AppLayout>
        <!-- Hero Section -->
        <section class="relative min-h-screen bg-gradient-radial bg-hero-pattern overflow-hidden pt-20">
            <!-- Floating Elements -->
            <div class="absolute inset-0 overflow-hidden pointer-events-none">
                <div class="absolute top-1/4 left-10 w-20 h-20 bg-lavender/30 rounded-full blur-xl animate-float"></div>
                <div class="absolute top-1/3 right-20 w-32 h-32 bg-teal/20 rounded-full blur-2xl animate-float delay-200"></div>
                <div class="absolute bottom-1/4 left-1/4 w-24 h-24 bg-lavender/20 rounded-full blur-xl animate-float delay-400"></div>
            </div>

            <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-20 pb-32">
                <div class="grid lg:grid-cols-2 gap-12 items-center">
                    <!-- Left Content -->
                    <div class="text-center lg:text-left">
                        <div class="animate-fade-in-up">
                            <span class="inline-flex items-center px-4 py-1.5 rounded-full text-sm font-medium bg-lavender/30 text-navy mb-6">
                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                Trusted by 10,000+ Teams Worldwide
                            </span>
                        </div>

                        <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold text-navy leading-tight animate-fade-in-up delay-100">
                            Efficient Meeting
                            <span class="text-teal"> Management</span>
                        </h1>

                        <p class="mt-6 text-lg text-teal/80 max-w-xl mx-auto lg:mx-0 animate-fade-in-up delay-200">
                            The all-in-one platform for scheduling, managing, and optimizing your meetings. Streamlined organization, smart contacts, and comprehensive surveys for modern teams.
                        </p>

                        <div class="mt-10 flex flex-col sm:flex-row gap-4 justify-center lg:justify-start animate-fade-in-up delay-300">
                            <Link href="/get-started" class="btn-primary px-8 py-4 text-base font-semibold rounded-xl shadow-lg shadow-teal/25 hover:shadow-xl hover:shadow-teal/30 transition-all">
                                Get Started Free
                            </Link>
                            <Link href="/features" class="btn-outline px-8 py-4 text-base font-semibold rounded-xl">
                                Explore Features
                            </Link>
                        </div>
                    </div>

                    <!-- Right Content - Hero Image/Illustration -->
                    <div class="relative animate-fade-in-up delay-200">
                        <div class="relative">
                            <!-- Main Card -->
                            <div class="glass rounded-3xl p-6 shadow-2xl">
                                <div class="flex items-center justify-between mb-6">
                                    <h3 class="text-lg font-semibold text-navy">Today's Schedule</h3>
                                    <div class="flex gap-2">
                                        <span class="px-3 py-1 text-xs bg-teal text-white rounded-lg">Month</span>
                                        <span class="px-3 py-1 text-xs text-teal hover:bg-lavender/20 rounded-lg">Week</span>
                                        <span class="px-3 py-1 text-xs text-teal hover:bg-lavender/20 rounded-lg">Day</span>
                                    </div>
                                </div>

                                <!-- Meeting Cards -->
                                <div class="space-y-3">
                                    <div class="bg-white rounded-xl p-4 border border-lavender/30 card-hover">
                                        <div class="flex items-start gap-4">
                                            <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center flex-shrink-0">
                                                <svg class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                                </svg>
                                            </div>
                                            <div class="flex-1">
                                                <h4 class="font-medium text-navy">Team Standup</h4>
                                                <p class="text-sm text-teal/70">9:00 AM - 9:30 AM</p>
                                                <div class="flex items-center gap-2 mt-2">
                                                    <div class="flex -space-x-2">
                                                        <div class="w-6 h-6 rounded-full bg-purple-200 border-2 border-white flex items-center justify-center text-xs font-medium text-purple-700">SC</div>
                                                        <div class="w-6 h-6 rounded-full bg-green-200 border-2 border-white flex items-center justify-center text-xs font-medium text-green-700">MR</div>
                                                        <div class="w-6 h-6 rounded-full bg-blue-200 border-2 border-white flex items-center justify-center text-xs font-medium text-blue-700">EW</div>
                                                    </div>
                                                    <span class="text-xs text-teal/60">+2 more</span>
                                                </div>
                                            </div>
                                            <span class="px-2 py-1 bg-green-100 text-green-700 text-xs font-medium rounded-lg">Active</span>
                                        </div>
                                    </div>

                                    <div class="bg-white rounded-xl p-4 border border-lavender/30 card-hover">
                                        <div class="flex items-start gap-4">
                                            <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center flex-shrink-0">
                                                <svg class="w-6 h-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                                </svg>
                                            </div>
                                            <div class="flex-1">
                                                <h4 class="font-medium text-navy">Client Review</h4>
                                                <p class="text-sm text-teal/70">2:00 PM - 3:00 PM</p>
                                                <div class="flex items-center gap-2 mt-2">
                                                    <div class="flex -space-x-2">
                                                        <div class="w-6 h-6 rounded-full bg-orange-200 border-2 border-white flex items-center justify-center text-xs font-medium text-orange-700">JD</div>
                                                        <div class="w-6 h-6 rounded-full bg-pink-200 border-2 border-white flex items-center justify-center text-xs font-medium text-pink-700">AK</div>
                                                    </div>
                                                </div>
                                            </div>
                                            <span class="px-2 py-1 bg-yellow-100 text-yellow-700 text-xs font-medium rounded-lg">Pending</span>
                                        </div>
                                    </div>

                                    <div class="bg-white rounded-xl p-4 border border-lavender/30 card-hover">
                                        <div class="flex items-start gap-4">
                                            <div class="w-12 h-12 bg-orange-100 rounded-xl flex items-center justify-center flex-shrink-0">
                                                <svg class="w-6 h-6 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                                </svg>
                                            </div>
                                            <div class="flex-1">
                                                <h4 class="font-medium text-navy">Quarterly Survey</h4>
                                                <p class="text-sm text-teal/70">4 responses collected</p>
                                            </div>
                                            <span class="px-2 py-1 bg-blue-100 text-blue-700 text-xs font-medium rounded-lg">Survey</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Floating notification -->
                            <div class="absolute -top-4 -right-4 glass rounded-xl p-3 shadow-lg animate-bounce-in delay-500">
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 bg-green-500 rounded-lg flex items-center justify-center">
                                        <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-xs font-medium text-navy">Meeting confirmed!</p>
                                        <p class="text-xs text-teal/60">Just now</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Floating stats -->
                            <div class="absolute -bottom-4 -left-4 glass rounded-xl p-3 shadow-lg animate-bounce-in delay-700">
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 bg-purple-500 rounded-lg flex items-center justify-center">
                                        <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-xs font-medium text-navy">24 Contacts</p>
                                        <p class="text-xs text-teal/60">5 favorites</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Wave Divider -->
            <div class="absolute bottom-0 left-0 right-0">
                <svg viewBox="0 0 1440 120" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M0 120L60 110C120 100 240 80 360 70C480 60 600 60 720 65C840 70 960 80 1080 85C1200 90 1320 90 1380 90L1440 90V120H1380C1320 120 1200 120 1080 120C960 120 840 120 720 120C600 120 480 120 360 120C240 120 120 120 60 120H0Z" fill="white"/>
                </svg>
            </div>
        </section>

        <!-- Features Section -->
        <section class="py-20 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="text-3xl sm:text-4xl font-bold text-navy animate-fade-in-up">
                        Everything You Need to Run Better Meetings
                    </h2>
                    <p class="mt-4 text-lg text-teal/70 max-w-2xl mx-auto animate-fade-in-up delay-100">
                        Powerful features designed to streamline your meeting workflow, organize contacts, and gather valuable feedback.
                    </p>
                </div>

                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <div
                        v-for="(feature, index) in features"
                        :key="feature.title"
                        class="group p-6 bg-cream-light rounded-2xl card-hover animate-fade-in-up"
                        :style="{ animationDelay: `${(index + 1) * 100}ms` }"
                    >
                        <div :class="[feature.color, 'w-14 h-14 rounded-xl flex items-center justify-center mb-5 group-hover:scale-110 transition-transform']">
                            <!-- Calendar Icon -->
                            <svg v-if="feature.icon === 'calendar'" class="w-7 h-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <!-- Users Icon -->
                            <svg v-else-if="feature.icon === 'users'" class="w-7 h-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            <!-- Contact Icon -->
                            <svg v-else-if="feature.icon === 'contact'" class="w-7 h-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            <!-- Clipboard Icon -->
                            <svg v-else-if="feature.icon === 'clipboard'" class="w-7 h-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                            </svg>
                            <!-- Bell Icon -->
                            <svg v-else-if="feature.icon === 'bell'" class="w-7 h-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                            <!-- Chart Icon -->
                            <svg v-else-if="feature.icon === 'chart'" class="w-7 h-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-navy mb-2">{{ feature.title }}</h3>
                        <p class="text-teal/70 leading-relaxed">{{ feature.description }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- How It Works Section -->
        <section class="py-20 bg-cream">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="text-3xl sm:text-4xl font-bold text-navy animate-fade-in-up">
                        How YUJIX Works
                    </h2>
                    <p class="mt-4 text-lg text-teal/70 animate-fade-in-up delay-100">
                        Simple workflow to manage your meetings effectively
                    </p>
                </div>

                <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
                    <div
                        v-for="(item, index) in workflows"
                        :key="item.step"
                        class="relative animate-fade-in-up"
                        :style="{ animationDelay: `${(index + 1) * 100}ms` }"
                    >
                        <div class="glass rounded-2xl p-6 text-center h-full">
                            <div class="w-16 h-16 bg-gradient-navy rounded-2xl flex items-center justify-center mx-auto mb-4 text-white text-2xl font-bold">
                                {{ item.step }}
                            </div>
                            <h3 class="text-lg font-semibold text-navy mb-2">{{ item.title }}</h3>
                            <p class="text-sm text-teal/70">{{ item.description }}</p>
                        </div>
                        <!-- Connector Arrow -->
                        <div v-if="index < 3" class="hidden lg:block absolute top-1/2 -right-4 transform -translate-y-1/2 z-10">
                            <svg class="w-8 h-8 text-teal/30" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Testimonials Section -->
        <section class="py-20 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="text-3xl sm:text-4xl font-bold text-navy animate-fade-in-up">
                        Loved by Teams Worldwide
                    </h2>
                    <p class="mt-4 text-lg text-teal/70 animate-fade-in-up delay-100">
                        See what our customers have to say about YUJIX
                    </p>
                </div>

                <div class="grid md:grid-cols-3 gap-8">
                    <div
                        v-for="(testimonial, index) in testimonials"
                        :key="testimonial.author"
                        class="glass p-6 rounded-2xl card-hover animate-fade-in-up"
                        :style="{ animationDelay: `${(index + 1) * 150}ms` }"
                    >
                        <div class="flex items-center gap-1 mb-4">
                            <svg v-for="n in 5" :key="n" class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                        </div>
                        <p class="text-navy/80 mb-6 leading-relaxed">"{{ testimonial.quote }}"</p>
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-full bg-gradient-navy flex items-center justify-center text-white font-semibold">
                                {{ testimonial.avatar }}
                            </div>
                            <div>
                                <p class="font-semibold text-navy">{{ testimonial.author }}</p>
                                <p class="text-sm text-teal/70">{{ testimonial.role }} at {{ testimonial.company }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="py-20 bg-gradient-navy relative overflow-hidden">
            <!-- Background Pattern -->
            <div class="absolute inset-0 opacity-10">
                <div class="absolute top-0 left-0 w-96 h-96 bg-white rounded-full blur-3xl transform -translate-x-1/2 -translate-y-1/2"></div>
                <div class="absolute bottom-0 right-0 w-96 h-96 bg-lavender rounded-full blur-3xl transform translate-x-1/2 translate-y-1/2"></div>
            </div>

            <div class="relative max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <h2 class="text-3xl sm:text-4xl lg:text-5xl font-bold text-white mb-6 animate-fade-in-up">
                    Ready to Transform Your Meetings?
                </h2>
                <p class="text-lg text-cream/80 mb-10 max-w-2xl mx-auto animate-fade-in-up delay-100">
                    Join thousands of teams already using YUJIX for efficient meeting management, smart contact organization, and comprehensive surveys.
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center animate-fade-in-up delay-200">
                    <Link href="/get-started" class="bg-white text-navy px-8 py-4 text-base font-semibold rounded-xl hover:bg-cream transition-colors shadow-lg">
                        Get Started Free
                    </Link>
                    <Link href="/pricing" class="border-2 border-white/30 text-white px-8 py-4 text-base font-semibold rounded-xl hover:bg-white/10 transition-colors">
                        View Pricing
                    </Link>
                </div>
            </div>
        </section>
    </AppLayout>
</template>
