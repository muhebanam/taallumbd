<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        // ---- Course categories (mega menu, left column) ----
        $courseCats = [
            'আক্বাঈদ' => 'aqeedah', 'কুরআন' => 'quran', 'তাফসীর' => 'tafsir', 'সীরাত' => 'seerah',
            'হাদীস' => 'hadith', 'ফিকহ' => 'fiqh', 'আরবী ভাষা' => 'arabic-language', 'বিবিধ' => 'miscellaneous',
        ];
        $this->insert($courseCats, 'course');

        // ---- Article categories ----
        $articleCats = [
            'আক্বীদা' => 'aqeedah', 'কুরআন' => 'quran', 'হাদীস' => 'hadith', 'ফিকহ' => 'fiqh',
            'সীরাত' => 'seerah', 'অর্থনীতি' => 'economics', 'হালাল-হারাম' => 'halal-haram',
            'ইতিহাস' => 'history', 'জীবনী' => 'biography', 'আরবী ভাষা' => 'arabic-language',
            'আত্মশুদ্ধি' => 'self-purification', 'সমসাময়িক' => 'contemporary', 'অন্যান্য' => 'others',
        ];
        $this->insert($articleCats, 'article');

        // ---- Fatwa categories (with nested ভ্রান্ত মতবাদ) ----
        $fatwaCats = [
            'আক্বীদা' => 'aqeedah', 'শিরক ও বিদআত' => 'shirk-bidah', 'গুনাহ/অপরাধ' => 'sins-crimes',
            'ইসলামী বিধিবিধান' => 'islamic-rulings', 'বিবাহ/শাদি/তালাক' => 'marriage-divorce',
            'মিরাস/উত্তরাধিকার' => 'inheritance', 'অর্থনীতি' => 'economics', 'হালাল-হারাম' => 'halal-haram',
            'সমকালীন প্রসঙ্গ' => 'contemporary-issues', 'ভ্রান্ত মতবাদ' => 'deviant-sects',
            'খেলাধুলা/বিনোদন' => 'sports-entertainment', 'ওয়াকফ' => 'waqf', 'কসম ও মানত' => 'oath-vow',
            'কাফন-দাফন' => 'funeral', 'অন্যান্য' => 'others',
        ];
        $this->insert($fatwaCats, 'fatwa');

        $deviant = Category::where('type', 'fatwa')->where('slug', 'deviant-sects')->first();
        $this->insert([
            'বিধর্মী মতবাদ' => 'non-muslim-ideologies', 'শিয়া মতবাদ' => 'shia', 'কাদিয়ানী মতবাদ' => 'qadiani',
            'আহলে কুরআন' => 'ahle-quran', 'হাদীস অস্বীকারকারী' => 'hadith-deniers',
            'জালিয়াতি নিরসন' => 'fraud-refutation', 'অন্যান্য' => 'deviant-others',
        ], 'fatwa', $deviant->id);

        // ---- Publication categories (with nested submenus) ----
        $this->insert(['অডিও / ভিডিও' => 'audio-video', 'বুক স্টোর' => 'book-store', 'দাওয়াহ পোস্টার' => 'dawah-poster'], 'publication');

        $av = Category::where('type', 'publication')->where('slug', 'audio-video')->first();
        $this->insert([
            'জুমার বয়ান' => 'jumma-bayan', 'মাসিক তাফসীর' => 'monthly-tafsir',
            'দ্বীনি আলোচনা' => 'islamic-discussion', 'ডকুমেন্টরি' => 'documentary',
        ], 'publication', $av->id);

        $books = Category::where('type', 'publication')->where('slug', 'book-store')->first();
        $this->insert(['প্রকাশিত বই' => 'printed-books', 'ই-বুক' => 'ebooks'], 'publication', $books->id);
    }

    private function insert(array $items, string $type, ?int $parentId = null): void
    {
        $order = 0;
        foreach ($items as $name => $slug) {
            Category::updateOrCreate(
                ['slug' => $slug, 'type' => $type],
                ['name' => $name, 'parent_id' => $parentId, 'sort_order' => ++$order, 'status' => 'active']
            );
        }
    }
}
