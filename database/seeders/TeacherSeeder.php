<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Teacher;
use App\Models\Fatwa;
use App\Models\Review;
use App\Models\Course;
use App\Models\Category;
use App\Models\Publication;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TeacherSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Get existing instructors
        $inst1 = User::where('email', 'instructor@taallumbd.local')->first();
        $inst2 = User::where('email', 'instructor2@taallumbd.local')->first();

        // Update existing instructor names to match seed data names
        $inst1->update(['name' => 'মুফতী আব্দুল্লাহ আল মাহমুদ']);
        $inst2->update(['name' => 'মাওলানা সালমান আহমাদ']);

        // Create remaining 4 instructor users
        $inst3 = User::firstOrCreate(
            ['email' => 'mahmud@taallumbd.local'],
            [
                'name' => 'মাওলানা মাহমুদ হাসান',
                'password' => bcrypt('password'),
                'role' => 'instructor'
            ]
        );

        $inst4 = User::firstOrCreate(
            ['email' => 'yahya@taallumbd.local'],
            [
                'name' => 'মুফতী ইয়াহইয়া ফারুক',
                'password' => bcrypt('password'),
                'role' => 'instructor'
            ]
        );

        $inst5 = User::firstOrCreate(
            ['email' => 'abubakr@taallumbd.local'],
            [
                'name' => 'মাওলানা আবু বকর সিদ্দীক',
                'password' => bcrypt('password'),
                'role' => 'instructor'
            ]
        );

        $inst6 = User::firstOrCreate(
            ['email' => 'umar@taallumbd.local'],
            [
                'name' => 'মাওলানা উমর ফারুক',
                'password' => bcrypt('password'),
                'role' => 'instructor'
            ]
        );

        // Fetch students for followers, questions, and reviews
        $students = User::where('role', 'student')->get();
        $courses = Course::published()->get();

        // Get publication categories
        $bookStoreCat = Category::where('type', 'publication')->where('slug', 'printed-books')->first();
        $ebookStoreCat = Category::where('type', 'publication')->where('slug', 'ebooks')->first();

        // 2. Seed Teachers
        $teachersData = [
            [
                'user_id' => $inst1->id,
                'name' => 'মুফতী আব্দুল্লাহ আল মাহমুদ',
                'slug' => 'mufti-abdullah-al-mahmud',
                'designation' => 'মুফতী ও ফিকহ ইন্সট্রাক্টর',
                'headline' => 'ফিকহ, হালাল-হারাম ও ইসলামী অর্থনীতি বিষয়ক শিক্ষক',
                'short_bio' => 'দীর্ঘ ১০ বছর যাবত ফিকহ ও ফাতাওয়া বিভাগে পাঠদান করছেন।',
                'bio' => "মুফতী আব্দুল্লাহ আল মাহমুদ একজন বিশিষ্ট ফিকহ গবেষক ও শিক্ষক। তিনি দেশের শীর্ষস্থানীয় ইসলামী বিদ্যাপীঠ থেকে দাওরায়ে হাদীস এবং পরবর্তীতে ফিকহ ও ফাতাওয়া (ইফতা) সম্পন্ন করেন।\n\nবর্তমানে তিনি আত-তাআল্লুম প্ল্যাটফর্মের ফিকহ ও হালাল-হারাম বিষয়ের সিনিয়র ইন্সট্রাক্টর হিসেবে দায়িত্বরত আছেন। দ্বীনের প্রামাণ্য এবং নির্ভরযোগ্য ইলমকে সহজ ভাষায় সকলের কাছে পৌঁছে দেওয়াই তার অন্যতম উদ্দেশ্য।",
                'location' => 'ঢাকা, বাংলাদেশ',
                'email' => 'abdullah@taallumbd.com',
                'phone' => '01711223344',
                'website' => 'https://abdullah.me',
                'specialties' => ['ফিকহ', 'হালাল-হারাম', 'ইসলামী অর্থনীতি'],
                'knowledge_path' => ['প্রাথমিক আক্বীদা', 'দৈনন্দিন ফিকহ', 'হালাল-হারাম বিধান', 'সমকালীন মাসআলা', 'আত্মশুদ্ধি'],
                'expertise_map' => [
                    ['main_area' => 'ফিকহ', 'sub_areas' => ['তাহারাত', 'সালাত', 'পারিবারিক মাসআলা']],
                    ['main_area' => 'ইসলামী অর্থনীতি', 'sub_areas' => ['হৈরি কেনা-বেচা', 'মুদারাবা', 'সুদমুক্ত অর্থায়ন']]
                ],
                'qualifications' => [
                    ['degree_title' => 'ইফতা (মুফতী সনদ)', 'institution' => 'মারকাযুদ দাওয়াহ আল ইসলামিয়া', 'department' => 'ফিকহ ও হাদীস', 'year' => '২০১৫', 'description' => 'উচ্চতর ফিকহ ও ফাতাওয়া গবেষণা কোর্স সম্পন্ন।'],
                    ['degree_title' => 'দাওরায়ে হাদীস (মাস্টার্স)', 'institution' => 'আল-জামিয়াতুল আহলিয়া দারুল উলূম মুঈনুল ইসলাম হাটহাজারী', 'department' => 'হাদীস', 'year' => '২০১৩', 'description' => 'প্রথম শ্রেণীতে উত্তীর্ণ।']
                ],
                'experiences' => [
                    ['title' => 'সিনিয়র মুফতী', 'organization' => 'ইসলামিক রিসার্চ একাডেমি', 'start_year' => '২০১৬', 'end_year' => '২০২০', 'currently_working' => false, 'description' => 'সমকালীন ফিকহী সমস্যা ও সমাধান নিয়ে গবেষণামূলক কাজ করেছেন।'],
                    ['title' => 'ফিকহ ইন্সট্রাক্টর', 'organization' => 'আত-তাআল্লুম', 'start_year' => '২০২১', 'end_year' => null, 'currently_working' => true, 'description' => 'অনলাইন ফিকহী কোর্সসমূহ পরিচালনা এবং প্রশ্নোত্তর বিভাগ তদারকি করছেন।']
                ],
                'office_hours' => [
                    ['day' => 'শনিবার - সোমবার', 'time' => 'রাত ৯.০০ - ১০.০০টা', 'method' => 'জুম লাইভ প্রশ্নোত্তর']
                ],
                'consultation_enabled' => true,
                'consultation_note' => 'যেকোনো ফিকহী প্রশ্ন করার জন্য উপরের প্রশ্নোত্তর ট্যাবটি ব্যবহার করুন।',
                'status' => 'active',
                'featured' => true,
                'is_verified' => true,
                'verified_at' => now(),
                'allow_follow' => true,
                'show_email' => true,
                'show_phone' => false,
                'sort_order' => 1
            ],
            [
                'user_id' => $inst2->id,
                'name' => 'মাওলানা সালমান আহমাদ',
                'slug' => 'maulana-salman-ahmad',
                'designation' => 'কুরআন ও তাজবীদ শিক্ষক',
                'headline' => 'কুরআন শিক্ষা, তাজবীদ ও কুরআনিক আরবি বিষয়ক প্রশিক্ষক',
                'short_bio' => '১০ বছরের বেশি সময় ধরে তাজবীদ ও বিশুদ্ধ কুরআন তিলাওয়াত শেখাচ্ছেন।',
                'bio' => "মাওলানা সালমান আহমাদ একজন আন্তর্জাতিক মানের ক্বারী ও আরবী ভাষার শিক্ষক। তিনি আরবী ভাষা ও সাহিত্যের উপর উচ্চতর ডিগ্রি অর্জন করেছেন এবং ক্বারীমন্ডলীর তত্ত্বাবধানে ক্বিরাআতের ইলম হাসিল করেছেন।\n\nআত-তাআল্লুম প্ল্যাটফর্মে শিক্ষার্থীদের সুন্দর কন্ঠে এবং বিশুদ্ধ তাজবীদের সাথে কুরআন শেখানোর পেছনে তিনি গুরুত্বপূর্ণ অবদান রাখছেন।",
                'location' => 'চট্টগ্রাম, বাংলাদেশ',
                'email' => 'salman@taallumbd.com',
                'phone' => '01811223344',
                'website' => 'https://qarisalman.com',
                'specialties' => ['কুরআন', 'তাজবীদ', 'আরবী ভাষা'],
                'knowledge_path' => ['আরবী হরফের মাখরাজ', 'তাজবীদের মৌলিক ও জটিল নিয়ম', 'তিলাওয়াত সংস্কার', 'কুরআনিক আরবীর ভিত্তি'],
                'expertise_map' => [
                    ['main_area' => 'তাজবীদ', 'sub_areas' => ['মাখরাজ', 'মীম সাকিন', 'ওয়াকফ বিধি']],
                    ['main_area' => 'আরবী ভাষা', 'sub_areas' => ['কুরআনিক ব্যাকরণ', 'নাহু-সরফ']]
                ],
                'qualifications' => [
                    ['degree_title' => 'তাজবীদ ও ক্বিরাআত স্পেশালিস্ট', 'institution' => 'দারুল ক্বিরাআত একাডেমি', 'department' => 'তাজবীদ', 'year' => '২০১৬', 'description' => 'বিশুদ্ধ তিলাওয়াত ও তাজবীদের সনদ।'],
                    ['degree_title' => 'কামিল (আরবী সাহিত্য)', 'institution' => 'ঢাকা আলিয়া মাদ্রাসা', 'department' => 'আদব', 'year' => '২০১৪', 'description' => 'প্রথম বিভাগ।']
                ],
                'experiences' => [
                    ['title' => 'প্রধান ক্বারী', 'organization' => 'নূরানী কুরআন শিক্ষা কেন্দ্র', 'start_year' => '২০১৫', 'end_year' => '২০২০', 'currently_working' => false, 'description' => 'কুরআন তিলাওয়াত প্রশিক্ষক হিসেবে দায়িত্ব পালন।'],
                    ['title' => 'কুরআন শিক্ষক', 'organization' => 'আত-তাআল্লুম', 'start_year' => '২০২০', 'end_year' => null, 'currently_working' => true, 'description' => 'সহীহ তিলাওয়াত কোর্সের মেন্টর।']
                ],
                'office_hours' => [
                    ['day' => 'রবিবার ও বুধবার', 'time' => 'বিকাল ৫.০০ - ৬.০০টা', 'method' => 'ভয়েস নোট গ্রুপ']
                ],
                'consultation_enabled' => true,
                'consultation_note' => 'বিশুদ্ধ তিলাওয়াত সংক্রান্ত প্রশ্নসমূহের উত্তর দেওয়া হয়।',
                'status' => 'active',
                'featured' => true,
                'is_verified' => true,
                'verified_at' => now(),
                'allow_follow' => true,
                'show_email' => false,
                'show_phone' => false,
                'sort_order' => 2
            ],
            [
                'user_id' => $inst3->id,
                'name' => 'মাওলানা মাহমুদ হাসান',
                'slug' => 'maulana-mahmud-hasan',
                'designation' => 'হাদীস গবেষক',
                'headline' => 'হাদীস ও উলূমুল হাদীস বিষয়ক গবেষক ও শিক্ষক',
                'short_bio' => 'ইলমে হাদীস এবং হাদীসের প্রামাণিকতা নিয়ে কাজ করছেন।',
                'bio' => 'মাওলানা মাহমুদ হাসান একজন তরুণ হাদীস গবেষক। তিনি দাওরায়ে হাদীস সমাপ্ত করে হাদীস শাস্ত্রের উপর বিশেষ গবেষণা করেছেন। দ্বীনি শিক্ষার্থীদের কাছে বিশুদ্ধ হাদীস ও হাদীসের ইতিহাস সঠিকভাবে ব্যাখ্যা করার জন্য তিনি সুপরিচিত।',
                'location' => 'সিলেট, বাংলাদেশ',
                'specialties' => ['হাদীস', 'উলূমুল হাদীস'],
                'knowledge_path' => ['হাদীসের নির্ভরযোগ্যতা', 'হাদীসের পরিভাষা', 'বুখারী শরীফের অধ্যয়ন'],
                'expertise_map' => [
                    ['main_area' => 'হাদীস', 'sub_areas' => ['সিহাহ সিত্তা', 'হাদীস শাস্ত্রের ইতিহাস']]
                ],
                'qualifications' => [
                    ['degree_title' => 'তাকহাসসুস ফি উলূমিল হাদীস', 'institution' => 'হাদীস গবেষণা কেন্দ্র', 'department' => 'হাদীস', 'year' => '২০১৯', 'description' => 'হাদীস ও অসমাউর রিজাল শাস্ত্রের উপর গবেষণা সম্পন্ন।']
                ],
                'experiences' => [
                    ['title' => 'গবেষক', 'organization' => 'ইসলামী বিশ্বকোষ প্রকল্প', 'start_year' => '২০২০', 'end_year' => null, 'currently_working' => true, 'description' => 'হাদীস অধ্যায়ের অনুবাদ ও সম্পাদনা করছেন।']
                ],
                'consultation_enabled' => false,
                'status' => 'active',
                'featured' => false,
                'is_verified' => true,
                'verified_at' => now(),
                'allow_follow' => true,
                'sort_order' => 3
            ],
            [
                'user_id' => $inst4->id,
                'name' => 'মুফতী ইয়াহইয়া ফারুক',
                'slug' => 'mufti-yahya-faruq',
                'designation' => 'ফাতাওয়া বিভাগ',
                'headline' => 'সমকালীন মাসআলা, আক্বীদা ও ফিকহ বিষয়ে উত্তর প্রদানকারী',
                'short_bio' => 'ইন্টারনেটে সঠিক আক্বীদা ও দৈনন্দিন মাসআলা ছড়িয়ে দিতে সক্রিয় আছেন।',
                'bio' => 'মুফতী ইয়াহইয়া ফারুক ফাতাওয়া পরিচালনা এবং সমকালীন মাসআলা সংক্রান্ত জটিল প্রশ্নের সমাধানের জন্য সুপরিচিত। তিনি ইফতা বিভাগ সম্পূর্ণ করে আধুনিক অর্থনৈতিক সমস্যা ও তার ইসলামী সমাধানের গবেষণায় নিয়োজিত আছেন।',
                'location' => 'ঢাকা, বাংলাদেশ',
                'email' => 'yahya@taallumbd.com',
                'specialties' => ['আক্বীদা', 'ফিকহ', 'সমকালীন মাসআলা'],
                'knowledge_path' => ['আক্বীদার বুনিয়াদী শিক্ষা', 'সমকালীন ফিকহী জিজ্ঞাসা', 'নাস্তিকতা ও সংশয়বাদ খণ্ডন'],
                'expertise_map' => [
                    ['main_area' => 'আক্বীদা', 'sub_areas' => ['আহলে সুন্নাত ওয়াল জামায়াত আক্বীদা', 'সংশয়বাদ সমাধান']]
                ],
                'qualifications' => [
                    ['degree_title' => 'উচ্চতর ইফতা (ফাতাওয়া)', 'institution' => 'দারুল উলূম ঢাকা', 'department' => 'ইফতা', 'year' => '২০১৮', 'description' => 'তাজবীদ ও ফিকহ ফাতাওয়া সনদ।']
                ],
                'experiences' => [
                    ['title' => 'মুফতী', 'organization' => 'দারুল উলূম ফাতাওয়া বিভাগ', 'start_year' => '২০১৯', 'end_year' => null, 'currently_working' => true, 'description' => 'দৈনিক প্রাপ্ত ফিকহী প্রশ্নাবলির উত্তর প্রদান করছেন।']
                ],
                'office_hours' => [
                    ['day' => 'মঙ্গলবার ও বৃহস্পতিবার', 'time' => 'রাত ৮.০০ - ৯.০০টা', 'method' => 'লিখিত প্রশ্নোত্তর']
                ],
                'consultation_enabled' => true,
                'consultation_note' => 'আক্বীদা ও ফিকহী মাসআলার নির্ভরযোগ্য সমাধান প্রদান করা হয়।',
                'status' => 'active',
                'featured' => false,
                'is_verified' => false,
                'allow_follow' => true,
                'sort_order' => 4
            ],
            [
                'user_id' => $inst5->id,
                'name' => 'মাওলানা আবু বকর সিদ্দীক',
                'slug' => 'maulana-abu-bakr-siddique',
                'designation' => 'সীরাত ও ইতিহাস শিক্ষক',
                'headline' => 'সীরাত, ইসলামী ইতিহাস ও জীবনী বিষয়ক শিক্ষক',
                'short_bio' => 'ইসলামের গৌরবময় ইতিহাস ও রাসূলের সীরাতকে সুন্দর ভাষায় শিক্ষার্থীদের সামনে তুলে ধরেন।',
                'bio' => 'মাওলানা আবু বকর সিদ্দীক একজন মিষ্টভাষী ইসলামী আলোচক ও শিক্ষক। তার প্রিয় গবেষণার ক্ষেত্র হলো রাসূলুল্লাহ ﷺ-এর জীবন ও খেলাফতে রাশেদার ইতিহাস। শিক্ষার্থীদের হৃদয়ে ইসলামের প্রকৃত আবেদন ফুটিয়ে তুলতে তিনি অক্লান্ত পরিশ্রম করছেন।',
                'location' => 'রাজশাহী, বাংলাদেশ',
                'specialties' => ['সীরাত', 'ইতিহাস', 'জীবনী'],
                'knowledge_path' => ['রাসূলুল্লাহ ﷺ-এর সীরাত', 'সাহাবীদের জীবন ও শিক্ষা', 'খিলাফতের ইতিহাস'],
                'qualifications' => [
                    ['degree_title' => 'দাওরায়ে হাদীস', 'institution' => 'জামিয়া সালাফিয়্যা রাজশাহী', 'department' => 'হাদীস ও ইতিহাস', 'year' => '২০১২', 'description' => 'প্রথম বিভাগ।']
                ],
                'experiences' => [
                    ['title' => 'ইতিহাস শিক্ষক', 'organization' => 'ইসলামী ইতিহাস রিসার্চ সেন্টার', 'start_year' => '২০১৪', 'end_year' => '২০১৯', 'currently_working' => false, 'description' => 'ঐতিহাসিক বিষয়ে লেকচার প্রধান ও তথ্য যাচাই।']
                ],
                'consultation_enabled' => false,
                'status' => 'active',
                'featured' => false,
                'is_verified' => true,
                'verified_at' => now(),
                'allow_follow' => true,
                'sort_order' => 5
            ],
            [
                'user_id' => $inst6->id,
                'name' => 'মাওলানা উমর ফারুক',
                'slug' => 'maulana-umar-faruq',
                'designation' => 'আরবী ভাষা ইন্সট্রাক্টর',
                'headline' => 'আরবী ভাষা ও কুরআনিক আরবি শেখানোর অভিজ্ঞ শিক্ষক',
                'short_bio' => 'সহজ পদ্ধতিতে অ-আরবীদের আরবী ভাষা ও ব্যাকরণ শেখান।',
                'bio' => 'মাওলানা উমর ফারুক আধুনিক আরবী ভাষা শিক্ষাদানের উপর বিশেষভাবে অভিজ্ঞ। আরবী ব্যাকরণ (নাহু ও সরফ) মুখস্থ না করিয়ে ব্যবহারিক প্রয়োগের মাধ্যমে কীভাবে সহজ পদ্ধতিতে আরবী লেখা, পড়া ও বলা শেখা যায়, তার নানা কৌশল তিনি শিক্ষার্থীদের শিখিয়ে থাকেন।',
                'location' => 'খুলনা, বাংলাদেশ',
                'specialties' => ['আরবী ভাষা', 'কুরআনিক আরবি'],
                'knowledge_path' => ['আরবী কথোপকথন', 'কুরআনিক আরবীর ব্যাকরণ', 'আরবী অনুবাদ দক্ষতা'],
                'qualifications' => [
                    ['degree_title' => 'ডিপ্লোমা ইন অ্যারাবিক ল্যাঙ্গুয়েজ', 'institution' => '킹 সাউদ ইউনিভার্সিটি, রিয়াদ', 'department' => 'আরবী ভাষা ইনস্টিটিউট', 'year' => '২০১৭', 'description' => 'ব্যবহারিক আরবী ভাষার উচ্চতর কোর্স সম্পন্ন।']
                ],
                'experiences' => [
                    ['title' => 'আরবী প্রশিক্ষক', 'organization' => 'আল-কুরআন একাডেমি', 'start_year' => '২০১৮', 'end_year' => null, 'currently_working' => true, 'description' => 'আরবী ক্লাসের প্রধান মেন্টর।']
                ],
                'consultation_enabled' => false,
                'status' => 'active',
                'featured' => false,
                'is_verified' => false,
                'allow_follow' => true,
                'sort_order' => 6
            ]
        ];

        foreach ($teachersData as $teacherVal) {
            $teacher = Teacher::create($teacherVal);

            // Seed followers (2-5 random followers from students)
            $fCount = rand(2, 5);
            $randomStudents = $students->random($fCount);
            foreach ($randomStudents as $student) {
                if ($student->id !== $teacher->user_id) {
                    $teacher->followers()->attach($student->id);
                }
            }

            // Seed Q&As using the Fatawa table
            if ($teacher->status === 'active' && $teacher->consultation_enabled) {
                // 1 answered question
                Fatwa::create([
                    'question_user_id' => $students->random()->id,
                    'answered_by' => $teacher->user_id,
                    'teacher_id' => $teacher->id,
                    'category_id' => 1, // Default category
                    'question_title' => 'অযু ভঙ্গের কারণ',
                    'question_body' => 'অযু করার পর রক্ত বের হলে কি অযু ভেঙে যায়?',
                    'answer_body' => 'হ্যাঁ, শরীর থেকে রক্ত বের হয়ে যদি প্রবাহিত হয় (অর্থাৎ গড়িয়ে পড়ে), তবে অযু ভেঙে যাবে। সামান্য রক্ত যা গড়িয়ে পড়েনি বা ক্ষতমুখেই জমে আছে, তার দ্বারা অযু ভাঙবে না।',
                    'is_private' => false,
                    'status' => 'published',
                    'published_at' => now()->subDays(2)
                ]);

                // 1 pending question
                Fatwa::create([
                    'question_user_id' => $students->random()->id,
                    'answered_by' => $teacher->user_id,
                    'teacher_id' => $teacher->id,
                    'category_id' => 1,
                    'question_title' => 'ফরয সালাতের পর সম্মিলিত দুআ',
                    'question_body' => 'ফরয নামায শেষ হওয়ার পর কি ইমাম ও মুক্তাদী মিলে সম্মিলিতভাবে দুআ করা যাবে?',
                    'answer_body' => null,
                    'is_private' => false,
                    'status' => 'pending'
                ]);
            }

            // Seed Reviews (using the Reviews table)
            $teacherCourseIds = Course::where('instructor_id', $teacher->user_id)->pluck('id');
            if ($teacherCourseIds->count() > 0) {
                foreach ($teacherCourseIds as $cId) {
                    // Seed 1 approved review
                    Review::create([
                        'teacher_id' => $teacher->id,
                        'user_id' => $students->random()->id,
                        'course_id' => $cId,
                        'rating' => rand(4, 5),
                        'comment' => 'আলহামদুলিল্লাহ, ওস্তাদের পড়ানোর স্টাইল খুবই চমৎকার এবং জটিল বিষয়গুলো খুব সহজ ভাষায় বুঝিয়ে দেন। এই কোর্সটি করে অনেক কিছু শিখতে পেরেছি।',
                        'status' => 'approved'
                    ]);

                    // Seed 1 pending review
                    Review::create([
                        'teacher_id' => $teacher->id,
                        'user_id' => $students->random()->id,
                        'course_id' => $cId,
                        'rating' => 5,
                        'comment' => 'দারুণ শিক্ষক এবং চমৎকার কোর্স!',
                        'status' => 'pending'
                    ]);
                }
            } else {
                // If the teacher has no courses, seed direct reviews
                Review::create([
                    'teacher_id' => $teacher->id,
                    'user_id' => $students->random()->id,
                    'course_id' => null,
                    'rating' => 5,
                    'comment' => 'আলহামদুলিল্লাহ, ওস্তাদের আখলাক ও ফিকহী ইলম চমৎকার। প্রশ্নোত্তরের মাধ্যমে অনেক উপকৃত হয়েছি।',
                    'status' => 'approved'
                ]);
            }

            // Seed sample publications for each teacher
            if ($bookStoreCat) {
                Publication::create([
                    'category_id' => $bookStoreCat->id,
                    'user_id' => $teacher->user_id,
                    'title' => $teacher->name . ' - এর ফিকহ সংকলন',
                    'slug' => Str::slug($teacher->slug . '-fiqh-compilation'),
                    'description' => 'দ্বীনী জীবন যাপনের জন্য প্রয়োজনীয় ফিকহী বিষয়াবলির উপর একটি নির্ভরযোগ্য সংকলন গ্রন্থ।',
                    'type' => 'book',
                    'external_url' => 'https://example.com/books',
                    'status' => 'published',
                    'published_at' => now()
                ]);
            }

            if ($ebookStoreCat) {
                Publication::create([
                    'category_id' => $ebookStoreCat->id,
                    'user_id' => $teacher->user_id,
                    'title' => $teacher->name . ' - এর তাজবীদ গাইড',
                    'slug' => Str::slug($teacher->slug . '-tajweed-guide'),
                    'description' => 'বিশুদ্ধ তাজবীদ শেখার এবং হরফের সঠিক উচ্চারণ শেখার সহজ ই-বুক।',
                    'type' => 'ebook',
                    'file_url' => 'https://example.com/files/guide.pdf',
                    'status' => 'published',
                    'published_at' => now()
                ]);
            }
        }
    }
}
