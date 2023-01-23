<?php

namespace App\Helpers;

use Goutte\Client;
use Botble\ACL\Models\User;
use Botble\Blog\Models\Post;
use Botble\Page\Models\Page;
use Botble\Slug\Models\Slug;
use App\Constants\DefineCode;
use Botble\Blog\Models\Category;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Botble\Blog\Models\PostCategory;
use Botble\Language\Models\Language;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Botble\Language\Models\LanguageMeta;
use Symfony\Component\DomCrawler\Crawler;

trait Functions
{
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

    public function saveImage($url, $storagePath, $name)
    {
        //$url: đường dẫn hình ảnh cần tải về (vd: https://phongreviews.com/wp-content/uploads/2021/07/binh-sua-pigeon-1.jpg)
        //$storagePath : đường dẫn thư mục sẽ lưu hình ảnh tải về (vd: crawlers/binh-sua-pigeon-1.jpg)
        //$name: tên hình ảnh (vd: image.jpg)
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
            // return $response;
        } catch (\Throwable $th) {
            $this->error('Có lỗi xảy ra: '.$th->getMessage().', file: '.$th->getFile().', dòng: '.$th->getLine());
            Log::channel('Crawlers')->error([
                $th->getMessage(),
                $th->getFile(),
                $th->getLine()
            ]);
        }
    }

    public function saveDB($data)
    {
        //name, description, content, thumbnail, category_id, slug
        try {
            DB::beginTransaction();
            $slug = Slug::where('Key',$data['slug'])
                        ->where('reference_type', Post::class)
                        ->first();
            if(blank($slug)) {
                $data['description'] = str_replace('Phong Reviews', 'Xoài Chua', $data['description']);
                $post = Post::create([
                    'name' => $data['name'],
                    'description' => $data['description'],
                    'status' => 'pending',
                    // 'status' => 'published',
                    'author_id' => 1,
                    'author_type' => User::class,
                    'format_type' => 'default'
                ]);
                PostCategory::create([
                    'category_id' => $data['category_id'],
                    'post_id' => $post->id
                ]);
                Slug::create([
                    'key' => $data['slug'],
                    'reference_id' => $post->id,
                    'reference_type' => Post::class
                ]);
                LanguageMeta::create([
                    'lang_meta_code' => 'vi',
                    'lang_meta_origin' =>  md5($post->id . Post::class . time()),
                    'reference_id' => $post->id,
                    'reference_type' => Post::class
                ]);
                //Format content dạng array thành string
                $data['content'] = implode('', $data['content']);
                $dataContentArr = explode('<noscript>', $data['content']);
                foreach ($dataContentArr as $key => $value) {
                    if($key % 2 != 0) {
                        $dataContentArr[$key] = explode('</noscript>', $value)[1];
                    }
                }
                $data['content'] = implode('', $dataContentArr);
                //Tải và lưu hình ảnh thumbnail
                if(!blank($data['thumbnail'])){
                    if(strpos($data['thumbnail'], 'https://') === 0) {
                        // Lưu hình ảnh ở local storage
                        $thumbnailName = array_reverse(explode ('/', $data['thumbnail']))[0];
                        $storagePath = 'news/'.$post->id.'/'.$thumbnailName;
                        $storagePathThumbnail = 'storage/news/'.$post->id.'/'.$thumbnailName;
                        $this->saveImage($data['thumbnail'], $storagePath, $thumbnailName);
                        // Kết thúc lưu hình ảnh ở local storage

                        // Lưu hình ảnh ở AWS S3
                        // $thumbnailName = array_reverse(explode ('/', $data['thumbnail']))[0];
                        // $storagePath = 'news/'.$post->id.'/'.$thumbnailName;
                        // $response = $this->saveImage($data['thumbnail'], $storagePath, $thumbnailName);
                        // $path = Storage::disk('s3')->put($storagePath, $response);
                        // $path = Storage::disk('s3')->url($storagePath);
                        // $storagePathThumbnail = $path;
                        // Kết thúc lưu hình ảnh ở AWS S3

                        //Update lại thumbnail bài viết
                        $post->update([
                            'image' => $storagePath
                        ]);
                    }
                }
                //Tải hình ảnh trong bài viết và cập nhật lại đường dẫn trong nội dung
                foreach ($data['images'] as $keyImage => $image) {
                    if(strpos($image, 'https://') === 0) {
                        // Lưu hình ảnh ở local storage
                        $imageName = array_reverse(explode ('/', $image))[0];
                        $storagePath = 'news/'.$post->id.'/'.$imageName;
                        $storagePathReplace = 'storage/news/'.$post->id.'/'.$imageName;
                        $this->saveImage($image, $storagePath, $imageName);
                        $data['content'] = str_replace($image, $storagePathReplace, $data['content'] );
                        // Kết thúc lưu hình ảnh ở local storage

                        // Lưu hình ảnh ở AWS S3
                        // $imageName = array_reverse(explode ('/', $image))[0];
                        // $storagePath = 'news/'.$post->id.'/'.$imageName;
                        // $response = $this->saveImage($image, $storagePath, $imageName);
                        // $path = Storage::disk('s3')->put($storagePath, $response);
                        // $path = Storage::disk('s3')->url($storagePath);
                        // $data['content'] = str_replace($image, $path, $data['content'] );
                        // Kết thúc lưu hình ảnh ở AWS S3
                    }else {
                        $data['content'] = str_replace($image, !blank($storagePathThumbnail) ? $storagePathThumbnail : '', $data['content'] );
                    }
                }
                $data['content'] = str_replace('src=""', '', $data['content'] );
                $data['content'] = str_replace('https://phongreviews.com', '', $data['content'] );
                $data['content'] = str_replace('Phong Reviews', 'Xoài Chua', $data['content'] );
                $post->update([
                    'content' => $data['content']
                ]);
            }
            DB::commit();
            return true;
        } catch (\Throwable $th) {
            DB::rollBack();
            $this->error('Có lỗi xảy ra: '.$th->getMessage().', file: '.$th->getFile().', dòng: '.$th->getLine());
            Log::channel('Crawlers')->error([
                $th->getMessage(),
                $th->getFile(),
                $th->getLine()
            ]);
        }
    }

    public function crawlersPhongReviews()
    {
        // Ghi chú
        // category_id là id danh Mục
        // url là danh sách url page
        // url_item là danh sách url chi tiết tin của danh mục đó
        $UrlList = [
            [
                'category_id' => 22,
                'page' => 25,
                'url' => [
                    'https://phongreviews.com/chuyen-muc/cong-nghe/'
                ]
            ],
            // [
            //     'category_id' => 19,
            //     'page' => 3,
            //     'url' => [
            //         'https://phongreviews.com/chuyen-muc/suc-khoe/'
            //     ]
            // ],
            // [
            //     'category_id' => 20,
            //     'page' => 2,
            //     'url' => [
            //         'https://phongreviews.com/chuyen-muc/do-gia-dung/'
            //     ]
            // ],
            // [
            //     'category_id' => 23,
            //     'page' => 2,
            //     'url' => [
            //         'https://phongreviews.com/chuyen-muc/nha-cua-doi-song/'
            //     ]
            // ],
            // [
            //     'category_id' => 37,
            //     'page' => 2,
            //     'url' => [
            //         'https://phongreviews.com/chuyen-muc/the-thao-da-ngoai/'
            //     ]
            // ],
            // [
            //     'category_id' => 21,
            //     'page' => 2,
            //     'url' => [
            //         'https://phongreviews.com/chuyen-muc/me-be/'
            //     ]
            // ],
            // [
            //     'category_id' => 1,
            //     'page' => 200,
            //     'url' => [
            //         'https://phongreviews.com/chuyen-muc/kinh-nghiem/'
            //     ]
            // ],
            // [
            //     'category_id' => 1,
            //     'page' => 200,
            //     'url' => [
            //         'https://phongreviews.com/chuyen-muc/hoc-tap/'
            //     ]
            // ],
            // [
            //     'category_id' => 14,
            //     'page' => 200,
            //     'url' => [
            //         'https://phongreviews.com/chuyen-muc/am-thuc/'
            //     ]
            // ],
            // [
            //     'category_id' => 11,
            //     'page' => 200,
            //     'url' => [
            //         'https://phongreviews.com/chuyen-muc/du-lich/'
            //     ]
            // ],
            // [
            //     'category_id' => 1,
            //     'page' => 200,
            //     'url' => [
            //         'https://phongreviews.com/chuyen-muc/giai-tri/'
            //     ]
            // ],
            // [
            //     'category_id' => 1,
            //     'page' => 200,
            //     'url' => [
            //         'https://phongreviews.com/chuyen-muc/giai-tri/sach-va-truyen/'
            //     ]
            // ],
            // [
            //     'category_id' => 1,
            //     'page' => 200,
            //     'url' => [
            //         'https://phongreviews.com/chuyen-muc/giai-tri/review-phim/'
            //     ]
            // ],
            // [
            //     'category_id' => 31,
            //     'page' => 2,
            //     'url' => [
            //         'https://phongreviews.com/chuyen-muc/kinh-nghiem/anh-dep/'
            //     ]
            // ],
            // [
            //     'category_id' => 18,
            //     'page' => 200,
            //     'url' => [
            //         'https://phongreviews.com/chuyen-muc/than-so-hoc/'
            //     ]
            // ]
        ];
        $client = new Client();

        //Lấy đường dẫn theo page cài sẵn vì đường dẫn trên phongreviews không đủ chỉ có 5 page
        foreach ($UrlList as $key => $value) {
            $pageUrl = [];
            for ($i=2; $i <= $value['page']; $i++) {
                $pageUrl[] = $value['url'][0].'page/'.$i.'/';
            }
            $UrlList[$key]['url'] = array_merge($UrlList[$key]['url'],  $pageUrl);
        }

        //Lấy đường dẫn theo page (k xài cách này nữa)
        // foreach ($UrlList as $key => $value) {
        //     $crawler = $client->request('GET', $value['url'][0]);
        //     $result = $crawler->filter('nav.elementor-pagination a.page-numbers')->each(
        //         function (Crawler $node) {
        //             $url = $node->filter('a')->attr('href');
        //             return $url;
        //         }
        //     );
        //     $UrlList[$key]['url'] = array_merge($UrlList[$key]['url'],  $result);
        // }

        //Lấy đường dẫn chi tiết tin
        foreach ($UrlList as $key => $value) {
            $data = [];
            foreach ($value['url'] as $item) {
                $crawler = $client->request('GET', $item);
                $baseHref = $crawler->getBaseHref(); //Lấy getBaseHref của client trả về để so sánh với page url vì nếu quá page nó sẽ redirect về trang chủ phongreviews
                if($item == $baseHref) {
                    //Lấy đường dẫn và thumbnail của tin nổi bật page 1, từ page 2 sẽ ko có phần này
                    $result1 = $crawler->filter('.elementor-widget-container .featured_grid .col-feat-grid')->each(
                        function (Crawler $node) {
                            $url = $node->filter('a.feat_overlay_link')->attr('href');
                            $text = $node->filter('style')->text();
                            $text = explode('(', $text);
                            $thumbnail = '';
                            if(count($text) > 1){
                                $text = explode(')', $text[1]);
                                if(count($text) > 1) {
                                    $thumbnail = $text[0];
                                }
                            }
                            return [
                                'href' => $url,
                                'thumbnail' => $thumbnail
                            ];
                        }
                    );
                    $data = array_merge($data, $result1);

                    //Lấy đường dẫn và thumbnail
                    $result2 = $crawler->filter('.elementor-widget-container article.elementor-post')->each(
                        function (Crawler $node) {
                            $url = $node->filter('a.elementor-post__thumbnail__link')->attr('href');
                            $thumbnail = $node->filter('.elementor-post__thumbnail img')->attr('data-src');
                            return [
                                'href' => $url,
                                'thumbnail' => $thumbnail
                            ];
                        }
                    );
                    $data = array_merge($data, $result2);
                }
            }
            $UrlList[$key]['url_item'] = $data;
        }

        foreach ($UrlList as $valueUrlList) {
            foreach ($valueUrlList['url_item'] as $valueUrlItem) {
                $this->crawlersPhongReviewsDetail($valueUrlList['category_id'], $valueUrlItem['href'], $valueUrlItem['thumbnail']);
            }
        }
    }

    public function crawlersPhongReviewsDetail($categoryId, $url, $urlThumbnail)
    {
        $client = new Client();
        $crawler = $client->request('GET', $url);

        //Lấy tiêu đề bài viết
        $title = $crawler->filter('article.post-inner .rh_post_layout_compact .single_top_main')
                        ->each(function (Crawler $node) {
                            return $node->filter('h1')->text();
                        });

        //Lấy nội dung html cần lưu (đã loại trừ những thẻ không cần thiết)
        $content = $crawler->filter('article.post-inner')
                            ->children()
                            ->reduce(function (Crawler $node) {
                                $check = strpos($node->attr('class'), 'rh_post_layout_compact');
                                return $check === false ? true : false;
                            })
                            ->reduce(function (Crawler $node) {
                                $check = strpos($node->attr('class'), 'wpsm-titlebox');
                                return $check === false ? true : false;
                            })
                            ->reduce(function (Crawler $node) {
                                $check = strpos($node->attr('class'), 'google-auto-placed');
                                return $check === false ? true : false;
                            })
                            ->reduce(function (Crawler $node) {
                                $check = strpos($node->attr('class'), 'kk-star-ratings');
                                return $check === false ? true : false;
                            })
                            ->reduce(function (Crawler $node) {
                                $check = strpos($node->attr('class'), 'priced_block');
                                return $check === false ? true : false;
                            })
                            ->reduce(function (Crawler $node) {
                                $check = strpos($node->attr('style'), 'clear:both; margin-top:0em; margin-bottom:1em;');
                                return $check === false ? true : false;
                            })
                            ->each(function (Crawler $node) {
                                return $node->outerHtml();
                            });

        foreach ($content as $keyContent =>  $contentValue) {
            $arrContentValues = explode ( '<noscript>' , $contentValue);
            if(count($arrContentValues) > 1) {
                $contentValue1 = explode ( '<img' , $arrContentValues[0])[0];
                $contentValue2 = implode('', explode ( '</noscript>' , $arrContentValues[1]));
                $content[$keyContent] = $contentValue1.$contentValue2;
            }
        }

        $description = strip_tags($content[0]);

        //Lấy đường dẫn hình ảnh cần lưu
        $images = $crawler->filter('article.post-inner')
                            ->children()
                            ->reduce(function (Crawler $node) {
                                $check = strpos($node->attr('class'), 'rh_post_layout_compact');
                                return $check === false ? true : false;
                            })
                            ->reduce(function (Crawler $node) {
                                $check = strpos($node->attr('class'), 'wpsm-titlebox');
                                return $check === false ? true : false;
                            })
                            ->reduce(function (Crawler $node) {
                                $check = strpos($node->attr('class'), 'google-auto-placed');
                                return $check === false ? true : false;
                            })
                            ->reduce(function (Crawler $node) {
                                $check = strpos($node->attr('class'), 'kk-star-ratings');
                                return $check === false ? true : false;
                            })
                            ->reduce(function (Crawler $node) {
                                $check = strpos($node->attr('class'), 'priced_block');
                                return $check === false ? true : false;
                            })
                            ->reduce(function (Crawler $node) {
                                $check = strpos($node->attr('style'), 'clear:both; margin-top:0em; margin-bottom:1em;');
                                return $check === false ? true : false;
                            })
                            ->each(function (Crawler $node) {
                                return $node->filter('img')
                                            ->each(function (Crawler $node) {
                                                return [
                                                    'src' => $node->attr('src'),
                                                    'srcset' => $node->attr('srcset'),
                                                    'data-lazy-srcset' => $node->attr('data-lazy-srcset'),
                                                    'data-lazy-src' => $node->attr('data-lazy-src')
                                                ];
                                            });
                            });
        $images = array_filter($images);
        $listImage = [];
        foreach ($images as $image) {
            foreach ($image as $item) {
                foreach ($item as $k => $it) {
                    if(!blank($it)) {
                        // if($k == 'srcset') {
                            $srcsetArr = explode (', ', $it);
                            if(count($srcsetArr) > 0){
                                foreach ($srcsetArr as $srcsetArrItem) {
                                    $srcsetArrItemUrl = explode (' ', $srcsetArrItem);
                                    if(count($srcsetArrItemUrl) > 0){
                                        if(!in_array($srcsetArrItemUrl[0], $listImage)) {
                                            $listImage[] = $srcsetArrItemUrl[0];
                                        }
                                    }
                                }
                            }
                        // }
                    }
                }
            }
        }
        $slug = array_reverse(explode ('/', $url))[1];
        $data = [
            'category_id' => $categoryId,
            'name' => count($title) > 0 ? $title[0] : '',
            'description' => $description,
            'content' => $content,
            'thumbnail' => $urlThumbnail,
            'slug' => $slug,
            'images' => $listImage
        ];
        $this->saveDB($data);
    }

    public function crawlersChanhtuoi()
    {
        // Ghi chú
        // category_id là id danh Mục
        // url là danh sách url page
        // url_item là danh sách url chi tiết tin của danh mục đó
        $UrlList = [
            [
                'category_id' => 22,
                'page' => 10,
                'url' => [
                    'https://chanhtuoi.com/kinh-nghiem/tai-chinh'
                ]
            ],
        ];
        $client = new Client();

        //Lấy đường dẫn
        foreach ($UrlList as $key => $value) {
            $pageUrl = [];
            for ($i=2; $i <= $value['page']; $i++) {
                $pageUrl[] = $value['url'][0].'page/'.$i.'/';
            }
            $UrlList[$key]['url'] = array_merge($UrlList[$key]['url'],  $pageUrl);
        }
    }
}
