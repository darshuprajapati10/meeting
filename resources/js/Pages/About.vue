<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';

const team = [
    { name: 'Alex Johnson', role: 'CEO & Co-Founder', image: 'AJ', color: 'bg-blue-500', bio: 'Former product lead at Google, passionate about productivity tools.' },
    { name: 'Sarah Chen', role: 'CTO & Co-Founder', image: 'SC', color: 'bg-purple-500', bio: 'Full-stack engineer with 15 years of experience building scalable systems.' },
    { name: 'Michael Park', role: 'Head of Product', image: 'MP', color: 'bg-green-500', bio: 'Design thinking advocate focused on user-centered solutions.' },
    { name: 'Emily Watson', role: 'Head of Design', image: 'EW', color: 'bg-orange-500', bio: 'Award-winning designer specializing in mobile and web interfaces.' },
];

const values = [
    { title: 'Efficiency', description: 'We build tools that save time and streamline workflows for teams everywhere.', icon: 'clock' },
    { title: 'Simplicity', description: 'Complex problems deserve elegant, intuitive solutions that anyone can use.', icon: 'cube' },
    { title: 'Privacy First', description: 'Your data security and privacy are our top priorities. Full control, always.', icon: 'shield' },
    { title: 'Collaboration', description: 'Great things happen when teams work together seamlessly across platforms.', icon: 'users' },
];

const milestones = [
    { year: '2023', title: 'Founded', description: 'YUJIX was born from a simple idea: meetings should be effortless to manage.' },
    { year: '2023', title: 'First Launch', description: 'Released our MVP with core meeting scheduling and contact management features.' },
    { year: '2024', title: 'Survey Builder', description: 'Added comprehensive multi-step surveys with 9 question types.' },
    { year: '2024', title: '10K Teams', description: 'Reached 10,000 active teams using YUJIX daily for meeting management.' },
    { year: '2024', title: 'Cross-Platform', description: 'Launched on iOS, Android, and Web with seamless synchronization.' },
    { year: '2025', title: 'Enterprise', description: 'Introduced Enterprise plan with SSO, API access, and custom integrations.' },
];

const stats = [
    { value: '10K+', label: 'Active Teams', description: 'Teams trust YUJIX daily', target: 10000, suffix: 'K+' },
    { value: '50K+', label: 'Meetings Daily', description: 'Scheduled and managed', target: 50000, suffix: 'K+' },
    { value: '30+', label: 'Countries', description: 'Global presence', target: 30, suffix: '+' },
    { value: '99.9%', label: 'Uptime', description: 'Reliability guaranteed', target: 99.9, suffix: '%' },
];

const displayedStats = ref(stats.map(stat => ({ ...stat, current: 0, animated: false })));
const statsRef = ref(null);
let observer = null;

// Format number for display
function formatNumber(num, originalValue) {
    if (originalValue.includes('K+')) {
        return (num / 1000).toFixed(0) + 'K+';
    } else if (originalValue.includes('%')) {
        return num.toFixed(1) + '%';
    } else if (originalValue.includes('+')) {
        return Math.floor(num) + '+';
    } else {
        return num.toFixed(1);
    }
}

// Animate counting
function animateCounter(stat, index) {
    if (stat.animated) return;
    
    stat.animated = true;
    const duration = 2000; // 2 seconds
    const startTime = Date.now();
    const startValue = 0;
    const endValue = stat.target;
    
    const animate = () => {
        const now = Date.now();
        const elapsed = now - startTime;
        const progress = Math.min(elapsed / duration, 1);
        
        // Easing function (ease-out)
        const easeOut = 1 - Math.pow(1 - progress, 3);
        const currentValue = startValue + (endValue - startValue) * easeOut;
        
        displayedStats.value[index].current = currentValue;
        
        if (progress < 1) {
            requestAnimationFrame(animate);
        } else {
            displayedStats.value[index].current = endValue;
        }
    };
    
    requestAnimationFrame(animate);
}

// Setup intersection observer
onMounted(() => {
    if (!statsRef.value) return;
    
    observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    const index = parseInt(entry.target.dataset.index);
                    if (index !== undefined && !displayedStats.value[index].animated) {
                        animateCounter(displayedStats.value[index], index);
                    }
                }
            });
        },
        {
            threshold: 0.3 // Trigger when 30% visible
        }
    );
    
    // Observe each stat element
    const statElements = statsRef.value.querySelectorAll('[data-stat-index]');
    statElements.forEach((el) => observer.observe(el));
});

