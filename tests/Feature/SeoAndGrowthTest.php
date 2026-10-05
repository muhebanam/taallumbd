<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Course;
use App\Models\Fatwa;
use App\Models\NewsletterSubscriber;
use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoAndGrowthTest extends TestCase
{
    use RefreshDatabase;

    public function test_sitemap_xml_returns_valid_xml_with_all_entities(): void
    {
        Course::factory()->published()->create(['slug' => 'quranic-arabic-basics']);
        Article::factory()->published()->create(['slug' => 'importance-of-tajweed']);
        Fatwa::factory()->published()->create(['id' => 101]);
        Page::create([
            'title' => 'ব্যবহারের শর্তাবলি',
            'slug' => 'terms',
            'body' => 'শর্তাবলি...',
            'is_published' => true,
        ]);

        $response = $this->get('/sitemap.xml');

        $response->assertStatus(200);
        $this->assertStringContainsString('xml', $response->headers->get('Content-Type'));

        $content = $response->getContent();
        $xml = simplexml_load_string($content);
        $this->assertNotFalse($xml, 'Sitemap should be valid parseable XML');

        $this->assertStringContainsString('/courses/quranic-arabic-basics', $content);
        $this->assertStringContainsString('/articles/importance-of-tajweed', $content);
        $this->assertStringContainsString('/fatawa/101', $content);
        $this->assertStringContainsString('/terms', $content);
        $this->assertStringContainsString('/quran', $content);
        $this->assertStringContainsString('/hadith', $content);
    }

    public function test_robots_txt_returns_valid_directives(): void
    {
        $response = $this->get('/robots.txt');

        $response->assertStatus(200);
        $content = $response->getContent();
        $this->assertStringContainsString('User-agent: *', $content);
        $this->assertStringContainsString('Disallow: /admin', $content);
        $this->assertStringContainsString('Disallow: /dashboard', $content);
        $this->assertStringContainsString('Sitemap:', $content);
    }

    public function test_utm_parameters_captured_in_session_and_persisted_on_registration(): void
    {
        // 1. Visit with UTM params
        $this->get('/courses?utm_source=facebook&utm_medium=cpc&utm_campaign=ramadan_2026&utm_term=quran&utm_content=ad_v1')
            ->assertStatus(200)
            ->assertSessionHas('utm_attribution', [
                'utm_source' => 'facebook',
                'utm_medium' => 'cpc',
                'utm_campaign' => 'ramadan_2026',
                'utm_term' => 'quran',
                'utm_content' => 'ad_v1',
            ]);

        // 2. Register user
        $referrer = User::factory()->create([
            'referral_code' => 'TAALLUM_REF_100',
        ]);

        $response = $this->post('/register', [
            'name' => 'আব্দুর রহমান',
            'email' => 'abdur.rahman@example.com',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
            'ref' => 'TAALLUM_REF_100',
        ]);

        $response->assertRedirect('/dashboard');

        $user = User::where('email', 'abdur.rahman@example.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('facebook', $user->utm_source);
        $this->assertEquals('ramadan_2026', $user->utm_campaign);
        $this->assertEquals($referrer->id, $user->referred_by_id);
        $this->assertNotEmpty($user->referral_code);

        // Assert referral record exists
        $this->assertDatabaseHas('referrals', [
            'referrer_id' => $referrer->id,
            'referred_id' => $user->id,
            'code' => 'TAALLUM_REF_100',
            'reward_status' => 'pending',
        ]);
    }

    public function test_newsletter_double_opt_in_subscription_flow(): void
    {
        // 1. Subscribe
        $response = $this->post('/newsletter/subscribe', [
            'email' => 'subscriber@example.com',
            'locale' => 'bn',
        ]);

        $response->assertStatus(302);
        $subscriber = NewsletterSubscriber::where('email', 'subscriber@example.com')->first();
        $this->assertNotNull($subscriber);
        $this->assertEquals('pending', $subscriber->status);
        $this->assertNotNull($subscriber->token);
        $this->assertNull($subscriber->verified_at);

        // 2. Verify token
        $verifyResponse = $this->get("/newsletter/verify/{$subscriber->token}");
        $verifyResponse->assertStatus(200);

        $subscriber->refresh();
        $this->assertEquals('active', $subscriber->status);
        $this->assertNotNull($subscriber->verified_at);

        // 3. Unsubscribe
        $unsubResponse = $this->get("/newsletter/unsubscribe/{$subscriber->token}");
        $unsubResponse->assertStatus(200);

        $subscriber->refresh();
        $this->assertEquals('unsubscribed', $subscriber->status);
    }
}
