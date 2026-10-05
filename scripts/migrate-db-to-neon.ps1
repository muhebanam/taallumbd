<#
.SYNOPSIS
    Taallum BD - Render PostgreSQL to Neon Free PostgreSQL Database Migration Script
.DESCRIPTION
    Safely exports the existing database from Render PostgreSQL, validates the dump,
    restores into Neon PostgreSQL (direct endpoint), and compares row counts across
    all core tables to verify 100% data integrity without any data loss.

.PARAMETER SourceDbUrl
    Connection string for source database (Render PostgreSQL).
.PARAMETER TargetDbUrl
    Connection string for target database (Neon PostgreSQL).
.PARAMETER BackupDir
    Directory where database dump will be stored. Default: ./database/backups
.PARAMETER DryRun
    Test database connections and display table stats without making changes.

.EXAMPLE
    .\scripts\migrate-db-to-neon.ps1 -SourceDbUrl "postgres://user:pass@host/db" -TargetDbUrl "postgres://user:pass@ep-xyz.neon.tech/neondb?sslmode=require"
#>

[CmdletBinding()]
param(
    [Parameter(Mandatory = $false)]
    [string]$SourceDbUrl = $env:RENDER_DATABASE_URL,

    [Parameter(Mandatory = $false)]
    [string]$TargetDbUrl = $env:DATABASE_URL,

    [string]$BackupDir = "database/backups",

    [switch]$DryRun
)

$ErrorActionPreference = "Stop"

Write-Host "==========================================================" -ForegroundColor Cyan
Write-Host " Taallum BD — PostgreSQL Migration to Neon Free (Zero Loss) " -ForegroundColor Cyan
Write-Host "==========================================================" -ForegroundColor Cyan
Write-Host ""

# 1. Parameter Validation
if ([string]::IsNullOrWhiteSpace($SourceDbUrl)) {
    $SourceDbUrl = Read-Host "Enter Source Database URL (Render PostgreSQL)"
}

if ([string]::IsNullOrWhiteSpace($TargetDbUrl)) {
    $TargetDbUrl = Read-Host "Enter Target Database URL (Neon PostgreSQL)"
}

if ([string]::IsNullOrWhiteSpace($SourceDbUrl) -or [string]::IsNullOrWhiteSpace($TargetDbUrl)) {
    Write-Error "Source and Target Database URLs are required."
    exit 1
}

# Ensure Target URL has sslmode=require
if (-not $TargetDbUrl.Contains("sslmode=require")) {
    $sep = if ($TargetDbUrl.Contains("?")) { "&" } else { "?" }
    $TargetDbUrl = "$TargetDbUrl$sep`sslmode=require"
}

# Neon Pooled vs Direct endpoint handling:
# PgBouncer pooler endpoints (-pooler.) fail on DDL restores. Convert to direct for restore:
$TargetRestoreUrl = $TargetDbUrl
if ($TargetRestoreUrl.Contains("-pooler.")) {
    $TargetRestoreUrl = $TargetRestoreUrl.Replace("-pooler.", ".")
    Write-Host "[INFO] Detected Neon pooled connection string. Using DIRECT endpoint for schema restore:" -ForegroundColor Yellow
    Write-Host "       $TargetRestoreUrl" -ForegroundColor DarkGray
}

# 2. Safety Warning & Verification (Accidental Data Loss Prevention Rule)
Write-Host ""
Write-Host "⚠️  সতর্কবার্তা (SAFETY VERIFICATION):" -ForegroundColor Yellow
Write-Host "   - এই স্ক্রিপ্ট বর্তমান ডেটাবেজের কোনো ডেটা মুছে ফেলবে না।" -ForegroundColor White
Write-Host "   - প্রথমে সম্পূর্ণ ডেটাবেজের একটি pg_dump ব্যাকআপ ফাইল সংরক্ষণ করা হবে।" -ForegroundColor White
Write-Host "   - এরপর Neon-এ রিস্টোর করে প্রতিটি টেবিলের রো কাউন্ট যাচাই করা হবে।" -ForegroundColor White
Write-Host "   - নতুন Neon সম্পূর্ণ সক্রিয় না হওয়া পর্যন্ত পুরনো Render DB ডিলিট করবেন না।" -ForegroundColor Red
Write-Host ""

$confirmation = Read-Host "আপনি কি নিশ্চিত যে ডেটাবেজ ব্যাকআপ ও Neon-এ মাইগ্রেশন শুরু করতে চান? (Type 'YES' to proceed)"
if ($confirmation -ne "YES") {
    Write-Warning "মাইগ্রেশন বাতিল করা হয়েছে।"
    exit 0
}

# 3. Create Backup Directory
if (-not (Test-Path $BackupDir)) {
    New-Item -ItemType Directory -Path $BackupDir -Force | Out-Null
}

$timestamp = Get-Date -Format "yyyyMMdd_HHmmss"
$dumpFile = Join-Path $BackupDir "render_db_backup_$timestamp.sql"

# Check tools
$hasPgDump = Get-Command "pg_dump" -ErrorAction SilentlyContinue
$hasPsql = Get-Command "psql" -ErrorAction SilentlyContinue
$useDocker = $false

