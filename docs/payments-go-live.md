# Taallum BD — Payment Gateway Go-Live Guide & Checklist
## SSLCommerz ও bKash Tokenized Checkout ইন্টিগ্রেশন নির্দেশিকা

> **নীতিমালা (Free-First Architecture):**
> Taallum BD-এর প্রোডাকশন সিস্টেমে ডিফল্টভাবে কোনো পেইড গেটওয়ে বাধ্যতামূলক নয়। প্রাথমিক অবস্থায় শূন্য খরচে **ManualGateway** (bKash, Nagad, Rocket সেন্ড মানি ও অ্যাডমিন ভেরিফিকেশন) চালু থাকে। 
> মার্চেন্ট একাউন্ট ও লাইভ ক্রেডেনশিয়াল প্রস্তুত হলে **কোনো প্রকার কোড পরিবর্তন ছাড়াই** শুধুমাত্র এনভায়রনমেন্ট ভেরিয়েবল পরিবর্তনের মাধ্যমে অটোমেটেড গেটওয়ে লাইভ চালু করা যায়।

---

## ১. ওভারভিউ ও গেটওয়ে আর্কিটেকচার

Taallum BD সিস্টেমে `PaymentGateway` কন্ট্রাক্ট অনুসারে ড্রাইভারগুলো চালিত হয়:
- `ManualGateway`: ম্যানুয়াল সেন্ড মানি ভেরিফিকেশন (ডিফল্ট)
- `SslCommerzGateway`: ক্রেডিট/ডেবিট কার্ড, ইন্টারনেট ব্যাংকিং, মোবাইল ব্যাংকিং
- `BkashGateway`: bKash Tokenized Checkout (সরাসরি পিন ও ওটিপি পেমেন্ট)
- `MockGateway`: লোকাল/টেস্টিং সিমুলেশন

### সিকিউরিটি স্ট্যান্ডার্ড:
1. **কখনো ব্রাউজার রিডাইরেক্টে বিশ্বাস করা হয় না:** ব্রাউজার থেকে ক্লায়েন্ট যাই ফেরত পাঠাক না কেন, আমাদের ব্যাকএন্ড সার্ভার সরাসরি গেটওয়ের ভ্যালিডেশন এপিআই (`validationserverAPI.php` / `/checkout/execute`) কল করে পেমেন্ট যাচাই করে।
2. **অ্যামাউন্ট ও কারেন্সি চেক:** গেটওয়ে থেকে অনুমোদিত টাকার পরিমাণ কোর্সের মূল্যের সাথে হুবহু মিলতে হবে এবং কারেন্সি `BDT` হতে হবে; গরমিল থাকলে সাথে সাথে `tampered` ফ্ল্যাগ দিয়ে পেমেন্ট বাতিল করা হয়।
3. **আইডিমপোটেন্সি (Idempotency):** ডুপ্লিকেট আইপিএন (IPN) বা রিফ্রেশে কখনো একাধিকবার অর্ডার পেইড বা কোর্স এনরোল হয় না।

---

## ২. SSLCommerz স্যান্ডবক্স ও মার্চেন্ট আবেদন

