<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const surveys = [
    { id: 1, title: 'Meeting Feedback Survey', status: 'active', questions: 8, responses: 45, createdAt: 'Dec 15, 2024' },
    { id: 2, title: 'Team Satisfaction Q4', status: 'active', questions: 12, responses: 32, createdAt: 'Dec 10, 2024' },
    { id: 3, title: 'Client Onboarding Feedback', status: 'draft', questions: 5, responses: 0, createdAt: 'Dec 18, 2024' },
    { id: 4, title: 'Product Feature Request', status: 'completed', questions: 6, responses: 78, createdAt: 'Nov 28, 2024' },
    { id: 5, title: 'Event Registration', status: 'active', questions: 4, responses: 124, createdAt: 'Dec 5, 2024' },
];

const getStatusClass = (status) => {
    switch (status) {
        case 'active': return 'bg-green-100 text-green-700';
        case 'draft': return 'bg-yellow-100 text-yellow-700';
        case 'completed': return 'bg-blue-100 text-blue-700';
        default: return 'bg-gray-100 text-gray-700';
    }
};
</script>

<template>
    <Head title="Surveys" />
    <AppLayout>
        <!-- Hero Section -->
        <section class="pt-28 pb-12 bg-gradient-radial">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8 animate-fade-in-up">
                    <div>
                        <h1 class="text-3xl sm:text-4xl font-bold text-navy">Surveys</h1>
                        <p class="text-teal/70 mt-1">Create and manage feedback surveys</p>
                    </div>
                    <button class="btn-primary px-6 py-3 rounded-xl font-medium inline-flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Create Survey
                    </button>
                </div>

                <!-- Stats -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 animate-fade-in-up delay-100">
                    <div class="glass rounded-xl p-4">
                        <p class="text-2xl font-bold text-navy">5</p>
                        <p class="text-sm text-teal/70">Total Surveys</p>
                    </div>
                    <div class="glass rounded-xl p-4">
                        <p class="text-2xl font-bold text-green-600">3</p>
                        <p class="text-sm text-teal/70">Active</p>
                    </div>
                    <div class="glass rounded-xl p-4">
                        <p class="text-2xl font-bold text-navy">279</p>
                        <p class="text-sm text-teal/70">Total Responses</p>
                    </div>
                    <div class="glass rounded-xl p-4">
                        <p class="text-2xl font-bold text-navy">92%</p>
                        <p class="text-sm text-teal/70">Completion Rate</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Surveys List -->
        <section class="pb-20 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="space-y-4">
                    <div
                        v-for="(survey, index) in surveys"
                        :key="survey.id"
                        class="glass rounded-2xl p-6 card-hover animate-fade-in-up"
                        :style="{ animationDelay: `${index * 50}ms` }"
                    >
                        <div class="flex flex-col md:flex-row md:items-center gap-4">
                            <div class="w-14 h-14 bg-gradient-navy rounded-xl flex items-center justify-center flex-shrink-0">
                                <svg class="w-7 h-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex flex-wrap items-center gap-2 mb-1">
                                    <h3 class="text-lg font-semibold text-navy">{{ survey.title }}</h3>
                                    <span :class="[getStatusClass(survey.status), 'px-2 py-0.5 rounded-full text-xs font-medium capitalize']">
                                        {{ survey.status }}
                                    </span>
                                </div>
                                <div class="flex flex-wrap items-center gap-4 text-sm text-teal/70">
                                    <span>{{ survey.questions }} questions</span>
                                    <span>{{ survey.responses }} responses</span>
                                    <span>Created {{ survey.createdAt }}</span>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <button class="px-4 py-2 text-sm font-medium text-teal bg-cream rounded-lg hover:bg-lavender/30 transition-colors">
                                    Analytics
                                </button>
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
        </section>
    </AppLayout>
</template>
