<?php

use App\Models\Teacher;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $scholarsImages = [
            'mufti-abdullah-al-mahmud' => [
                'avatar' => '/images/teachers/mufti_abdullah.jpg',
                'cover_photo' => '/images/covers/cover_fiqh.jpg',
            ],
            'maulana-salman-ahmad' => [
                'avatar' => '/images/teachers/qari_ubaidullah.jpg',
                'cover_photo' => '/images/covers/cover_quran.jpg',
            ],
            'shaykh-qari-salman-ahmad' => [
                'avatar' => '/images/teachers/qari_ubaidullah.jpg',
                'cover_photo' => '/images/covers/cover_quran.jpg',
            ],
            'maulana-mahmud-hasan' => [
                'avatar' => '/images/teachers/shaykh_ahmad.jpg',
                'cover_photo' => '/images/covers/cover_hadith.jpg',
            ],
            'mufti-yahya-faruq' => [
                'avatar' => '/images/teachers/ustadh_zubayer.jpg',
                'cover_photo' => '/images/covers/cover_dawah.jpg',
            ],
            'dr-yahya-faruq' => [
                'avatar' => '/images/teachers/ustadh_zubayer.jpg',
                'cover_photo' => '/images/covers/cover_dawah.jpg',
            ],
            'ustadha-ayesha-binte-shafiq' => [
                'avatar' => '/images/teachers/ustadha_ayesha.jpg',
                'cover_photo' => '/images/covers/cover_women_fiqh.jpg',
            ],
            'aalima-fatema-akter-noor' => [
                'avatar' => '/images/teachers/ustadha_fatema.jpg',
                'cover_photo' => '/images/covers/cover_counseling.jpg',
            ],
            'ustadha-maryam-tasnim' => [
                'avatar' => '/images/teachers/ustadha_mariam.jpg',
                'cover_photo' => '/images/covers/cover_hafiza.jpg',
            ],
            'ustadha-umme-salma-khadija' => [
                'avatar' => '/images/teachers/ustadha_sumaiya.jpg',
                'cover_photo' => '/images/covers/cover_kids_quran.jpg',
            ],
        ];

        foreach ($scholarsImages as $slug => $images) {
            Teacher::where('slug', $slug)->update($images);
        }

        // Also update by email as a secondary guarantee
        $emailImages = [
            'abdullah@taallumbd.com' => ['avatar' => '/images/teachers/mufti_abdullah.jpg', 'cover_photo' => '/images/covers/cover_fiqh.jpg'],
            'salman@taallumbd.com' => ['avatar' => '/images/teachers/qari_ubaidullah.jpg', 'cover_photo' => '/images/covers/cover_quran.jpg'],
            'mahmud@taallumbd.com' => ['avatar' => '/images/teachers/shaykh_ahmad.jpg', 'cover_photo' => '/images/covers/cover_hadith.jpg'],
            'yahya@taallumbd.com' => ['avatar' => '/images/teachers/ustadh_zubayer.jpg', 'cover_photo' => '/images/covers/cover_dawah.jpg'],
            'ayesha@taallumbd.com' => ['avatar' => '/images/teachers/ustadha_ayesha.jpg', 'cover_photo' => '/images/covers/cover_women_fiqh.jpg'],
            'fatema@taallumbd.com' => ['avatar' => '/images/teachers/ustadha_fatema.jpg', 'cover_photo' => '/images/covers/cover_counseling.jpg'],
            'maryam@taallumbd.com' => ['avatar' => '/images/teachers/ustadha_mariam.jpg', 'cover_photo' => '/images/covers/cover_hafiza.jpg'],
            'ummesalma@taallumbd.com' => ['avatar' => '/images/teachers/ustadha_sumaiya.jpg', 'cover_photo' => '/images/covers/cover_kids_quran.jpg'],
        ];

        foreach ($emailImages as $email => $images) {
            Teacher::where('email', $email)->update($images);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {}
};
