<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, computed } from 'vue';

const currentView = ref('month');
const currentDate = ref(new Date());

const views = ['month', 'week', 'day'];

const months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
const days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

const currentMonthName = computed(() => months[currentDate.value.getMonth()]);
const currentYear = computed(() => currentDate.value.getFullYear());

const daysInMonth = computed(() => {
    const year = currentDate.value.getFullYear();
    const month = currentDate.value.getMonth();
    const firstDay = new Date(year, month, 1).getDay();
    const totalDays = new Date(year, month + 1, 0).getDate();

    const days = [];
    // Previous month days
    for (let i = 0; i < firstDay; i++) {
        days.push({ day: '', isCurrentMonth: false });
    }
    // Current month days
    for (let i = 1; i <= totalDays; i++) {
        days.push({ day: i, isCurrentMonth: true, isToday: i === new Date().getDate() && month === new Date().getMonth() });
    }
    return days;
});

// Sample meetings data
const meetings = [
    { id: 1, title: 'Team Standup', time: '9:00 AM', type: 'video', day: 5 },
    { id: 2, title: 'Client Call', time: '2:00 PM', type: 'phone', day: 12 },
    { id: 3, title: 'Design Review', time: '11:00 AM', type: 'in-person', day: 15 },
    { id: 4, title: 'Sprint Planning', time: '3:00 PM', type: 'video', day: 19 },
    { id: 5, title: 'One-on-One', time: '10:00 AM', type: 'video', day: 22 },
    { id: 6, title: 'Quarterly Review', time: '4:00 PM', type: 'in-person', day: 28 },
];

const getMeetingsForDay = (day) => meetings.filter(m => m.day === day);

const prevMonth = () => {
    currentDate.value = new Date(currentDate.value.getFullYear(), currentDate.value.getMonth() - 1);
};

const nextMonth = () => {
    currentDate.value = new Date(currentDate.value.getFullYear(), currentDate.value.getMonth() + 1);
};

const stats = [
    { label: 'Total Meetings', value: '24', color: 'bg-blue-500' },
    { label: 'Video Calls', value: '12', color: 'bg-purple-500' },
    { label: 'In-Person', value: '8', color: 'bg-green-500' },
    { label: 'Phone Calls', value: '4', color: 'bg-orange-500' },
];
</script>

<template>
    <Head title="Calendar" />
    <AppLayout>
        <!-- Hero Section -->
        <section class="pt-28 pb-12 bg-gradient-radial">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8 animate-fade-in-up">
                    <div>
                        <h1 class="text-3xl sm:text-4xl font-bold text-navy">Calendar</h1>
                        <p class="text-teal/70 mt-1">Manage your meeting schedule</p>
                    </div>
                    <Link href="/meetings/create" class="btn-primary px-6 py-3 rounded-xl font-medium inline-flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        New Meeting
                    </Link>
                </div>

                <!-- Stats -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8 animate-fade-in-up delay-100">
                    <div v-for="stat in stats" :key="stat.label" class="glass rounded-xl p-4">
                        <div class="flex items-center gap-3">
                            <div :class="[stat.color, 'w-10 h-10 rounded-lg flex items-center justify-center']">
                                <span class="text-white font-bold">{{ stat.value }}</span>
                            </div>
                            <span class="text-sm text-teal/70">{{ stat.label }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Calendar Section -->
        <section class="pb-20 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="glass rounded-2xl p-6 shadow-lg animate-fade-in-up delay-200">
                    <!-- Calendar Header -->
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                        <div class="flex items-center gap-4">
                            <button @click="prevMonth" class="p-2 hover:bg-lavender/20 rounded-lg transition-colors">
                                <svg class="w-5 h-5 text-navy" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                </svg>
                            </button>
                            <h2 class="text-xl font-semibold text-navy">{{ currentMonthName }} {{ currentYear }}</h2>
                            <button @click="nextMonth" class="p-2 hover:bg-lavender/20 rounded-lg transition-colors">
                                <svg class="w-5 h-5 text-navy" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </button>
                        </div>
                        <div class="flex gap-1 bg-cream rounded-lg p-1">
                            <button
                                v-for="view in views"
                                :key="view"
                                @click="currentView = view"
                                :class="[
                                    'px-4 py-2 text-sm font-medium rounded-lg capitalize transition-colors',
                                    currentView === view ? 'bg-teal text-white' : 'text-teal hover:bg-lavender/20'
                                ]"
                            >
                                {{ view }}
                            </button>
                        </div>
                    </div>

                    <!-- Calendar Grid -->
                    <div class="grid grid-cols-7 gap-px bg-lavender/30 rounded-xl overflow-hidden">
                        <!-- Day Headers -->
                        <div v-for="day in days" :key="day" class="bg-cream p-3 text-center">
                            <span class="text-sm font-medium text-teal/70">{{ day }}</span>
                        </div>

                        <!-- Calendar Days -->
                        <div
                            v-for="(dayObj, index) in daysInMonth"
                            :key="index"
                            :class="[
                                'bg-white p-2 min-h-24 md:min-h-32 transition-colors hover:bg-cream-light cursor-pointer',
                                !dayObj.isCurrentMonth && 'opacity-30'
                            ]"
                        >
                            <div class="flex items-center justify-between mb-2">
                                <span :class="[
                                    'w-7 h-7 flex items-center justify-center rounded-full text-sm',
                                    dayObj.isToday ? 'bg-teal text-white font-bold' : 'text-navy'
                                ]">
                                    {{ dayObj.day }}
                                </span>
                            </div>
                            <!-- Meetings for this day -->
                            <div class="space-y-1">
                                <div
                                    v-for="meeting in getMeetingsForDay(dayObj.day)"
                                    :key="meeting.id"
                                    :class="[
                                        'text-xs p-1.5 rounded-md truncate',
                                        meeting.type === 'video' ? 'bg-blue-100 text-blue-700' :
                                        meeting.type === 'phone' ? 'bg-orange-100 text-orange-700' :
                                        'bg-green-100 text-green-700'
                                    ]"
                                >
                                    <span class="hidden md:inline">{{ meeting.time }}</span> {{ meeting.title }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Upcoming Meetings -->
                <div class="mt-8 animate-fade-in-up delay-300">
                    <h3 class="text-xl font-semibold text-navy mb-4">Upcoming This Week</h3>
                    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div v-for="meeting in meetings.slice(0, 3)" :key="meeting.id" class="glass rounded-xl p-4 card-hover">
                            <div class="flex items-start gap-4">
                                <div :class="[
                                    'w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0',
                                    meeting.type === 'video' ? 'bg-blue-100' :
                                    meeting.type === 'phone' ? 'bg-orange-100' : 'bg-green-100'
                                ]">
                                    <svg v-if="meeting.type === 'video'" class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                    </svg>
                                    <svg v-else-if="meeting.type === 'phone'" class="w-6 h-6 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                    </svg>
                                    <svg v-else class="w-6 h-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h4 class="font-medium text-navy truncate">{{ meeting.title }}</h4>
                                    <p class="text-sm text-teal/70">Dec {{ meeting.day }}, {{ meeting.time }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </AppLayout>
</template>
