<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ref, watch } from 'vue';

const props = defineProps({
    users: Array,
    pagination: Object,
    filters: Object,
    auth: Object,
});

const searchQuery = ref(props.filters?.search || '');

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

// Debounced search
let searchTimeout = null;
const performSearch = (query) => {
    if (searchTimeout) {
        clearTimeout(searchTimeout);
    }
    searchTimeout = setTimeout(() => {
        router.get('/admin/users', { search: query }, {
            preserveState: true,
            replace: true,
        });
    }, 500);
};

watch(searchQuery, (newValue) => {
    performSearch(newValue);
});

const handleSearch = (e) => {
    e.preventDefault();
    performSearch.cancel(); // Cancel debounce
    router.get('/admin/users', { search: searchQuery.value }, {
        preserveState: true,
        replace: true,
    });
};

const changePage = (page) => {
    router.get('/admin/users', { 
        search: searchQuery.value,
        page: page 
    }, {
        preserveState: true,
    });
};
</script>

<template>
    <Head title="Users - Admin" />
    <AdminLayout :user="auth?.user" title="Users Management">
        <div class="py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-8 flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Users</h1>
                        <p class="mt-2 text-gray-600">Manage all platform users</p>
                    </div>
                    <Link 
                        href="/admin/dashboard" 
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
                    >
                        ← Back to Dashboard
                    </Link>
                </div>

                <!-- Search and Filters -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
                    <form @submit.prevent="handleSearch" class="flex gap-4">
                        <div class="flex-1">
                            <input
                                v-model="searchQuery"
                                type="text"
                                placeholder="Search users by name or email..."
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            />
                        </div>
                        <button
                            type="submit"
                            class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium"
                        >
                            Search
                        </button>
                    </form>
                </div>

                <!-- Users Table -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-gray-900">
                            All Users
                        </h2>
                        <span class="text-sm text-gray-500">
                            Showing {{ pagination?.from || 0 }} to {{ pagination?.to || 0 }} of {{ pagination?.total || 0 }} users
                        </span>
                    </div>

                    <div v-if="users && users.length > 0" class="divide-y divide-gray-200">
                        <div
                            v-for="user in users"
                            :key="user.id"
                            class="px-6 py-4 hover:bg-gray-50 transition-colors"
                        >
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-4 flex-1">
                                    <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-purple-500 rounded-full flex items-center justify-center text-white font-semibold text-lg">
                                        {{ user.name ? user.name.charAt(0).toUpperCase() : 'U' }}
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-3 flex-wrap">
                                            <p class="text-lg font-semibold text-gray-900">{{ user.name || 'Unknown User' }}</p>
                                            <span
                                                v-if="user.is_platform_admin"
                                                class="px-2 py-1 text-xs font-medium bg-indigo-100 text-indigo-700 rounded-full"
                                            >
                                                Platform Admin
                                            </span>
                                            <span
                                                v-if="user.plan_name || user.plan_display_name"
                                                :class="[
                                                    'px-2 py-1 text-xs font-medium rounded-full whitespace-nowrap',
                                                    user.is_trial
                                                        ? 'bg-yellow-100 text-yellow-700'
                                                        : user.plan_name && user.plan_name.toLowerCase() === 'pro'
                                                            ? 'bg-purple-100 text-purple-700'
                                                            : 'bg-gray-100 text-gray-700'
                                                ]"
                                            >
                                                <template v-if="user.is_trial && user.trial_days_remaining !== null && user.trial_days_remaining > 0">
                                                    Pro Trial ({{ user.trial_days_remaining }} days)
                                                </template>
                                                <template v-else-if="user.is_trial && user.trial_days_remaining === 0">
                                                    Pro Trial (Expired)
                                                </template>
                                                <template v-else>
                                                    {{ user.plan_display_name || (user.plan_name ? user.plan_name.charAt(0).toUpperCase() + user.plan_name.slice(1) : 'Free') }}
                                                </template>
                                            </span>
                                        </div>
                                        <p class="text-sm text-gray-500 mt-1">{{ user.email || 'No email' }}</p>
                                        <div v-if="user.organizations && user.organizations.length > 0" class="flex flex-wrap gap-2 mt-2">
                                            <span
                                                v-for="org in user.organizations"
                                                :key="org.id"
                                                class="px-2 py-0.5 text-xs font-medium bg-blue-100 text-blue-700 rounded-full"
                                            >
                                                {{ org.name }} ({{ org.role }})
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm text-gray-500">
                                        Joined: {{ formatDate(user.created_at) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-else class="px-6 py-12 text-center text-gray-500">
                        <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        <p class="text-lg font-medium">No users found</p>
                        <p class="text-sm mt-1">{{ searchQuery ? 'Try adjusting your search query' : 'Users will appear here once they register' }}</p>
                    </div>

                    <!-- Pagination -->
                    <div v-if="pagination && pagination.last_page > 1" class="px-6 py-4 border-t border-gray-200 flex items-center justify-between">
                        <div class="text-sm text-gray-700">
                            Showing {{ pagination.from }} to {{ pagination.to }} of {{ pagination.total }} results
                        </div>
                        <div class="flex gap-2">
                            <button
                                @click="changePage(pagination.current_page - 1)"
                                :disabled="pagination.current_page === 1"
                                :class="[
                                    'px-4 py-2 text-sm font-medium rounded-lg transition-colors',
                                    pagination.current_page === 1
                                        ? 'bg-gray-100 text-gray-400 cursor-not-allowed'
                                        : 'bg-white border border-gray-300 text-gray-700 hover:bg-gray-50'
                                ]"
                            >
                                Previous
                            </button>
                            <span class="px-4 py-2 text-sm font-medium text-gray-700">
                                Page {{ pagination.current_page }} of {{ pagination.last_page }}
                            </span>
                            <button
                                @click="changePage(pagination.current_page + 1)"
                                :disabled="pagination.current_page === pagination.last_page"
                                :class="[
                                    'px-4 py-2 text-sm font-medium rounded-lg transition-colors',
                                    pagination.current_page === pagination.last_page
                                        ? 'bg-gray-100 text-gray-400 cursor-not-allowed'
                                        : 'bg-white border border-gray-300 text-gray-700 hover:bg-gray-50'
                                ]"
                            >
                                Next
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

