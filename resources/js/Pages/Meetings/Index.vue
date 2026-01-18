<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref } from 'vue';

const activeFilter = ref('all');
const searchQuery = ref('');

const filters = [
    { id: 'all', label: 'All Meetings' },
    { id: 'upcoming', label: 'Upcoming' },
    { id: 'completed', label: 'Completed' },
    { id: 'cancelled', label: 'Cancelled' },
];

const meetings = ref([
    { id: 1, title: 'Weekly Team Standup', date: 'Dec 20, 2024', time: '9:00 AM', duration: '30 min', type: 'video', status: 'upcoming', attendees: ['JD', 'SC', 'MR'] },
    { id: 2, title: 'Client Project Review', date: 'Dec 20, 2024', time: '2:00 PM', duration: '1 hour', type: 'video', status: 'upcoming', attendees: ['AB', 'CD'] },
    { id: 3, title: 'Design System Workshop', date: 'Dec 19, 2024', time: '11:00 AM', duration: '2 hours', type: 'in-person', status: 'completed', attendees: ['EF', 'GH', 'IJ', 'KL'] },
    { id: 4, title: 'Sprint Planning', date: 'Dec 18, 2024', time: '3:00 PM', duration: '1.5 hours', type: 'video', status: 'completed', attendees: ['MN', 'OP'] },
    { id: 5, title: 'Product Demo', date: 'Dec 17, 2024', time: '10:00 AM', duration: '45 min', type: 'phone', status: 'completed', attendees: ['QR'] },
    { id: 6, title: 'Quarterly Business Review', date: 'Dec 22, 2024', time: '4:00 PM', duration: '2 hours', type: 'in-person', status: 'upcoming', attendees: ['ST', 'UV', 'WX'] },
]);

const getStatusClass = (status) => {
    switch (status) {
        case 'upcoming': return 'bg-blue-100 text-blue-700';
        case 'completed': return 'bg-green-100 text-green-700';
        case 'cancelled': return 'bg-red-100 text-red-700';
        default: return 'bg-gray-100 text-gray-700';
    }
};

const getTypeIcon = (type) => type;
</script>

<template>
    <Head title="Meetings" />
    <AppLayout>
        <!-- Hero Section -->
        <section class="pt-28 pb-12 bg-gradient-radial">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8 animate-fade-in-up">
                    <div>
                        <h1 class="text-3xl sm:text-4xl font-bold text-navy">Meetings</h1>
                        <p class="text-teal/70 mt-1">View and manage all your meetings</p>
                    </div>
                    <Link href="/meetings/create" class="btn-primary px-6 py-3 rounded-xl font-medium inline-flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        New Meeting
                    </Link>
                </div>

                <!-- Search and Filters -->
                <div class="flex flex-col md:flex-row gap-4 animate-fade-in-up delay-100">
                    <div class="flex-1 relative">
                        <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-teal/50" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Search meetings..."
                            class="w-full pl-12 pr-4 py-3 rounded-xl border border-lavender/50 focus:ring-2 focus:ring-teal/30 focus:border-teal bg-white"
                        />
                    </div>
                    <div class="flex gap-2 overflow-x-auto pb-2 md:pb-0">
                        <button
                            v-for="filter in filters"
                            :key="filter.id"
                            @click="activeFilter = filter.id"
                            :class="[
                                'px-4 py-3 rounded-xl text-sm font-medium whitespace-nowrap transition-colors',
                                activeFilter === filter.id ? 'bg-teal text-white' : 'bg-white text-teal hover:bg-lavender/20'
                            ]"
                        >
                            {{ filter.label }}
                        </button>
                    </div>
                </div>
            </div>
        </section>

        <!-- Meetings List -->
        <section class="pb-20 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="space-y-4">
                    <div
                        v-for="(meeting, index) in meetings"
                        :key="meeting.id"
                        class="glass rounded-2xl p-6 card-hover animate-fade-in-up"
                        :style="{ animationDelay: `${index * 50}ms` }"
                    >
                        <div class="flex flex-col lg:flex-row lg:items-center gap-4">
                            <!-- Meeting Type Icon -->
                            <div :class="[
                                'w-14 h-14 rounded-xl flex items-center justify-center flex-shrink-0',
                                meeting.type === 'video' ? 'bg-blue-100' :
                                meeting.type === 'phone' ? 'bg-orange-100' : 'bg-green-100'
                            ]">
                                <svg v-if="meeting.type === 'video'" class="w-7 h-7 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                </svg>
                                <svg v-else-if="meeting.type === 'phone'" class="w-7 h-7 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                </svg>
                                <svg v-else class="w-7 h-7 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </div>

                            <!-- Meeting Info -->
                            <div class="flex-1 min-w-0">
                                <div class="flex flex-wrap items-center gap-2 mb-1">
                                    <h3 class="text-lg font-semibold text-navy">{{ meeting.title }}</h3>
                                    <span :class="[getStatusClass(meeting.status), 'px-2 py-0.5 rounded-full text-xs font-medium capitalize']">
                                        {{ meeting.status }}
                                    </span>
                                </div>
                                <div class="flex flex-wrap items-center gap-4 text-sm text-teal/70">
                                    <span class="flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        {{ meeting.date }}
                                    </span>
                                    <span class="flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        {{ meeting.time }} ({{ meeting.duration }})
                                    </span>
                                </div>
                            </div>

                            <!-- Attendees -->
                            <div class="flex items-center gap-3">
                                <div class="flex -space-x-2">
                                    <div
                                        v-for="(attendee, i) in meeting.attendees.slice(0, 3)"
                                        :key="attendee"
                                        :class="[
                                            'w-8 h-8 rounded-full border-2 border-white flex items-center justify-center text-xs font-medium text-white',
                                            i === 0 ? 'bg-purple-500' : i === 1 ? 'bg-green-500' : 'bg-blue-500'
                                        ]"
                                    >
                                        {{ attendee }}
                                    </div>
                                    <div v-if="meeting.attendees.length > 3" class="w-8 h-8 rounded-full border-2 border-white bg-gray-200 flex items-center justify-center text-xs font-medium text-gray-600">
                                        +{{ meeting.attendees.length - 3 }}
                                    </div>
                                </div>

                                <!-- Actions -->
                                <div class="flex items-center gap-2">
                                    <button class="p-2 text-teal hover:bg-lavender/20 rounded-lg transition-colors">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                        </svg>
                                    </button>
                                    <button class="p-2 text-red-500 hover:bg-red-50 rounded-lg transition-colors">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <div class="mt-8 flex justify-center">
                    <nav class="flex items-center gap-2">
                        <button class="p-2 text-teal hover:bg-lavender/20 rounded-lg transition-colors">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                        </button>
                        <button class="w-10 h-10 bg-teal text-white rounded-lg font-medium">1</button>
                        <button class="w-10 h-10 text-teal hover:bg-lavender/20 rounded-lg font-medium">2</button>
                        <button class="w-10 h-10 text-teal hover:bg-lavender/20 rounded-lg font-medium">3</button>
                        <button class="p-2 text-teal hover:bg-lavender/20 rounded-lg transition-colors">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </button>
                    </nav>
                </div>
            </div>
        </section>
    </AppLayout>
</template>
