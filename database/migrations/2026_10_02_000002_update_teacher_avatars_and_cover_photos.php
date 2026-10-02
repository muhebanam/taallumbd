<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\Teacher;

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
            'shaykh-qari-salman-ahmad' => [
                'avatar' => '/images/teachers/qari_ubaidullah.jpg',
                'cover_photo' => '/images/covers/cover_quran.jpg',
            ],
            'maulana-mahmud-hasan' => [
                'avatar' => '/images/teachers/shaykh_ahmad.jpg',
                'cover_photo' => '/images/covers/cover_hadith.jpg',
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
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No down rollback required
    }
};
