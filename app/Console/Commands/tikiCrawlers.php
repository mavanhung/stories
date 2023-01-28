<?php

namespace App\Console\Commands;

use GuzzleHttp\Client;
use App\Constants\DefineCode;
use Botble\Tiki\Models\Seller;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class tikiCrawlers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:tiki';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tiki crawlers';

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
            DB::rollBack();
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
                        dump($url);
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
                dump($i);
                // if($result[$i] == 'https://tiki.vn/cua-hang/balo-mr-vui') {
                //     dd($i);
                // }
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
}
