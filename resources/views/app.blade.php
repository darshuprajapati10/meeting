<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Primary Meta Tags -->
    <title inertia>{{ config('app.name', 'YUJIX') }}</title>
    <meta name="title" content="YUJIX - Modern Meeting Management Platform">
    <meta name="description" content="YUJIX is a complete meeting management solution. Schedule meetings, manage contacts, send reminders, and collect feedback with surveys. Free for small teams.">
    <meta name="keywords" content="meeting scheduler, meeting management app, team meetings, meeting reminders, post-meeting surveys, contact management, meeting attendees, free meeting app">
    <meta name="author" content="YUJIX">
    <meta name="robots" content="index, follow">
    <meta name="language" content="English">
    <meta name="revisit-after" content="7 days">

    <!-- Canonical URL -->
    <link rel="canonical" href="{{ url()->current() }}">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="YUJIX - Modern Meeting Management Platform">
    <meta property="og:description" content="Schedule meetings, manage contacts, send reminders, and collect feedback with surveys. The complete meeting management solution for teams.">
    <meta property="og:image" content="{{ asset('images/og-image.png') }}">
    <meta property="og:site_name" content="YUJIX">
    <meta property="og:locale" content="en_US">

    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="{{ url()->current() }}">
    <meta name="twitter:title" content="YUJIX - Modern Meeting Management Platform">
    <meta name="twitter:description" content="Schedule meetings, manage contacts, send reminders, and collect feedback with surveys. The complete meeting management solution for teams.">
    <meta name="twitter:image" content="{{ asset('images/og-image.png') }}">

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">
    <meta name="theme-color" content="#17313E">
    <meta name="msapplication-TileColor" content="#17313E">
    <meta name="msapplication-TileImage" content="{{ asset('android-chrome-192x192.png') }}">

    <!-- Preconnect for Performance -->
    <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
    <link rel="dns-prefetch" href="https://fonts.bunny.net">

    <!-- Fonts with display=swap for performance -->
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet">

    <!-- Structured Data - Organization -->
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@@type": "Organization",
        "name": "YUJIX",
        "url": "{{ config('app.url') }}",
        "logo": "{{ asset('images/logo.png') }}",
        "description": "Modern meeting management platform for teams who value their time.",
        "sameAs": [
            "https://facebook.com/yujix",
            "https://twitter.com/yujix",
            "https://linkedin.com/company/yujix"
        ],
        "contactPoint": {
            "@@type": "ContactPoint",
            "contactType": "customer service",
            "email": "support@yujix.com"
        }
    }
    </script>

    <!-- Structured Data - WebSite -->
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@@type": "WebSite",
        "name": "YUJIX",
        "url": "{{ config('app.url') }}",
        "potentialAction": {
            "@@type": "SearchAction",
            "target": "{{ config('app.url') }}/blog?search={search_term_string}",
            "query-input": "required name=search_term_string"
        }
    }
    </script>

    <!-- Structured Data - SoftwareApplication -->
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@@type": "SoftwareApplication",
        "name": "YUJIX",
        "applicationCategory": "BusinessApplication",
        "operatingSystem": "iOS, Android",
        "offers": {
            "@@type": "Offer",
            "price": "0",
            "priceCurrency": "USD"
        },
        "aggregateRating": {
            "@@type": "AggregateRating",
            "ratingValue": "4.8",
            "ratingCount": "1250"
        }
    }
    </script>

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @inertiaHead
</head>
<body class="font-sans antialiased">
    @inertia

    <!-- Noscript fallback -->
    <noscript>
        <div style="padding: 20px; text-align: center; background: #17313E; color: white;">
            Please enable JavaScript to use YUJIX Meeting Management Platform.
        </div>
    </noscript>
</body>
</html>
