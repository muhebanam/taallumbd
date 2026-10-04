# ডেটাবেজ ব্যাকআপ ও রিস্টোর নির্দেশিকা (Database Backup & Disaster Recovery Guide)

আত-তাআল্লুম (TaallumBD) প্ল্যাটফর্মের ডেটাবেজ ব্যাকআপ ও বিপর্যয় পুনরুদ্ধার (Disaster Recovery) নির্দেশিকা।

---

## ১. স্বয়ংক্রিয় ব্যাকআপ (GitHub Actions)
- প্রতিদিন রাত ০৮:০০ (বাংলাদেশ সময় / 02:00 UTC)-এ `.github/workflows/db-backup.yml` স্বয়ংক্রিয়ভাবে চলে।
- `pg_dump` কমান্ড দিয়ে সম্পূর্ণ স্কিমা ও ডেটা `gzip` কম্প্রেস করে ব্যাকআপ তৈরি হয়।

---

## ২. ম্যানুয়াল ব্যাকআপ তৈরি (Manual Backup)
যেকোনো বড় আপগ্রেড বা মাইগ্রেশনের আগে ম্যানুয়াল ব্যাকআপ তৈরি করুন:
```bash
# Neon PostgreSQL ডাম্প তৈরি
pg_dump "postgres://neondb_owner:YOUR_PASSWORD@ep-fragrant-heart-azvj2tgt.c-3.ap-southeast-1.aws.neon.tech/neondb?sslmode=require" | gzip > backup_$(date +%Y%m%d_%H%M%S).sql.gz
```

---

## ৩. ডেটাবেজ পুনরুদ্ধার ড্রিল (Restore Drill)

### ক. লোকাল Docker PostgreSQL-এ রিস্টোর:
```bash
# ১. ব্যাকআপ ফাইল আনজিপ করুন
gunzip -k backup_20261004.sql.gz

# ২. লোকাল ডকার কন্টেইনারে রিস্টোর করুন
docker exec -i taallumbd-postgres-1 psql -U postgres -d taallumbd < backup_20261004.sql
```

### খ. নতুন Neon ডেটাবেজে রিস্টোর:
```bash
gunzip -c backup_20261004.sql.gz | psql "postgres://neondb_owner:YOUR_PASSWORD@ep-new-endpoint.neon.tech/neondb?sslmode=require"
```

---

## ৪. যাচাইকরণ চেকলিস্ট
রিস্টোরের পর নিচের টেবিলগুলো যাচাই করুন:
- `users`: ইউজার অ্যাকাউন্ট ও রোলসমূহ
- `courses` ও `curriculum_items`: কোর্স এবং পাঠ্যক্রম
- `orders`, `payments` ও `payment_transactions`: আর্থিক হিসাব ও লেনদেন
- `teachers` ও `fatawa`: স্কলার ও ফতোয়া তালিকা
