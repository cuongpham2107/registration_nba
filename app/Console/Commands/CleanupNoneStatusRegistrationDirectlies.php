<?php

namespace App\Console\Commands;

use App\Models\RegisterDirectly;
use Illuminate\Console\Command;

class CleanupNoneStatusRegistrationDirectlies extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:cleanup-none-status-registration-directlies';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Xoá các bản ghi đăng kí có trạng thái chờ duyệt mà quá 1 tiếng mà không chuyển trạng thái';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // lấy bản ghi có status = none,created_at trong ngày hôm nay và created_at < 1 tiếng
        $deleted = RegisterDirectly::where('status', 'none')
            ->where('type', 'vehicle')
            ->where('created_at', '<=', now()->subHour())
            ->delete();

        $this->info("Đã xóa {$deleted} bản ghi có trạng thái 'Chờ duyệt'");
    }
}
