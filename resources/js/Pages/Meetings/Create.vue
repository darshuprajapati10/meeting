<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref } from 'vue';

const form = ref({
    title: '',
    date: '',
    time: '',
    duration: '30',
    type: 'video',
    location: '',
    description: '',
    attendees: [],
    notifications: ['30min'],
    survey: ''
});

const meetingTypes = [
    { id: 'video', label: 'Video Call', icon: 'video', color: 'bg-blue-100 text-blue-700 border-blue-300' },
    { id: 'phone', label: 'Phone Call', icon: 'phone', color: 'bg-orange-100 text-orange-700 border-orange-300' },
    { id: 'in-person', label: 'In-Person', icon: 'location', color: 'bg-green-100 text-green-700 border-green-300' },
    { id: 'virtual', label: 'Virtual', icon: 'globe', color: 'bg-purple-100 text-purple-700 border-purple-300' },
];

const durations = ['15', '30', '45', '60', '90', '120'];

const notificationOptions = [
    { id: '15min', label: '15 minutes before' },
    { id: '30min', label: '30 minutes before' },
    { id: '1hour', label: '1 hour before' },
    { id: '1day', label: '1 day before' },
];

const contacts = [
    { id: 1, name: 'Sarah Chen', email: 'sarah@example.com', initials: 'SC', color: 'bg-purple-500' },
    { id: 2, name: 'Michael Rodriguez', email: 'michael@example.com', initials: 'MR', color: 'bg-green-500' },
    { id: 3, name: 'Emily Watson', email: 'emily@example.com', initials: 'EW', color: 'bg-blue-500' },
    { id: 4, name: 'James Park', email: 'james@example.com', initials: 'JP', color: 'bg-orange-500' },
];

const toggleAttendee = (contactId) => {
    const index = form.value.attendees.indexOf(contactId);
    if (index === -1) {
        form.value.attendees.push(contactId);
    } else {
        form.value.attendees.splice(index, 1);
    }
};

const toggleNotification = (notifId) => {
    const index = form.value.notifications.indexOf(notifId);
    if (index === -1) {
        form.value.notifications.push(notifId);
    } else {
        form.value.notifications.splice(index, 1);
    }
};
</script>

