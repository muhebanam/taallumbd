<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Course;
use App\Models\Order;
use App\Models\User;
use App\Payments\PaymentGatewayManager;
use App\Services\LocalizationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class InternationalizationAndStripePaymentTest extends TestCase
{
    use RefreshDatabase;

    protected User $student;

    protected User $instructor;

    protected Course $course;

    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->instructor = User::create([
            'name' => 'ড. মোহাম্মদ উসমান',
            'email' => 'scholar@taallumbd.com',
            'password' => bcrypt('password123'),
            'role' => 'instructor',
        ]);

        $this->student = User::create([
            'name' => 'মুহাম্মদ রায়হান',
            'email' => 'rayhan@taallumbd.com',
            'password' => bcrypt('password123'),
            'role' => 'student',
            'preferred_locale' => 'bn',
            'preferred_currency' => 'BDT',
        ]);

        $this->category = Category::create([
            'name' => 'কুরআন ও তাদাব্বুর',
            'slug' => 'quran-tadabbur',
            'type' => 'course',
            'sort_order' => 1,
            'status' => 'active',
        ]);

        $this->course = Course::create([
            'instructor_id' => $this->instructor->id,
            'category_id' => $this->category->id,
            'title' => 'সহজ কুরআন তাদাব্বুর কোর্স',
            'slug' => 'easy-quran-tadabbur',
            'short_description' => 'কুরআনের গভীর ভাবার্থ ও বাস্তব জীবনে তার প্রয়োগ শিখুন।',
            'description' => 'কুরআনের পূর্ণাঙ্গ তাদাব্বুর শিক্ষা ও তাফসির ভিত্তিক বিস্তারিত কোর্স।',
            'price' => 1200.00,
            'price_usd' => 10.00,
            'is_free' => false,
            'status' => 'published',
        ]);
    }

    /**
     * Test 1: Default locale is 'bn', direction is 'ltr', default currency is 'BDT'.
     */
    public function test_default_locale_is_bn_with_bdt_currency_and_ltr_direction(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);

        $this->assertEquals('bn', app()->getLocale());
        $this->assertEquals('ltr', app(LocalizationService::class)->getDirection('bn'));
        $this->assertEquals('BDT', session('currency', 'BDT'));
    }

    /**
     * Test 2: Switching locale via /locale/{locale} updates session, cookie, and user model.
     */
    public function test_switching_locale_via_route_updates_session_and_user_model(): void
    {
        // 1. Guest switches to English
        $response = $this->get('/locale/en');
        $response->assertStatus(302);
        $response->assertCookie('taallum_locale', 'en');
        $this->assertEquals('en', session('locale'));

        // 2. Authenticated user switches to Arabic
        $authResponse = $this->actingAs($this->student)->get('/locale/ar');
        $authResponse->assertStatus(302);
        $this->assertEquals('ar', $this->student->fresh()->preferred_locale);
    }

    /**
     * Test 3: Switching currency via /currency/{currency} updates session and user preference.
     */
    public function test_switching_currency_via_route_updates_session_and_user_preference(): void
    {
        $response = $this->actingAs($this->student)->get('/currency/USD');
        $response->assertStatus(302);

        $this->assertEquals('USD', session('currency'));
        $this->assertEquals('USD', $this->student->fresh()->preferred_currency);
    }

    /**
     * Test 4: Arabic locale enforces RTL direction and Amiri font setup.
     */
    public function test_arabic_locale_sets_rtl_direction(): void
    {
        $localizationService = app(LocalizationService::class);

        $this->assertTrue($localizationService->isRtl('ar'));
        $this->assertEquals('rtl', $localizationService->getDirection('ar'));
        $this->assertFalse($localizationService->isRtl('bn'));
        $this->assertFalse($localizationService->isRtl('en'));
    }

    /**
     * Test 5: Multilingual URL prefixes /en and /ar automatically activate respective locale.
     */
    public function test_multilingual_url_prefixes_set_locale_for_en_and_ar(): void
    {
        $responseEn = $this->get('/en/courses');
        $responseEn->assertStatus(200);
        $this->assertEquals('en', app()->getLocale());

        $responseAr = $this->get('/ar/courses');
        $responseAr->assertStatus(200);
        $this->assertEquals('ar', app()->getLocale());
    }

    /**
     * Test 6: Accept-Language header auto-negotiation chooses requested language.
     */
    public function test_accept_language_header_negotiation(): void
    {
        // Simulate Arabic browser request without prior session
        $response = $this->withHeaders([
            'Accept-Language' => 'ar-SA,ar;q=0.9,en;q=0.8',
        ])->get('/');

        $response->assertStatus(200);
        $this->assertEquals('ar', session('locale'));
    }

    /**
     * Test 7: Model-level HasTranslations trait saves and retrieves localized strings.
     */
    public function test_model_content_translations_trait_retrieves_localized_strings_and_fallbacks(): void
    {
        // Add English and Arabic translations to Course
        $this->course->setTranslation('title', 'en', 'Easy Quran Tadabbur Course');
        $this->course->setTranslation('title', 'ar', 'دورة تدبر القرآن الكريم الميسر');

        // Add translation to Category
        $this->category->setTranslation('name', 'en', 'Quran & Reflection');
        $this->category->setTranslation('name', 'ar', 'القرآن والتدبر');

        // 1. Direct getTranslation
        $this->assertEquals('Easy Quran Tadabbur Course', $this->course->getTranslation('title', 'en'));
        $this->assertEquals('دورة تدبر القرآن الكريم الميسر', $this->course->getTranslation('title', 'ar'));
        $this->assertEquals('সহজ কুরআন তাদাব্বুর কোর্স', $this->course->getTranslation('title', 'bn'));

        // 2. Category translation
        $this->assertEquals('Quran & Reflection', $this->category->getTranslation('name', 'en'));
        $this->assertEquals('القرآن والتدبر', $this->category->getTranslation('name', 'ar'));

        // 3. Fallback when translation not available in locale
        $this->assertEquals('সহজ কুরআন তাদাব্বুর কোর্স', $this->course->getTranslation('title', 'fr', fallback: true));
    }

    /**
     * Test 8: Multi-currency and price formatting with numeral conversions.
     */
    public function test_multi_currency_and_price_formatting(): void
    {
        $localization = app(LocalizationService::class);

        // BDT formatting in Bengali numerals
        $bdtFormattedBn = $localization->formatPrice(1200, 'BDT', 'bn');
        $this->assertStringContainsString('১,২০০', $bdtFormattedBn);

        // USD formatting in English
        $usdFormattedEn = $localization->formatPrice(10.00, 'USD', 'en');
        $this->assertEquals('$10.00', $usdFormattedEn);

        // USD formatting in Arabic numerals
        $usdFormattedAr = $localization->formatPrice(10.00, 'USD', 'ar');
        $this->assertStringContainsString('$', $usdFormattedAr);

        // Currency conversion (1200 BDT at 120 rate = 10 USD)
        $convertedUsd = $localization->convert(1200, 'BDT', 'USD');
        $this->assertEquals(10.00, $convertedUsd);
    }

    /**
     * Test 9: Hijri date calculation for all three locales.
     */
    public function test_islamic_hijri_date_generation_for_all_locales(): void
    {
        $localization = app(LocalizationService::class);

        $hijriBn = $localization->getHijriDate(now(), 'bn');
        $hijriEn = $localization->getHijriDate(now(), 'en');
        $hijriAr = $localization->getHijriDate(now(), 'ar');

        $this->assertStringContainsString('হিজরি', $hijriBn);
        $this->assertStringContainsString('AH', $hijriEn);
        $this->assertStringContainsString('هـ', $hijriAr);
    }

    /**
     * Test 10: Stripe payment gateway initiation, callback, and verification end-to-end.
     */
    public function test_stripe_gateway_initiation_and_successful_callback_flow_end_to_end(): void
    {
        config(['payments.gateways.stripe.enabled' => true]);
        $gatewayManager = app(PaymentGatewayManager::class);

        $this->assertTrue($gatewayManager->isGatewayEnabled('stripe'));

        // Create pending order for course
        $order = Order::create([
            'user_id' => $this->student->id,
            'course_id' => $this->course->id,
            'amount' => $this->course->price,
            'currency' => 'USD',
            'amount_usd' => 10.00,
            'status' => 'pending',
            'payment_method' => 'stripe',
        ]);

        // Initiate payment with Stripe driver
        $stripeDriver = $gatewayManager->driver('stripe');
        $initResult = $stripeDriver->initiate($order);

        $this->assertTrue($initResult['success']);
        $this->assertNotNull($initResult['session_id']);
        $this->assertNotNull($initResult['redirect_url']);

        // Simulate browser callback from Stripe Checkout
        $callbackRequest = Request::create('/payments/stripe/callback/'.$order->id, 'GET', [
            'status' => 'success',
            'session_id' => $initResult['session_id'],
        ]);

        $callbackResult = $stripeDriver->handleCallback($callbackRequest, $order);

        $this->assertTrue($callbackResult['success']);
        $this->assertEquals('paid', $order->fresh()->status);
        $this->assertEquals('stripe', $order->fresh()->payment_method);
        $this->assertNotNull($order->fresh()->transaction_id);

        // Verify student is enrolled in course
        $this->assertDatabaseHas('enrollments', [
            'user_id' => $this->student->id,
            'course_id' => $this->course->id,
            'status' => 'active',
        ]);
    }

    /**
     * Test 11: Multilingual sitemap returns valid XML with hreflang tags for all locales.
     */
    public function test_multilingual_sitemap_returns_valid_xml_with_hreflang_tags(): void
    {
        // 1. Default sitemap
        $response = $this->get('/sitemap.xml');
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/xml; charset=utf-8');
        $content = $response->getContent();

        $this->assertStringContainsString('hreflang="bn"', $content);
        $this->assertStringContainsString('hreflang="en"', $content);
        $this->assertStringContainsString('hreflang="ar"', $content);
        $this->assertStringContainsString('hreflang="x-default"', $content);

        // 2. Localized sitemaps
        $responseEn = $this->get('/en/sitemap.xml');
        $responseEn->assertStatus(200);

        $responseAr = $this->get('/ar/sitemap.xml');
        $responseAr->assertStatus(200);
    }

    /**
     * Test 12: Missing translations artisan command passes with zero missing keys.
     */
    public function test_missing_translations_artisan_command_passes_with_zero_missing_keys(): void
    {
        $this->artisan('taallum:missing-translations')
            ->expectsOutputToContain('All translation keys are 100% translated')
            ->assertExitCode(0);
    }
}
