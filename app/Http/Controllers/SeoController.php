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
     * Generate dynamic sitemap.xml.
     */
    public function sitemap(): Response
    {
        $urls = collect();

        // 1. Static & Core Pages
        $staticRoutes = [
            ['loc' => url('/'), 'priority' => '1.0', 'changefreq' => 'daily', 'lastmod' => now()->toAtomString()],
            ['loc' => url('/courses'), 'priority' => '0.9', 'changefreq' => 'daily', 'lastmod' => now()->toAtomString()],
            ['loc' => url('/teachers'), 'priority' => '0.8', 'changefreq' => 'weekly', 'lastmod' => now()->toAtomString()],
            ['loc' => url('/about/scholar-board'), 'priority' => '0.8', 'changefreq' => 'weekly', 'lastmod' => now()->toAtomString()],
            ['loc' => url('/fatawa'), 'priority' => '0.9', 'changefreq' => 'daily', 'lastmod' => now()->toAtomString()],
            ['loc' => url('/articles'), 'priority' => '0.9', 'changefreq' => 'daily', 'lastmod' => now()->toAtomString()],
            ['loc' => url('/publications'), 'priority' => '0.8', 'changefreq' => 'weekly', 'lastmod' => now()->toAtomString()],
            ['loc' => url('/quran'), 'priority' => '0.9', 'changefreq' => 'monthly', 'lastmod' => now()->toAtomString()],
            ['loc' => url('/hadith'), 'priority' => '0.9', 'changefreq' => 'monthly', 'lastmod' => now()->toAtomString()],
            ['loc' => url('/contact'), 'priority' => '0.5', 'changefreq' => 'monthly', 'lastmod' => now()->toAtomString()],
            ['loc' => url('/about'), 'priority' => '0.6', 'changefreq' => 'monthly', 'lastmod' => now()->toAtomString()],
        ];

        foreach ($staticRoutes as $r) {
            $urls->push($r);
        }

        // 2. Policy Pages
        foreach (Page::published()->get() as $p) {
            $urls->push([
                'loc' => url('/'.$p->slug),
                'priority' => '0.4',
                'changefreq' => 'monthly',
                'lastmod' => $p->updated_at->toAtomString(),
            ]);
        }

        // 3. Courses
        foreach (Course::published()->get(['slug', 'updated_at']) as $c) {
            $urls->push([
                'loc' => url('/courses/'.$c->slug),
                'priority' => '0.9',
                'changefreq' => 'weekly',
                'lastmod' => $c->updated_at->toAtomString(),
            ]);
        }

        // 4. Scholars
        foreach (Teacher::active()->get(['slug', 'updated_at']) as $t) {
            $urls->push([
                'loc' => url('/scholars/'.$t->slug),
                'priority' => '0.8',
                'changefreq' => 'weekly',
                'lastmod' => $t->updated_at->toAtomString(),
            ]);
        }

        // 5. Articles
        foreach (Article::published()->get(['slug', 'updated_at']) as $a) {
            $urls->push([
                'loc' => url('/articles/'.$a->slug),
                'priority' => '0.8',
                'changefreq' => 'weekly',
                'lastmod' => $a->updated_at->toAtomString(),
            ]);
        }

        // 6. Fatawa
        foreach (Fatwa::published()->get(['id', 'updated_at']) as $f) {
            $urls->push([
                'loc' => url('/fatawa/'.$f->id),
                'priority' => '0.7',
                'changefreq' => 'monthly',
                'lastmod' => $f->updated_at->toAtomString(),
            ]);
        }

        // 7. Quran Surahs
        foreach (Surah::all(['id', 'updated_at']) as $s) {
            $urls->push([
                'loc' => url('/quran/'.$s->id),
                'priority' => '0.7',
                'changefreq' => 'monthly',
                'lastmod' => ($s->updated_at ?? now())->toAtomString(),
            ]);
        }

        // 8. Hadith Books
        foreach (HadithBook::all(['slug', 'updated_at']) as $b) {
            $urls->push([
                'loc' => url('/hadith/'.$b->slug),
                'priority' => '0.7',
                'changefreq' => 'monthly',
                'lastmod' => ($b->updated_at ?? now())->toAtomString(),
            ]);
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";

        foreach ($urls as $item) {
            $xml .= "  <url>\n";
            $xml .= '    <loc>'.htmlspecialchars($item['loc'], ENT_XML1, 'UTF-8')."</loc>\n";
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
