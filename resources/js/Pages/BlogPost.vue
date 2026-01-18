<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { computed } from 'vue';

const props = defineProps({
    slug: String
});

// Blog posts data
const blogPosts = {
    'how-to-schedule-team-meetings-effectively': {
        title: 'How to Schedule Team Meetings Effectively in 2024',
        category: 'Meeting Tips',
        date: 'Dec 15, 2024',
        readTime: '5 min read',
        image: 'meeting',
        content: [
            {
                type: 'paragraph',
                text: 'Effective meeting scheduling is crucial for team productivity. In this guide, we will explore the best practices for scheduling team meetings that maximize productivity and minimize time waste.'
            },
            {
                type: 'heading',
                text: '1. Choose the Right Time'
            },
            {
                type: 'paragraph',
                text: 'Consider your team members\' schedules and time zones. Avoid early mornings and late afternoons when energy levels are typically low. Mid-morning (10-11 AM) is often the most productive time for meetings.'
            },
            {
                type: 'heading',
                text: '2. Set Clear Objectives'
            },
            {
                type: 'paragraph',
                text: 'Every meeting should have a clear purpose. Before scheduling, ask yourself: What do we need to accomplish? What decisions need to be made? Who needs to be present?'
            },
            {
                type: 'heading',
                text: '3. Keep Meetings Short'
            },
            {
                type: 'paragraph',
                text: 'Research shows that attention spans drop significantly after 30 minutes. Try to keep meetings between 15-30 minutes for quick updates, and no longer than 60 minutes for complex discussions.'
            },
            {
                type: 'heading',
                text: '4. Use Meeting Management Tools'
            },
            {
                type: 'paragraph',
                text: 'Tools like YUJIX help you schedule meetings, manage attendees, send reminders, and collect feedback - all in one place. This streamlines the entire meeting process from start to finish.'
            },
            {
                type: 'heading',
                text: '5. Send Reminders'
            },
            {
                type: 'paragraph',
                text: 'Automated meeting reminders ensure that no one forgets about important meetings. Set up notifications for 24 hours before and 15 minutes before the meeting starts.'
            }
        ]
    },
    'best-meeting-scheduler-app-small-business': {
        title: 'Best Meeting Scheduler App for Small Business Teams',
        category: 'Product Reviews',
        date: 'Dec 12, 2024',
        readTime: '7 min read',
        image: 'calendar',
        content: [
            {
                type: 'paragraph',
                text: 'Finding the right meeting scheduler app can transform how your small business operates. In this review, we compare the top options available in 2024.'
            },
            {
                type: 'heading',
                text: 'What to Look for in a Meeting Scheduler'
            },
            {
                type: 'paragraph',
                text: 'The best meeting scheduler apps offer calendar integration, easy attendee management, automated reminders, and mobile access. For small businesses, pricing and ease of use are also critical factors.'
            },
            {
                type: 'heading',
                text: 'Top Features for Small Teams'
            },
            {
                type: 'paragraph',
                text: 'Small business teams benefit most from features like contact management, recurring meeting setup, status tracking, and post-meeting surveys to gather feedback and improve future sessions.'
            },
            {
                type: 'heading',
                text: 'Why YUJIX Stands Out'
            },
            {
                type: 'paragraph',
                text: 'YUJIX offers a complete meeting management solution with intuitive scheduling, contact organization, push notifications, and built-in survey tools - perfect for growing small business teams.'
            }
        ]
    },
    'create-post-meeting-surveys-get-responses': {
        title: 'How to Create Post-Meeting Surveys That Get Responses',
        category: 'Survey Guide',
        date: 'Dec 10, 2024',
        readTime: '6 min read',
        image: 'survey',
        content: [
            {
                type: 'paragraph',
                text: 'Post-meeting surveys are essential for continuous improvement. Learn how to design surveys that participants actually complete and provide valuable feedback.'
            },
            {
                type: 'heading',
                text: 'Keep Surveys Short'
            },
            {
                type: 'paragraph',
                text: 'The ideal post-meeting survey has 3-5 questions. Longer surveys lead to survey fatigue and lower completion rates. Focus on the most important insights you need.'
            },
            {
                type: 'heading',
                text: 'Use the Right Question Types'
            },
            {
                type: 'paragraph',
                text: 'Mix rating scales for quick feedback, multiple choice for specific topics, and one open-ended question for detailed comments. This balance provides both quantitative and qualitative data.'
            },
            {
                type: 'heading',
                text: 'Send Surveys Immediately'
            },
            {
                type: 'paragraph',
                text: 'The best time to send a survey is right after the meeting ends when everything is fresh in participants\' minds. Automated survey delivery ensures you never miss this window.'
            }
        ]
    },
    'meeting-reminder-apps-never-miss-meeting': {
        title: 'Meeting Reminder Apps: Never Miss an Important Meeting Again',
        category: 'Productivity',
        date: 'Dec 8, 2024',
        readTime: '4 min read',
        image: 'notification',
        content: [
            {
                type: 'paragraph',
                text: 'Missing meetings can damage professional relationships and productivity. Discover how meeting reminder apps can ensure you and your team are always prepared.'
            },
            {
                type: 'heading',
                text: 'The Cost of Missed Meetings'
            },
            {
                type: 'paragraph',
                text: 'Missed meetings waste time, require rescheduling, and can lead to missed opportunities. Automated reminders are a simple solution to this common problem.'
            },
            {
                type: 'heading',
                text: 'Types of Meeting Reminders'
            },
            {
                type: 'paragraph',
                text: 'Effective reminder systems include push notifications on mobile devices, email reminders, and calendar alerts. The best apps offer all three options.'
            },
            {
                type: 'heading',
                text: 'Setting Up Smart Reminders'
            },
            {
                type: 'paragraph',
                text: 'Configure reminders at multiple intervals: 24 hours before for preparation, 1 hour before for final review, and 15 minutes before for the immediate heads-up.'
            }
        ]
    },
    'manage-meeting-attendees-contacts-efficiently': {
        title: 'How to Manage Meeting Attendees and Contacts Efficiently',
        category: 'Organization',
        date: 'Dec 5, 2024',
        readTime: '5 min read',
        image: 'contacts',
        content: [
            {
                type: 'paragraph',
                text: 'Efficient contact management is the foundation of smooth meeting coordination. Learn how to organize your professional network for seamless meeting scheduling.'
            },
            {
                type: 'heading',
                text: 'Organize Contacts into Groups'
            },
            {
                type: 'paragraph',
                text: 'Create logical groups like "Engineering Team," "Sales," or "External Partners." This makes it easy to quickly invite the right people to relevant meetings.'
            },
            {
                type: 'heading',
                text: 'Use Favorites for Quick Access'
            },
            {
                type: 'paragraph',
                text: 'Mark your most frequent meeting participants as favorites. This saves time when scheduling recurring meetings with the same core team.'
            },
            {
                type: 'heading',
                text: 'Keep Contact Information Updated'
            },
            {
                type: 'paragraph',
                text: 'Regularly review and update contact details. Import new contacts easily with CSV files and remove outdated entries to keep your list clean and useful.'
            }
        ]
    }
};

