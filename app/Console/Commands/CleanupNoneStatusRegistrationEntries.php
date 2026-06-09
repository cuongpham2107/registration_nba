<?php

namespace App\Console\Commands;

use App\Models\RegistrationEntry;
use Illuminate\Console\Command;

class CleanupNoneStatusRegistrationEntries extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:cleanup-none-status-registration-entries';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Xoá các bản ghi đăng ký có trạng thái none của ngày hôm qua (chạy lúc 6h sáng)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $todayStart = now()->startOfDay();

        // Xoá các bản ghi có status = 'none' và start_date (ngày vào dự kiến) trước hôm nay
        // Giữ lại những bản ghi có start_date từ hôm nay trở đi (còn thời gian để xử lý)
        $deleted = RegistrationEntry::where('status', 'none')
            ->where('start_date', '<', $todayStart)
            ->delete();

        $this->info("Đã xóa {$deleted} bản ghi RegistrationEntry có trạng thái 'none' và start_date trước ngày hôm nay");
    }
}
