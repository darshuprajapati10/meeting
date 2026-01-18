<script setup>
import { Head } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { computed } from 'vue';

const props = defineProps({
    stats: Object,
    recentUsers: Array,
    recentOrganizations: Array,
    usersByMonth: Array,
    subscriptionPlans: Array,
    auth: Object,
});

// Format date consistently
const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-GB', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    });
};

// Calculate max count for chart (ensure it's at least 1 to avoid division by zero)
const maxCount = computed(() => {
    if (!props.usersByMonth || props.usersByMonth.length === 0) {
        return 1;
    }
    const max = Math.max(...props.usersByMonth.map(m => m.count || 0));
    return max > 0 ? max : 1;
});

import { router } from '@inertiajs/vue3';

// Handle card clicks
const handleCardClick = (type) => {
    // Navigate to individual pages
    const routes = {
        'users': '/admin/users',
        'organizations': '/admin/organizations',
        'contacts': '/admin/contacts',
        'meetings': '/admin/meetings',
        'surveys': '/admin/surveys',
        'admins': '/admin/users?filter=admins'
    };
    
    const route = routes[type];
    if (route) {
        router.visit(route);
    }
};
</script>

<template>
    <Head title="Admin Dashboard" />
    <AdminLayout :user="auth?.user">
        <div class="py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-gray-900">Admin Dashboard</h1>
                    <p class="mt-2 text-gray-600">Platform overview and statistics</p>
                </div>

                <!-- Stats Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                    <div 
                        @click="handleCardClick('users')"
                        class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md hover:border-blue-300 transition-all cursor-pointer active:scale-95"
                    >
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Total Users</p>
                                <p class="text-3xl font-bold text-gray-900 mt-2">{{ stats?.total_users || 0 }}</p>
                            </div>
                            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div 
                        @click="handleCardClick('organizations')"
                        class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md hover:border-purple-300 transition-all cursor-pointer active:scale-95"
                    >
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Organizations</p>
                                <p class="text-3xl font-bold text-gray-900 mt-2">{{ stats?.total_organizations || 0 }}</p>
                            </div>
                            <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div 
                        @click="handleCardClick('contacts')"
                        class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md hover:border-green-300 transition-all cursor-pointer active:scale-95"
                    >
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Contacts</p>
                                <p class="text-3xl font-bold text-gray-900 mt-2">{{ stats?.total_contacts || 0 }}</p>
                            </div>
                            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div 
                        @click="handleCardClick('meetings')"
                        class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md hover:border-orange-300 transition-all cursor-pointer active:scale-95"
                    >
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Meetings</p>
                                <p class="text-3xl font-bold text-gray-900 mt-2">{{ stats?.total_meetings || 0 }}</p>
                            </div>
                            <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div 
                        @click="handleCardClick('surveys')"
                        class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md hover:border-pink-300 transition-all cursor-pointer active:scale-95"
                    >
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Surveys</p>
                                <p class="text-3xl font-bold text-gray-900 mt-2">{{ stats?.total_surveys || 0 }}</p>
                            </div>
                            <div class="w-12 h-12 bg-pink-100 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-pink-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div 
                        @click="handleCardClick('admins')"
                        class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md hover:border-indigo-300 transition-all cursor-pointer active:scale-95"
                    >
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Platform Admins</p>
                                <p class="text-3xl font-bold text-gray-900 mt-2">{{ stats?.platform_admins || 0 }}</p>
                            </div>
                            <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                    <!-- Recent Users -->
                    <div id="recent-users" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 scroll-mt-8">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-xl font-semibold text-gray-900">Recent Users</h2>
                            <span class="text-sm text-gray-500">{{ recentUsers?.length || 0 }} shown</span>
                        </div>
                        <div class="space-y-3 max-h-96 overflow-y-auto pr-2">
                            <div v-if="!recentUsers || recentUsers.length === 0" class="text-center py-8 text-gray-500">
                                <p>No users found</p>
                            </div>
                            <div
                                v-for="user in recentUsers"
                                :key="user.id"
                                class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors"
                            >
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-purple-500 rounded-full flex items-center justify-center text-white font-semibold">
                                        {{ user.name ? user.name.charAt(0).toUpperCase() : 'U' }}
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900">{{ user.name || 'Unknown User' }}</p>
                                        <p class="text-sm text-gray-500">{{ user.email || 'No email' }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span
                                        v-if="user.is_platform_admin"
                                        class="px-2 py-1 text-xs font-medium bg-indigo-100 text-indigo-700 rounded-full whitespace-nowrap"
                                    >
                                        Admin
                                    </span>
                                    <span class="text-xs text-gray-400 whitespace-nowrap">
                                        {{ formatDate(user.created_at) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Organizations -->
                    <div id="recent-organizations" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 scroll-mt-8">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-xl font-semibold text-gray-900">Recent Organizations</h2>
                            <span class="text-sm text-gray-500">{{ recentOrganizations?.length || 0 }} shown</span>
                        </div>
                        <div class="space-y-3 max-h-96 overflow-y-auto pr-2">
                            <div v-if="!recentOrganizations || recentOrganizations.length === 0" class="text-center py-8 text-gray-500">
                                <p>No organizations found</p>
                            </div>
                            <div
                                v-for="org in recentOrganizations"
                                :key="org.id"
                                class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors"
                            >
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-pink-500 rounded-full flex items-center justify-center text-white font-semibold">
                                        {{ org.name ? org.name.charAt(0).toUpperCase() : 'O' }}
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900">{{ org.name || 'Unnamed Organization' }}</p>
                                        <p class="text-sm text-gray-500">Organization</p>
                                    </div>
                                </div>
                                <span class="text-xs text-gray-400 whitespace-nowrap">
                                    {{ formatDate(org.created_at) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Subscription Plans -->
                <div id="subscription-plans" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-8 scroll-mt-8">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-xl font-semibold text-gray-900">Subscription Plans</h2>
                        <span class="text-sm text-gray-500">{{ subscriptionPlans?.length || 0 }} active plans</span>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div
                            v-for="plan in subscriptionPlans"
                            :key="plan.id"
                            class="border-2 rounded-xl p-6 transition-all hover:shadow-lg"
                            :class="plan.name === 'pro' ? 'border-purple-300 bg-gradient-to-br from-purple-50 to-pink-50' : 'border-gray-200 bg-white'"
                        >
                            <div class="flex items-center justify-between mb-4">
                                <div>
                                    <h3 class="text-2xl font-bold text-gray-900">{{ plan.display_name }}</h3>
                                    <p class="text-sm text-gray-600 mt-1">{{ plan.description }}</p>
                                </div>
                                <span
                                    v-if="plan.name === 'pro'"
                                    class="px-3 py-1 text-xs font-medium bg-purple-100 text-purple-700 rounded-full"
                                >
                                    Popular
                                </span>
                            </div>
                            <div class="mb-4">
                                <div class="flex items-baseline gap-2">
                                    <span class="text-3xl font-bold text-gray-900">{{ plan.price_monthly_formatted }}</span>
                                    <span class="text-gray-600">/month</span>
                                </div>
                                <div class="flex items-baseline gap-2 mt-2">
                                    <span class="text-xl font-semibold text-gray-700">{{ plan.price_yearly_formatted }}</span>
                                    <span class="text-gray-500 text-sm">/year</span>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 mt-4 mb-4 pb-4 border-b border-gray-200">
                                <span
                                    class="px-2 py-1 text-xs font-medium rounded-full"
                                    :class="plan.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700'"
                                >
                                    {{ plan.is_active ? 'Active' : 'Inactive' }}
                                </span>
                                <span class="text-xs text-gray-500">Sort Order: {{ plan.sort_order }}</span>
                            </div>

                            <!-- Users Section -->
                            <div class="mt-4">
                                <div class="flex items-center justify-between mb-3">
                                    <h4 class="text-sm font-semibold text-gray-900">Users</h4>
                                    <span class="text-xs text-gray-500">{{ plan.user_count || 0 }} users • {{ plan.organization_count || 0 }} organizations</span>
                                </div>
                                <div class="space-y-2 max-h-64 overflow-y-auto pr-2">
                                    <div v-if="!plan.users || plan.users.length === 0" class="text-center py-6 text-gray-400">
                                        <svg class="w-8 h-8 mx-auto mb-2 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                        </svg>
                                        <p class="text-xs">No users on this plan</p>
                                    </div>
                                    <div
                                        v-for="user in plan.users"
                                        :key="user.id"
                                        class="flex items-center justify-between p-2 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors border border-gray-200"
                                    >
                                        <div class="flex items-center gap-2 flex-1 min-w-0">
                                            <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-purple-500 rounded-full flex items-center justify-center text-white text-xs font-semibold flex-shrink-0">
                                                {{ user.name ? user.name.charAt(0).toUpperCase() : 'U' }}
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <p class="font-medium text-gray-900 text-xs truncate">{{ user.name || 'Unknown User' }}</p>
                                                <p class="text-xs text-gray-500 truncate">{{ user.email || 'No email' }}</p>
                                                <p v-if="user.organization_name" class="text-xs text-gray-400 truncate mt-0.5">
                                                    {{ user.organization_name }}
                                                </p>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2 flex-shrink-0 ml-2">
                                            <span
                                                v-if="user.role"
                                                class="px-2 py-0.5 text-xs font-medium bg-blue-100 text-blue-700 rounded-full whitespace-nowrap"
                                            >
                                                {{ user.role }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div v-if="plan.user_count > (plan.users?.length || 0)" class="mt-3 pt-3 border-t border-gray-200 text-center">
                                    <p class="text-xs text-gray-500">
                                        Showing {{ plan.users?.length || 0 }} of {{ plan.user_count }} users
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-if="!subscriptionPlans || subscriptionPlans.length === 0" class="text-center py-12 text-gray-500">
                        <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="text-lg font-medium">No subscription plans found</p>
                        <p class="text-sm mt-1">Subscription plans will appear here</p>
                    </div>
                </div>

                <!-- Users Growth Chart -->
                <div id="chart-section" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 scroll-mt-8">
                    <h2 class="text-xl font-semibold text-gray-900 mb-6">User Growth (Last 6 Months)</h2>
                    <div v-if="usersByMonth && usersByMonth.length > 0" class="space-y-4">
                        <!-- Chart Container -->
                        <div class="flex items-end justify-between gap-2 h-64 px-4 pb-8 border-b border-gray-200">
                            <div
                                v-for="(month, index) in usersByMonth"
                                :key="index"
                                class="flex-1 flex flex-col items-center justify-end h-full max-w-[80px]"
                            >
                                <!-- Bar -->
                                <div
                                    class="w-full bg-gradient-to-t from-blue-500 to-blue-400 rounded-t-lg transition-all hover:from-blue-600 hover:to-blue-500 cursor-pointer group relative min-h-[4px]"
                                    :style="{ 
                                        height: maxCount.value > 0 ? `${Math.max((month.count / maxCount.value) * 80, month.count > 0 ? 8 : 4)}%` : '4px'
                                    }"
                                    :title="`${month.month}: ${month.count} users`"
                                >
                                    <!-- Tooltip on hover -->
                                    <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-2 py-1 bg-gray-800 text-white text-xs rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none">
                                        {{ month.count }} user{{ month.count !== 1 ? 's' : '' }}
                                    </div>
                                </div>
                                <!-- Month Label -->
                                <p class="text-xs text-gray-500 mt-3 text-center font-medium truncate w-full">{{ month.month || 'N/A' }}</p>
                                <!-- Count Label -->
                                <p class="text-xs font-bold text-gray-900 mt-1">{{ month.count || 0 }}</p>
                            </div>
                        </div>
                        <!-- Chart Summary -->
                        <div class="flex justify-between items-center pt-4">
                            <div class="text-sm text-gray-600">
                                <span class="font-semibold">Total:</span> 
                                <span class="text-gray-900">{{ usersByMonth.reduce((sum, m) => sum + (m.count || 0), 0) }} users</span>
                            </div>
                            <div class="text-sm text-gray-600">
                                <span class="font-semibold">Average:</span> 
                                <span class="text-gray-900">{{ Math.round(usersByMonth.reduce((sum, m) => sum + (m.count || 0), 0) / usersByMonth.length) }} users/month</span>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-center py-12 text-gray-500">
                        <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                        <p class="text-lg font-medium">No data available</p>
                        <p class="text-sm mt-1">User growth data will appear here</p>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
