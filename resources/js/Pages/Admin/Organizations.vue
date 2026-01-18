<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ref, watch } from 'vue';

const props = defineProps({
    organizations: Array,
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
        router.get('/admin/organizations', { search: query }, {
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
    if (searchTimeout) {
        clearTimeout(searchTimeout);
    }
    router.get('/admin/organizations', { search: searchQuery.value }, {
        preserveState: true,
        replace: true,
    });
};

const changePage = (page) => {
    router.get('/admin/organizations', { 
        search: searchQuery.value,
        page: page 
    }, {
        preserveState: true,
    });
};
</script>

<template>
    <Head title="Organizations - Admin" />
    <AdminLayout :user="auth?.user" title="Organizations Management">
        <div class="py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-8 flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Organizations</h1>
                        <p class="mt-2 text-gray-600">Manage all platform organizations</p>
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
                                placeholder="Search organizations by name or email..."
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                            />
                        </div>
                        <button
                            type="submit"
                            class="px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors font-medium"
                        >
                            Search
                        </button>
                    </form>
                </div>

                <!-- Organizations Table -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-gray-900">
                            All Organizations
                        </h2>
                        <span class="text-sm text-gray-500">
                            Showing {{ pagination?.from || 0 }} to {{ pagination?.to || 0 }} of {{ pagination?.total || 0 }} organizations
                        </span>
                    </div>

                    <div v-if="organizations && organizations.length > 0" class="divide-y divide-gray-200">
                        <div
                            v-for="org in organizations"
                            :key="org.id"
                            class="px-6 py-4 hover:bg-gray-50 transition-colors"
                        >
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-4 flex-1">
                                    <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-pink-500 rounded-full flex items-center justify-center text-white font-semibold text-lg">
                                        {{ org.name ? org.name.charAt(0).toUpperCase() : 'O' }}
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-3 flex-wrap">
                                            <p class="text-lg font-semibold text-gray-900">{{ org.name || 'Unnamed Organization' }}</p>
                                            <span
                                                class="px-2 py-1 text-xs font-medium rounded-full"
                                                :class="org.status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700'"
                                            >
                                                {{ org.status || 'inactive' }}
                                            </span>
                                            <span
                                                v-if="org.type"
                                                class="px-2 py-1 text-xs font-medium bg-blue-100 text-blue-700 rounded-full"
                                            >
                                                {{ org.type }}
                                            </span>
                                            <span
                                                v-if="org.subscription_plan"
                                                class="px-2 py-1 text-xs font-medium rounded-full"
                                                :class="org.subscription_plan.name === 'pro' ? 'bg-purple-100 text-purple-700' : 'bg-gray-100 text-gray-700'"
                                            >
                                                {{ org.subscription_plan.display_name }} Plan
                                            </span>
                                        </div>
                                        <p v-if="org.email" class="text-sm text-gray-500 mt-1">{{ org.email }}</p>
                                        <div class="flex items-center gap-4 mt-2">
                                            <span class="text-sm text-gray-600">
                                                <span class="font-medium">{{ org.user_count || 0 }}</span> users
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm text-gray-500">
                                        Created: {{ formatDate(org.created_at) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-else class="px-6 py-12 text-center text-gray-500">
                        <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                        <p class="text-lg font-medium">No organizations found</p>
                        <p class="text-sm mt-1">{{ searchQuery ? 'Try adjusting your search query' : 'Organizations will appear here once they are created' }}</p>
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

