# Cloudflare Free Tier কনফিগারেশন নির্দেশিকা (Cloudflare Configuration Guide)

এই ডকুমেন্টে Taallum BD প্ল্যাটফর্মের ডোমেইন (`taallumbd.com`), DNS, ফ্রি SSL/TLS, ক্যাশিং ও সিকিউরিটি WAF রুলস কনফিগারেশন লিপিবদ্ধ রয়েছে।

---

## ১. DNS রেকর্ড সেটআপ (DNS Records)

Cloudflare ড্যাশবোর্ডে ডোমেইনের জন্য নিম্নলিখিত DNS রেকর্ড যোগ করুন:

| Type  | Name | Target / Content | Proxy status | TTL | উদ্দেশ্য |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **CNAME** | `@` (apex) | `taallumbd-app.onrender.com` | **Proxied (Orange Cloud)** | Auto | মূল ওয়েবসাইট ট্রাফিক |
| **CNAME** | `www` | `taallumbd-app.onrender.com` | **Proxied (Orange Cloud)** | Auto | www সাবডোমেইন রিডাইরেক্ট |
| **CNAME** | `cdn` / `assets` | `<bucket-public-url>.r2.dev` (অথবা Custom R2 Domain) | **Proxied (Orange Cloud)** | Auto | R2 পাবলিক ফাইল ও মিডিয়া সিডিএন |
| **TXT** | `@` | `v=spf1 include:spf.brevo.com ~all` | DNS only (Grey Cloud) | Auto | Brevo ইমেইল SPF ভেরিফিকেশন |
| **TXT** | `mail._domainkey` | `k=rsa; p=...` (Brevo DKIM key) | DNS only (Grey Cloud) | Auto | Brevo ইমেইল DKIM ভেরিফিকেশন |
| **TXT** | `_dmarc` | `v=DMARC1; p=none; rua=mailto:dmarc@taallumbd.com` | DNS only (Grey Cloud) | Auto | DMARC পলিসি ও ডেলিভারিবিলিটি |

> [!TIP]
> Render-এ কাস্টম ডোমেইন ভেরিফিকেশনের জন্য Render ড্যাশবোর্ডে নির্দেশিত TXT বা CNAME রেকর্ডটি DNS-এ যোগ করুন।

---

## ২. SSL / TLS মোড (SSL/TLS Encryption)

Cloudflare ড্যাশবোর্ড থেকে **SSL/TLS** মেনুতে যান:

- **এনক্রিপশন মোড (Encryption Mode)**: **Full (strict)** সিলেক্ট করুন।
  - *ব্যাখ্যা*: ব্রাউজার থেকে Cloudflare এবং Cloudflare থেকে Render অরিজিন সার্ভারের মধ্যবর্তী সম্পূর্ণ ট্রাফিক এনক্রিপ্টেড থাকে। Render স্বয়ংক্রিয়ভাবে ভ্যালিড Let's Encrypt SSL সার্টিফিকেট প্রদান করে, তাই `Full (strict)` ব্যবহার করা সম্পূর্ণ নিরাপদ ও সুপারিশকৃত।
- **Edge Certificates**:
  - **Always Use HTTPS**: `ON` (সমস্ত HTTP ট্রাফিক স্বয়ংক্রিয়ভাবে HTTPS-এ রিডাইরেক্ট হবে)।
  - **Automatic HTTPS Rewrites**: `ON` (মিক্সড কনটেন্ট সমস্যা প্রতিরোধ করে)।
  - **Minimum TLS Version**: `TLS 1.2` (পুরনো ও অনিরাপদ প্রোটোকল ব্লক করবে)।
  - **Opportunistic Encryption**: `ON`।
  - **TLS 1.3**: `ON` (দ্রুত হ্যান্ডশেক ও উন্নত নিরাপত্তা)।

---

## ৩. পেজ রুল / ক্যাশ রুল (Cache Rules for Static Assets)

Vite-এর মাধ্যমে বিল্ড করা এসেট ফাইলগুলো (`/build/*`) হ্যাসড নামযুক্ত (যেমন: `app-C3s_z5dF.js`, `app-BL99_j2A.css`)। এগুলো ব্রাউজার ও এজ-এ দীর্ঘ সময় ক্যাশ করা নিরাপদ।

Cloudflare ড্যাশবোর্ডে **Caching > Cache Rules**-এ গিয়ে **Create rule** ক্লিক করুন:

### রুল ১: Vite Built Assets Cache
- **Rule Name**: `Cache Vite Build Assets`
- **When incoming requests match**:
  - `(http.request.uri.path starts_with "/build/")`
