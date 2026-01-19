<script setup>
import { ref, computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    title: String
});

const isMobileMenuOpen = ref(false);
const isScrolled = ref(false);

// Handle scroll for header effect
if (typeof window !== 'undefined') {
    window.addEventListener('scroll', () => {
        isScrolled.value = window.scrollY > 20;
    });
}

const navLinks = [
    { name: 'Home', href: '/' },
    { name: 'Features', href: '/features' },
    { name: 'Pricing', href: '/pricing' },
    { name: 'About', href: '/about' },
    { name: 'Contact', href: '/contact' },
];
</script>

<template>
    <div class="min-h-screen bg-cream">
        <!-- Navigation -->
        <nav
            :class="[
                'fixed top-0 left-0 right-0 z-50 transition-all duration-300',
                isScrolled ? 'glass shadow-lg' : 'bg-transparent'
            ]"
        >
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16 md:h-20">
                    <!-- Logo -->
                    <Link href="/" class="flex items-center group">
                        <svg class="h-10 w-auto transform group-hover:scale-105 transition-transform" viewBox="0 0 180 50" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <!-- Icon: Two connected figures -->
                            <circle cx="12" cy="10" r="6" fill="#17313E"/>
                            <path d="M6 22 C6 16, 18 16, 18 22 C18 28, 30 28, 30 22" stroke="#17313E" stroke-width="4" fill="none" stroke-linecap="round"/>
                            <circle cx="30" cy="28" r="5" fill="#C5B0CD"/>
                            <!-- Text: YUJIX -->
                            <text x="48" y="34" font-family="system-ui, -apple-system, sans-serif" font-size="24" font-weight="600" fill="#17313E" letter-spacing="2">YUJIX</text>
                        </svg>
                    </Link>

                    <!-- Desktop Navigation -->
                    <div class="hidden lg:flex items-center space-x-1">
                        <Link
                            v-for="link in navLinks"
                            :key="link.name"
                            :href="link.href"
                            class="px-4 py-2 text-sm font-medium text-teal hover:text-navy hover:bg-lavender/20 rounded-lg transition-all duration-200"
                        >
                            {{ link.name }}
                        </Link>
                    </div>

                    <!-- CTA Button -->
                    <div class="hidden lg:flex items-center">
                        <Link href="/register" class="btn-primary px-5 py-2.5 text-sm font-medium rounded-xl shadow-lg shadow-teal/20">
                            Get Started
                        </Link>
                    </div>

                    <!-- Mobile Menu Button -->
                    <button
                        @click="isMobileMenuOpen = !isMobileMenuOpen"
                        class="lg:hidden p-2 text-navy hover:bg-lavender/20 rounded-lg transition-colors"
                    >
                        <svg v-if="!isMobileMenuOpen" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                        <svg v-else class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Mobile Menu -->
            <div
                v-show="isMobileMenuOpen"
                class="lg:hidden glass border-t border-lavender/30 animate-fade-in-down"
            >
                <div class="px-4 py-4 space-y-1">
                    <Link
                        v-for="link in navLinks"
                        :key="link.name"
                        :href="link.href"
                        class="block px-4 py-3 text-sm font-medium text-navy hover:bg-lavender/20 rounded-lg transition-colors"
                        @click="isMobileMenuOpen = false"
                    >
                        {{ link.name }}
                    </Link>
                    <div class="pt-4 border-t border-lavender/30 mt-4">
                        <Link href="/register" class="block w-full btn-primary px-4 py-3 text-center text-sm font-medium rounded-xl">
                            Get Started
                        </Link>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main>
            <slot />
        </main>

        <!-- Footer -->
        <footer class="bg-navy text-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 md:py-16">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-12">
                    <!-- Brand -->
                    <div>
                        <div class="flex items-center mb-4">
                            <svg class="h-10 w-auto" viewBox="0 0 180 50" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <!-- Icon: Two connected figures -->
                                <circle cx="12" cy="10" r="6" fill="#ffffff"/>
                                <path d="M6 22 C6 16, 18 16, 18 22 C18 28, 30 28, 30 22" stroke="#ffffff" stroke-width="4" fill="none" stroke-linecap="round"/>
                                <circle cx="30" cy="28" r="5" fill="#C5B0CD"/>
                                <!-- Text: YUJIX -->
                                <text x="48" y="34" font-family="system-ui, -apple-system, sans-serif" font-size="24" font-weight="600" fill="#ffffff" letter-spacing="2">YUJIX</text>
                            </svg>
                        </div>
                        <p class="text-cream/70 text-sm leading-relaxed max-w-sm">
                            Modern meeting management platform for teams who value their time.
                        </p>
                    </div>

                    <!-- Quick Links -->
                    <div class="flex flex-wrap gap-x-8 gap-y-4 md:justify-end">
                        <div class="flex flex-col gap-2">
                            <div class="flex flex-wrap gap-x-6 gap-y-2">
                                <Link href="/" class="text-sm text-cream/70 hover:text-white transition-colors">Home</Link>
                                <Link href="/features" class="text-sm text-cream/70 hover:text-white transition-colors">Features</Link>
                                <Link href="/pricing" class="text-sm text-cream/70 hover:text-white transition-colors">Pricing</Link>
                                <Link href="/about" class="text-sm text-cream/70 hover:text-white transition-colors">About</Link>
                                <Link href="/contact" class="text-sm text-cream/70 hover:text-white transition-colors">Contact</Link>
                                <Link href="/blog" class="text-sm text-cream/70 hover:text-white transition-colors">Blog</Link>
                            </div>
                            <div class="flex flex-wrap gap-x-6 gap-y-2 mt-2 pt-2 border-t border-white/10">
                                <Link href="/terms-and-conditions" class="text-sm text-cream/70 hover:text-white transition-colors">Terms & Conditions</Link>
                                <Link href="/privacy" class="text-sm text-cream/70 hover:text-white transition-colors">Privacy</Link>
                                <Link href="/cancellation-refunds" class="text-sm text-cream/70 hover:text-white transition-colors">Cancellation & Refunds</Link>
                                <Link href="/shipping" class="text-sm text-cream/70 hover:text-white transition-colors">Shipping</Link>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="pt-8 border-t border-white/10 flex flex-col md:flex-row justify-between items-center gap-4">
                    <p class="text-sm text-cream/50">
                        &copy; {{ new Date().getFullYear() }} YUJIX. All rights reserved.
                    </p>
                    <div class="flex items-center space-x-4">
                        <a href="https://facebook.com/yujix" target="_blank" rel="noopener noreferrer" class="text-cream/50 hover:text-white transition-colors">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                        </a>
                        <a href="https://twitter.com/yujix" target="_blank" rel="noopener noreferrer" class="text-cream/50 hover:text-white transition-colors">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/></svg>
                        </a>
                        <a href="https://linkedin.com/company/yujix" target="_blank" rel="noopener noreferrer" class="text-cream/50 hover:text-white transition-colors">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
                        </a>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</template>
