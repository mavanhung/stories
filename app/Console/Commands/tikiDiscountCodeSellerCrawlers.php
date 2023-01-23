<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Botble\Tiki\Models\DiscountCode;
use Illuminate\Support\Facades\Storage;

class tikiDiscountCodeSellerCrawlers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:tiki-discount-code-seller';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tiki discount code seller';

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
        $this->sellers();
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
                        $this->getSellerDiscountCode($data['seller_id']);
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

    public function sellers()
    {
        try {
            $url = 'https://tiki.vn/seo/sitemaps/seller.xml';

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

            $arr = explode('<loc>', $response);

            $result = [];

            for ($i=1; $i < count($arr); $i++) {
                $v = explode('</loc>', $arr[$i]);
                if(count($v) > 0) {
                    $result[] = $v[0];
                }
            }

            for($i = 0; $i < count($result); $i++) {
                $this->sellerDetail($result[$i]);
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

    public function getSellerDiscountCode($sellerId)
    {
        try {
            $url = 'https://api.tiki.vn/tequila/v1/consumer/sellers/'.$sellerId.'/coupons';

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

            $response = json_decode($response, true)['data'];

            if(count($response) > 0) {
                foreach ($response as $value) {
                    $this->saveDiscountCode($value);
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
