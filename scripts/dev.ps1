<#
.SYNOPSIS
    Taallum BD Development Environment Helper Script (Docker Compose)
.DESCRIPTION
    Manages local Docker Compose services, Laravel artisan, Composer, NPM,
    database migrations, and test runner without requiring PHP/Node on Windows host.
#>

[CmdletBinding()]
param(
    [Parameter(Position = 0, Mandatory = $false)]
    [ValidateSet('up', 'down', 'artisan', 'composer', 'npm', 'test', 'fresh', 'status', 'logs', 'help')]
    [string]$Command = 'help',

    [Parameter(Position = 1, ValueFromRemainingArguments = $true)]
    [string[]]$RemainingArgs
)

function Write-BrandHeader {
    Write-Host ""
    Write-Host "==============================================================" -ForegroundColor DarkCyan
    Write-Host "   Taallum BD - Docker Dev Environment" -ForegroundColor Cyan
    Write-Host "==============================================================" -ForegroundColor DarkCyan
    Write-Host ""
}

function Test-DockerEnvironment {
    $dockerCmd = Get-Command docker -ErrorAction SilentlyContinue
    if (-not $dockerCmd) {
        Write-Host "[ERROR] Docker is not installed or not in PATH!" -ForegroundColor Red
        Write-Host "Please install Docker Desktop: https://www.docker.com/products/docker-desktop/" -ForegroundColor Yellow
        exit 1
    }

    $dockerInfo = docker info 2>&1
    if ($LASTEXITCODE -ne 0) {
        Write-Host "[ERROR] Docker Desktop daemon is not running!" -ForegroundColor Red
        Write-Host "Please start Docker Desktop from the Start Menu and wait until it is ready." -ForegroundColor Yellow
        exit 1
    }
}

function Show-Help {
    Write-BrandHeader
    Write-Host "Usage: .\scripts\dev.ps1 [command] [arguments]" -ForegroundColor White
    Write-Host ""
    Write-Host "Commands:" -ForegroundColor Yellow
    Write-Host "  up        Start Docker containers in background (app, postgres, redis, mailpit, node)" -ForegroundColor Green
    Write-Host "  down      Stop running Docker containers" -ForegroundColor Green
    Write-Host "  artisan   Execute php artisan in app container (e.g., .\scripts\dev.ps1 artisan route:list)" -ForegroundColor Green
    Write-Host "  composer  Execute composer in app container (e.g., .\scripts\dev.ps1 composer install)" -ForegroundColor Green
    Write-Host "  npm       Execute npm in node container (e.g., .\scripts\dev.ps1 npm run build)" -ForegroundColor Green
    Write-Host "  test      Run tests inside container (e.g., .\scripts\dev.ps1 test)" -ForegroundColor Green
    Write-Host "  fresh     Run migrate:fresh --seed on local development database" -ForegroundColor Green
    Write-Host "  status    Show status of running containers (docker compose ps)" -ForegroundColor Green
    Write-Host "  logs      Show live logs (docker compose logs -f)" -ForegroundColor Green
    Write-Host "  help      Show this help information" -ForegroundColor Green
    Write-Host ""
}

switch ($Command) {
    'up' {
        Test-DockerEnvironment
        Write-BrandHeader
        Write-Host "Starting Docker Compose services..." -ForegroundColor Cyan
        & docker compose up -d $RemainingArgs
        if ($LASTEXITCODE -eq 0) {
            Write-Host ""
            Write-Host "[SUCCESS] Services started successfully!" -ForegroundColor Green
            Write-Host " - Web App:    http://localhost:8000" -ForegroundColor Cyan
            Write-Host " - Vite HMR:   http://localhost:5173" -ForegroundColor Cyan
            Write-Host " - Mailpit UI: http://localhost:8025" -ForegroundColor Cyan
            Write-Host " - PostgreSQL: localhost:5432 (taallumbd_dev / postgres)" -ForegroundColor Cyan
            Write-Host " - Redis:      localhost:6379" -ForegroundColor Cyan
            Write-Host ""
        }
    }

    'down' {
        Test-DockerEnvironment
        Write-BrandHeader
        Write-Host "Stopping Docker Compose services..." -ForegroundColor Yellow
        & docker compose down $RemainingArgs
    }

    'artisan' {
        Test-DockerEnvironment
        if (-not $RemainingArgs) {
            Write-Host "[ERROR] Please provide artisan arguments (e.g., .\scripts\dev.ps1 artisan migrate)" -ForegroundColor Red
            exit 1
        }
        & docker compose exec app php artisan @RemainingArgs
    }

    'composer' {
        Test-DockerEnvironment
        if (-not $RemainingArgs) {
            & docker compose exec app composer
        } else {
            & docker compose exec app composer @RemainingArgs
        }
    }

    'npm' {
        Test-DockerEnvironment
        if (-not $RemainingArgs) {
            & docker compose exec node npm
        } else {
            & docker compose exec node npm @RemainingArgs
        }
    }

    'test' {
        Test-DockerEnvironment
        Write-Host "Running tests inside container..." -ForegroundColor Cyan
        & docker compose exec app php artisan test @RemainingArgs
    }

    'fresh' {
        Test-DockerEnvironment
        Write-BrandHeader
        Write-Host "[WARNING] This will wipe the local database and run fresh migrations with seeders!" -ForegroundColor Yellow
        $confirm = Read-Host "Are you sure? (y/N)"
        if ($confirm -match '^[yY]') {
            Write-Host "Resetting and seeding local database..." -ForegroundColor Cyan
            & docker compose exec app php artisan migrate:fresh --seed
        } else {
            Write-Host "Operation cancelled." -ForegroundColor Gray
        }
    }

    'status' {
        Test-DockerEnvironment
        & docker compose ps $RemainingArgs
    }

    'logs' {
        Test-DockerEnvironment
        & docker compose logs -f @RemainingArgs
    }

    Default {
        Show-Help
    }
}