onUnmounted(() => {
    if (observer) {
        observer.disconnect();
    }
});
</script>

<template>
    <Head>
        <title>About YUJIX - Modern Meeting Management Platform for Teams</title>
        <meta name="description" content="Learn about YUJIX - the meeting management platform built for teams who value their time. Our mission is to make every meeting productive and organized." />
        <meta name="keywords" content="about YUJIX, meeting management company, team productivity, meeting software" />
    </Head>
    <AppLayout>
        <!-- Hero Section -->
        <section class="pt-32 pb-20 bg-gradient-radial bg-hero-pattern">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="max-w-3xl mx-auto text-center">
                    <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold text-navy mb-6 animate-fade-in-up">
                        Our Mission is
                        <span class="text-teal">Efficient Meetings</span>
                    </h1>
                    <p class="text-lg text-teal/80 animate-fade-in-up delay-100">
                        We're building the future of meeting management, helping teams save time, organize contacts, and gather valuable feedback through comprehensive surveys.
                    </p>
                </div>
            </div>
        </section>

        <!-- Story Section -->
        <section class="py-20 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid lg:grid-cols-2 gap-12 items-center">
                    <div class="animate-fade-in-up">
                        <h2 class="text-3xl font-bold text-navy mb-6">Our Story</h2>
                        <div class="space-y-4 text-teal/80 leading-relaxed">
                            <p>
                                YUJIX was born out of frustration. Like many professionals, our founders spent countless hours in back-to-back meetings, struggling with multiple tools, missed reminders, and scattered contacts.
                            </p>
                            <p>
                                In 2023, we set out to create something better - a unified platform that makes scheduling, managing, and optimizing meetings effortless. We believed that with the right tools, teams could reclaim their time and make every meeting count.
                            </p>
                            <p>
                                Our solution combines smart calendar views, powerful contact management with CSV import/export, multi-step surveys with 9 question types, and real-time push notifications via Firebase - all wrapped in an intuitive interface that works across iOS, Android, and Web.
                            </p>
                            <p>
                                Today, thousands of teams trust YUJIX to streamline their meeting workflows, from small startups to Fortune 500 companies. But we're just getting started.
                            </p>
                        </div>
                    </div>
                    <div ref="statsRef" class="animate-fade-in-up delay-200">
                        <div class="glass rounded-3xl p-8">
                            <div class="grid grid-cols-2 gap-8">
                                <div 
                                    v-for="(stat, index) in displayedStats" 
                                    :key="stat.label" 
                                    :data-stat-index="index"
                                    class="text-center"
                                >
                                    <div class="text-4xl font-bold text-navy">
                                        {{ formatNumber(stat.current, stat.value) }}
                                    </div>
                                    <div class="text-teal font-medium">{{ stat.label }}</div>
                                    <div class="text-sm text-teal/60">{{ stat.description }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Timeline Section -->
        <section class="py-20 bg-cream">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="text-3xl font-bold text-navy mb-4 animate-fade-in-up">Our Journey</h2>
                    <p class="text-lg text-teal/70 animate-fade-in-up delay-100">
                        From idea to 10,000+ teams in just two years
                    </p>
                </div>

                <div class="relative">
                    <!-- Timeline line -->
                    <div class="absolute left-4 md:left-1/2 top-0 bottom-0 w-0.5 bg-lavender/50 transform md:-translate-x-1/2"></div>

                    <div class="space-y-8">
                        <div
                            v-for="(milestone, index) in milestones"
                            :key="milestone.title"
                            :class="['relative flex items-center gap-8 animate-fade-in-up', index % 2 === 0 ? 'md:flex-row' : 'md:flex-row-reverse']"
                            :style="{ animationDelay: `${index * 100}ms` }"
                        >
                            <!-- Content -->
                            <div :class="['flex-1 ml-12 md:ml-0', index % 2 === 0 ? 'md:text-right md:pr-12' : 'md:pl-12']">
                                <div class="glass rounded-xl p-6">
                                    <span class="text-sm font-semibold text-teal">{{ milestone.year }}</span>
                                    <h3 class="text-lg font-bold text-navy mt-1">{{ milestone.title }}</h3>
                                    <p class="text-sm text-teal/70 mt-2">{{ milestone.description }}</p>
                                </div>
                            </div>

                            <!-- Dot -->
                            <div class="absolute left-4 md:left-1/2 w-4 h-4 bg-teal rounded-full transform md:-translate-x-1/2 ring-4 ring-cream"></div>

                            <!-- Spacer for alternating layout -->
                            <div class="hidden md:block flex-1"></div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Values Section -->
        <section class="py-20 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="text-3xl font-bold text-navy mb-4 animate-fade-in-up">Our Values</h2>
                    <p class="text-lg text-teal/70 animate-fade-in-up delay-100">
                        The principles that guide everything we build
                    </p>
                </div>

                <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div
                        v-for="(value, index) in values"
                        :key="value.title"
                        class="glass rounded-2xl p-6 text-center card-hover animate-fade-in-up"
                        :style="{ animationDelay: `${index * 100}ms` }"
                    >
                        <div class="w-14 h-14 bg-gradient-navy rounded-xl flex items-center justify-center mx-auto mb-4">
                            <svg v-if="value.icon === 'clock'" class="w-7 h-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <svg v-else-if="value.icon === 'cube'" class="w-7 h-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                            <svg v-else-if="value.icon === 'shield'" class="w-7 h-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                            <svg v-else-if="value.icon === 'users'" class="w-7 h-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                        </div>
                        <h3 class="font-semibold text-navy mb-2">{{ value.title }}</h3>
                        <p class="text-sm text-teal/70">{{ value.description }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Team Section -->
        <section class="py-20 bg-cream">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="text-3xl font-bold text-navy mb-4 animate-fade-in-up">Meet Our Team</h2>
                    <p class="text-lg text-teal/70 animate-fade-in-up delay-100">
                        The people behind YUJIX
                    </p>
                </div>

                <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
                    <div
                        v-for="(member, index) in team"
                        :key="member.name"
                        class="glass rounded-2xl p-6 text-center card-hover animate-fade-in-up"
                        :style="{ animationDelay: `${index * 100}ms` }"
                    >
                        <div :class="[member.color, 'w-24 h-24 rounded-2xl mx-auto mb-4 flex items-center justify-center text-white text-2xl font-bold']">
                            {{ member.image }}
                        </div>
                        <h3 class="font-semibold text-navy">{{ member.name }}</h3>
                        <p class="text-sm text-teal font-medium mb-2">{{ member.role }}</p>
                        <p class="text-xs text-teal/60">{{ member.bio }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Tech Stack Section -->
        <section class="py-20 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="text-3xl font-bold text-navy mb-4 animate-fade-in-up">Built with Modern Technology</h2>
                    <p class="text-lg text-teal/70 animate-fade-in-up delay-100">
                        Reliable, scalable, and secure infrastructure
                    </p>
                </div>

                <div class="grid md:grid-cols-3 gap-8">
                    <div class="glass rounded-2xl p-6 text-center animate-fade-in-up">
                        <div class="w-16 h-16 bg-blue-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <h3 class="font-semibold text-navy mb-2">Flutter Mobile App</h3>
                        <p class="text-sm text-teal/70">Cross-platform iOS and Android app with native performance and beautiful UI.</p>
                    </div>

                    <div class="glass rounded-2xl p-6 text-center animate-fade-in-up delay-100">
                        <div class="w-16 h-16 bg-red-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01" />
                            </svg>
                        </div>
                        <h3 class="font-semibold text-navy mb-2">Laravel Backend</h3>
                        <p class="text-sm text-teal/70">Robust Laravel 12 API with Sanctum authentication and Firebase notifications.</p>
                    </div>

                    <div class="glass rounded-2xl p-6 text-center animate-fade-in-up delay-200">
                        <div class="w-16 h-16 bg-yellow-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                        </div>
                        <h3 class="font-semibold text-navy mb-2">Firebase Cloud Messaging</h3>
                        <p class="text-sm text-teal/70">Real-time push notifications to keep your team informed and on schedule.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="py-20 bg-gradient-navy">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <h2 class="text-3xl sm:text-4xl font-bold text-white mb-6 animate-fade-in-up">
                    Join Our Journey
                </h2>
                <p class="text-lg text-cream/80 mb-10 animate-fade-in-up delay-100">
                    Be part of the meeting management revolution. Start using YUJIX today and transform how your team collaborates.
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
