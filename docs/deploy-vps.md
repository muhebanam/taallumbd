# Taallum BD — VPS ডেপ্লয়মেন্ট ও মাইগ্রেশন নির্দেশিকা (Production VPS Migration Guide)

এই ডকুমেন্টে Taallum BD-কে রেন্ডার/নিয়ন ফ্রি-টিয়ার থেকে নিজস্ব ডেডিকেটেড ক্লাউড VPS-এ (যেমন: Hetzner Cloud, DigitalOcean, বা Linode) মাইগ্রেট করার সম্পূর্ণ গাইডলাইন এবং আর্কিটেকচার বর্ণনা করা হয়েছে।

---

## ১. কখন VPS-এ মাইগ্রেট করবেন? (Migration Triggers)

নিম্নলিখিত ৫টি পরিস্থিতির যেকোনো একটি পূরণ হলেই ফ্রি-টিয়ার থেকে Hetzner VPS-এ মাইগ্রেট করার সিদ্ধান্ত নিন:

1. **১,০০০+ সক্রিয় ব্যবহারকারী (Active Users)**:
   - প্ল্যাটফর্মে যখন একযোগে ১০০+ সক্রিয় ভিজিটর বা প্রতি মাসে ১,০০০+ এনরোল্ড শিক্ষার্থী নিয়মিত ক্লাস ও পরীক্ষা দেবেন।
2. **দৈনিক এনরোলমেন্ট বৃদ্ধি ও পিক লোড**:
   - নতুন কোর্স লঞ্চিংয়ের সময় বা দৈনিক ২০+ পেইড অর্ডার ট্রানজ্যাকশন প্রক্রিয়াকরণের সময়।
3. **ভিডিও ও মিডিয়া ট্রাফিক বৃদ্ধি**:
   - রেন্ডারের ৫১২ MB মেমোরি লিমিট এবং ক্লাউডফ্লেয়ার ফ্রি রেট লিমিটের কারণে বড় ব্যাচ প্রসেসিং ধীরগতির হলে।
4. **Neon ডেটাবেজ স্টোরেজ ৮০% পূর্ণ (৪০০ MB+)**:
   - Neon Free প্ল্যানে ০.৫ GB (৫১২ MB) ফ্রি স্টোরেজ রয়েছে। যখন মোট ডেটাবেজ সাইজ ৪০০ MB (৮০%) অতিক্রম করবে।
5. **Cold-Start অভিযোগ ও ব্যাকগ্রাউন্ড ওয়ার্কারের প্রয়োজনীয়তা**:
   - রেন্ডারের ১৫ মিনিটের স্লিপ মোড ও ওয়েক-আপ ল্যাটেন্সি (৪০–৬০ সেকেন্ড) শিক্ষার্থীদের অভিজ্ঞতা ব্যাহত করলে এবং ডেডিকেটেড দীর্ঘমেয়াদী ব্যাকগ্রাউন্ড কিউ ওয়ার্কার ও ফুল-টেক্সট সার্চ দরকার হলে।

---

## ২. প্রস্তাবিত VPS সার্ভার স্পেসিফিকেশন (Hetzner Cloud)

| প্যারামিটার | সুপারিশকৃত স্পেসিফিকেশন (Tier 1) | উচ্চ ট্রাফিকের জন্য (Tier 2) |
| :--- | :--- | :--- |
| **সার্ভার প্ল্যান** | **Hetzner CX22** (~€৩.৭৯ – €৪.৫০/মাস) | **Hetzner CPX21** (~€৭.৫০ – €৮.৫০/মাস) |
| **vCPU** | ২ vCPU (AMD EPYC™) | ৩ vCPU |
| **RAM** | ৪ GB ECC RAM | ৪ GB ECC RAM |
| **ডিস্ক** | ৪০ GB NVMe SSD | ৮০ GB NVMe SSD |
| **ট্রাফিক** | ২০ TB প্রতি মাসে (ফ্রি আউটবাউন্ড) | ২০ TB প্রতি মাসে |
| **লোকেশন** | জার্মানি (Falkenstein) অথবা ফিনল্যান্ড (Helsinki) | সিঙ্গাপুর (সিঙ্গাপুরে ট্রাফিক ল্যাটেন্সি আরও কম) |
| **OS** | Ubuntu 24.04 LTS (Noble Numbat) | Ubuntu 24.04 LTS |

---

## ৩. ডেপ্লয়মেন্ট মেথড ক: Docker Compose দিয়ে ডেপ্লয় (Recommended)

আমাদের কোডবেসে প্রডাকশন-রেডি `docker-compose.prod.yml` প্রস্তুত রাখা আছে।

### ধাপ ১: সার্ভার ইনিশিয়ালাইজেশন ও ডকার ইনস্টলেশন
```bash
# সিস্টেম আপডেট
sudo apt update && sudo apt upgrade -y

# Docker ও Docker Compose ইনস্টল
curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh get-docker.sh
sudo usermod -aG docker $USER
```

### ধাপ ২: রিপোজিটরি ক্লোন ও কনফিগারেশন
```bash
sudo mkdir -p /var/www/taallumbd
sudo chown -R $USER:$USER /var/www/taallumbd
git clone https://github.com/muhebanam/taallumbd.git /var/www/taallumbd
cd /var/www/taallumbd

# প্রোডাকশন এনভায়রনমেন্ট ফাইল তৈরি
cp .env.example .env.production
nano .env.production
```

