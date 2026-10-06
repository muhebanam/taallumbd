import http from 'k6/http';
import { check, group, sleep } from 'k6';
import { Trend, Rate } from 'k6/metrics';

// Custom Metrics
const homeTrend = new Trend('response_time_home');
const coursesTrend = new Trend('response_time_courses');
const lessonTrend = new Trend('response_time_lesson');
const quranTrend = new Trend('response_time_quran');
const searchTrend = new Trend('response_time_search');
const loginTrend = new Trend('response_time_api_login');
const errorRate = new Rate('errors');

export const options = {
    stages: [
        { duration: '20s', target: 50 },   // Warm-up ramp to 50 concurrent users
        { duration: '40s', target: 100 },  // Scale to 100 users
        { duration: '1m',  target: 200 },  // Peak load at 200 concurrent users
        { duration: '30s', target: 200 },  // Sustain 200 users
        { duration: '20s', target: 0 },    // Cool down
    ],
    thresholds: {
        http_req_duration: ['p(95)<500'], // 95% of requests must complete below 500ms
        http_req_failed: ['rate<0.01'],   // Less than 1% errors
        'response_time_home': ['p(95)<400'],
        'response_time_courses': ['p(95)<450'],
        'response_time_quran': ['p(95)<350'],
        'response_time_search': ['p(95)<500'],
        'response_time_api_login': ['p(95)<500'],
    },
};

const BASE_URL = __ENV.BASE_URL || 'http://127.0.0.1:8000';

export default function () {
    const params = {
        headers: {
            'Accept': 'application/json, text/html',
            'User-Agent': 'k6-load-test/TaallumBD',
        },
    };

    // 1. Home Page
    group('01_Home_Page', () => {
        const res = http.get(`${BASE_URL}/`, params);
        const success = check(res, {
            'home status is 200': (r) => r.status === 200,
        });
        homeTrend.add(res.timings.duration);
        errorRate.add(!success);
    });

    sleep(1);

    // 2. Course Catalog & Filter
    group('02_Course_List', () => {
        const res = http.get(`${BASE_URL}/courses`, params);
        const success = check(res, {
            'courses list status is 200': (r) => r.status === 200,
        });
        coursesTrend.add(res.timings.duration);
        errorRate.add(!success);
    });

    sleep(1);

    // 3. Lesson Detail / Course Show
    group('03_Lesson_Or_Course_Show', () => {
        const res = http.get(`${BASE_URL}/api/v1/courses`, params);
        const success = check(res, {
            'api courses status is 200': (r) => r.status === 200,
        });
        lessonTrend.add(res.timings.duration);
        errorRate.add(!success);
    });

    sleep(1);

    // 4. Quran Surah List & Reading
    group('04_Quran_Reading', () => {
        const resList = http.get(`${BASE_URL}/api/v1/quran/surahs`, params);
        const successList = check(resList, {
            'quran surahs status is 200': (r) => r.status === 200,
        });

        const resSurah = http.get(`${BASE_URL}/api/v1/quran/surahs/1`, params);
        const successSurah = check(resSurah, {
            'surah al-fatihah status is 200': (r) => r.status === 200,
        });

        quranTrend.add(resSurah.timings.duration);
        errorRate.add(!successList || !successSurah);
    });

    sleep(1);

    // 5. Unified Search
    group('05_Unified_Search', () => {
        const res = http.get(`${BASE_URL}/api/v1/search?q=quran`, params);
        const success = check(res, {
            'search status is 200': (r) => r.status === 200,
        });
        searchTrend.add(res.timings.duration);
        errorRate.add(!success);
    });

    sleep(1);

    // 6. API Authentication Login
    group('06_API_Login', () => {
        const loginPayload = JSON.stringify({
            email: 'student@taallumbd.com',
            password: 'password',
        });
        const loginParams = {
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
        };

        const res = http.post(`${BASE_URL}/api/v1/auth/login`, loginPayload, loginParams);
        // Either 200 with token, or 422/401 invalid creds under benchmark
        const success = check(res, {
            'login response status is valid': (r) => r.status === 200 || r.status === 422 || r.status === 401,
        });
        loginTrend.add(res.timings.duration);
        errorRate.add(!success);
    });

    sleep(2);
}