// Get current post data
const post = computed(() => {
    return blogPosts[props.slug] || null;
});

// Related posts (exclude current)
const relatedPosts = computed(() => {
    return Object.entries(blogPosts)
        .filter(([slug]) => slug !== props.slug)
        .slice(0, 3)
        .map(([slug, data]) => ({ slug, ...data }));
});
</script>

<template>
    <Head>
        <title v-if="post">{{ post.title }} | YUJIX Blog</title>
        <title v-else>Blog Post Not Found | YUJIX</title>
        <meta v-if="post" name="description" :content="post.content[0]?.text || ''" />
    </Head>
    <AppLayout>
        <!-- Post Not Found -->
        <template v-if="!post">
            <section class="pt-32 pb-20 bg-gradient-radial bg-hero-pattern min-h-screen">
                <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                    <h1 class="text-4xl font-bold text-navy mb-6">Post Not Found</h1>
                    <p class="text-teal/70 mb-8">The blog post you're looking for doesn't exist.</p>
                    <Link href="/blog" class="btn-primary px-6 py-3 rounded-xl">
                        Back to Blog
                    </Link>
                </div>
            </section>
        </template>

        <!-- Blog Post Content -->
        <template v-else>
            <!-- Hero Section -->
            <section class="pt-32 pb-12 bg-gradient-radial bg-hero-pattern">
                <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                    <Link href="/blog" class="inline-flex items-center gap-2 text-teal hover:text-navy transition-colors mb-6">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        Back to Blog
                    </Link>
                    <div class="flex items-center gap-3 mb-4">
                        <span class="px-3 py-1 bg-teal/10 text-teal text-sm font-medium rounded-full">{{ post.category }}</span>
                        <span class="text-sm text-teal/60">{{ post.readTime }}</span>
                        <span class="text-sm text-teal/60">{{ post.date }}</span>
                    </div>
                    <h1 class="text-3xl sm:text-4xl lg:text-5xl font-bold text-navy leading-tight animate-fade-in-up">
                        {{ post.title }}
                    </h1>
                </div>
            </section>

            <!-- Article Content -->
            <section class="py-12 bg-white">
                <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                    <article class="prose prose-lg max-w-none">
                        <template v-for="(block, index) in post.content" :key="index">
                            <h2 v-if="block.type === 'heading'" class="text-2xl font-bold text-navy mt-8 mb-4">
                                {{ block.text }}
                            </h2>
                            <p v-else-if="block.type === 'paragraph'" class="text-teal/80 leading-relaxed mb-6">
                                {{ block.text }}
                            </p>
                        </template>
                    </article>

                    <!-- CTA Box -->
                    <div class="mt-12 bg-gradient-navy rounded-2xl p-8 text-white text-center">
                        <h3 class="text-2xl font-bold mb-4">Ready to Improve Your Meetings?</h3>
                        <p class="text-cream/80 mb-6">Start using YUJIX today and transform how your team manages meetings.</p>
                        <Link href="/get-started" class="inline-block bg-white text-navy px-8 py-3 rounded-xl font-semibold hover:bg-cream transition-colors">
                            Get Started Free
                        </Link>
                    </div>
                </div>
            </section>

            <!-- Related Posts -->
            <section class="py-16 bg-cream">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <h2 class="text-2xl font-bold text-navy mb-8 text-center">Related Articles</h2>
                    <div class="grid md:grid-cols-3 gap-8">
                        <Link
                            v-for="related in relatedPosts"
                            :key="related.slug"
                            :href="`/blog/${related.slug}`"
                            class="glass rounded-2xl p-6 card-hover group"
                        >
                            <span class="px-3 py-1 bg-teal/10 text-teal text-xs font-medium rounded-full">{{ related.category }}</span>
                            <h3 class="text-lg font-bold text-navy mt-3 mb-2 group-hover:text-teal transition-colors">{{ related.title }}</h3>
                            <p class="text-sm text-teal/70">{{ related.readTime }}</p>
                        </Link>
                    </div>
                </div>
            </section>
        </template>
    </AppLayout>
</template>
