<?php

namespace App\Console\Commands;

use App\Helpers\Functions;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class crawlers extends Command
{
    use Functions;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:crawlers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crawlers data website';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // $storagePath = 'crawlers/binh-sua-pigeon-1.jpg';
        // $data = $this->get_file_curl('https://phongreviews.com/wp-content/uploads/2021/07/binh-sua-pigeon-1.jpg', 'binh-sua-pigeon-1');
        // Storage::disk('public')->put($storagePath, $data);
        $this->crawlersPhongReviews();
        // $this->crawlersPhongReviewsDetail(22, 'https://phongreviews.com/may-scan-hp/', 'https://phongreviews.com/wp-content/uploads/2021/08/may-scan-hp-8-1536x960.jpg');
        return 0;
    }
}
