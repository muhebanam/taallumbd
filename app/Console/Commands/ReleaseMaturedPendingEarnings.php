<?php

namespace App\Console\Commands;

use App\Services\TeacherWalletService;
use Illuminate\Console\Command;

class ReleaseMaturedPendingEarnings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wallet:release-pending';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Release matured pending wallet earnings into available balance after the escrow hold window';

    /**
     * Execute the console command.
     */
    public function handle(TeacherWalletService $walletService): int
    {
        $this->info('Scanning for matured pending wallet earnings...');

        $count = $walletService->releaseMaturedPendingBalances();

        $this->info("Successfully released {$count} matured pending transactions into available balance.");

        return Command::SUCCESS;
    }
}
