<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const mainFeatures = [
    {
        title: 'Meeting Scheduler',
        description: 'Create and manage meetings effortlessly. Add titles, dates, times, locations, and invite attendees. Track meeting progress from scheduled to completed.',
        features: ['Add meeting attendees', 'Set date, time & duration', 'Meeting status tracking', 'Location & notes'],
        image: 'meeting',
        color: 'from-green-500 to-green-600'
    },
    {
        title: 'Meeting Attendees',
        description: 'Manage your meeting contacts efficiently. Mark favorites for quick access, organize by groups, and easily add attendees to your meetings.',
        features: ['Favorite contacts', 'Contact groups', 'Quick search & filter', 'Add to meetings easily'],
        image: 'contact',
        color: 'from-purple-500 to-purple-600'
    },
    {
        title: 'Meeting Surveys',
        description: 'Collect feedback after meetings with customizable surveys. Create multi-step forms with various question types to improve future meetings.',
        features: ['Post-meeting feedback', '9 question types', 'Multi-step surveys', 'File attachments'],
        image: 'survey',
        color: 'from-orange-500 to-orange-600'
    }
];

const additionalFeatures = [
    { icon: 'bell', title: 'Meeting Reminders', description: 'Get notified before meetings start. Never miss an important meeting again.' },
    { icon: 'clock', title: 'Meeting Status', description: 'Track meetings from Created to Pending, Active, and Completed status.' },
    { icon: 'users', title: 'Attendee Management', description: 'Add, remove, and manage meeting participants with ease.' },
    { icon: 'chart', title: 'Survey Responses', description: 'View and analyze feedback collected from post-meeting surveys.' },
    { icon: 'mobile', title: 'Mobile App', description: 'Access your meetings on the go with iOS and Android apps.' }
];

const questionTypes = [
    { name: 'Short Answer', icon: 'text' },
    { name: 'Email', icon: 'mail' },
    { name: 'Number', icon: 'hash' },
    { name: 'Paragraph', icon: 'paragraph' },
    { name: 'Dropdown', icon: 'dropdown' },
    { name: 'Date', icon: 'calendar' },
    { name: 'Multiple Choice', icon: 'radio' },
    { name: 'Checkboxes', icon: 'checkbox' },
    { name: 'Rating Scale', icon: 'star' }
];

const sampleMeetings = [
    { title: 'Weekly Team Standup', date: 'Today', time: '9:00 AM', duration: '30 min', status: 'Active', statusColor: 'bg-green-100 text-green-700', attendees: ['SC', 'MR', 'EW'] },
    { title: 'Product Review Meeting', date: 'Tomorrow', time: '2:00 PM', duration: '1 hour', status: 'Pending', statusColor: 'bg-yellow-100 text-yellow-700', attendees: ['JD', 'AK'] },
    { title: 'Client Presentation', date: 'Dec 22', time: '11:00 AM', duration: '45 min', status: 'Scheduled', statusColor: 'bg-blue-100 text-blue-700', attendees: ['SC', 'JD', 'MR', 'EW'] }
];

const contacts = [
    { name: 'Sarah Chen', role: 'Product Manager', email: 'sarah@company.com', color: 'bg-purple-500', favorite: true },
    { name: 'Michael Rodriguez', role: 'Software Engineer', email: 'michael@company.com', color: 'bg-green-500', favorite: true },
    { name: 'Emily Watson', role: 'UX Designer', email: 'emily@company.com', color: 'bg-blue-500', favorite: false }
];
</script>

