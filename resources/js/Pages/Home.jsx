import React from 'react';
import { Head } from '@inertiajs/react';
import MainLayout from '../Layouts/MainLayout';
import HeroSection from '../Components/HeroSection';
import LearningPaths from '../Components/LearningPaths';
import FeaturedCourses from '../Components/FeaturedCourses';
import TopScholars from '../Components/TopScholars';
import CategoriesSection from '../Components/CategoriesSection';
import FatwaPreview from '../Components/FatwaPreview';
import ArticlesSection from '../Components/ArticlesSection';
import Testimonials from '../Components/Testimonials';
import Newsletter from '../Components/Newsletter';

export default function Home({
    popularCourses = [],
    featuredTeachers = [],
    categories = [],
    latestFatawa = [],
    latestArticles = [],
    stats = []
}) {
    return (
        <MainLayout>
            <Head>
                <title>আত-তাআল্লুম — আধুনিক ডিজিটাল ইসলামী একাডেমি</title>
                <meta 
                    name="description" 
                    content="উলামায়ে কেরামের পরিচালনায় প্রাতিষ্ঠানিক সিলেবাসে ঘরে বসেই অর্জন করুন দ্বীনি ইলম। কুরআন, হাদীস, ফিকহ ও আরবী ভাষার উচ্চতর জ্ঞান।" 
                />
            </Head>

            {/* 1. Hero with Calligraphy and CTAs */}
            <HeroSection stats={stats} />

            {/* 2. Structured Learning Paths (Beginner → Intermediate → Advanced) */}
            <LearningPaths />

            {/* 3. Featured Courses Grid with Enrollment CTA */}
            <FeaturedCourses courses={popularCourses} />

            {/* 4. Top Verified Scholars and Teachers */}
            <TopScholars teachers={featuredTeachers} />

            {/* 5. Knowledge Categories (Quran, Hadith, Fiqh, Aqeedah, Arabic) */}
            <CategoriesSection categories={categories} />

            {/* 6. Recent Answered Fatawa and Direct Q&A CTA */}
            <FatwaPreview fatawa={latestFatawa} />

            {/* 7. Research Articles and Islamic Writings */}
            <ArticlesSection articles={latestArticles} />

            {/* 8. Student Testimonials */}
            <Testimonials />

            {/* 9. Newsletter Subscription */}
            <Newsletter />
        </MainLayout>
    );
}
