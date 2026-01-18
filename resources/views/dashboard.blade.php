@extends('layouts.app')

@section('title', 'Dashboard - MeetUI')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Page Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-slate-900 dark:text-white mb-2">Dashboard</h1>
        <p class="text-slate-600 dark:text-slate-400">Welcome back, {{ auth()->user()->name ?? 'User' }}! Here's what's happening today.</p>
    </div>
    
    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Total Meetings -->
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-lg p-6 border border-slate-200 dark:border-slate-700 hover:shadow-xl transition-shadow transform hover:scale-105">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-600 dark:text-slate-400">Total Meetings</p>
                    <p class="text-3xl font-bold text-slate-900 dark:text-white mt-2" id="total-meetings">-</p>
                </div>
                <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
            </div>
        </div>
        
        <!-- Today's Meetings -->
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-lg p-6 border border-slate-200 dark:border-slate-700 hover:shadow-xl transition-shadow transform hover:scale-105">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-600 dark:text-slate-400">Today's Meetings</p>
                    <p class="text-3xl font-bold text-slate-900 dark:text-white mt-2" id="today-meetings">-</p>
                </div>
                <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-green-600 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
        </div>
        
        <!-- Total Contacts -->
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-lg p-6 border border-slate-200 dark:border-slate-700 hover:shadow-xl transition-shadow transform hover:scale-105">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-600 dark:text-slate-400">Total Contacts</p>
                    <p class="text-3xl font-bold text-slate-900 dark:text-white mt-2" id="total-contacts">-</p>
                </div>
                <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-purple-600 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
            </div>
        </div>
        
        <!-- Total Surveys -->
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-lg p-6 border border-slate-200 dark:border-slate-700 hover:shadow-xl transition-shadow transform hover:scale-105">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-600 dark:text-slate-400">Total Surveys</p>
                    <p class="text-3xl font-bold text-slate-900 dark:text-white mt-2" id="total-surveys">-</p>
                </div>
                <div class="w-12 h-12 bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Quick Actions & Recent Activity -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Quick Actions -->
        <div class="lg:col-span-2 bg-white dark:bg-slate-800 rounded-xl shadow-lg p-6 border border-slate-200 dark:border-slate-700">
            <h2 class="text-xl font-bold text-slate-900 dark:text-white mb-4">Quick Actions</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <a href="{{ route('meetings.create') }}" class="flex items-center space-x-3 p-4 bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/20 rounded-lg hover:shadow-md transition-all group">
                    <div class="w-10 h-10 bg-blue-500 rounded-lg flex items-center justify-center group-hover:scale-110 transition-transform">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                    </div>
                    <div>
                        <p class="font-semibold text-slate-900 dark:text-white">New Meeting</p>
                        <p class="text-sm text-slate-600 dark:text-slate-400">Schedule a meeting</p>
                    </div>
                </a>
                
                <a href="{{ route('contacts.create') }}" class="flex items-center space-x-3 p-4 bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900/20 dark:to-green-800/20 rounded-lg hover:shadow-md transition-all group">
                    <div class="w-10 h-10 bg-green-500 rounded-lg flex items-center justify-center group-hover:scale-110 transition-transform">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                        </svg>
                    </div>
                    <div>
                        <p class="font-semibold text-slate-900 dark:text-white">Add Contact</p>
                        <p class="text-sm text-slate-600 dark:text-slate-400">Create new contact</p>
                    </div>
                </a>
                
                <a href="{{ route('surveys.create') }}" class="flex items-center space-x-3 p-4 bg-gradient-to-br from-purple-50 to-purple-100 dark:from-purple-900/20 dark:to-purple-800/20 rounded-lg hover:shadow-md transition-all group">
                    <div class="w-10 h-10 bg-purple-500 rounded-lg flex items-center justify-center group-hover:scale-110 transition-transform">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div>
                        <p class="font-semibold text-slate-900 dark:text-white">Create Survey</p>
                        <p class="text-sm text-slate-600 dark:text-slate-400">Build a new survey</p>
                    </div>
                </a>
                
                <a href="{{ route('calendar.index') }}" class="flex items-center space-x-3 p-4 bg-gradient-to-br from-indigo-50 to-indigo-100 dark:from-indigo-900/20 dark:to-indigo-800/20 rounded-lg hover:shadow-md transition-all group">
                    <div class="w-10 h-10 bg-indigo-500 rounded-lg flex items-center justify-center group-hover:scale-110 transition-transform">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div>
                        <p class="font-semibold text-slate-900 dark:text-white">View Calendar</p>
                        <p class="text-sm text-slate-600 dark:text-slate-400">See all meetings</p>
                    </div>
                </a>
            </div>
        </div>
        
        <!-- Upcoming Meetings -->
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-lg p-6 border border-slate-200 dark:border-slate-700">
            <h2 class="text-xl font-bold text-slate-900 dark:text-white mb-4">Upcoming Meetings</h2>
            <div id="upcoming-meetings" class="space-y-3">
                <div class="text-center py-8 text-slate-500">
                    <svg class="w-12 h-12 mx-auto mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="text-sm">Loading...</p>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
async function loadDashboardData() {
    try {
        // Load meeting statistics
        const meetingResponse = await axios.post('/api/meeting/index', {
            per_page: 1,
            page: 1
        });
        
        if (meetingResponse.data.statistics) {
            document.getElementById('total-meetings').textContent = meetingResponse.data.statistics.total_meetings || 0;
            document.getElementById('today-meetings').textContent = meetingResponse.data.statistics.today || 0;
        }
        
        // Load contact statistics
        const contactResponse = await axios.post('/api/contacts/state');
        if (contactResponse.data.data) {
            document.getElementById('total-contacts').textContent = contactResponse.data.data.total || 0;
        }
        
        // Load survey statistics
        const surveyResponse = await axios.post('/api/survey/state');
        if (surveyResponse.data.data) {
            document.getElementById('total-surveys').textContent = surveyResponse.data.data.total || 0;
        }
        
        // Load upcoming meetings
        const upcomingResponse = await axios.post('/api/meeting/index', {
            per_page: 5,
            page: 1,
            filters: {
                status: 'upcoming'
            }
        });
        
        const upcomingContainer = document.getElementById('upcoming-meetings');
        if (upcomingResponse.data.data && upcomingResponse.data.data.length > 0) {
            upcomingContainer.innerHTML = upcomingResponse.data.data.map(meeting => `
                <div class="p-3 bg-slate-50 dark:bg-slate-700/50 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                    <p class="font-semibold text-slate-900 dark:text-white text-sm">${meeting.meeting_title}</p>
                    <p class="text-xs text-slate-600 dark:text-slate-400 mt-1">${meeting.date} at ${meeting.time}</p>
                </div>
            `).join('');
        } else {
            upcomingContainer.innerHTML = `
                <div class="text-center py-8 text-slate-500">
                    <svg class="w-12 h-12 mx-auto mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <p class="text-sm">No upcoming meetings</p>
                </div>
            `;
        }
    } catch (error) {
        console.error('Error loading dashboard data:', error);
    }
}

// Load data on page load
document.addEventListener('DOMContentLoaded', loadDashboardData);
</script>
@endpush











