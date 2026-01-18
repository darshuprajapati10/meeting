<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref } from 'vue';

// Active category filter
const activeCategory = ref('All Posts');

// Newsletter subscription state
const isSubscribed = ref(false);

// SEO-optimized blog posts with long-tail keywords
const blogPosts = [
    {
        id: 1,
        title: 'How to Schedule Team Meetings Effectively in 2024',
        excerpt: 'Learn the best practices for scheduling team meetings that maximize productivity and minimize time waste. Discover proven strategies for meeting management.',
        category: 'Meeting Tips',
        date: 'Dec 15, 2024',
        readTime: '5 min read',
        image: 'meeting',
        slug: 'how-to-schedule-team-meetings-effectively'
    },
    {
        id: 2,
        title: 'Best Meeting Scheduler App for Small Business Teams',
        excerpt: 'Compare the top meeting scheduler apps designed for small businesses. Find the perfect tool to organize your team meetings and boost collaboration.',
        category: 'Product Reviews',
        date: 'Dec 12, 2024',
        readTime: '7 min read',
        image: 'calendar',
        slug: 'best-meeting-scheduler-app-small-business'
    },
    {
        id: 3,
        title: 'How to Create Post-Meeting Surveys That Get Responses',
        excerpt: 'Discover how to design effective post-meeting surveys that capture valuable feedback. Improve your meetings with actionable survey insights.',
        category: 'Survey Guide',
        date: 'Dec 10, 2024',
        readTime: '6 min read',
        image: 'survey',
        slug: 'create-post-meeting-surveys-get-responses'
    },
    {
        id: 4,
        title: 'Meeting Reminder Apps: Never Miss an Important Meeting Again',
        excerpt: 'Explore the best meeting reminder solutions to ensure you and your team never miss important meetings. Set up smart notifications that work.',
        category: 'Productivity',
        date: 'Dec 8, 2024',
        readTime: '4 min read',
        image: 'notification',
        slug: 'meeting-reminder-apps-never-miss-meeting'
    },
    {
        id: 5,
        title: 'How to Manage Meeting Attendees and Contacts Efficiently',
        excerpt: 'Learn how to organize your meeting contacts, create groups, and manage attendees for seamless meeting coordination across your organization.',
        category: 'Organization',
        date: 'Dec 5, 2024',
        readTime: '5 min read',
        image: 'contacts',
        slug: 'manage-meeting-attendees-contacts-efficiently'
    }
];

const categories = [
    { name: 'All Posts', count: 5 },
    { name: 'Meeting Tips', count: 1 },
    { name: 'Product Reviews', count: 1 },
    { name: 'Survey Guide', count: 1 },
    { name: 'Productivity', count: 1 },
    { name: 'Organization', count: 1 }
];

const popularKeywords = [
    'meeting scheduler app',
    'team meeting management',
    'post-meeting surveys',
    'meeting reminders',
    'contact management'
];

// Filter posts by category
const filteredPosts = () => {
    if (activeCategory.value === 'All Posts') {
        return blogPosts;
    }
    return blogPosts.filter(post => post.category === activeCategory.value);
};

// Set active category
const setCategory = (categoryName) => {
    activeCategory.value = categoryName;
};
</script>

