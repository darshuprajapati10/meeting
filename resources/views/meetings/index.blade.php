@extends('layouts.app')

@section('title', 'Meetings - MeetUI')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 gap-4">
        <div>
            <h1 class="text-3xl font-bold text-slate-900 dark:text-white mb-2">Meetings</h1>
            <p class="text-slate-600 dark:text-slate-400">Manage and schedule your meetings</p>
        </div>
        <a href="{{ route('meetings.create') }}" 
            class="inline-flex items-center space-x-2 px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-lg font-semibold hover:from-blue-700 hover:to-indigo-700 transform transition-all hover:scale-105 shadow-lg hover:shadow-xl">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            <span>New Meeting</span>
        </a>
    </div>
    
    <!-- Filters -->
    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-lg p-6 border border-slate-200 dark:border-slate-700 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Search</label>
                <input type="text" id="search-input" placeholder="Search meetings..." 
                    class="w-full px-4 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-200 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Type</label>
                <select id="filter-type" 
                    class="w-full px-4 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-200 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">All Types</option>
                    <option value="In-person">In-person</option>
                    <option value="Video">Video</option>
                    <option value="Phone">Phone</option>
                    <option value="Hybrid">Hybrid</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Status</label>
                <select id="filter-status" 
                    class="w-full px-4 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-200 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">All Status</option>
                    <option value="upcoming">Upcoming</option>
                    <option value="completed">Completed</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Duration</label>
                <select id="filter-duration" 
                    class="w-full px-4 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-200 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">All Durations</option>
                    <option value="15">15 minutes</option>
                    <option value="30">30 minutes</option>
                    <option value="60">1 hour</option>
                    <option value="120">2+ hours</option>
                </select>
            </div>
        </div>
    </div>
    
    <!-- Meetings List -->
    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-lg border border-slate-200 dark:border-slate-700 overflow-hidden">
        <div id="meetings-container" class="divide-y divide-slate-200 dark:divide-slate-700">
            <!-- Loading State -->
            <div class="p-12 text-center">
                <svg class="animate-spin h-8 w-8 text-blue-600 mx-auto mb-4" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <p class="text-slate-600 dark:text-slate-400">Loading meetings...</p>
            </div>
        </div>
        
        <!-- Pagination -->
        <div id="pagination-container" class="p-4 border-t border-slate-200 dark:border-slate-700"></div>
    </div>
</div>

@push('scripts')
<script>
let currentPage = 1;
let currentFilters = {};