`.env.production`-এ নিচের পরিবর্তনগুলো নিশ্চিত করুন:
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://taallumbd.com

DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=taallumbd
DB_USERNAME=taallum_user
DB_PASSWORD=YOUR_VERY_SECURE_PASSWORD

REDIS_HOST=redis
REDIS_PASSWORD=YOUR_REDIS_PASSWORD
QUEUE_CONNECTION=redis
CACHE_STORE=redis
SESSION_DRIVER=redis

FILESYSTEM_DISK=r2_public
```

### ধাপ ৩: কনটেইনার বিল্ড ও রান
```bash
docker compose -f docker-compose.prod.yml --env-file .env.production up -d --build
```

### ধাপ ৪: মাইগ্রেশন ও স্টোরেজ সিঙ্ক
```bash
docker compose -f docker-compose.prod.yml exec app php artisan migrate --force
docker compose -f docker-compose.prod.yml exec app php artisan storage:link
docker compose -f docker-compose.prod.yml exec app php artisan config:cache
docker compose -f docker-compose.prod.yml exec app php artisan route:cache
docker compose -f docker-compose.prod.yml exec app php artisan view:cache
```

---

## ৪. ডেপ্লয়মেন্ট মেথড খ: বেয়ার-মেটাল / নেটিভ Ubuntu + Nginx + PHP 8.3-FPM

যদি ডকার ব্যতীত সরাসরি উবুন্টু ওএস-এ হোস্ট করতে চান:

### ধাপ ১: ডিপেন্ডেন্সি ইনস্টলেশন
```bash
sudo apt update
sudo apt install -y software-properties-common curl git unzip nginx supervisor certbot python3-certbot-nginx
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
sudo apt install -y php8.3-fpm php8.3-cli php8.3-pgsql php8.3-redis php8.3-curl php8.3-gd \
                    php8.3-intl php8.3-mbstring php8.3-xml php8.3-zip php8.3-bcmath

# PostgreSQL 16 ও Redis ইনস্টল
sudo apt install -y postgresql-16 redis-server
```

### ধাপ ২: ডেটাবেজ সেটআপ
```bash
sudo -u postgres psql
CREATE DATABASE taallumbd;
CREATE USER taallum_user WITH ENCRYPTED PASSWORD 'YOUR_STRONG_PASSWORD';
GRANT ALL PRIVILEGES ON DATABASE taallumbd TO taallum_user;
ALTER DATABASE taallumbd OWNER TO taallum_user;
\q
```

### ধাপ ৩: Nginx কনফিগারেশন (`/etc/nginx/sites-available/taallumbd.com`)
```nginx
server {
    listen 80;
    server_name taallumbd.com www.taallumbd.com;
    root /var/www/taallumbd/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;
    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```
এনাবল করুন ও SSL সার্টিফিকেট যুক্ত করুন:
```bash
sudo ln -s /etc/nginx/sites-available/taallumbd.com /etc/nginx/sites-enabled/
sudo nginx -t && sudo systemctl reload nginx
sudo certbot --nginx -d taallumbd.com -d www.taallumbd.com
```

### ধাপ ৪: Supervisor কিউ ওয়ার্কার কনফিগারেশন (`/etc/supervisor/conf.d/taallum-worker.conf`)
```ini
[program:taallum-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/taallumbd/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/taallumbd/storage/logs/worker.log
stopwaitsecs=3600
```
সক্রিয় করুন:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start all
```

### ধাপ ৫: Crontab শিডিউলার (লারাভেল ক্রন)
```bash
sudo crontab -u www-data -e
# নিচের লাইনটি যুক্ত করুন:
* * * * * cd /var/www/taallumbd && php artisan schedule:run >> /dev/null 2>&1
```

---

## ৫. ডেটা মাইগ্রেশন রানবুক (Neon থেকে VPS Postgres-এ ডেটা আনা)

1. **ব্যাকআপ তৈরি করুন (Neon)**:
   ```bash
   pg_dump "postgres://neondb_owner:PASSWORD@ep-fragrant-heart-azvj2tgt.c-3.ap-southeast-1.aws.neon.tech/neondb?sslmode=require" | gzip > neon_backup.sql.gz
   ```
2. **ব্যাকআপ স্থানান্তর করুন (VPS-এ)**:
   ```bash
   scp neon_backup.sql.gz user@YOUR_VPS_IP:/tmp/
   ```
3. **VPS PostgreSQL-এ রিস্টোর করুন**:
   ```bash
   gunzip -c /tmp/neon_backup.sql.gz | psql -U taallum_user -d taallumbd
   ```
4. **টেবিলের রো কাউন্ট যাচাই করুন**:
   ```bash
   php artisan tinker --execute="echo 'Users: '.App\Models\User::count().' | Orders: '.App\Models\Order::count().' | Payments: '.App\Models\Payment::count().PHP_EOL;"
   ```
5. **DNS সুইপ করুন**:
   - Cloudflare-এ `@` রেকর্ডের টার্গেট Render থেকে বদলে VPS-এর পাবলিক আইপি (`A` Record) বসিয়ে দিন।
