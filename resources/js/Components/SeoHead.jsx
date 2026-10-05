import React from 'react';
import { Head, usePage } from '@inertiajs/react';

export default function SeoHead({
    title,
    description = 'উলামায়ে কেরামের পরিচালনায় প্রাতিষ্ঠানিক সিলেবাসে ঘরে বসেই অর্জন করুন দ্বীনি ইলম। কুরআন, হাদীস, ফিকহ ও আরবী ভাষার উচ্চতর জ্ঞান।',
    canonical,
    ogImage = '/images/og-banner.jpg',
    ogType = 'website',
    jsonLd = null,
    children,
}) {
    const { url } = usePage();
    const siteUrl = 'https://taallum.org'; // Canonical base fallback
    const fullCanonical = canonical || (url ? `${siteUrl}${url}` : siteUrl);
    const fullOgImage = ogImage.startsWith('http') ? ogImage : `${siteUrl}${ogImage}`;
    const displayTitle = title ? `${title} — আত-তাআল্লুম` : 'আত-তাআল্লুম — আধুনিক ডিজিটাল ইসলামী একাডেমি';

    return (
        <Head>
            <title>{displayTitle}</title>
            <meta name="description" content={description} />
            <link rel="canonical" href={fullCanonical} />

            {/* Open Graph / Facebook */}
            <meta property="og:type" content={ogType} />
            <meta property="og:title" content={displayTitle} />
            <meta property="og:description" content={description} />
            <meta property="og:image" content={fullOgImage} />
            <meta property="og:url" content={fullCanonical} />
            <meta property="og:site_name" content="আত-তাআল্লুম" />
            <meta property="og:locale" content="bn_BD" />

            {/* Twitter */}
            <meta name="twitter:card" content="summary_large_image" />
            <meta name="twitter:title" content={displayTitle} />
            <meta name="twitter:description" content={description} />
            <meta name="twitter:image" content={fullOgImage} />
            <meta name="twitter:site" content="@taallumbd" />

            {/* Structured Data (JSON-LD) */}
            {jsonLd && (
                <script type="application/ld+json">
                    {JSON.stringify(jsonLd)}
                </script>
            )}

            {children}
        </Head>
    );
}
