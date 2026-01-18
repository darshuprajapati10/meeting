<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ref, watch } from 'vue';

const props = defineProps({
    surveys: Array,
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
        router.get('/admin/surveys', { search: query }, {
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
    router.get('/admin/surveys', { search: searchQuery.value }, {
        preserveState: true,
        replace: true,
    });
};

const changePage = (page) => {
    router.get('/admin/surveys', { 
        search: searchQuery.value,
        page: page 
    }, {
        preserveState: true,
    });
};
</script>

<template>
    <Head title="Surveys - Admin" />
    <AdminLayout :user="auth?.user" title="Surveys Management">
        <div class="py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-8 flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Surveys</h1>
                        <p class="mt-2 text-gray-600">Manage all platform surveys</p>
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
                                placeholder="Search surveys by title or description..."
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                            />
                        </div>
                        <button
                            type="submit"
                            class="px-6 py-2 bg-pink-600 text-white rounded-lg hover:bg-pink-700 transition-colors font-medium"
                        >
                            Search
                        </button>
                    </form>
                </div>

                <!-- Surveys Table -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-gray-900">
                            All Surveys
                        </h2>
                        <span class="text-sm text-gray-500">
                            Showing {{ pagination?.from || 0 }} to {{ pagination?.to || 0 }} of {{ pagination?.total || 0 }} surveys
                        </span>
                    </div>

                    <div v-if="surveys && surveys.length > 0" class="divide-y divide-gray-200">
                        <div
                            v-for="survey in surveys"
                            :key="survey.id"
                            class="px-6 py-4 hover:bg-gray-50 transition-colors"
                        >
                            <div class="flex items-start justify-between">
                                <div class="flex items-start gap-4 flex-1">
                                    <div class="w-12 h-12 bg-gradient-to-br from-pink-500 to-rose-500 rounded-lg flex items-center justify-center text-white flex-shrink-0">
                                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                        </svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-3 flex-wrap mb-2">
                                            <p class="text-lg font-semibold text-gray-900">{{ survey.title || 'Untitled Survey' }}</p>
                                            <span
                                                class="px-2 py-1 text-xs font-medium rounded-full"
                                                :class="survey.status === 'active' ? 'bg-green-100 text-green-700' : survey.status === 'draft' ? 'bg-yellow-100 text-yellow-700' : survey.status === 'closed' ? 'bg-gray-100 text-gray-700' : 'bg-gray-100 text-gray-700'"
                                            >
                                                {{ survey.status || 'unknown' }}
                                            </span>
                                        </div>
                                        <p v-if="survey.description" class="text-sm text-gray-600 mb-2 line-clamp-2">{{ survey.description }}</p>
                                        <div v-if="survey.organization" class="mt-2">
                                            <span class="px-2 py-0.5 text-xs font-medium bg-blue-100 text-blue-700 rounded-full">
                                                {{ survey.organization.name }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-right ml-4">
                                    <p class="text-sm text-gray-500">
                                        Created: {{ formatDate(survey.created_at) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-else class="px-6 py-12 text-center text-gray-500">
                        <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        <p class="text-lg font-medium">No surveys found</p>
                        <p class="text-sm mt-1">{{ searchQuery ? 'Try adjusting your search query' : 'Surveys will appear here once they are created' }}</p>
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

