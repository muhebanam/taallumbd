import React from 'react';
import { Head } from '@inertiajs/react';
import MainLayout from '../Layouts/MainLayout';
import HeroSection from '../Components/HeroSection';
import LearningPaths from '../Components/LearningPaths';
import FeaturedCourses from '../Components/FeaturedCourses';
import RecommendedSection from '../Components/RecommendedSection';
import TopScholars from '../Components/TopScholars';
import CategoriesSection from '../Components/CategoriesSection';
import FatwaPreview from '../Components/FatwaPreview';
import ArticlesSection from '../Components/ArticlesSection';
import Testimonials from '../Components/Testimonials';
import Newsletter from '../Components/Newsletter';

export default function Home({
    popularCourses = [],
    recommendedCourses = [],
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

            {/* 2. Personalized Recommendations for Learner */}
            {recommendedCourses.length > 0 && (
                <RecommendedSection
                    title="আপনার জন্য প্রস্তাবিত কোর্সসমূহ"
                    subtitle="আপনার শিক্ষা আগ্রহ, উস্তাযের অ্যাফিনিটি ও জনপ্রিয়তার ভিত্তিতে নির্বাচিত"
                    badge="আপনার জন্য"
                    courses={recommendedCourses}
                />
            )}

            {/* 3. Structured Learning Paths (Beginner → Intermediate → Advanced) */}
            <LearningPaths />

            {/* 4. Featured Courses Grid with Enrollment CTA */}
            <FeaturedCourses courses={popularCourses} />

            {/* 5. Top Verified Scholars and Teachers */}
            <TopScholars teachers={featuredTeachers} />

            {/* 6. Knowledge Categories (Quran, Hadith, Fiqh, Aqeedah, Arabic) */}
            <CategoriesSection categories={categories} />

            {/* 7. Recent Answered Fatawa and Direct Q&A CTA */}
            <FatwaPreview fatawa={latestFatawa} />

            {/* 8. Research Articles and Islamic Writings */}
            <ArticlesSection articles={latestArticles} />

            {/* 9. Student Testimonials */}
            <Testimonials />

            {/* 10. Newsletter Subscription */}
            <Newsletter />
        </MainLayout>
    );
}
