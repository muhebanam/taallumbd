<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Course;
use App\Models\Fatwa;
use App\Models\HadithBook;
use App\Models\Page;
use App\Models\Surah;
use App\Models\Teacher;
use Illuminate\Http\Response;

class SeoController extends Controller
{
    /**
     * Generate dynamic sitemap.xml with multilingual hreflang links.
     */
    public function sitemap(?string $locale = null): Response
    {
        $activeLocale = $locale ?: request()->query('locale', 'bn');
        $prefix = ($activeLocale === 'bn') ? '' : '/'.$activeLocale;

        $urls = collect();

        // 1. Static & Core Pages
        $staticPaths = [
            '/' => '1.0',
            '/courses' => '0.9',
            '/teachers' => '0.8',
            '/about/scholar-board' => '0.8',
            '/fatawa' => '0.9',
            '/articles' => '0.9',
            '/publications' => '0.8',
            '/quran' => '0.9',
            '/hadith' => '0.9',
            '/contact' => '0.5',
            '/about' => '0.6',
        ];

        foreach ($staticPaths as $path => $priority) {
            $urls->push([
                'path' => $path,
                'priority' => $priority,
                'changefreq' => in_array($path, ['/', '/courses', '/fatawa', '/articles']) ? 'daily' : 'weekly',
                'lastmod' => now()->toAtomString(),
            ]);
        }

        // 2. Policy Pages
        foreach (Page::published()->get() as $p) {
            $urls->push([
                'path' => '/'.$p->slug,
                'priority' => '0.4',
                'changefreq' => 'monthly',
                'lastmod' => $p->updated_at->toAtomString(),
            ]);
        }

        // 3. Courses
        foreach (Course::published()->get(['slug', 'updated_at']) as $c) {
            $urls->push([
                'path' => '/courses/'.$c->slug,
                'priority' => '0.9',
                'changefreq' => 'weekly',
                'lastmod' => $c->updated_at->toAtomString(),
            ]);
        }

        // 4. Scholars
        foreach (Teacher::active()->get(['slug', 'updated_at']) as $t) {
            $urls->push([
                'path' => '/scholars/'.$t->slug,
                'priority' => '0.8',
                'changefreq' => 'weekly',
                'lastmod' => $t->updated_at->toAtomString(),
            ]);
        }

        // 5. Articles
        foreach (Article::published()->get(['slug', 'updated_at']) as $a) {
            $urls->push([
                'path' => '/articles/'.$a->slug,
                'priority' => '0.8',
                'changefreq' => 'weekly',
                'lastmod' => $a->updated_at->toAtomString(),
            ]);
        }

        // 6. Fatawa
        foreach (Fatwa::published()->get(['id', 'updated_at']) as $f) {
            $urls->push([
                'path' => '/fatawa/'.$f->id,
                'priority' => '0.7',
                'changefreq' => 'monthly',
                'lastmod' => $f->updated_at->toAtomString(),
            ]);
        }

        // 7. Quran Surahs
        foreach (Surah::all(['id', 'updated_at']) as $s) {
            $urls->push([
                'path' => '/quran/'.$s->id,
                'priority' => '0.7',
                'changefreq' => 'monthly',
                'lastmod' => ($s->updated_at ?? now())->toAtomString(),
            ]);
        }

        // 8. Hadith Books
        foreach (HadithBook::all(['slug', 'updated_at']) as $b) {
            $urls->push([
                'path' => '/hadith/'.$b->slug,
                'priority' => '0.7',
                'changefreq' => 'monthly',
                'lastmod' => ($b->updated_at ?? now())->toAtomString(),
            ]);
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">'."\n";

        foreach ($urls as $item) {
            $path = $item['path'];
            $targetLoc = url($prefix.$path);
            $bnLoc = url($path);
            $enLoc = url('/en'.$path);
            $arLoc = url('/ar'.$path);

            $xml .= "  <url>\n";
            $xml .= '    <loc>'.htmlspecialchars($targetLoc, ENT_XML1, 'UTF-8')."</loc>\n";
            $xml .= '    <xhtml:link rel="alternate" hreflang="bn" href="'.htmlspecialchars($bnLoc, ENT_XML1, 'UTF-8').'" />'."\n";
            $xml .= '    <xhtml:link rel="alternate" hreflang="en" href="'.htmlspecialchars($enLoc, ENT_XML1, 'UTF-8').'" />'."\n";
            $xml .= '    <xhtml:link rel="alternate" hreflang="ar" href="'.htmlspecialchars($arLoc, ENT_XML1, 'UTF-8').'" />'."\n";
            $xml .= '    <xhtml:link rel="alternate" hreflang="x-default" href="'.htmlspecialchars($bnLoc, ENT_XML1, 'UTF-8').'" />'."\n";
            $xml .= '    <lastmod>'.$item['lastmod']."</lastmod>\n";
            $xml .= '    <changefreq>'.$item['changefreq']."</changefreq>\n";
            $xml .= '    <priority>'.$item['priority']."</priority>\n";
            $xml .= "  </url>\n";
        }

        $xml .= '</urlset>';

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=utf-8',
            'X-Robots-Tag' => 'noindex',
        ]);
    }

    /**
     * Generate robots.txt dynamically.
     */
    public function robots(): Response
    {
        $sitemapUrl = url('/sitemap.xml');

        $content = "User-agent: *\n";
        $content .= "Disallow: /admin/\n";
        $content .= "Disallow: /dashboard/\n";
        $content .= "Disallow: /checkout/\n";
        $content .= "Disallow: /internal/\n";
        $content .= "Disallow: /login\n";
        $content .= "Disallow: /register\n";
        $content .= "Allow: /\n\n";
        $content .= "Sitemap: {$sitemapUrl}\n";

        return response($content, 200, [
            'Content-Type' => 'text/plain; charset=utf-8',
        ]);
    }
}
