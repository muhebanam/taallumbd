<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Coupon;
use App\Models\Course;
use App\Models\Fatwa;
use App\Models\ForumComment;
use App\Models\ForumPost;
use App\Models\Hadith;
use App\Models\HadithBook;
use App\Models\HadithChapter;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Seeder;

class PlatformContentSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first() ?? User::first();
        $teachers = Teacher::with('user')->get();
        $firstTeacher = $teachers->first();

        /* -------------------------------------------------------------
         * 1. Hadith Books & Sample Hadiths
         * ------------------------------------------------------------- */
        $booksData = [
            [
                'name_bn' => 'সহীহুল বুখারী',
                'name_ar' => 'صحيح البخاري',
                'name_en' => 'Sahih al-Bukhari',
                'slug' => 'sahih-bukhari',
                'compiler_bn' => 'ইমাম মুহাম্মদ বিন ইসমাইল আল-বুখারী (রহ.)',
                'description' => 'আসমাউর রিজাল ও হাদীসের সনদের ক্ষেত্রে সর্বোচ্চ বিশুদ্ধতম হাদীস সংকলন। এতে মোট ৭,৫৬৩ টি হাদীস রয়েছে।',
                'total_hadiths' => 7563,
                'is_active' => true,
                'sort_order' => 1,
                'chapters' => [
                    [
                        'chapter_number' => 1,
                        'name_bn' => 'ওহীর সূচনা পর্ব',
                        'name_ar' => 'كتاب بدء الوحي',
                        'hadiths' => [
                            [
                                'hadith_number' => 1,
                                'arabic_text' => 'إِنَّمَا الأَعْمَالُ بِالنِّيَّاتِ، وَإِنَّمَا لِكُلِّ امْرِئٍ مَا نَوَى، فَمَنْ كَانَتْ هِجْرَتُهُ إِلَى دُنْيَا يُصِيبُهَا أَوْ إِلَى امْرَأَةٍ يَنْكِحُهَا، فَهِجْرَتُهُ إِلَى مَا هَاجَرَ إِلَيْهِ.',
                                'bangla_translation' => 'নিশ্চয়ই প্রত্যেক কাজ নিয়তের উপর নির্ভরশীল। মানুষ যা নিয়ত করে কেবল তাই পায়। অতএব যার হিজরত হবে দুনিয়া অর্জনের উদ্দেশ্যে কিংবা কোনো নারীকে বিবাহ করার উদ্দেশ্যে, তার হিজরত সেই উদ্দেশ্যেই গণ্য হবে যার জন্য সে হিজরত করেছে।',
                                'narrator_bn' => 'হযরত উমর ইবনুল খাত্তাব (রা.)',
                                'grade' => 'সহীহ',
                            ],
                            [
                                'hadith_number' => 2,
                                'arabic_text' => 'أَنَّ عَائِشَةَ أُمَّ الْمُؤْمِنِينَ ـ رضى الله عنها ـ أَخْبَرَتْهُ أَنَّ الْحَارِثَ بْنَ هِشَامٍ ـ رضى الله عنه ـ سَأَلَ رَسُولَ اللَّهِ صلى الله عليه وسلم فَقَالَ يَا رَسُولَ اللَّهِ كَيْفَ يَأْتِيكَ الْوَحْىُ فَقَالَ رَسُولُ اللَّهِ صلى الله عليه وسلم: أَحْيَانًا يَأْتِينِي مِثْلَ صَلْصَلَةِ الْجَرَسِ وَهُوَ أَشَدُّهُ عَلَىَّ...',
                                'bangla_translation' => 'উম্মুল মুমিনীন আয়িশা (রা.) হতে বর্ণিত, হারিস ইবনে হিশাম (রা.) রাসুলুল্লাহ (ﷺ)-কে জিজ্ঞাসা করলেন: হে আল্লাহর রাসুল! আপনার কাছে ওহী কীভাবে আসে? তিনি বললেন: কোনো কোনো সময় ওহী আমার নিকট ঘন্টার টুংটাং শব্দের ন্যায় আসে, আর এটিই আমার জন্য অধিক কষ্টসাধ্য হয়...',
                                'narrator_bn' => 'উম্মুল মুমিনীন হযরত আয়িশা (রা.)',
                                'grade' => 'সহীহ',
                            ],
                        ],
                    ],
                    [
                        'chapter_number' => 2,
                        'name_bn' => 'ঈমান অধ্যায়',
                        'name_ar' => 'كتاب الإيمان',
                        'hadiths' => [
                            [
                                'hadith_number' => 8,
                                'arabic_text' => 'بُنِيَ الإِسْلاَمُ عَلَى خَمْسٍ: شَهَادَةِ أَنْ لاَ إِلَهَ إِلاَّ اللَّهُ وَأَنَّ مُحَمَّدًا رَسُولُ اللَّهِ، وَإِقَامِ الصَّلاَةِ، وَإِيتَاءِ الزَّكَاةِ، وَالْحَجِّ، وَصَوْمِ رَمَضَانَ.',
                                'bangla_translation' => 'ইসলামের স্তম্ভ পাঁচটি: সাক্ষ্য দেওয়া যে, আল্লাহ ব্যতীত কোনো উপাস্য নেই এবং মুহাম্মদ (ﷺ) আল্লাহর রাসুল, সালাত কায়েম করা, যাকাত আদায় করা, হজ্জ করা এবং রমযানের সিয়াম পালন করা।',
                                'narrator_bn' => 'হযরত আব্দুল্লাহ ইবনে উমর (রা.)',
                                'grade' => 'সহীহ',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'name_bn' => 'সহীহ মুসলিম',
                'name_ar' => 'صحيح مسلم',
                'name_en' => 'Sahih Muslim',
                'slug' => 'sahih-muslim',
                'compiler_bn' => 'ইমাম আবুল হুসাইন মুসলিম ইবনুল হাজ্জাজ আল-কুশায়রী (রহ.)',
                'description' => 'সিহাহ সিত্তার অন্যতম শ্রেষ্ঠ গ্রন্থ। চমৎকার অধ্যায়বিন্যাস ও বিশুদ্ধ বর্ণনাসূত্রের জন্য এটি বিশ্বখ্যাত।',
                'total_hadiths' => 7500,
                'is_active' => true,
                'sort_order' => 2,
                'chapters' => [
                    [
                        'chapter_number' => 1,
                        'name_bn' => 'ঈমানের ভূমিকা পর্ব',
                        'name_ar' => 'كتاب الإيمان',
                        'hadiths' => [
                            [
                                'hadith_number' => 1,
                                'arabic_text' => 'بَيْنَمَا نَحْنُ عِنْدَ رَسُولِ اللَّهِ صلى الله عليه وسلم ذَاتَ يَوْمٍ إِذْ طَلَعَ عَلَيْنَا رَجُلٌ شَدِيدُ بَيَاضِ الثِّيَابِ شَدِيدُ سَوَادِ الشَّعَرِ لاَ يُرَى عَلَيْهِ أَثَرُ السَّفَرِ وَلاَ يَعْرِفُهُ مِنَّا أَحَدٌ... (حديث جبريل)',
                                'bangla_translation' => 'একদিন আমরা রাসুলুল্লাহ (ﷺ)-এর দরবারে বসা ছিলাম। এমন সময় ধবধবে সাদা পোশাক ও কুচকুচে কালো চুল বিশিষ্ট এক ব্যক্তি আসলেন, যার মধ্যে সফরের কোনো চিহ্ন ছিল না... (হাদীসে জিবরীল: ইসলাম, ঈমান ও ইহসান পরিচিতি)।',
                                'narrator_bn' => 'হযরত উমর ইবনুল খাত্তাব (রা.)',
                                'grade' => 'সহীহ',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'name_bn' => 'জামে আত-তিরমিযী',
                'name_ar' => 'جامع الترمذي',
                'name_en' => 'Jami at-Tirmidhi',
                'slug' => 'jami-tirmidhi',
                'compiler_bn' => 'ইমাম আবু ঈসা মুহাম্মদ বিন ঈসা আত-তিরমিযী (রহ.)',
                'description' => 'ফিকহী মাসআলা ও হাদীসের মান (সহীহ, হাসান, গরীব) বিশ্লেষণের অনন্য সংকলন।',
                'total_hadiths' => 3956,
                'is_active' => true,
                'sort_order' => 3,
                'chapters' => [],
            ],
            [
                'name_bn' => 'সুনান আবী দাউদ',
                'name_ar' => 'سنن أبي داود',
                'name_en' => 'Sunan Abi Dawud',
                'compiler_bn' => 'ইমাম আবু দাউদ সুলাইমান ইবনুল আশআস আস-সিজিস্তানী (রহ.)',
                'slug' => 'sunan-abu-dawud',
                'description' => 'আহকাম ও ফিকহুল হাদীসের নির্ভরযোগ্য সর্বশ্রেষ্ঠ কিতাব।',
                'total_hadiths' => 5274,
                'is_active' => true,
                'sort_order' => 4,
                'chapters' => [],
            ],
            [
                'name_bn' => 'সুনান আন-নাসায়ী',
                'name_ar' => 'سنن النسائي',
                'name_en' => 'Sunan an-Nasa\'i',
                'compiler_bn' => 'ইমাম আহমদ বিন শুআইব আন-নাসায়ী (রহ.)',
                'slug' => 'sunan-an-nasai',
                'description' => 'কঠোর শর্তযুক্ত সনদ যাচাই বাছাইয়ের জন্য প্রসিদ্ধ সংকলন।',
                'total_hadiths' => 5758,
                'is_active' => true,
                'sort_order' => 5,
                'chapters' => [],
            ],
            [
                'name_bn' => 'সুনান ইবনে মাজাহ',
                'name_ar' => 'سنن ابن ماجه',
                'name_en' => 'Sunan Ibn Majah',
                'compiler_bn' => 'ইমাম মুহাম্মদ বিন ইয়াজিদ ইবনে মাজাহ আল-কাযভীনী (রহ.)',
                'slug' => 'sunan-ibn-majah',
                'description' => 'সিহাহ সিত্তার ষষ্ঠ কিতাব, চমৎকার বিন্যাস ও দুর্লভ মাসআলার হাদীস সমৃদ্ধ।',
                'total_hadiths' => 4341,
                'is_active' => true,
                'sort_order' => 6,
                'chapters' => [],
            ],
        ];

        foreach ($booksData as $bData) {
            $chapters = $bData['chapters'] ?? [];
            unset($bData['chapters']);

            $book = HadithBook::updateOrCreate(['slug' => $bData['slug']], $bData);

            foreach ($chapters as $chData) {
                $hadiths = $chData['hadiths'] ?? [];
                unset($chData['hadiths']);
                $chData['hadith_book_id'] = $book->id;

                $chapter = HadithChapter::updateOrCreate([
                    'hadith_book_id' => $book->id,
                    'chapter_number' => $chData['chapter_number'],
                ], $chData);

                foreach ($hadiths as $hData) {
                    $hData['hadith_book_id'] = $book->id;
                    $hData['hadith_chapter_id'] = $chapter->id;

                    Hadith::updateOrCreate([
                        'hadith_book_id' => $book->id,
                        'hadith_number' => $hData['hadith_number'],
                    ], $hData);
                }
            }
        }

        /* -------------------------------------------------------------
         * 2. Promos & Coupons
         * ------------------------------------------------------------- */
        $coupons = [
            [
                'code' => 'TAALLUM10',
                'discount_type' => 'percent',
                'discount_amount' => 10.00,
                'min_order_amount' => 500.00,
                'status' => 'active',
            ],
            [
                'code' => 'RAMADAN50',
                'discount_type' => 'percent',
                'discount_amount' => 50.00,
                'min_order_amount' => 1000.00,
                'status' => 'active',
            ],
            [
                'code' => 'TALIB500',
                'discount_type' => 'fixed',
                'discount_amount' => 500.00,
                'min_order_amount' => 1500.00,
                'status' => 'active',
            ],
            [
                'code' => 'FREEILM',
                'discount_type' => 'percent',
                'discount_amount' => 100.00,
                'min_order_amount' => 0.00,
                'usage_limit' => 50,
                'status' => 'active',
            ],
        ];

        foreach ($coupons as $c) {
            Coupon::updateOrCreate(['code' => $c['code']], $c);
        }

        /* -------------------------------------------------------------
         * 3. Sample Fatawa with References & Scholar Assignment
         * ------------------------------------------------------------- */
        $fatwaCategory = Category::ofType('fatwa')->first();
        $sampleCourse = Course::first();

        if ($fatwaCategory) {
            $sampleFatawa = [
                [
                    'question_title' => 'সফর অবস্থায় চার রাকাত বিশিষ্ট ফরজ নামাজ কসর করার শরয়ী বিধান ও দূরত্ব কতটুকু?',
                    'question_body' => 'মুহতারাম মুফতী সাহেব, আমি কর্মস্থল থেকে বাড়ি যাওয়ার সময় প্রায় ৬০ কিলোমিটার পথ অতিক্রম করি। এই দূরত্বে কি আমি চার রাকাত ফরজ নামাজ দুই রাকাত কসর করতে পারব? কতটুকু দূরত্বের সফর হলে শরিয়তে কসর ওয়াজিব হয়?',
                    'answer_body' => "বিসমিল্লাহির রাহমানির রাহীম।\nআলহামদুলিল্লাহ, ওয়াসসালাতু ওয়াসসালামু আলা রাসুলিল্লাহ।\n\nশরীয়তের পরিভাষায় মুসাফির হওয়ার জন্য কোনো ব্যক্তি নিজ এলাকার লোকালয় অতিক্রম করে এমন দূরত্বের উদ্দেশ্যে রওনা হতে হবে যা তিন দিনের সাধারণ পদব্রজের পথ—বর্তমান হিসেবে যা কমপক্ষে প্রায় ৪৮ মাইল বা প্রায় ৭৭-৭৮ কিলোমিটার।\n\nঅতএব, ৬০ কিলোমিটার পথ অতিক্রম করলে ব্যক্তি শরয়ী মুসাফির হিসেবে গণ্য হবেন না। এই দূরত্বে আপনাকে চার রাকাত বিশিষ্ট ফরজ নামাজ চার রাকাতই পূর্ণ (ইতমাম) আদায় করতে হবে। পথ যদি ৪৮ মাইল বা ৭৭ কিলোমিটার বা তার বেশি হয় এবং গন্তব্যে ১৫ দিনের কম অবস্থানের নিয়ত থাকে, তবেই চার রাকাত বিশিষ্ট নামাজ দুই রাকাত কসর পড়া ওয়াজিব হবে।\n\nআল্লাহু আ'লামু বিস-সওয়াব।",
                    'references' => "[১] ফাতাওয়ায়ে শামী (রদ্দুল মুহতার): খণ্ড ২, পৃষ্ঠা ১২১-১২২ (মাকতাবায়ে ইমদাদিয়া)\n[২] আল-হিদায়া শরহে বিদায়াতুল মুবতাদী: কিতাবুস সালাত, বাবে সালাতিল মুসাফির, খণ্ড ১, পৃষ্ঠা ৮০\n[৩] বাদায়েউস সানায়ে: খণ্ড ১, পৃষ্ঠা ৯৩\n[৪] সহীহ মুসলিম: হাদীস নং ৬৮৫",
                    'status' => 'published',
                    'is_private' => false,
                    'category_id' => $fatwaCategory->id,
                    'teacher_id' => $firstTeacher?->id,
                    'assigned_scholar_id' => $firstTeacher?->id,
                    'related_course_id' => $sampleCourse?->id,
                    'published_at' => now()->subDays(3),
                    'answered_at' => now()->subDays(3),
                    'views_count' => 142,
                ],
                [
                    'question_title' => 'অনলাইনে ডিজিটাল পণ্য বা সফটওয়্যারের লাইসেন্স বেচাকেনা শরীয়তসম্মত কি না?',
                    'question_body' => 'বর্তমান যুগে সফটওয়্যার, ই-বুক বা ডিজিটাল কোর্স ইত্যাদি কপিরাইটযুক্ত পণ্যের লাইসেন্স বিক্রি করা হয়। দৃশ্যমান কোনো বস্তুগত পণ্য না থাকা সত্ত্বেও কি এ ধরনের ডিজিটাল সম্পদের মালিকানা বিক্রয় ও এর থেকে অর্জিত আয় হালাল হবে?',
                    'answer_body' => "বিসমিল্লাহির রাহমানির রাহীম।\n\nআধুনিক ফিকহ একাডেমি (যেমন: ইসলামী ফিকহ একাডেমি জেদ্দা) ও শীর্ষস্থানীয় ইসলামিক স্কলারগণের ঐক্যমত অনুযায়ী, মেধা সম্পদ (Intellectual Property), সফটওয়্যার লাইসেন্স বা ডিজিটাল কনটেন্টের শরয়ী আর্থিক মূল্য (মালে মুতাকাওউয়িম) স্বীকৃত।\n\nযেহেতু এতে মেধা ও শ্রমের বিনিময় রয়েছে এবং এর দ্বারা বৈধ সেবা গ্রহণ সম্ভব, তাই শর্তসাপেক্ষে ডিজিটাল পণ্যের লাইসেন্স ক্রয়-বিক্রয় এবং তা থেকে অর্জিত মুনাফা গ্রহণ করা সম্পূর্ণ বৈধ ও হালাল। তবে পণ্যটি ইসলামী শরিয়তে হারাম কোনো কাজের মাধ্যম হওয়া যাবে না।\n\nওয়াল্লাহু সুবহানাহু ওয়া তা'আলা আ'লাম।",
                    'references' => "[১] কারারাতুল মাজমাউল ফিকহিল ইসলামী (জেদ্দা): প্রস্তাবনা নং ৪৩ (৫/৫)\n[২] ফিকহুল বুয়ূ: মুফতী তাকী উসমানী, খণ্ড ১, পৃষ্ঠা ২৭৫\n[৩] বুহুস ফী কাযায়া ফিকহিয়্যাহ মুআসিরাহ: পৃষ্ঠা ১১৮",
                    'status' => 'published',
                    'is_private' => false,
                    'category_id' => $fatwaCategory->id,
                    'teacher_id' => $firstTeacher?->id,
                    'assigned_scholar_id' => $firstTeacher?->id,
                    'related_course_id' => $sampleCourse?->id,
                    'published_at' => now()->subDays(1),
                    'answered_at' => now()->subDays(1),
                    'views_count' => 98,
                ],
            ];

            foreach ($sampleFatawa as $f) {
                Fatwa::firstOrCreate(['question_title' => $f['question_title']], $f);
            }
        }

        /* -------------------------------------------------------------
         * 4. Sample Community Forum Posts
         * ------------------------------------------------------------- */
        if ($admin) {
            $samplePosts = [
                [
                    'title' => 'কুরআন হিফয ও তিলাওয়াত দীর্ঘমেয়াদে স্মরণে রাখার সর্বোত্তম কার্যপদ্ধতি কী?',
                    'topic' => 'quran-hadith',
                    'body' => 'মুহতারাম ভাই ও বোনেরা, কুরআন মুখস্থ করার পর তা যাতে বিস্মৃত না হয় সেজন্য প্রতিদিনের তিলাওয়াত ও পুনরাবৃত্তি (দাওর) কীভাবে সংগঠিত করা সবচেয়ে ফলপ্রসূ? বিজ্ঞ হাফেজ ও উস্তাযগণের অভিজ্ঞতা ও সুন্নাহসম্মত পরামর্শ জানতে চাচ্ছি।',
                    'user_id' => $admin->id,
                    'course_id' => $sampleCourse?->id,
                    'is_pinned' => true,
                    'is_solved' => true,
                    'upvotes_count' => 15,
                    'views_count' => 210,
                    'status' => 'published',
                    'comments' => [
                        [
                            'user_id' => $firstTeacher ? $firstTeacher->user_id : $admin->id,
                            'body' => "মাশাআল্লাহ, অত্যন্ত গুরুত্বপূর্ণ জিজ্ঞাসা। হিফয ধরে রাখার জন্য সবচেয়ে কার্যকর পদ্ধতি হলো: দৈনিক তাহাজ্জুদের সালাতে এবং নফল নামাজে নতুন ও পুরানো সবক তিলাওয়াত করা। রাসুলুল্লাহ (ﷺ) ইরশাদ করেছেন: 'কুরআনের সাথে সম্পর্ক রাখো, কারণ উট তার রশি থেকে যেভাবে দ্রুত ছুটে যায়, মানুষের অন্তর থেকে কুরআন তার চেয়েও দ্রুত বিলীন হয়।' [সহীহ বুখারী]",
                        ],
                        [
                            'user_id' => $admin->id,
                            'body' => 'জাযাকাল্লাহু খাইরান উস্তায! আলহামদুলিল্লাহ, তাহাজ্জুদে তিলাওয়াতের অভ্যাস মুখস্থ ধরে রাখতে চমৎকার ভূমিকা রাখে।',
                        ],
                    ],
                ],
                [
                    'title' => 'সহজ পদ্ধতিতে আরবি ব্যাকরণ (নাহব ও সরফ) আয়ত্ত করার কার্যকরী রোডম্যাপ',
                    'topic' => 'arabic-lang',
                    'body' => 'যারা বাংলা মাধ্যমে পড়াশোনা করেছেন কিন্তু সরাসরি আরবি কিতাব ও কুরআনের ভাষা বুঝতে চান, তাদের জন্য কোন কিতাবগুলো ক্রমানুসারে অধ্যয়ন করা উপকারী হবে? এ ব্যাপারে অভিজ্ঞ আলেমগণের দিকনির্দেশনা প্রত্যাশা করছি।',
                    'user_id' => $admin->id,
                    'is_pinned' => false,
                    'is_solved' => false,
                    'upvotes_count' => 8,
                    'views_count' => 125,
                    'status' => 'published',
                    'comments' => [],
                ],
            ];

            foreach ($samplePosts as $p) {
                $comments = $p['comments'] ?? [];
                unset($p['comments']);

                $post = ForumPost::firstOrCreate(['title' => $p['title']], $p);

                foreach ($comments as $c) {
                    $c['forum_post_id'] = $post->id;
                    ForumComment::firstOrCreate([
                        'forum_post_id' => $post->id,
                        'user_id' => $c['user_id'],
                        'body' => $c['body'],
                    ], $c);
                }
            }
        }
    }
}
