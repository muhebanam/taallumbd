# আত-তাআল্লুম (TaallumBD) — মাস্টার ডেভেলপমেন্ট ও ডিপ্লয়মেন্ট গাইড

**আত-তাআল্লুম (taallumbd.com)** হলো একটি আধুনিক, পূর্ণাঙ্গ ডিজিটাল ইসলামী শিক্ষা ইকোসিস্টেম, স্কলার নেটওয়ার্ক ও উন্মুক্ত জ্ঞান প্ল্যাটফর্ম (LMS + Fatwa + Quran/Hadith + Community Forum + Multi-Gateway Payments)।

---

## 🛠️ প্রযুক্তি স্ট্যাক (Tech Stack)

- **ব্যাকএন্ড**: Laravel 13 (PHP 8.3+)
- **ফ্রন্টএন্ড**: React 18 + Inertia.js v2
- **স্টাইলিং**: Tailwind CSS + Custom Islamic Typography & Brand Palettes
- **ডেটাবেজ**: PostgreSQL 16 (প্রোডাকশন Neon Free), লোকাল Docker Postgres, টেস্ট SQLite in-memory
- **ফন্টসমূহ**: Amiri (আরবি ক্যালিগ্রাফি), Hind Siliguri (বাংলা), Outfit (ইংরেজি)
- **পেমেন্ট গেটওয়ে**: বিকাশ (bKash), নগদ (Nagad), রকেট (Rocket), কার্ড (SSLCommerz) এবং ম্যানুয়াল TrxID ভেরিফিকেশন।

---

## 🎨 ব্র্যান্ড কালার ও ভিজ্যুয়াল সিস্টেম

- **Deep Evergreen (Primary)**: `#102526`
- **Forest Slate (Secondary)**: `#1A2E2F`
- **Islamic Gold (Accent)**: `#FFF99A`
- **Soft Off-White (Canvas)**: `#F8FAF8`

---

## 🐳 Docker দিয়ে লোকাল সেটআপ (Docker Compose — শূন্য হোস্ট ডিপেন্ডেন্সি)

লোকাল কম্পিউটারে কোনো PHP, Composer বা Node.js ইনস্টল করার প্রয়োজন নেই। সব কিছু Docker Compose-এর মাধ্যমে আইসোলেটেড ও ফ্রি-ফার্স্ট নীতিতে চলবে।

### ধাপ ১: রিপোজিটরি ক্লোন ও কনফিগারেশন
```bash
git clone https://github.com/muhebanam/taallumbd.git
cd taallumbd

# .env ফাইল তৈরি করুন (Docker ডিফল্ট কনফিগসহ)
cp .env.example .env
```

### ধাপ ২: Docker সার্ভিসসমূহ চালু করুন
Windows-এ হেল্পার স্ক্রিপ্ট দিয়ে:
```powershell
.\scripts\dev.ps1 up
```
অথবা স্ট্যান্ডার্ড Docker Compose দিয়ে:
```bash
docker compose up -d
```
> **সার্ভিসসমূহ চালু হবে:**
> - `app`: Laravel 13 API ও ওয়েব সার্ভার (`http://localhost:8000`)
> - `node`: Vite ৫ HMR সার্ভার (`http://localhost:5173`)
> - `postgres`: PostgreSQL 16 ডেটাবেজ (`localhost:5432`)
> - `redis`: Redis 7 ক্যাশ ও সেশন স্টোর (`localhost:6379`)
> - `mailpit`: লোকাল টেস্ট ইমেইল ইনবক্স (`http://localhost:8025`)
>
> **উইন্ডোজ পারফরম্যান্স অপ্টিমাইজেশন:** Windows 9P ফাইল-সিস্টেম স্লোডাউন এড়াতে `vendor` এবং `node_modules`-কে Docker Named Volumes-এ মাউন্ট করা হয়েছে।

### ধাপ ৩: ডিপেন্ডেন্সি ইনস্টল ও ইনিশিয়ালাইজেশন
```bash
# Composer ডিপেন্ডেন্সি ইনস্টল
docker compose exec app composer install
# অথবা: .\scripts\dev.ps1 composer install

# NPM ডিপেন্ডেন্সি ইনস্টল
docker compose exec node npm install
# অথবা: .\scripts\dev.ps1 npm install

# অ্যাপ্লিকেশন কি (Key) তৈরি
docker compose exec app php artisan key:generate
# অথবা: .\scripts\dev.ps1 artisan key:generate

# ডেটাবেজ মাইগ্রেশন ও সম্পূর্ণ সিডিং
docker compose exec app php artisan migrate --seed
# অথবা: .\scripts\dev.ps1 fresh
```

### ধাপ ৪: ডেভেলপমেন্ট রান ও ব্রাউজারে প্রবেশ
- **ওয়েব অ্যাপ্লিকেশন**: [http://localhost:8000](http://localhost:8000)
- **Vite HMR**: [http://localhost:5173](http://localhost:5173)
- **Mailpit ইমেইল ইনবক্স**: [http://localhost:8025](http://localhost:8025)

---

## 🧪 টেস্ট চালানো (Testing & Code Quality)

লোকাল টেস্টগুলো বিদ্যুৎগতিতে SQLite In-Memory ডেটাবেজে রান করে।

### কন্টেইনারের ভেতরে টেস্ট রান:
```bash
# সব টেস্ট চালানো
docker compose exec app php artisan test
# অথবা: .\scripts\dev.ps1 test

# নির্দিষ্ট টেস্ট ফাইল চালানো
docker compose exec app php artisan test tests/Feature/FreeCourseEnrollmentTest.php
```

### কোড স্টাইল ও লিন্ট (Laravel Pint):
```bash
# কোড স্টাইল ভ্যালিডেশন
docker compose exec app vendor/bin/pint --test
# অথবা: docker compose exec app composer lint

# কোড স্বয়ংক্রিয় ফরম্যাটিং
docker compose exec app vendor/bin/pint
```

---

## 💻 হোস্ট মেশিনে সরাসরি লোকাল সেটআপ (ঐচ্ছিক — হোস্ট ডিপেন্ডেন্সিসহ)

যদি আপনি Docker ব্যবহার না করে সরাসরি আপনার লোকাল মেশিনের PHP/Node দিয়ে রান করতে চান:

### ১. ডিপেন্ডেন্সি ইনস্টল করুন
```bash
# PHP Dependencies
composer install

# Node.js Dependencies
npm install
```

### ২. এনভায়রনমেন্ট কনফিগারেশন (.env)
```bash
cp .env.example .env
php artisan key:generate
```

### ৩. ডেটাবেজ মাইগ্রেশন ও সিডিং
```bash
php artisan migrate
php artisan db:seed
```

### ৪. লোকাল সার্ভার ও Vite ডেভেলপমেন্ট রান করুন
```bash
# টার্মিনাল ১ (Laravel Server):
php artisan serve

# টার্মিনাল ২ (Vite HMR Server):
npm run dev
```

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