<template>
    <Head>
        <title>Blog - Meeting Management Tips & Best Practices | YUJIX</title>
        <meta name="description" content="Expert tips on meeting management, scheduling best practices, post-meeting surveys, and team productivity. Learn how to run better meetings with YUJIX." />
        <meta name="keywords" content="meeting management blog, meeting scheduler tips, team meeting best practices, post-meeting survey guide, meeting productivity" />
    </Head>
    <AppLayout>
        <!-- Hero Section -->
        <section class="pt-32 pb-16 bg-gradient-radial bg-hero-pattern">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold text-navy mb-6 animate-fade-in-up">
                    Meeting Management
                    <span class="text-teal">Blog</span>
                </h1>
                <p class="text-lg text-teal/80 max-w-2xl mx-auto animate-fade-in-up delay-100">
                    Expert tips, best practices, and guides to help you run more effective meetings and boost team productivity.
                </p>
            </div>
        </section>

        <!-- Blog Content -->
        <section class="py-16 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid lg:grid-cols-4 gap-12">
                    <!-- Main Content -->
                    <div class="lg:col-span-3">
                        <div class="grid md:grid-cols-2 gap-8">
                            <article
                                v-for="(post, index) in filteredPosts()"
                                :key="post.id"
                                class="glass rounded-2xl overflow-hidden card-hover animate-fade-in-up cursor-pointer group"
                                :style="{ animationDelay: `${index * 100}ms` }"
                                @click="$inertia.visit(`/blog/${post.slug}`)"
                            >
                                <!-- Post Image -->
                                <div class="h-48 bg-gradient-to-br from-teal/20 to-lavender/30 flex items-center justify-center group-hover:from-teal/30 group-hover:to-lavender/40 transition-all">
                                    <div class="w-16 h-16 bg-gradient-navy rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform">
                                        <svg v-if="post.image === 'meeting'" class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                        <svg v-else-if="post.image === 'calendar'" class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        <svg v-else-if="post.image === 'survey'" class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                                        </svg>
                                        <svg v-else-if="post.image === 'notification'" class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                        </svg>
                                        <svg v-else-if="post.image === 'contacts'" class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                        <svg v-else class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                                        </svg>
                                    </div>
                                </div>

                                <!-- Post Content -->
                                <div class="p-6">
                                    <div class="flex items-center gap-3 mb-3">
                                        <span class="px-3 py-1 bg-teal/10 text-teal text-xs font-medium rounded-full">{{ post.category }}</span>
                                        <span class="text-xs text-teal/60">{{ post.readTime }}</span>
                                    </div>
                                    <h2 class="text-xl font-bold text-navy mb-3 leading-tight group-hover:text-teal transition-colors">
                                        {{ post.title }}
                                    </h2>
                                    <p class="text-teal/70 text-sm mb-4 line-clamp-3">{{ post.excerpt }}</p>
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs text-teal/50">{{ post.date }}</span>
                                        <span class="text-teal font-medium text-sm flex items-center gap-1 group-hover:gap-2 transition-all">
                                            Read More
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                            </svg>
                                        </span>
                                    </div>
                                </div>
                            </article>
                        </div>

                        <!-- No posts message -->
                        <div v-if="filteredPosts().length === 0" class="text-center py-12">
                            <p class="text-teal/70">No posts found in this category.</p>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="lg:col-span-1 space-y-8">
                        <!-- Categories -->
                        <div class="glass rounded-2xl p-6 animate-fade-in-up">
                            <h3 class="font-bold text-navy mb-4">Categories</h3>
                            <ul class="space-y-2">
                                <li v-for="category in categories" :key="category.name">
                                    <button
                                        @click="setCategory(category.name)"
                                        :class="[
                                            'flex items-center justify-between w-full text-sm py-2 px-3 rounded-lg transition-colors',
                                            activeCategory === category.name
                                                ? 'bg-teal text-white'
                                                : 'text-teal/70 hover:bg-cream hover:text-navy'
                                        ]"
                                    >
                                        <span>{{ category.name }}</span>
                                        <span :class="['text-xs px-2 py-0.5 rounded-full', activeCategory === category.name ? 'bg-white/20' : 'bg-cream']">{{ category.count }}</span>
                                    </button>
                                </li>
                            </ul>
                        </div>

                        <!-- Popular Topics -->
                        <div class="glass rounded-2xl p-6 animate-fade-in-up delay-100">
                            <h3 class="font-bold text-navy mb-4">Popular Topics</h3>
                            <div class="flex flex-wrap gap-2">
                                <span
                                    v-for="keyword in popularKeywords"
                                    :key="keyword"
                                    class="px-3 py-1.5 bg-cream text-navy text-xs rounded-full"
                                >
                                    {{ keyword }}
                                </span>
                            </div>
                        </div>

                        <!-- Newsletter -->
                        <div class="glass rounded-2xl p-6 animate-fade-in-up delay-200">
                            <!-- Success View -->
                            <div v-if="isSubscribed" class="text-center py-4">
                                <div class="w-14 h-14 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <svg class="w-7 h-7 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                                <h3 class="font-bold text-navy mb-2">You're Subscribed!</h3>
                                <p class="text-sm text-teal/70">Thanks for subscribing. You'll receive the latest meeting tips in your inbox.</p>
                            </div>
                            <!-- Subscribe Form -->
                            <div v-else>
                                <h3 class="font-bold text-navy mb-2">Subscribe to Updates</h3>
                                <p class="text-sm text-teal/70 mb-4">Get the latest meeting management tips delivered to your inbox.</p>
                                <div class="space-y-3">
                                    <div class="w-full px-4 py-2.5 rounded-xl border border-lavender/50 text-sm text-teal/40 bg-white">Enter your email</div>
                                    <button @click="isSubscribed = true" class="block w-full py-2.5 bg-teal text-white rounded-xl font-medium text-center text-sm hover:bg-teal/90 transition-colors">Subscribe</button>
                                </div>
                            </div>
                        </div>

                        <!-- CTA -->
                        <div class="bg-gradient-navy rounded-2xl p-6 text-white animate-fade-in-up delay-300">
                            <h3 class="font-bold mb-2">Try YUJIX Free</h3>
                            <p class="text-sm text-cream/80 mb-4">Start managing your meetings more effectively today.</p>
                            <Link href="/get-started" class="block w-full py-2.5 bg-white text-navy rounded-xl font-medium text-center text-sm hover:bg-cream transition-colors">
                                Get Started
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- SEO Content Section -->
        <section class="py-16 bg-cream">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-12">
                    <h2 class="text-3xl font-bold text-navy mb-4 animate-fade-in-up">
                        Your Guide to Better Meeting Management
                    </h2>
                    <p class="text-teal/70 animate-fade-in-up delay-100">
                        Discover proven strategies to run more productive meetings
                    </p>
                </div>

                <div class="grid md:grid-cols-3 gap-8">
                    <div class="text-center animate-fade-in-up">
                        <div class="w-14 h-14 bg-gradient-navy rounded-xl flex items-center justify-center mx-auto mb-4">
                            <svg class="w-7 h-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="font-semibold text-navy mb-2">Save Time</h3>
                        <p class="text-sm text-teal/70">Learn how to schedule and manage meetings efficiently to save hours every week.</p>
                    </div>
                    <div class="text-center animate-fade-in-up delay-100">
                        <div class="w-14 h-14 bg-gradient-navy rounded-xl flex items-center justify-center mx-auto mb-4">
                            <svg class="w-7 h-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </div>
                        <h3 class="font-semibold text-navy mb-2">Boost Productivity</h3>
                        <p class="text-sm text-teal/70">Get actionable tips to make every meeting count and drive better outcomes.</p>
                    </div>
                    <div class="text-center animate-fade-in-up delay-200">
                        <div class="w-14 h-14 bg-gradient-navy rounded-xl flex items-center justify-center mx-auto mb-4">
                            <svg class="w-7 h-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                        <h3 class="font-semibold text-navy mb-2">Better Collaboration</h3>
                        <p class="text-sm text-teal/70">Improve team communication with effective meeting practices and feedback.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="py-20 bg-gradient-navy">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <h2 class="text-3xl sm:text-4xl font-bold text-white mb-6 animate-fade-in-up">
                    Ready to Transform Your Meetings?
                </h2>
                <p class="text-lg text-cream/80 mb-10 animate-fade-in-up delay-100">
                    Join thousands of teams using YUJIX for better meeting management.
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center animate-fade-in-up delay-200">
                    <Link href="/get-started" class="bg-white text-navy px-8 py-4 text-base font-semibold rounded-xl hover:bg-cream transition-colors shadow-lg">
                        Get Started Free
                    </Link>
                    <Link href="/features" class="border-2 border-white/30 text-white px-8 py-4 text-base font-semibold rounded-xl hover:bg-white/10 transition-colors">
                        Explore Features
                    </Link>
                </div>
            </div>
        </section>
    </AppLayout>
</template>