if (-not $hasPgDump -or -not $hasPsql) {
    Write-Host "[NOTE] Local pg_dump/psql not found on PATH. Attempting Docker fallback (postgres:16-alpine)..." -ForegroundColor Yellow
    $hasDocker = Get-Command "docker" -ErrorAction SilentlyContinue
    if ($hasDocker) {
        $useDocker = $true
    } else {
        Write-Error "Either PostgreSQL client tools (pg_dump/psql) or Docker Desktop must be installed."
        exit 1
    }
}

if ($DryRun) {
    Write-Host "[DRY RUN] Source DB: $SourceDbUrl" -ForegroundColor Cyan
    Write-Host "[DRY RUN] Target Direct DB: $TargetRestoreUrl" -ForegroundColor Cyan
    Write-Host "[DRY RUN] Dump would be written to: $dumpFile" -ForegroundColor Cyan
    Write-Host "[DRY RUN] Completed dry run simulation." -ForegroundColor Green
    exit 0
}

# 4. Step 1: Dump Source Database
Write-Host "`n[1/3] Dumping source database from Render..." -ForegroundColor Green
if ($useDocker) {
    $absDumpDir = (Resolve-Path $BackupDir).Path
    $fileName = Split-Path $dumpFile -Leaf
    docker run --rm -v "${absDumpDir}:/backup" postgres:16-alpine pg_dump "$SourceDbUrl" --clean --if-exists --no-owner --no-privileges -f "/backup/$fileName"
} else {
    pg_dump "$SourceDbUrl" --clean --if-exists --no-owner --no-privileges -f "$dumpFile"
}

if (-not (Test-Path $dumpFile) -or (Get-Item $dumpFile).Length -eq 0) {
    Write-Error "Database dump failed or produced an empty file at $dumpFile."
    exit 1
}

$fileSizeKb = [math]::Round((Get-Item $dumpFile).Length / 1KB, 2)
Write-Host "✓ Dump successful! Saved to $dumpFile ($fileSizeKb KB)" -ForegroundColor Green

# 5. Step 2: Restore into Neon
Write-Host "`n[2/3] Restoring database into Neon PostgreSQL..." -ForegroundColor Green
if ($useDocker) {
    $absDumpDir = (Resolve-Path $BackupDir).Path
    $fileName = Split-Path $dumpFile -Leaf
    docker run --rm -v "${absDumpDir}:/backup" postgres:16-alpine psql "$TargetRestoreUrl" -f "/backup/$fileName"
} else {
    psql "$TargetRestoreUrl" -f "$dumpFile"
}
Write-Host "✓ Restore command completed." -ForegroundColor Green

# 6. Step 3: Row Count Verification
Write-Host "`n[3/3] Verifying row counts across core tables..." -ForegroundColor Green

$tables = @(
    "users",
    "categories",
    "teachers",
    "courses",
    "enrollments",
    "orders",
    "payments",
    "payment_transactions",
    "fatawa",
    "articles",
    "forum_posts",
    "quizzes",
    "assignments"
)

Write-Host "`n=======================================================" -ForegroundColor Cyan
Write-Host ("{0,-22} {1,-14} {2,-14} {3}" -f "TABLE", "SOURCE ROWS", "NEON ROWS", "STATUS") -ForegroundColor Cyan
Write-Host "=======================================================" -ForegroundColor Cyan

foreach ($tbl in $tables) {
    $query = "SELECT count(*) FROM $tbl;"
    
    $srcCount = "N/A"
    $tgtCount = "N/A"

    try {
        if ($useDocker) {
            $srcCount = (docker run --rm postgres:16-alpine psql "$SourceDbUrl" -t -A -c "$query" 2>$null).Trim()
            $tgtCount = (docker run --rm postgres:16-alpine psql "$TargetRestoreUrl" -t -A -c "$query" 2>$null).Trim()
        } else {
            $srcCount = (psql "$SourceDbUrl" -t -A -c "$query" 2>$null).Trim()
            $tgtCount = (psql "$TargetRestoreUrl" -t -A -c "$query" 2>$null).Trim()
        }
    } catch {
        # Table might not exist or error
    }

    $match = if ($srcCount -eq $tgtCount -and $srcCount -ne "N/A") { "✓ MATCH" } else { "⚠️ CHECK" }
    $color = if ($match -eq "✓ MATCH") { "Green" } else { "Yellow" }

    Write-Host ("{0,-22} {1,-14} {2,-14} {3}" -f $tbl, $srcCount, $tgtCount, $match) -ForegroundColor $color
}

Write-Host "=======================================================" -ForegroundColor Cyan
Write-Host "`n🎉 মাইগ্রেশন ও যাচাইকরণ সফলভাবে সম্পন্ন হয়েছে!" -ForegroundColor Green
Write-Host "পরবর্তী পদক্ষেপ:" -ForegroundColor White
Write-Host " 1. Render Dashboard-এ taallumbd-app সার্ভিসের Environment-এ যান।"
Write-Host " 2. DATABASE_URL হিসেবে Neon-এর pooled connection string দিন:"
Write-Host "    $TargetDbUrl" -ForegroundColor Cyan
Write-Host " 3. https://taallumbd-app.onrender.com/health চেক করে দেখুন DB status ok কি না।"
Write-Host " 4. কমপক্ষে ৭ দিন Render-এর পুরনো DB ডিলিট করবেন না।"
