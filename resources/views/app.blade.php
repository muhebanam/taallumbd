<!DOCTYPE html>
<html lang="bn" class="scroll-smooth">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="theme-color" content="#102526">
        <meta name="description" content="আত-তাআল্লুম — আধুনিক ডিজিটাল ইসলামী একাডেমি, স্কলার নেটওয়ার্ক ও কুরআন-হাদিস জ্ঞানভাণ্ডার। বিশ্বমানের কোর্সে ইলম অর্জন করুন।">
        <meta property="og:title" content="আত-তাআল্লুম — ডিজিটাল ইসলামী একাডেমি">
        <meta property="og:description" content="উচ্চতর ইসলামী শিক্ষা, কুরআন, হাদিস, ফিকহ ও আরবি ভাষার বিশ্বস্ত প্ল্যাটফর্ম।">
        <meta property="og:type" content="website">
        <meta property="og:url" content="https://taallumbd.com">

        <title inertia>{{ config('app.name', 'আত-তাআল্লুম | TaallumBD') }}</title>

        <!-- Google Fonts: Amiri (Arabic) & Hind Siliguri (Bengali) & Outfit (Latin) -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=Amiri:ital,wght@0,400;0,700;1,400&family=Hind+Siliguri:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700;800&display=swap">
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Amiri:ital,wght@0,400;0,700;1,400&family=Hind+Siliguri:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700;800&display=swap" media="print" onload="this.media='all'">
        <noscript>
            <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Amiri:ital,wght@0,400;0,700;1,400&family=Hind+Siliguri:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700;800&display=swap">
        </noscript>

        <!-- Scripts & Styles -->
        @routes
        @viteReactRefresh
        @vite(['resources/css/app.css', 'resources/js/app.jsx'])
        @inertiaHead
    </head>
    <body class="font-bangla bg-[#F8FAF8] text-[#102526] antialiased selection:bg-[#FFF99A] selection:text-[#102526]">
        @inertia
    </body>
</html>