<template>
    <Head title="Create Meeting" />
    <AppLayout>
        <section class="pt-28 pb-20 bg-gradient-radial min-h-screen">
            <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-8 animate-fade-in-up">
                    <Link href="/meetings" class="inline-flex items-center gap-2 text-teal hover:text-navy mb-4 transition-colors">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        Back to Meetings
                    </Link>
                    <h1 class="text-3xl sm:text-4xl font-bold text-navy">Create Meeting</h1>
                    <p class="text-teal/70 mt-1">Schedule a new meeting with your team</p>
                </div>

                <!-- Form -->
                <div class="glass rounded-2xl p-6 md:p-8 shadow-lg animate-fade-in-up delay-100">
                    <form class="space-y-6">
                        <!-- Title -->
                        <div>
                            <label class="block text-sm font-medium text-navy mb-2">Meeting Title *</label>
                            <input
                                v-model="form.title"
                                type="text"
                                placeholder="Enter meeting title"
                                class="w-full px-4 py-3 rounded-xl border border-lavender/50 focus:ring-2 focus:ring-teal/30 focus:border-teal"
                            />
                        </div>

                        <!-- Date & Time -->
                        <div class="grid md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-navy mb-2">Date *</label>
                                <input
                                    v-model="form.date"
                                    type="date"
                                    class="w-full px-4 py-3 rounded-xl border border-lavender/50 focus:ring-2 focus:ring-teal/30 focus:border-teal"
                                />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-navy mb-2">Time *</label>
                                <input
                                    v-model="form.time"
                                    type="time"
                                    class="w-full px-4 py-3 rounded-xl border border-lavender/50 focus:ring-2 focus:ring-teal/30 focus:border-teal"
                                />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-navy mb-2">Duration</label>
                                <select
                                    v-model="form.duration"
                                    class="w-full px-4 py-3 rounded-xl border border-lavender/50 focus:ring-2 focus:ring-teal/30 focus:border-teal"
                                >
                                    <option v-for="d in durations" :key="d" :value="d">{{ d }} minutes</option>
                                </select>
                            </div>
                        </div>

                        <!-- Meeting Type -->
                        <div>
                            <label class="block text-sm font-medium text-navy mb-3">Meeting Type</label>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                                <button
                                    v-for="type in meetingTypes"
                                    :key="type.id"
                                    type="button"
                                    @click="form.type = type.id"
                                    :class="[
                                        'p-4 rounded-xl border-2 text-center transition-all',
                                        form.type === type.id ? type.color + ' border-current' : 'bg-white border-lavender/30 hover:border-lavender'
                                    ]"
                                >
                                    <div class="flex flex-col items-center gap-2">
                                        <svg v-if="type.icon === 'video'" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                        </svg>
                                        <svg v-else-if="type.icon === 'phone'" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                        </svg>
                                        <svg v-else-if="type.icon === 'location'" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                        <svg v-else class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                                        </svg>
                                        <span class="text-sm font-medium">{{ type.label }}</span>
                                    </div>
                                </button>
                            </div>
                        </div>

                        <!-- Location/Link -->
                        <div>
                            <label class="block text-sm font-medium text-navy mb-2">
                                {{ form.type === 'video' || form.type === 'virtual' ? 'Meeting Link' : 'Location' }}
                            </label>
                            <input
                                v-model="form.location"
                                type="text"
                                :placeholder="form.type === 'video' || form.type === 'virtual' ? 'https://meet.example.com/...' : 'Enter meeting location'"
                                class="w-full px-4 py-3 rounded-xl border border-lavender/50 focus:ring-2 focus:ring-teal/30 focus:border-teal"
                            />
                        </div>

                        <!-- Attendees -->
                        <div>
                            <label class="block text-sm font-medium text-navy mb-3">Attendees</label>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <button
                                    v-for="contact in contacts"
                                    :key="contact.id"
                                    type="button"
                                    @click="toggleAttendee(contact.id)"
                                    :class="[
                                        'flex items-center gap-3 p-3 rounded-xl border-2 transition-all text-left',
                                        form.attendees.includes(contact.id) ? 'border-teal bg-teal/5' : 'border-lavender/30 hover:border-lavender bg-white'
                                    ]"
                                >
                                    <div :class="[contact.color, 'w-10 h-10 rounded-full flex items-center justify-center text-white font-medium']">
                                        {{ contact.initials }}
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="font-medium text-navy truncate">{{ contact.name }}</p>
                                        <p class="text-sm text-teal/60 truncate">{{ contact.email }}</p>
                                    </div>
                                    <div v-if="form.attendees.includes(contact.id)" class="w-6 h-6 bg-teal rounded-full flex items-center justify-center">
                                        <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                </button>
                            </div>
                        </div>

                        <!-- Notifications -->
                        <div>
                            <label class="block text-sm font-medium text-navy mb-3">Reminder Notifications</label>
                            <div class="flex flex-wrap gap-2">
                                <button
                                    v-for="notif in notificationOptions"
                                    :key="notif.id"
                                    type="button"
                                    @click="toggleNotification(notif.id)"
                                    :class="[
                                        'px-4 py-2 rounded-lg text-sm font-medium transition-all',
                                        form.notifications.includes(notif.id) ? 'bg-teal text-white' : 'bg-cream text-teal hover:bg-lavender/20'
                                    ]"
                                >
                                    {{ notif.label }}
                                </button>
                            </div>
                        </div>

                        <!-- Description -->
                        <div>
                            <label class="block text-sm font-medium text-navy mb-2">Description / Agenda</label>
                            <textarea
                                v-model="form.description"
                                rows="4"
                                placeholder="Add meeting description or agenda..."
                                class="w-full px-4 py-3 rounded-xl border border-lavender/50 focus:ring-2 focus:ring-teal/30 focus:border-teal resize-none"
                            ></textarea>
                        </div>

                        <!-- Actions -->
                        <div class="flex flex-col sm:flex-row gap-3 pt-4">
                            <button type="submit" class="btn-primary px-8 py-3 rounded-xl font-medium flex-1 sm:flex-none">
                                Create Meeting
                            </button>
                            <Link href="/meetings" class="btn-outline px-8 py-3 rounded-xl font-medium text-center">
                                Cancel
                            </Link>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </AppLayout>
</template>