- **Cache Eligibility**: **Eligible for cache**
- **Edge TTL**: **Override origin** -> `1 month` (বা `1 year`)
- **Browser TTL**: **Override origin** -> `1 month`
- **Cache Key**:
  - Query string: Ignore query string
- **Serve Stale Content**: Enabled while revalidating

### রুল ২: Bypass Dynamic Routes
- **Rule Name**: `Bypass Cache for Dynamic & Auth`
- **When incoming requests match**:
  - `(http.request.uri.path starts_with "/internal/") or (http.request.uri.path starts_with "/api/") or (http.request.uri.path starts_with "/admin") or (http.request.uri.path starts_with "/dashboard")`
- **Cache Eligibility**: **Bypass cache**

---

## ৪. WAF ও রেট লিমিটিং রুলস (Web Application Firewall & Rate Limiting)

Cloudflare ফ্রি প্ল্যানে ৫টি ফ্রি WAF কাস্টম রুল এবং ১টি ফ্রি রেট লিমিটিং রুল পাওয়া যায়।

### ক. WAF কাস্টম রুলস (Security > WAF > Custom Rules)

#### রুল ১: ব্লকিং ক্ষতিকর বট ও স্ক্যানার (Block Scanners & Exploit Probes)
- **Expression**:
  ```text
  (http.request.uri.path contains "wp-login" or http.request.uri.path contains "xmlrpc.php" or http.request.uri.path contains "/.env" or http.request.uri.path contains "/.git" or http.request.uri.path contains "/phpmyadmin")
  ```
- **Action**: **Block**

#### রুল ২: ইন্টারনাল ক্রন সুরক্ষা (Internal Cron Shield)
- **Expression**:
  ```text
  (http.request.uri.path eq "/internal/cron" and not any(http.request.headers["x-cron-token"][*] eq "YOUR_CRON_SECRET"))
  ```
- **Action**: **Block** (অ্যাপ্লিকেশনের আগে ক্লাউডফ্লেয়ার এজ থেকেই টোকেনবিহীন অননুমোদিত রিকোয়েস্ট ব্লক হয়ে যাবে)।

### খ. রেট লিমিটিং রুল (Security > WAF > Rate Limiting Rules)

- **Rule Name**: `Protect Login & Payment Endpoints`
- **When incoming requests match**:
  - `(http.request.uri.path eq "/login" or http.request.uri.path eq "/register" or http.request.uri.path eq "/forgot-password" or http.request.uri.path eq "/checkout/manual")`
  - এবং `(http.request.method eq "POST")`
- **Rate Limit**:
  - **Threshold**: ২০টি রিকোয়েস্ট প্রতি ১ মিনিটে (20 requests per 1 minute per IP)
- **Action**: **Managed Challenge** (CAPTCHA)
- **Duration**: ১০ মিনিট

---

## ৫. স্পিড ও অপ্টিমাইজেশন (Speed Optimization)

Cloudflare ড্যাশবোর্ডে **Speed > Optimization** ট্যাবে নিম্নলিখিত সেটিংস অন রাখুন:

1. **Auto Minify**: HTML, CSS, JavaScript চেকমার্ক অন করুন (যদিও Vite ইতিমধ্যে মিন কোড তৈরি করে, এটি অতিরিক্ত বাইট সাশ্রয় করে)।
2. **Brotli Compression**: `ON` (Gzip-এর চেয়ে প্রায় ২০% বেশি কম্প্রেশন)।
3. **Early Hints**: `ON` (ব্রাউজারকে পূর্বেই CSS/JS রিসোর্স প্রিলোড করতে সাহায্য করে)।
4. **Rocket Loader**: `OFF` (সতর্কতা: React/Inertia.js অ্যাপ্লিকেশনে Rocket Loader স্ক্রিপ্ট এক্সিকিউশন অর্ডার এলোমেলো করে দিতে পারে, তাই এটি বন্ধ রাখা শ্রেয়)।

---

## ৬. চেক এবং ভেরিফিকেশন (Verification Steps)

১. টার্মিনাল থেকে SSL ও হেডার পরীক্ষা করুন:
```bash
curl -I https://taallumbd.com
```
প্রত্যাশিত আউটপুট:
- `CF-RAY: ...` (Cloudflare সক্রিয়)
- `strict-transport-security: max-age=...` (HSTS সক্রিয়)

২. স্ট্যাটিক এসেট ক্যাশ হিট পরীক্ষা:
```bash
curl -I https://taallumbd.com/build/assets/app.js
```
প্রত্যাশিত হেডার:
- `CF-Cache-Status: HIT` (দ্বিতীয়বার রিকোয়েস্টে)