async function loadMeetings(page = 1) {
    const container = document.getElementById('meetings-container');
    const paginationContainer = document.getElementById('pagination-container');
    
    // Show loading
    container.innerHTML = `
        <div class="p-12 text-center">
            <svg class="animate-spin h-8 w-8 text-blue-600 mx-auto mb-4" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <p class="text-slate-600 dark:text-slate-400">Loading meetings...</p>
        </div>
    `;
    
    try {
        const filters = {
            ...currentFilters,
            search: document.getElementById('search-input').value || undefined,
            meeting_type: document.getElementById('filter-type').value || undefined,
            status: document.getElementById('filter-status').value || undefined,
            duration: document.getElementById('filter-duration').value || undefined,
        };
        
        // Remove undefined values
        Object.keys(filters).forEach(key => filters[key] === undefined && delete filters[key]);
        
        const response = await axios.post('/api/meeting/index', {
            page: page,
            per_page: 15,
            filters: filters
        });
        
        const meetings = response.data.data || [];
        const meta = response.data.meta || {};
        
        if (meetings.length === 0) {
            container.innerHTML = `
                <div class="p-12 text-center">
                    <svg class="w-16 h-16 text-slate-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <p class="text-slate-600 dark:text-slate-400 text-lg">No meetings found</p>
                    <p class="text-slate-500 dark:text-slate-500 text-sm mt-2">Create your first meeting to get started</p>
                </div>
            `;
            paginationContainer.innerHTML = '';
            return;
        }
        
        // Render meetings
        container.innerHTML = meetings.map(meeting => {
            const statusColors = {
                'Created': 'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400',
                'Scheduled': 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400',
                'Completed': 'bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400',
                'Cancelled': 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400',
            };
            
            return `
                <div class="p-6 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors cursor-pointer" onclick="viewMeeting(${meeting.id})">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-2">
                                <h3 class="text-lg font-semibold text-slate-900 dark:text-white">${meeting.meeting_title}</h3>
                                <span class="px-2 py-1 text-xs font-medium rounded-full ${statusColors[meeting.status] || statusColors['Created']}">
                                    ${meeting.status}
                                </span>
                            </div>
                            <div class="flex flex-wrap items-center gap-4 text-sm text-slate-600 dark:text-slate-400">
                                <span class="flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    ${meeting.date}
                                </span>
                                <span class="flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    ${meeting.time}
                                </span>
                                <span class="flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    ${meeting.meeting_type}
                                </span>
                                ${meeting.attendees ? `<span class="flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                    ${meeting.attendees.length} attendee${meeting.attendees.length !== 1 ? 's' : ''}
                                </span>` : ''}
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <button onclick="event.stopPropagation(); editMeeting(${meeting.id})" 
                                class="p-2 text-slate-600 dark:text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </button>
                            <button onclick="event.stopPropagation(); deleteMeeting(${meeting.id})" 
                                class="p-2 text-slate-600 dark:text-slate-400 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            `;
        }).join('');
        
        // Render pagination
        if (meta.last_page > 1) {
            paginationContainer.innerHTML = `
                <div class="flex items-center justify-between">
                    <div class="text-sm text-slate-600 dark:text-slate-400">
                        Showing ${meta.from || 0} to ${meta.to || 0} of ${meta.total || 0} results
                    </div>
                    <div class="flex items-center gap-2">
                        <button onclick="loadMeetings(${meta.current_page - 1})" 
                            ${meta.current_page === 1 ? 'disabled' : ''}
                            class="px-4 py-2 rounded-lg border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 disabled:opacity-50 disabled:cursor-not-allowed">
                            Previous
                        </button>
                        <span class="px-4 py-2 text-slate-700 dark:text-slate-300">
                            Page ${meta.current_page} of ${meta.last_page}
                        </span>
                        <button onclick="loadMeetings(${meta.current_page + 1})" 
                            ${meta.current_page === meta.last_page ? 'disabled' : ''}
                            class="px-4 py-2 rounded-lg border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 disabled:opacity-50 disabled:cursor-not-allowed">
                            Next
                        </button>
                    </div>
                </div>
            `;
        } else {
            paginationContainer.innerHTML = '';
        }
        
    } catch (error) {
        console.error('Error loading meetings:', error);
        container.innerHTML = `
            <div class="p-12 text-center">
                <p class="text-red-600 dark:text-red-400">Error loading meetings. Please try again.</p>
            </div>
        `;
    }
}

function viewMeeting(id) {
    // Navigate to meeting details
    window.location.href = `/meetings/${id}`;
}

function editMeeting(id) {
    // Navigate to edit page
    window.location.href = `/meetings/${id}/edit`;
}

async function deleteMeeting(id) {
    if (!confirm('Are you sure you want to delete this meeting?')) return;
    
    try {
        await axios.post('/api/meeting/delete', { id: id });
        loadMeetings(currentPage);
    } catch (error) {
        alert('Error deleting meeting. Please try again.');
    }
}

// Filter event listeners
document.getElementById('search-input').addEventListener('input', debounce(() => loadMeetings(1), 500));
document.getElementById('filter-type').addEventListener('change', () => loadMeetings(1));
document.getElementById('filter-status').addEventListener('change', () => loadMeetings(1));
document.getElementById('filter-duration').addEventListener('change', () => loadMeetings(1));

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Load meetings on page load
document.addEventListener('DOMContentLoaded', () => loadMeetings(1));
</script>
@endpush











