<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const contacts = [
    { id: 1, name: 'Sarah Chen', role: 'Product Manager', company: 'TechCorp', email: 'sarah@techcorp.com', initials: 'SC', color: 'bg-purple-500', favorite: true },
    { id: 2, name: 'Michael Rodriguez', role: 'CEO', company: 'StartupX', email: 'michael@startupx.com', initials: 'MR', color: 'bg-green-500', favorite: true },
    { id: 3, name: 'Emily Watson', role: 'HR Director', company: 'GlobalCo', email: 'emily@globalco.com', initials: 'EW', color: 'bg-blue-500', favorite: false },
    { id: 4, name: 'James Park', role: 'Designer', company: 'Creative Inc', email: 'james@creative.com', initials: 'JP', color: 'bg-orange-500', favorite: false },
    { id: 5, name: 'Anna Lee', role: 'Developer', company: 'CodeLab', email: 'anna@codelab.com', initials: 'AL', color: 'bg-pink-500', favorite: true },
    { id: 6, name: 'David Kim', role: 'Marketing Lead', company: 'BrandCo', email: 'david@brandco.com', initials: 'DK', color: 'bg-teal-500', favorite: false },
];
</script>

<template>
    <Head title="Contacts" />
    <AppLayout>
        <!-- Hero Section -->
        <section class="pt-28 pb-12 bg-gradient-radial">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8 animate-fade-in-up">
                    <div>
                        <h1 class="text-3xl sm:text-4xl font-bold text-navy">Contacts</h1>
                        <p class="text-teal/70 mt-1">Manage your professional network</p>
                    </div>
                    <div class="flex gap-3">
                        <button class="btn-outline px-4 py-2.5 rounded-xl font-medium inline-flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                            </svg>
                            Import CSV
                        </button>
                        <button class="btn-primary px-5 py-2.5 rounded-xl font-medium inline-flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Add Contact
                        </button>
                    </div>
                </div>

                <!-- Search -->
                <div class="relative animate-fade-in-up delay-100">
                    <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-teal/50" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input
                        type="text"
                        placeholder="Search contacts..."
                        class="w-full pl-12 pr-4 py-3 rounded-xl border border-lavender/50 focus:ring-2 focus:ring-teal/30 focus:border-teal bg-white"
                    />
                </div>
            </div>
        </section>

        <!-- Contacts Grid -->
        <section class="pb-20 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div
                        v-for="(contact, index) in contacts"
                        :key="contact.id"
                        class="glass rounded-2xl p-6 card-hover animate-fade-in-up"
                        :style="{ animationDelay: `${index * 50}ms` }"
                    >
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex items-center gap-4">
                                <div :class="[contact.color, 'w-14 h-14 rounded-xl flex items-center justify-center text-white text-lg font-semibold']">
                                    {{ contact.initials }}
                                </div>
                                <div>
                                    <h3 class="font-semibold text-navy">{{ contact.name }}</h3>
                                    <p class="text-sm text-teal/70">{{ contact.role }}</p>
                                </div>
                            </div>
                            <button class="p-1">
                                <svg :class="['w-5 h-5', contact.favorite ? 'text-yellow-400 fill-current' : 'text-gray-300']" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                </svg>
                            </button>
                        </div>
                        <div class="space-y-2 text-sm">
                            <p class="flex items-center gap-2 text-teal/70">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                                {{ contact.company }}
                            </p>
                            <p class="flex items-center gap-2 text-teal/70">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                                {{ contact.email }}
                            </p>
                        </div>
                        <div class="flex gap-2 mt-4 pt-4 border-t border-lavender/30">
                            <button class="flex-1 px-3 py-2 text-sm font-medium text-teal bg-cream rounded-lg hover:bg-lavender/30 transition-colors">
                                View Profile
                            </button>
                            <button class="flex-1 px-3 py-2 text-sm font-medium text-white bg-teal rounded-lg hover:bg-teal-dark transition-colors">
                                Schedule
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </AppLayout>
</template>
