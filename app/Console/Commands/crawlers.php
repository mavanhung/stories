<?php

namespace App\Console\Commands;

use App\Helpers\Functions;
use Botble\Blog\Models\Post;
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
    protected $signature = 'command:crawlers {website}';

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
        $posts = Post::where('website', 'tuvanmuasam.com')->select('id', 'content')->get();
        $count = 0;
        $ids = [];
        foreach ($posts as $post) {
            $content = $post->content;
            $check = strpos($content, 'Tư Vấn Mua Sắm');
            if(!($check === false)){
                $ids[] = $post->id;
                $count++;
                $search = ['Tư Vấn Mua Sắm', 'Tư vấn mua sắm'];
                $replace = ['XoaiChua', 'XoaiChua'];
                $result = str_replace($search, $replace, $content);
                $post->update([
                    'content' => $result
                ]);
            }
        }
        $this->sendNotificationTelegram(implode(', ', $ids));
        dump($count, $ids);

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

        //Lỗi phongreviews crawler ngày 10/03/2023 danh mục mẹ và bé
        // https://phongreviews.com/sua-non/
        // https://phongreviews.com/sua-tam-cho-be/



        // $website = $this->argument('website');
        // if($website == 'phongreviews'){
        //     $this->crawlersPhongReviews();
        // }
        // if($website == 'trustreview'){
        //     $this->crawlersTrustReview();
        // }
        // if($website == 'tuvanmuasam'){
        //     // https://tuvanmuasam.com/co-rua-binh-sua
        //     // https://tuvanmuasam.com/gang-tay-tap-gym
        //     // https://tuvanmuasam.com/kem-tri-tham-mong  //có chứa thẻ a khác
        //     // https://tuvanmuasam.com/dau-goi-phu-bac //có link khác
        //     // https://tuvanmuasam.com/chuot-bay-tot-nhat
        //     // url: https://tuvanmuasam.com/usb-wifi
        //     // https://tuvanmuasam.com/tong-do-cat-toc-cho-be //sai url

        //     //////////// Những link có chứa shorten.asia ///////////
        //     // https://tuvanmuasam.com/may-rua-mat
        //     // https://tuvanmuasam.com/collagen-nhat-tot-nhat
        //     // https://tuvanmuasam.com/coc-nguyet-san
        //     // https://tuvanmuasam.com/dai-nit-bung
        //     // https://tuvanmuasam.com/tinh-dau-toi
        //     // https://tuvanmuasam.com/bot-can-tay-giam-can
        //     // https://tuvanmuasam.com/dau-goi-phu-bac
        //     // https://tuvanmuasam.com/may-massage-chan
        //     // https://tuvanmuasam.com/noi-chien-hoi-nuoc
        //     // https://tuvanmuasam.com/rong-bien-ngon-nhat
        //     // https://tuvanmuasam.com/may-rua-mat-halio
        //     // https://tuvanmuasam.com/khau-trang-wakamono
        //     // https://tuvanmuasam.com/review-rong-nho-yukibudo
        //     // https://tuvanmuasam.com/dau-goi-phu-bac-sin-hair
        //     // https://tuvanmuasam.com/dau-goi-dego-pharma
        //     // https://tuvanmuasam.com/noi-chien-khong-dau-lotte
        //     // https://tuvanmuasam.com/rong-nho-sabudo
        //     // https://tuvanmuasam.com/coc-nguyet-san-ovacup
        //     // https://tuvanmuasam.com/coc-nguyet-san-beucup
        //     // https://tuvanmuasam.com/tien-dinh-khang
        //     // https://tuvanmuasam.com/may-tam-nuoc-iris-care
        //     // https://tuvanmuasam.com/sua-non-colomi
        //     // https://tuvanmuasam.com/com-tri-nao-gbrain

        //     // https://tuvanmuasam.com/chuot-bay-tot-nhat //có link google search

        //     //https://tuvanmuasam.com/kem-chong-nang-han-quoc //Lỗi hình ảnh
        //     //https://tuvanmuasam.com/keo-dan-go //Lỗi hình ảnh
        //     //https://tuvanmuasam.com/android-tv-box //Lỗi hình ảnh
        //     //https://tuvanmuasam.com/quat-dieu-hoa-sunhouse //Lỗi hình ảnh
        //     //https://tuvanmuasam.com/bang-ve-dien-tu //Lỗi hình ảnh

        //     $this->crawlersTuVanMuaSam();
        // }
        // if($website == 'removefolder'){
        //     $this->removefolder();
        // }
        // $this->crawlersPhongReviewsDetail(19, 'https://phongreviews.com/thuoc-nhuom-toc-han-quoc/', 'https://phongreviews.com/wp-content/uploads/2022/02/thuoc-nhuom-toc-han-quoc-0-1024x841.jpg');
        // $this->crawlersTrustReviewDetail(19, 'https://trustreview.vn/noi-chien-khong-dau-loai-nao-tot.html', 'https://trustreview.vn/wp-content/uploads/2020/11/noi-chien-khong-dau-loai-nao-tot-11-1-300x171.jpg');
        // $this->crawlersTuVanMuaSamDetail(19, 'https://tuvanmuasam.com/quay-cui-cho-be', 'https://tuvanmuasam.com/wp-content/uploads/2023/03/quay-cui-cho-be-300x240.jpg');
        return 0;
    }
}
