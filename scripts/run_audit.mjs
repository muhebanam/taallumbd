import lighthouse from 'lighthouse';
import * as chromeLauncher from 'chrome-launcher';
import fs from 'fs';

const pages = [
  { name: 'Home Page', url: 'http://127.0.0.1:8000/' },
  { name: 'Courses Page', url: 'http://127.0.0.1:8000/courses' },
  { name: 'Course Detail', url: 'http://127.0.0.1:8000/courses/tajweedul-quran-masterclass' },
  { name: 'Quran Index', url: 'http://127.0.0.1:8000/quran' },
];

async function audit() {
  console.log('Starting Mobile Lighthouse Audit...');
  const chrome = await chromeLauncher.launch({
    chromeFlags: [
      '--headless=new',
      '--disable-gpu',
      '--no-sandbox',
      '--disable-dev-shm-usage',
      '--disable-background-timer-throttling',
      '--disable-backgrounding-occluded-windows',
      '--disable-renderer-backgrounding',
      '--window-size=412,823',
    ],
  });

  const options = {
    logLevel: 'error',
    output: 'json',
    onlyCategories: ['performance'],
    port: chrome.port,
    formFactor: 'mobile',
    screenEmulation: {
      mobile: true,
      width: 412,
      height: 823,
      deviceScaleFactor: 2.625,
      disabled: false,
    },
    throttling: {
      rttMs: 40,
      throughputKbps: 10 * 1024,
      cpuSlowdownMultiplier: 1,
    },
  };

  const results = [];

  for (const page of pages) {
    try {
      console.log(`Auditing ${page.name} (${page.url})...`);
      const runnerResult = await lighthouse(page.url, options);
      const score = Math.round(runnerResult.lhr.categories.performance.score * 100);
      const audits = runnerResult.lhr.audits;
      const fcp = audits['first-contentful-paint']?.displayValue || 'N/A';
      const lcp = audits['largest-contentful-paint']?.displayValue || 'N/A';
      const tbt = audits['total-blocking-time']?.displayValue || 'N/A';
      const cls = audits['cumulative-layout-shift']?.displayValue || 'N/A';

      results.push({
        page: page.name,
        url: page.url,
        score,
        fcp,
        lcp,
        tbt,
        cls,
      });
      console.log(`✓ ${page.name}: Score = ${score}, FCP = ${fcp}, LCP = ${lcp}, TBT = ${tbt}, CLS = ${cls}`);
    } catch (err) {
      console.error(`✗ Error auditing ${page.name}:`, err.message);
      results.push({
        page: page.name,
        url: page.url,
        score: 'N/A',
        error: err.message,
      });
    }
  }

  await chrome.kill();

  fs.writeFileSync('storage/lighthouse-summary.json', JSON.stringify(results, null, 2));
  console.log('Saved Lighthouse report to storage/lighthouse-summary.json');
}

audit().catch(console.error);