### ক. স্যান্ডবক্স রেজিস্ট্রেশন (ডেভেলপার টেস্ট):
1. [SSLCommerz Developer Portal](https://developer.sslcommerz.com/registration/)-এ যান।
2. রেজিস্ট্রেশন ফরম পূরণ করুন (Store Name: `Taallum BD`, Email, Phone)।
3. ইমেইলে প্রাপ্ত `STORE_ID` এবং `STORE_PASSWORD` সংগ্রহ করুন (উদা: `taallumlive001`, `taallumlive001@ssl`)।
4. বেস ইউআরএল: `https://sandbox.sslcommerz.com`

### খ. মার্চেন্ট আবেদনের জন্য প্রয়োজনীয় কাগজপত্র (Live Merchant Onboarding):
লাইভ মার্চেন্ট অ্যাকাউন্ট পেতে SSLCommerz সেলস টিমের (`operation@sslcommerz.com` / `sales@sslcommerz.com`) কাছে নিচের কাগজপত্র জমা দিতে হয়:
- [x] প্রতিষ্ঠানের ট্রেড লাইসেন্স (Trade License - হালনাগাদ)
- [x] প্রতিষ্ঠানের ই-টিন সার্টিফিকেট (E-TIN Certificate)
- [x] প্রতিষ্ঠানের নামে ব্যাংক একাউন্টের তথ্য ও ব্যাংক সলভেন্সি সার্টিফিকেট / ক্যান্সেলড চেক
- [x] সত্ত্বাধিকারী / পরিচালকদের জাতীয় পরিচয়পত্র (NID) ও ছবি
- [x] ওয়েবসাইট অডিট ও নীতিমালা পেজ (Terms & Conditions, Privacy Policy, Refund Policy ওয়েবসাইটে দৃশ্যমান থাকতে হবে — Taallum BD-তে ইতোমধ্যে যুক্ত রয়েছে)
- [x] SSLCommerz মার্চেন্ট চুক্তিপত্র (স্বাক্ষরিত)

---

## ৩. bKash Tokenized Checkout স্যান্ডবক্স ও মার্চেন্ট আবেদন

### ক. স্যান্ডবক্স রেজিস্ট্রেশন (PGW Sandbox):
1. bKash Developer Portal ([developer.bka.sh](https://developer.bka.sh))-এ অ্যাকাউন্ট খুলুন।
2. Tokenized Checkout v2 API স্যান্ডবক্স অ্যাক্সেসের জন্য টেস্ট ক্রেডেনশিয়াল সংগ্রহ করুন:
   - `app_key`
   - `app_secret`
   - `username`
   - `password`
3. বেস ইউআরএল: `https://tokenized.sandbox.bka.sh/v2.0`

### খ. মার্চেন্ট আবেদনের জন্য প্রয়োজনীয় কাগজপত্র:
- [x] প্রতিষ্ঠানের ট্রেড লাইসেন্স (হালনাগাদ)
- [x] টিআইএন (TIN) সার্টিফিকেট
- [x] প্রাতিষ্ঠানিক ব্যাংক অ্যাকাউন্ট স্টেটমেন্ট / চেক পাতার কপি
- [x] ম্যানেজিং ডিরেক্টর বা মালিকের এনআইডি ও ছবি
- [x] bKash Merchant Agreement ফরম পূরণ ও স্বাক্ষর

---

## ৪. লাইভ চালুর চেকলিস্ট (Go-Live Checklist)

### ধাপ ১: এনভায়রনমেন্ট ভেরিয়েবল কনফিগারেশন

Render ড্যাশবোর্ড অথবা `.env` ফাইলে নিচের কি-গুলো পূরণ করুন:

```env
# --- SSLCommerz Live Config ---
SSLCOMMERZ_ENABLED=true
SSLCOMMERZ_MODE=live
SSLCOMMERZ_STORE_ID=your_live_store_id
SSLCOMMERZ_STORE_PASSWORD=your_live_store_password

# --- bKash Tokenized Live Config ---
BKASH_ENABLED=true
BKASH_MODE=live
BKASH_APP_KEY=your_live_app_key
BKASH_APP_SECRET=your_live_app_secret
BKASH_USERNAME=your_live_username
BKASH_PASSWORD=your_live_password
```

### ধাপ ২: গেটওয়ে প্যানেলে IPN ও কলব্যাক ইউআরএল সেট করা

| গেটওয়ে | ফিল্ড / সেটিংস | প্রোডাকশন ইউআরএল |
| :--- | :--- | :--- |
| **SSLCommerz** | IPN Webhook URL | `https://taallumbd.com/webhooks/sslcommerz/ipn` |
| **SSLCommerz** | Return URLs | স্বয়ংক্রিয়ভাবে কোড থেকে প্যারামিটার আকারে যায় |
| **bKash** | Webhook URL (ঐচ্ছিক) | `https://taallumbd.com/webhooks/bkash/ipn` |

### ধাপ ৩: সিকিউরিটি ও সিএসআরএফ (CSRF) যাচাই
- `bootstrap/app.php`-তে `webhooks/*` এবং `payments/*/callback/*` সিএসআরএফ এক্সেম্পট করা আছে।
- প্রোডাকশন ডোমেইনে ভ্যালিড HTTPS / TLS সার্টিফিকেট চালু থাকতে হবে।

### ধাপ ৪: পরীক্ষামূলক রিয়েল ট্রানজ্যাকশন (Test Transaction)
1. অ্যাডমিন প্যানেল থেকে একটি টেস্ট কোর্স তৈরি করুন যার ফি ১০ টাকা (৳১০)।
2. সাধারণ স্টুডেন্ট একাউন্ট দিয়ে লগইন করে চেকআউটে যান।
3. bKash Auto Checkout সিলেক্ট করে ১০ টাকা পেমেন্ট সম্পন্ন করুন।
4. সাথে সাথে ক্লাসরুমে এনরোলমেন্ট ও ইনভয়েস জেনারেট হচ্ছে কি না নিশ্চিত করুন।
5. SSLCommerz সিলেক্ট করে কার্ড বা অন্য ব্যাংকিং মাধ্যমে আরেকটি টেস্ট লেনদেন করুন।
6. অ্যাডমিন প্যানেলের `/admin/orders` পেজে ট্রানজ্যাকশন ও অর্ডার ভেরিফাই করুন।

---

## ৫. রিকনসিলিয়েশন ও ক্রন শিডিউল (Reconciliation)

যদি কোনো শিক্ষার্থী ব্রাউজার বন্ধ করে দেয় বা ইন্টারনেট সমস্যার কারণে রিডাইরেক্ট মিস হয়, তবে:
- Artisan কমান্ড: `php artisan payments:reconcile`
- এই কমান্ডটি ৩০ মিনিটের বেশি পুরনো অপেক্ষমাণ অর্ডারগুলোর জন্য গেটওয়ের সার্ভারে কোয়েরি চালিয়ে পেমেন্ট কনফার্ম করে।

### Render Free Tier-এ ক্রন চালানো:
Render Free Tier-এ আলাদা ব্যাকগ্রাউন্ড ওয়ার্কার বা ডেমন ক্রন না থাকায়:
- ক্রন রুট: `GET https://taallumbd.com/internal/cron?token=YOUR_CRON_SECRET`
- [cron-job.org](https://cron-job.org) বা যেকোনো ফ্রি আপটাইম মনিটর দিয়ে প্রতি ৫-১০ মিনিটে এই রুটে পিং পাঠাবেন।
- এটি স্বয়ংক্রিয়ভাবে `Schedule::command('payments:reconcile')->everyThirtyMinutes()` নির্বাহ করবে।

---

## ৬. রিফান্ড ও নীতি বাস্তবায়ন

1. অ্যাডমিন প্যানেলের **Orders** (`/admin/orders`) পেজে যান।
2. যে অর্ডারটি রিফান্ড করতে চান তার পাশে থাকা **"রিফান্ড"** বাটনে ক্লিক করুন।
3. কারণ উল্লেখ করে সাবমিট করলে:
   - সংশ্লিষ্ট গেটওয়ে (SSLCommerz / bKash)-এর রিফান্ড এপিআই কল হবে।
   - অর্ডারের অবস্থা `cancelled` হিসেবে আপডেট হবে।
   - কোর্সের এনরোলমেন্ট সরাসরি বাতিল (`cancelled`) হবে।
   - অডিট লগে প্রশাসনিক রিফান্ডের রেকর্ড সংরক্ষিত হবে।
