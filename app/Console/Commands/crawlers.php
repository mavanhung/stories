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
        // https://phongreviews.com/sua-rua-mat-sk-ii/    click
        // https://phongreviews.com/son-espoir/   rutgon
        // https://phongreviews.com/laptop-acer/   tiki.vn
        // https://phongreviews.com/tai-nghe-nhet-tai/   NO
        // https://phongreviews.com/tai-nghe-chup-tai/
        // https://phongreviews.com/ban-phim-co-gia-re/
        // https://phongreviews.com/may-duoi-chuot/
        // https://phongreviews.com/loa-hat-karaoke-di-dong/
        // https://phongreviews.com/may-ghi-am-tot/
        // https://phongreviews.com/bo-kich-song-wifi-nao-tot/
        // https://phongreviews.com/may-in-mau-nao-tot/
        // https://phongreviews.com/workstation-laptop-tot-nhat/
        // https://phongreviews.com/nen-mua-may-doc-sach-nao/
        // https://phongreviews.com/bo-phat-wifi-3g-4g-nao-tot/
        // https://phongreviews.com/nen-mua-android-tv-box-nao-tot-nhat/

        // https://phongreviews.com/may-rua-mat-emmie/ shorten.asia

        //ERROR
        // https://phongreviews.com/tu-dong-mini/
        // https://phongreviews.com/may-chieu-mini/

        //Cần xem xét lại hình ảnh và btn
        // https://phongreviews.com/may-chay-bo-impulse


        $this->crawlersPhongReviews();
        // $this->crawlersTrustReview();
        // $this->crawlersPhongReviewsDetail(32, 'https://phongreviews.com/dung-cu-tap-yoga/', 'https://phongreviews.com/wp-content/uploads/2022/06/dung-cu-tap-yoga-336x220.jpg');
        return 0;
    }
}
