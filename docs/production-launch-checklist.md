# Taallum BD — Commercial Production Launch Checklist

**Document Version:** 1.0.0 (Production Go-Live Protocol)  
**Target Platform:** [taallumbd.com](https://taallumbd.com)  
**Security & Operational Readiness:** Commercial Grade

---

## ১. ডোমেইন, ডিএনএস ও এসএসএল সিকিউরিটি (Domain, DNS & SSL)

- [ ] **Cloudflare DNS Configuration:**
  - `taallumbd.com` এবং `www.taallumbd.com` সঠিক CNAME / A রেকর্ডে পয়েন্ট করা আছে।
  - Cloudflare Proxy (Orange Cloud) সক্রিয় রয়েছে (DDoS ও বট প্রটেকশন অন)।
- [ ] **SSL/TLS Encryption Mode:**
  - Cloudflare SSL মোড **"Full (Strict)"** সেট করা আছে।
  - Always Use HTTPS অপশন অন রয়েছে।
- [ ] **HSTS & Security Headers Verification:**
  - `Strict-Transport-Security: max-age=31536000; includeSubDomains; preload` সক্রিয়।
  - `X-Content-Type-Options: nosniff` এবং `Cross-Origin-Opener-Policy: same-origin` নিশ্চিত।
  - [SecurityHeaders.com](https://securityheaders.com)-এ টেস্ট করে A/A+ গ্রেড যাচাই করা।

---

## ২. পেমেন্ট গেটওয়ে লাইভ মোড ও ওয়েবহুক (Payment Gateways Go-Live)

- [ ] **bKash Tokenized Checkout:**
  - `BKASH_MODE=live` এবং প্রোডাকশন App Key, App Secret, Username, Password সেট করা।
  - লাইভ ১ টাকার টেস্ট পেমেন্ট ও সফল রিফান্ড টেস্ট করা হয়েছে।
- [ ] **SSLCommerz (কার্ড, নগদ, রকেট, ইন্টারনেট ব্যাংকিং):**
  - `SSLCOMMERZ_MODE=live`, Store ID ও Store Password সক্রিয়।
  - আইপিএন (IPN) ইউআরএল: `https://taallumbd.com/webhooks/sslcommerz/ipn` কনফিগার করা।
- [ ] **Stripe (আন্তর্জাতিক কার্ড পেমেন্ট):**
  - `STRIPE_KEY` এবং `STRIPE_SECRET` লাইভ `pk_live_...` ও `sk_live_...` দিয়ে প্রতিস্থাপিত।
  - `STRIPE_WEBHOOK_SECRET` সেট করা এবং টেস্ট সিগনেচার যাচাই সম্পন্ন।
- [ ] **পেমেন্ট স্টেট মেশিন ও ডুপ্লিকেট রোধ:**
  - `PaymentStateMachine` এনফোর্সড (`initiated` → `pending` → `paid` → `completed`)।
  - ডুপ্লিকেট TrxID দিয়ে রি-এনরোলমেন্ট অসম্ভব।

---

## ৩. রিফান্ড পলিসি ও ব্যবসায়িক নিয়ম (Refund Business Rules)

- [ ] **৭ দিনের রিফান্ড উইন্ডো:** অর্ডারের ৭ দিনের বেশি অতিবাহিত হলে শিক্ষার্থী রিফান্ড আবেদন করতে পারবে না।
- [ ] **কোর্স অগ্রগতি সীমা (Max 20%):** শিক্ষার্থী কোর্সের ২০%-এর বেশি সম্পন্ন করলে স্বয়ংক্রিয়ভাবে রিফান্ড বন্ধ থাকবে।
- [ ] **উস্তাযের ওয়ালেট সমন্বয় (Negative Balance Protection):**
  - রিফান্ড কার্যকর হলে শিক্ষকের পেন্ডিং বা ব্যালেন্স থেকে রেভিনিউ শেয়ার কেটে নেওয়া হবে।
  - ব্যালেন্স ঋণাত্মক হলে পরবর্তী কোর্সের আয় থেকে স্বয়ংক্রিয়ভাবে সমন্বয় না হওয়া পর্যন্ত পেআউট স্থগিত থাকবে।
- [ ] **পাবলিক রিফান্ড পলিসি পেজ:** `/refund` পেজে এই নিয়মাবলি স্পষ্টভাবে উল্লেখ করা আছে।

---

## ৪. ডাটাবেজ ব্যাকআপ, রেপ্লিকা ও ডুপ্লিকেট অডিট (Database Integrity & Disaster Recovery)

- [ ] **প্রি-মাইগ্রেশন ডুপ্লিকেট অডিট:**
  - `php artisan db:audit-duplicates` কমান্ড চালিয়ে কোনো ডুপ্লিকেট এনরোলমেন্ট বা ট্রানজ্যাকশন নেই তা নিশ্চিত করা হয়েছে।
- [ ] **ফুল ডাটাবেজ ব্যাকআপ:**
  - `php artisan db:backup` চালিয়ে ক্লাউড স্টোরেজে (Cloudflare R2) ব্যাকআপ তৈরি ও যাচাই করা হয়েছে।
- [ ] **রিস্টোর রিহার্সাল (Disaster Recovery Drill):**
  - ব্যাকআপ ফাইল থেকে লোকাল বা স্টেজিং ডাটাবেজে ডাটা সফলভাবে রিস্টোর করে দেখা হয়েছে।
- [ ] **Neon PostgreSQL কানেকশন পুলিং:**
  - রেগুলার ট্রাফিকের জন্য PgBouncer পুলার সংযোগ ও মাইগ্রেশনের জন্য ডিরেক্ট সংযোগ পৃথক রাখা।

---

## ৫. ইমেইল ডেলিভারি ও নোটিফিকেশনস (Transactional Email & SMTP)

- [ ] **প্রোডাকশন SMTP সেটআপ:**
  - Brevo / Resend / SendGrid কনফিগারেশন সম্পন্ন।
- [ ] **DNS রেকর্ড যাচাই (ইমেইল স্প্যাম রোধ):**
  - SPF রেকর্ড (`v=spf1 include:... ~all`) সক্রিয়।
  - DKIM সিগনেচার ভ্যালিড।
  - DMARC পলিসি সেট করা।
- [ ] **ইমেইল নোটিফিকেশন টেস্ট:**
  - সাইন-আপ ওটিপি/ভেরিফিকেশন ইমেইল ইনবক্সে যাচ্ছে (স্প্যামে নয়)।
  - অর্ডার কনফার্মেশন ও ইনভয়েস ইমেইল ডেলিভারি সঠিক।

---

## ৬. অ্যাডমিন ও অ্যাকাউন্ট সিকিউরিটি (Admin & Authentication Security)

- [ ] **সুপার অ্যাডমিন পাসওয়ার্ড:**
  - ডিফল্ট সিডেড পাসওয়ার্ড (`password`) পরিবর্তন করে শক্তিশালী পাসওয়ার্ড দেওয়া হয়েছে।
- [ ] **টু-ফ্যাক্টর অথেনটিকেশন (2FA):**
  - সকল অ্যাডমিন ও মডারেটর অ্যাকাউন্টে TOTP (Google Authenticator) বাধ্যতামূলক।
- [ ] **সেশন ও কুকি সিকিউরিটি:**
  - `SESSION_SECURE_COOKIE=true`, `SameSite=lax`, `HttpOnly=true` বলবৎ।
- [ ] **রেট লিমিটিং (Throttling):**
  - লগইন, ওটিপি, পাসওয়ার্ড রিসেট এবং পেমেন্ট চেকআউট এন্ডপয়েন্টে ব্রুট-ফোর্স রোধক থ্রোটলিং চালু।

---

## ৭. এরর পেজ ও এআই গভর্নেন্স (Error Pages & AI Safety)

- [ ] **কাস্টম ব্র্যান্ডেড এরর পেজ:**
  - 404 (Not Found), 403 (Forbidden), 500 (Server Error) পেজগুলো বাংলা ও ইসলামিক ব্র্যান্ড থিমে সাজানো।
- [ ] **প্রম্পটগার্ড PII স্ক্রাবার:**
  - ব্যবহারকারীর ফোন, ইমেইল বা কার্ড তথ্য এআই মডেলে যাওয়ার আগেই রেড্যাক্ট হচ্ছে।
- [ ] **এআই কোটা ও কস্ট ট্র্যাকিং:**
  - অ্যাডমিন এআই ড্যাশবোর্ডে (`/admin/ai`) দৈনিক টোকেন ব্যবহার ও ফ্ল্যাগড প্রম্পট মনিটর করা যাচ্ছে।

---

## ৮. সিস্টেম পারফরম্যান্স ও ক্যাশিং (Production Optimization)

- [ ] **ফ্রন্টএন্ড প্রোডাকশন বিল্ড:**
  - `npm run build` সফলভাবে সম্পন্ন এবং অ্যাসেট ভার্সনিং ঠিক আছে।
- [ ] **লারাভেল ক্যাশিং অপ্টিমাইজেশন:**
  ```bash
  php artisan config:cache
  php artisan route:cache
  php artisan view:cache
  php artisan event:cache
  ```
- [ ] **লাইভ সিস্টেম হেলথচেক:**
  - `https://taallumbd.com/health` হিট করে `{"status":"healthy"}` রেসপন্স যাচাই।
- [ ] **ক্রন শিডিউলার সচল রাখা:**
  - cron-job.org থেকে প্রতি ২ মিনিটে `https://taallumbd.com/internal/cron` হিট হচ্ছে।

---

> 🚀 **সকল চেকবক্স সম্পন্ন হলে প্ল্যাটফর্মটি আনুষ্ঠানিকভাবে পাবলিক ও কমার্শিয়াল লঞ্চের জন্য প্রস্তুত!**
