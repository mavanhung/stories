<?php

namespace App\Console\Commands;

use Botble\Tiki\Models\Seller;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Botble\Tiki\Models\DiscountCode;
use Illuminate\Support\Facades\Storage;

class tikiDiscountCode extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:tiki-discount-code';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tiki discount code';

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
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://tiki.vn/khuyen-mai/ma-giam-gia',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        $arr = explode('<script id="__NEXT_DATA__" type="application/json">', $response);

        if(count($arr) > 0){
            $arr = explode('</script>', $arr[1]);
            if (count($arr) > 0) {
                $data = json_decode($arr[0], true)['props']['initialProps']['pageProps']['pageData']['pages'][0]['widgets'];
                $codes = [];
                for ($i=0; $i < count($data); $i++) {
                    $v = $data[$i];
                    if($v['type'] == 'COUPON') {
                        $payload = json_decode($data[$i]['payload'], true)['codes'];
                        $codes = array_merge($codes, $payload);
                    }
                }
                for ($i=0; $i < count($codes); $i++) {
                    $this->getCodeDetail($codes[$i]);
                    // if('JAN300PXD' == $codes[$i]) {
                    //     dd('have');
                    // }
                }
            }
        }

        return 0;
    }

    public function sendNotificationTelegram($message = '')
    {
        $client = new Client();
        $url = 'https://api.telegram.org/bot'.DefineCode::TELEGRAM_BOT_TOKEN.'/sendMessage?chat_id='.DefineCode::TELEGRAM_CHAT_ID.'&text='.$message;
        $client->request('GET', $url);
    }

    public function myUrlEncode($string)
    {
        $entities = array('%21', '%2A', '%27', '%28', '%29', '%3B', '%3A', '%40', '%26', '%3D', '%2B', '%24', '%2C', '%2F', '%3F', '%25', '%23', '%5B', '%5D');
        $replacements = array('!', '*', "'", "(", ")", ";", ":", "@", "&", "=", "+", "$", ",", "/", "?", "%", "#", "[", "]");
        return str_replace($entities, $replacements, urlencode($string));
    }

    public function saveImage($url, $storagePath)
    {
        //$url: đường dẫn hình ảnh cần tải về
        //$storagePath : đường dẫn thư mục sẽ lưu hình ảnh tải về (vd: crawlers/binh-sua-pigeon-1.jpg)
        try {
            ini_set("memory_limit", "-1");
            set_time_limit(0);
            ini_set('max_execution_time', 0);
            ignore_user_abort(true);
            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => $this->myUrlEncode($url),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
            ]);
            $response = curl_exec($curl);
            curl_close($curl);
            Storage::disk('public')->put($storagePath, $response);
            return true;
        } catch (\Throwable $th) {
            $message = 'Có lỗi xảy ra: '.$th->getMessage().', file: '.$th->getFile().', dòng: '.$th->getLine();
            $this->error($message);
            Log::channel('Tiki Crawlers')->error([
                $th->getMessage(),
                $th->getFile(),
                $th->getLine()
            ]);
            $this->sendNotificationTelegram($message);
        }
    }

    public function saveSeller($data)
    {
        try {
            DB::beginTransaction();
            $seller = Seller::where('seller_id', $data['seller_id'])->first();

            //Tải và lưu logo
            if(!blank($data['logo'])){
                $logoName = array_reverse(explode ('/', $data['logo']))[0];
                $storagePath = 'tiki/seller/logo/'.$logoName;
                $this->saveImage($data['logo'], $storagePath);
            }

            if(blank($seller)) {
                Seller::create([
                    'name' => $data['name'] ?? null,
                    'seller_name' => $data['seller_name'],
                    'store_name' => $data['store_name'] ?? null,
                    'seller_id' => $data['seller_id'],
                    'store_id' => $data['store_id'] ?? null,
                    'store_level' => $data['store_level'] ?? null,
                    'seller_type' => $data['seller_type'] ?? null,
                    'storefront_label' => $data['storefront_label'] ?? null,
                    'logo' => $storagePath ?? null,
                    'seller_url' => $data['seller_url'] ?? null,
                    'url_slug' => $data['url_slug'] ?? null,
                    'live_at' => $data['live_at'] ?? null,
                    // 'status' => 'pending',
                    'status' => 'published',
                ]);
            }else if(!blank($seller)) {
                $seller->update([
                    'name' => $data['name'] ?? null,
                    'seller_name' => $data['seller_name'],
                    'store_name' => $data['store_name'] ?? null,
                    'store_id' => $data['store_id'] ?? null,
                    'store_level' => $data['store_level'] ?? null,
                    'seller_type' => $data['seller_type'] ?? null,
                    'storefront_label' => $data['storefront_label'] ?? null,
                    'logo' => $storagePath ?? null,
                    'seller_url' => $data['seller_url'] ?? null,
                    'url_slug' => $data['url_slug'] ?? null,
                    'live_at' => $data['live_at'] ?? null,
                ]);
            }
            DB::commit();
            return true;
        } catch (\Throwable $th) {
            $message = 'Có lỗi xảy ra: '.$th->getMessage().', file: '.$th->getFile().', dòng: '.$th->getLine();
            $this->error($message);
            Log::channel('Tiki Crawlers')->error([
                $th->getMessage(),
                $th->getFile(),
                $th->getLine()
            ]);
            $this->sendNotificationTelegram($message);
        }
    }

    public function sellerDetail($url)
    {
        try {
            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
            ));

            $response = curl_exec($curl);

            curl_close($curl);

            $arr = explode('<script id="__NEXT_DATA__" type="application/json">', $response);

            if(count($arr) > 0){
                $arr = explode('</script>', $arr[1]);
                if (count($arr) > 0) {
                    if(!empty(json_decode($arr[0], true)['props']['initialState']['desktop']['sellerStore']['seller'])) {
                        $data = json_decode($arr[0], true)['props']['initialState']['desktop']['sellerStore']['seller'];
                        $data['seller_url'] = $url;
                        $this->saveSeller($data);
                        dump('Tạo mới seller: '.$url);
                    }
                }
            }
        } catch (\Throwable $th) {
            $message = 'Có lỗi xảy ra: '.$th->getMessage().', file: '.$th->getFile().', dòng: '.$th->getLine();
            $this->error($message);
            Log::channel('Tiki Crawlers')->error([
                $th->getMessage(),
                $th->getFile(),
                $th->getLine()
            ]);
            $this->sendNotificationTelegram($message);
        }

    }

    public function getCodeDetail($code)
    {
        try {
            $url = 'https://tiki.vn/api/v2/events/coupon/v2?codes='.$code;
            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
            ));

            $response = curl_exec($curl);
            curl_close($curl);

            if(!empty(json_decode($response, true)['data'][0])) {
                $response = json_decode($response, true)['data'][0];
                if($response['status'] == 'active') {
                    // dump($response);
                    $this->saveDiscountCode($response);
                }
            }
        } catch (\Throwable $th) {
            $message = 'Có lỗi xảy ra: '.$th->getMessage().', file: '.$th->getFile().', dòng: '.$th->getLine();
            $this->error($message);
            Log::channel('Tiki Crawlers')->error([
                $th->getMessage(),
                $th->getFile(),
                $th->getLine()
            ]);
            $this->sendNotificationTelegram($message);
        }
    }

    public function saveDiscountCode($data)
    {
        try {
            DB::beginTransaction();
            $seller = Seller::where('seller_id', $data['seller_id'])->first();

            //Kiểm tra nếu chưa có cửa hàng và seller_id != 0 thì tạo mới cửa hàng (seller_id = 0 là tiki)
            if(blank($seller) && $data['seller_id'] != 0) {
                $this->sellerDetail($data['url']);
            }

            //Tải và lưu icon mã giảm giá
            if(!blank($data['icon_url'])){
                $iconName = array_reverse(explode ('/', $data['icon_url']))[0];
                $storagePath = 'tiki/discount-code/icon/'.$iconName;
                $this->saveImage($data['icon_url'], $storagePath);
            }

            //Kiểm tra discount code đã tồn tại hay chưa
            $discountCode = DiscountCode::where('seller_id', $data['seller_id'])->where('coupon_code', $data['coupon_code'])->first();

            if(blank($discountCode)) {
                DiscountCode::create([
                    'seller_id' => $data['seller_id'],
                    'coupon_id' => $data['coupon_id'],
                    'coupon_code' => $data['coupon_code'],
                    'label' => $data['label'],
                    'tags' => json_encode($data['tags']),
                    'short_title' => $data['short_title'],
                    'period' => $data['period'],
                    'simple_action' => $data['simple_action'],
                    'coupon_type' => $data['coupon_type'],
                    'discount_amount' => $data['discount_amount'],
                    'min_amount' => $data['min_amount'],
                    'rule_id' => $data['rule_id'],
                    'short_description' => $data['short_description'],
                    'long_description' => $data['long_description'],
                    'expired_at' => date("Y-m-d H:i:s", $data['expired_at']),
                    'icon_url' => $storagePath,
                    'is_crawler_home' => 1,
                    'status' => 'published'
                ]);
            }else {
                $discountCode->update([
                    'coupon_id' => $data['coupon_id'],
                    'label' => $data['label'],
                    'tags' => json_encode($data['tags']),
                    'short_title' => $data['short_title'],
                    'period' => $data['period'],
                    'simple_action' => $data['simple_action'],
                    'coupon_type' => $data['coupon_type'],
                    'discount_amount' => $data['discount_amount'],
                    'min_amount' => $data['min_amount'],
                    'rule_id' => $data['rule_id'],
                    'short_description' => $data['short_description'],
                    'long_description' => $data['long_description'],
                    'expired_at' => date("Y-m-d H:i:s", $data['expired_at']),
                    'icon_url' => $storagePath,
                ]);
            }
            DB::commit();
            return true;
        } catch (\Throwable $th) {
            $message = 'Có lỗi xảy ra: '.$th->getMessage().', file: '.$th->getFile().', dòng: '.$th->getLine();
            $this->error($message);
            Log::channel('Tiki Crawlers')->error([
                $th->getMessage(),
                $th->getFile(),
                $th->getLine()
            ]);
            $this->sendNotificationTelegram($message);
        }
    }
}