<template>
    <Head>
        <title>Features - Meeting Scheduler, Surveys & Contact Management | YUJIX</title>
        <meta name="description" content="Discover YUJIX features: meeting scheduling, attendee management, post-meeting surveys with 9 question types, smart notifications, and contact organization." />
        <meta name="keywords" content="meeting scheduler features, survey builder, meeting attendees management, meeting reminders, contact groups" />
    </Head>
    <AppLayout>
        <!-- Hero Section -->
        <section class="relative pt-32 pb-20 bg-gradient-radial bg-hero-pattern overflow-hidden">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold text-navy mb-6 animate-fade-in-up">
                    Complete Meeting
                    <span class="text-teal">Management Tools</span>
                </h1>
                <p class="text-lg text-teal/80 max-w-2xl mx-auto animate-fade-in-up delay-100">
                    Schedule meetings, manage attendees, collect feedback with surveys, and never miss a meeting with smart reminders.
                </p>
            </div>
        </section>

        <!-- Main Features -->
        <section class="py-20 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="space-y-24">
                    <div
                        v-for="(feature, index) in mainFeatures"
                        :key="feature.title"
                        class="grid lg:grid-cols-2 gap-12 items-center"
                    >
                        <!-- Content -->
                        <div :class="[index % 2 === 1 ? 'lg:order-2' : '', 'animate-fade-in-up']">
                            <h2 class="text-3xl sm:text-4xl font-bold text-navy mb-4">{{ feature.title }}</h2>
                            <p class="text-lg text-teal/70 mb-6">{{ feature.description }}</p>
                            <ul class="space-y-3">
                                <li v-for="item in feature.features" :key="item" class="flex items-center gap-3">
                                    <div class="w-6 h-6 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0">
                                        <svg class="w-4 h-4 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                    <span class="text-navy">{{ item }}</span>
                                </li>
                            </ul>
                        </div>

                        <!-- Visual -->
                        <div :class="[index % 2 === 1 ? 'lg:order-1' : '', 'animate-fade-in-up delay-200']">
                            <div class="glass rounded-3xl p-6 shadow-xl">
                                <!-- Meeting Preview (Static) -->
                                <div v-if="feature.image === 'meeting'" class="space-y-4">
                                    <div class="flex items-center justify-between">
                                        <h4 class="font-semibold text-navy">My Meetings</h4>
                                        <span class="px-3 py-1.5 bg-teal text-white text-sm rounded-lg font-medium">+ New Meeting</span>
                                    </div>
                                    <div class="space-y-3">
                                        <div v-for="meeting in sampleMeetings" :key="meeting.title" class="p-4 bg-cream rounded-xl">
                                            <div class="flex items-start justify-between mb-2">
                                                <div>
                                                    <h5 class="font-medium text-navy">{{ meeting.title }}</h5>
                                                    <p class="text-sm text-teal/70">{{ meeting.date }} at {{ meeting.time }} ({{ meeting.duration }})</p>
                                                </div>
                                                <span :class="[meeting.statusColor, 'px-2 py-1 rounded-md text-xs font-medium']">{{ meeting.status }}</span>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <div class="flex -space-x-2">
                                                    <div v-for="(initials, i) in meeting.attendees.slice(0, 3)" :key="i" class="w-6 h-6 rounded-full bg-gradient-navy text-white text-xs flex items-center justify-center border-2 border-cream font-medium">{{ initials }}</div>
                                                </div>
                                                <span v-if="meeting.attendees.length > 3" class="text-xs text-teal/60">+{{ meeting.attendees.length - 3 }} more</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Contact Preview (Static) -->
                                <div v-else-if="feature.image === 'contact'" class="space-y-4">
                                    <div class="flex items-center justify-between">
                                        <h4 class="font-semibold text-navy">Contacts</h4>
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm text-teal/70">24 total</span>
                                            <span class="p-1.5 bg-cream rounded-lg">
                                                <svg class="w-4 h-4 text-teal" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                                </svg>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="relative">
                                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-teal/40" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                        </svg>
                                        <div class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-lavender/50 text-sm text-teal/40">Search contacts...</div>
                                    </div>
                                    <div class="flex flex-wrap gap-2">
                                        <span class="px-3 py-1 bg-teal text-white rounded-full text-xs font-medium">All</span>
                                        <span class="px-3 py-1 bg-cream text-navy rounded-full text-xs">Favorites</span>
                                        <span class="px-3 py-1 bg-cream text-navy rounded-full text-xs">Recent</span>
                                    </div>
                                    <div class="space-y-2">
                                        <div v-for="contact in contacts" :key="contact.name" class="flex items-center gap-3 p-3 bg-cream rounded-xl">
                                            <div :class="[contact.color, 'w-10 h-10 rounded-full flex items-center justify-center text-white font-medium']">
                                                {{ contact.name.split(' ').map(n => n[0]).join('') }}
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="font-medium text-navy truncate">{{ contact.name }}</p>
                                                <p class="text-sm text-teal/60 truncate">{{ contact.role }}</p>
                                            </div>
                                            <svg v-if="contact.favorite" class="w-5 h-5 text-yellow-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                            </svg>
                                            <svg v-else class="w-5 h-5 text-gray-300 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                                            </svg>
                                        </div>
                                    </div>
                                </div>

                                <!-- Survey Preview (Static) -->
                                <div v-else-if="feature.image === 'survey'" class="space-y-4">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <h4 class="font-semibold text-navy">Meeting Feedback</h4>
                                            <p class="text-sm text-teal/60">Post-meeting survey</p>
                                        </div>
                                        <span class="px-2.5 py-1 bg-green-100 text-green-700 text-xs rounded-full font-medium">Active</span>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <div class="flex-1 h-2 bg-cream rounded-full overflow-hidden">
                                            <div class="w-1/3 h-full bg-teal rounded-full"></div>
                                        </div>
                                        <span class="text-sm text-teal/70 font-medium">Step 1/3</span>
                                    </div>
                                    <div class="space-y-4">
                                        <div class="p-4 bg-cream rounded-xl">
                                            <p class="text-sm font-medium text-navy mb-3">How would you rate this meeting?</p>
                                            <div class="flex gap-2">
                                                <span v-for="n in 5" :key="n" :class="['w-10 h-10 rounded-lg flex items-center justify-center font-medium', n <= 4 ? 'bg-teal text-white' : 'bg-white border border-lavender/50 text-navy']">
                                                    {{ n }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="p-4 bg-cream rounded-xl">
                                            <p class="text-sm font-medium text-navy mb-3">What topics were most valuable?</p>
                                            <div class="space-y-2">
                                                <div class="flex items-center gap-2">
                                                    <div class="w-4 h-4 rounded bg-teal flex items-center justify-center">
                                                        <svg class="w-3 h-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                                        </svg>
                                                    </div>
                                                    <span class="text-sm text-navy">Project updates</span>
                                                </div>
                                                <div class="flex items-center gap-2">
                                                    <div class="w-4 h-4 rounded bg-teal flex items-center justify-center">
                                                        <svg class="w-3 h-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                                        </svg>
                                                    </div>
                                                    <span class="text-sm text-navy">Team discussion</span>
                                                </div>
                                                <div class="flex items-center gap-2">
                                                    <div class="w-4 h-4 rounded border border-lavender/50 bg-white"></div>
                                                    <span class="text-sm text-navy">Action items</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <span class="block w-full py-2.5 bg-teal text-white rounded-xl font-medium text-center">Continue</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Survey Question Types -->
        <section class="py-20 bg-cream">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="text-3xl sm:text-4xl font-bold text-navy mb-4 animate-fade-in-up">
                        Meeting Survey Question Types
                    </h2>
                    <p class="text-lg text-teal/70 animate-fade-in-up delay-100">
                        Collect meeting feedback with 9 different question formats to improve your future meetings
                    </p>
                </div>

                <div class="grid grid-cols-3 md:grid-cols-5 lg:grid-cols-9 gap-4">
                    <div
                        v-for="(type, index) in questionTypes"
                        :key="type.name"
                        class="glass p-4 rounded-xl text-center card-hover animate-fade-in-up"
                        :style="{ animationDelay: `${index * 50}ms` }"
                    >
                        <div class="w-10 h-10 bg-gradient-navy rounded-lg flex items-center justify-center mx-auto mb-2">
                            <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path v-if="type.icon === 'text'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h8m-8 6h16" />
                                <path v-else-if="type.icon === 'mail'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                <path v-else-if="type.icon === 'hash'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14" />
                                <path v-else-if="type.icon === 'paragraph'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h10M4 18h6" />
                                <path v-else-if="type.icon === 'dropdown'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                <path v-else-if="type.icon === 'calendar'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                <path v-else-if="type.icon === 'radio'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                <path v-else-if="type.icon === 'checkbox'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                <path v-else-if="type.icon === 'star'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                            </svg>
                        </div>
                        <p class="text-xs font-medium text-navy">{{ type.name }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Additional Features Grid -->
        <section class="py-20 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="text-3xl sm:text-4xl font-bold text-navy mb-4 animate-fade-in-up">
                        More Meeting Features
                    </h2>
                    <p class="text-lg text-teal/70 animate-fade-in-up delay-100">
                        Everything you need to manage meetings efficiently in one app
                    </p>
                </div>

                <div class="grid sm:grid-cols-2 lg:grid-cols-5 gap-6">
                    <div
                        v-for="(feature, index) in additionalFeatures"
                        :key="feature.title"
                        class="glass p-6 rounded-2xl card-hover animate-fade-in-up"
                        :style="{ animationDelay: `${index * 50}ms` }"
                    >
                        <div class="w-12 h-12 bg-gradient-navy rounded-xl flex items-center justify-center mb-4">
                            <svg v-if="feature.icon === 'bell'" class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                            <svg v-else-if="feature.icon === 'clock'" class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <svg v-else-if="feature.icon === 'users'" class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                            <svg v-else-if="feature.icon === 'chart'" class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                            <svg v-else-if="feature.icon === 'mobile'" class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <h3 class="font-semibold text-navy mb-2">{{ feature.title }}</h3>
                        <p class="text-sm text-teal/70">{{ feature.description }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="py-20 bg-gradient-navy">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <h2 class="text-3xl sm:text-4xl font-bold text-white mb-6 animate-fade-in-up">
                    Start Managing Your Meetings
                </h2>
                <p class="text-lg text-cream/80 mb-10 animate-fade-in-up delay-100">
                    Download YUJIX and take control of your meeting schedule today.
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
