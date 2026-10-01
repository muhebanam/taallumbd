# আত-তাআল্লুম (TaallumBD) — মাস্টার ডেভেলপমেন্ট ও ডিপ্লয়মেন্ট গাইড

**আত-তাআল্লুম (taallumbd.com)** হলো একটি আধুনিক, পূর্ণাঙ্গ ডিজিটাল ইসলামী শিক্ষা ইকোসিস্টেম, স্কলার নেটওয়ার্ক ও উন্মুক্ত জ্ঞান প্ল্যাটফর্ম (LMS + Fatwa + Quran/Hadith + Community Forum + Multi-Gateway Payments)।

---

## 🛠️ প্রযুক্তি স্ট্যাক (Tech Stack)

- **ব্যাকএন্ড**: Laravel 11 (PHP 8.2+)
- **ফ্রন্টএন্ড**: React 18 + Inertia.js v1
- **স্টাইলিং**: Tailwind CSS + Custom Islamic Typography & Brand Palettes
- **ডেটাবেজ**: MySQL 8.0+ / MariaDB
- **ফন্টসমূহ**: Amiri (আরবি ক্যালিগ্রাফি), Hind Siliguri (বাংলা), Outfit (ইংরেজি)
- **পেমেন্ট গেটওয়ে**: বিকাশ (bKash), নগদ (Nagad), রকেট (Rocket), কার্ড (SSLCommerz) এবং ম্যানুয়াল TrxID ভেরিফিকেশন।

---

## 🎨 ব্র্যান্ড কালার ও ভিজ্যুয়াল সিস্টেম

- **Deep Evergreen (Primary)**: `#102526`
- **Forest Slate (Secondary)**: `#1A2E2F`
- **Islamic Gold (Accent)**: `#FFF99A`
- **Soft Off-White (Canvas)**: `#F8FAF8`

---

## 🚀 লোকাল সেটআপ নির্দেশিকা (Local Installation Guide)

### ১. ডিপেন্ডেন্সি ইনস্টল করুন
```bash
# PHP Dependencies
composer install

# Node.js Dependencies
npm install
```

### ২. এনভায়রনমেন্ট কনফিগারেশন (.env)
```bash
# .env ফাইল তৈরি করুন
cp .env.example .env

# অ্যাপ্লিকেশন কি (Key) তৈরি করুন
php artisan key:generate
```
`.env` ফাইলে আপনার লোকাল ডেটাবেজ ক্রেডেনশিয়াল (`DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`) সেট করুন।

### ৩. ডেটাবেজ মাইগ্রেশন ও সিডিং
```bash
# সম্পূর্ণ ডেটাবেজ মাইগ্রেশন রান করুন
php artisan migrate

# ক্যাটাগরি, কোর্স, উলামা, হাদীস, ফাতাওয়া ও ফোরাম ডেমো ডেটা সিড করুন
php artisan db:seed
```

### ৪. স্টোরেজ সিমলিংক তৈরি
```bash
php artisan storage:link
```

### ৫. লোকাল সার্ভার ও Vite ডেভেলপমেন্ট রান করুন
```bash
# টার্মিনাল ১ (Laravel Server):
php artisan serve

# টার্মিনাল ২ (Vite HMR Server):
npm run dev
```
ব্রাউজারে ভিজিট করুন: `http://localhost:8000`

---

## 🌐 প্রোডাকশন বিল্ড ও ডিপ্লয়মেন্ট (Production Deployment)

### ১. অ্যাসেট কম্পাইল
```bash
npm run build
```

### ২. অপ্টিমাইজেশন ও ক্যাশিং
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### ৩. ব্যাকগ্রাউন্ড কিউ (Queue Worker) চালু রাখা
সার্টিফিকেট জেনারেশন ও ইমেইল নোটিফিকেশনের জন্য সুপারভাইজর (Supervisor) বা সিস্টেমড (Systemd) সার্ভিসে কিউ ওয়ার্কার চালান:
```bash
php artisan queue:work --tries=3
```

---

## 📚 প্ল্যাটফর্মের প্রধান ফিচারসমূহ ও ইউআরএল ম্যাপ (Feature & Route Sitemap)

| ফিচার | ইউআরএল | বিবরণ |
|---|---|---|
| **হোমপেজ** | `/` | হিরো সেকশন, ট্র্যাক, ফিচার্ড কোর্স, উলামা ও লাইভ পরিসংখ্যান |
| **কোর্স ব্রাউজিং** | `/courses` | ফিল্টার, সার্চ ও বিস্তারিত কোর্স ভিউ |
| **লার্নিং ক্লাসরুম** | `/dashboard/courses/{course}/lessons/{lesson}` | ৪-ট্যাব ইন্টারফেস (নোট, বুকমার্ক, প্রশ্নোত্তর, লেকচার) |
| **সনদপত্র যাচাই** | `/verify/{identifier}` | কিউআর কোড ও ইউনিক ইউইউআইডি ভিত্তিক পাবলিক সনদ যাচাই |
| **কুরআনুল কারীম** | `/quran` ও `/quran/{number}` | ১১৪ সূরার অডিও, আরবি টেক্সট, বাংলা অনুবাদ ও হিফয অগ্রগতি ট্র্যাকার |
| **হাদীস সম্ভার** | `/hadith` ও `/hadith/{slug}` | সিহাহ সিত্তার ৬টি মৌলিক গ্রন্থ, অধ্যায় ও সনদসহ হাদীস পাঠাগার |
| **ফাতাওয়া ও প্রশ্নোত্তর** | `/fatawa` ও `/fatawa/ask` | বিজ্ঞ মুফতিয়ানে কেরামের তাহকীকপূর্ণ ফাতাওয়া ও প্রশ্ন পেশ |
| **আমার প্রশ্নসমূহ** | `/dashboard/my-questions` | শিক্ষার্থীর জমাকৃত প্রশ্নের অগ্রগতি ও উত্তর দেখার ড্যাশবোর্ড |
| **কমিউনিটি ফোরাম** | `/community` | উম্মাহর উন্মুক্ত আলোচনা, টপিক ফিল্টার ও সমাধান চিহ্নিতকরণ |
| **নিরাপদ চেকআউট** | `/checkout/{course}` | কুপন ডিসকাউন্ট, বিকাশ/নগদ/কার্ড ও নিরাপদ এনরোলমেন্ট |
| **অর্ডার ও রসিদ** | `/orders/{order}/invoice` | শিক্ষার্থীর অফিশিয়াল মানি রিসিট ও প্রিন্টযোগ্য ইনভয়েস |
| **শিক্ষক পরিচিতি** | `/teachers/{slug}` | উলামায়ে কেরামের পূর্ণাঙ্গ একাডেমিক পরিচিতি ও লিঙ্কডইন-স্টাইল ট্যাব |

---

## 👥 ডিফল্ট টেস্ট অ্যাকাউন্টস (Seeded Accounts)

- **সুপার অ্যাডমিন**: `admin@taallumbd.com` (পাসওয়ার্ড: `password`)
- **মুদাররিস / উস্তায**: `instructor@taallumbd.com` (পাসওয়ার্ড: `password`)
- **সাধারণ শিক্ষার্থী**: `student@taallumbd.com` (পাসওয়ার্ড: `password`)

---

*আত-তাআল্লুম — ইলম হোক বিশ্বস্ত, সহজলভ্য ও আধুনিক প্রযুক্তিসমৃদ্ধ।*
