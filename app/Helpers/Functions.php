<?php

namespace App\Helpers;

use File;
use Goutte\Client;
use Botble\Media\RvMedia;
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
use Botble\Media\Models\MediaFolder;
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

    public function remoteFileExists($url) {
        $curl = curl_init($url);

        //don't fetch the actual page, you only want to check the connection is ok
        curl_setopt($curl, CURLOPT_NOBODY, true);

        //do request
        $result = curl_exec($curl);

        $ret = false;

        //if request did not fail
        if ($result !== false) {
            //if request was ok, check response code
            $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

            if ($statusCode == 200) {
                $ret = true;
            }
        }

        curl_close($curl);

        return $ret;
    }

    public function saveImage($url, $storagePath)
    {
        //$url: đường dẫn hình ảnh cần tải về (vd: https://phongreviews.com/wp-content/uploads/2021/07/binh-sua-pigeon-1.jpg)
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
                dump('save done');
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
            // [
            //     'category_id' => 22,
            //     'page' => 25,
            //     'url' => [
            //         'https://phongreviews.com/chuyen-muc/cong-nghe/'
            //     ]
            // ],
            // [
            //     'category_id' => 19,
            //     'page' => 142,
            //     'url' => [
            //         'https://phongreviews.com/chuyen-muc/suc-khoe/'
            //     ]
            // ],
            [
                'category_id' => 20,
                'page' => 52,
                'url' => [
                    'https://phongreviews.com/chuyen-muc/do-gia-dung/'
                ]
            ],
            [
                'category_id' => 23,
                'page' => 29,
                'url' => [
                    'https://phongreviews.com/chuyen-muc/nha-cua-doi-song/'
                ]
            ],
            [
                'category_id' => 37,
                'page' => 4,
                'url' => [
                    'https://phongreviews.com/chuyen-muc/the-thao-da-ngoai/'
                ]
            ],
            [
                'category_id' => 21,
                'page' => 24,
                'url' => [
                    'https://phongreviews.com/chuyen-muc/me-be/'
                ]
            ],
            // [
            //     'category_id' => 1,
            //     'page' => 27,
            //     'url' => [
            //         'https://phongreviews.com/chuyen-muc/kinh-nghiem/'
            //     ]
            // ],
            // [
            //     'category_id' => 1,
            //     'page' => 3,
            //     'url' => [
            //         'https://phongreviews.com/chuyen-muc/hoc-tap/'
            //     ]
            // ],
            // [
            //     'category_id' => 14,
            //     'page' => 73,
            //     'url' => [
            //         'https://phongreviews.com/chuyen-muc/am-thuc/'
            //     ]
            // ],
            // [
            //     'category_id' => 11,
            //     'page' => 17,
            //     'url' => [
            //         'https://phongreviews.com/chuyen-muc/du-lich/'
            //     ]
            // ],
            // [
            //     'category_id' => 1,
            //     'page' => 24,
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
        dump($url);
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

    public function crawlersTrustReview()
    {
        // Ghi chú
        // category_id là id danh Mục
        // url là danh sách url page
        // url_item là danh sách url chi tiết tin của danh mục đó
        $UrlList = [
            [
                'category_id' => 22,
                'page' => 5,
                'url' => [
                    'https://trustreview.vn/category/thiet-bi-dien-tu'
                ]
            ],
            // [
            //     'category_id' => 20,
            //     'page' => 15,
            //     'url' => [
            //         'https://trustreview.vn/category/do-gia-dung'
            //     ]
            // ],
            [
                'category_id' => 19,
                'page' => 5,
                'url' => [
                    'https://trustreview.vn/category/suc-khoe-lam-dep'
                ]
            ],
            [
                'category_id' => 19,
                'page' => 3,
                'url' => [
                    'https://trustreview.vn/category/suc-khoe'
                ]
            ],
            [
                'category_id' => 21,
                'page' => 3,
                'url' => [
                    'https://trustreview.vn/category/me-va-be'
                ]
            ],
            [
                'category_id' => 23,
                'page' => 5,
                'url' => [
                    'https://trustreview.vn/category/nha-cua-doi-song'
                ]
            ],
            [
                'category_id' => 25,
                'page' => 7,
                'url' => [
                    'https://trustreview.vn/category/kinh-nghiem'
                ]
            ],
            [
                'category_id' => 25,
                'page' => 5,
                'url' => [
                    'https://trustreview.vn/category/kinh-nghiem/kinh-nghiem-do-gia-dung'
                ]
            ],
            [
                'category_id' => 25,
                'page' => 1,
                'url' => [
                    'https://trustreview.vn/category/kinh-nghiem/kien-thuc-suc-khoe-lam-dep'
                ]
            ],
        ];
        $client = new Client();

        //Lấy đường dẫn theo page cài sẵn vì đường dẫn trên phongreviews không đủ chỉ có 5 page
        foreach ($UrlList as $key => $value) {
            $pageUrl = [];
            for ($i=2; $i <= $value['page']; $i++) {
                $pageUrl[] = $value['url'][0].'/page/'.$i.'/';
            }
            $UrlList[$key]['url'] = array_merge($UrlList[$key]['url'],  $pageUrl);
        }

        //Lấy đường dẫn chi tiết tin
        foreach ($UrlList as $key => $value) {
            $data = [];
            foreach ($value['url'] as $item) {
                $crawler = $client->request('GET', $item);
                $articles = $crawler->filter('article')->filter('span.left');
                if(count($articles) <= 0) {
                    // Lấy đường dẫn và thumbnail
                    $result = $crawler->filter('article')->each(
                        function (Crawler $node) {
                            $url = $node->filter('h3.entry-title a')->attr('href');
                            $thumbnail = '';
                            if(count($node->filter('img')) > 0){
                                $thumbnail = $node->filter('img')->attr('data-src');
                            }
                            return [
                                'href' => $url,
                                'thumbnail' => $thumbnail
                            ];
                        }
                    );
                    $data = array_merge($data, $result);
                }
            }
            $UrlList[$key]['url_item'] = $data;
        }

        foreach ($UrlList as $valueUrlList) {
            foreach ($valueUrlList['url_item'] as $valueUrlItem) {
                $this->crawlersTrustReviewDetail($valueUrlList['category_id'], $valueUrlItem['href'], $valueUrlItem['thumbnail']);
            }
        }
    }

    public function crawlersTrustReviewDetail($categoryId, $url, $urlThumbnail)
    {
        try {
            DB::beginTransaction();
            dump($url);
            $client = new Client();
            $crawler = $client->request('GET', $url);

            //Lấy tiêu đề bài viết
            $title = $crawler->filter('article h1.entry-title')->text();
            $checkData = $crawler->filter('#tve_editor');
            if(count($checkData) <= 0){
                //Lấy nội dung html cần lưu (đã loại trừ những thẻ không cần thiết)
                $content = $crawler->filter('article #ftwp-postcontent')
                                    ->children()
                                    ->reduce(function (Crawler $node) {
                                        $check = strpos($node->attr('id'), 'ftwp-container-outer');
                                        return $check === false ? true : false;
                                    })
                                    ->reduce(function (Crawler $node) {
                                        $check = strpos($node->attr('class'), 'kk-star-ratings');
                                        return $check === false ? true : false;
                                    })
                                    ->each(function (Crawler $node) {
                                        return $node->outerHtml();
                                    });
                                    $content = preg_replace('/data-id=".*?"/', '', $content);
                                    $content = preg_replace('/id=".*?"/', '', $content);
                                    // $content = preg_replace('/class=".*?"/', '', $content);
                                    $content = preg_replace('/dir=".*?"/', '', $content);
                                    // $content = preg_replace('/style=".*?"/', '', $content);
                                    $content = preg_replace('/data-width=".*?"/', '', $content);
                                    $content = preg_replace('/data-height=".*?"/', '', $content);
                                    $content = preg_replace('/data-css=".*?"/', '', $content);
                                    $content = preg_replace('/data-init-width=".*?"/', '', $content);
                                    $content = preg_replace('/data-init-height=".*?"/', '', $content);
                                    $content = preg_replace('/data-lazyloaded=".*?"/', '', $content);
                                    $content = preg_replace('/data-placeholder-resp=".*?"/', '', $content);
                                    $content = preg_replace('/data-ll-status=".*?"/', '', $content);
                                    $content = preg_replace('/data-sizes=".*?"/', '', $content);
                                    $content = preg_replace('/sizes=".*?"/', '', $content);
                                    $content = preg_replace('/data-srcset=".*?"/', '', $content);
                                    $content = preg_replace('/srcset=".*?"/', '', $content);

                $data = [];
                $slug_components = parse_url($url);
                $slug_components = str_replace('.html', '', explode('/', $slug_components['path'])[1]);
                $slug = Slug::where('Key', $slug_components)
                                            ->where('reference_type', Post::class)
                                            ->first();
                if(blank($slug)) {
                    $post = Post::create([
                        'name' => $title,
                        // 'description' => $data['description'],
                        // 'status' => 'pending',
                        'status' => 'published',
                        'author_id' => 1,
                        'author_type' => User::class,
                        'format_type' => 'default',
                        'website' => 'trustreview.vn'
                    ]);
                    $folder = MediaFolder::where('slug', 'news')->first();
                    $folderChild = MediaFolder::where('parent_id', $folder->id)
                                                ->where('slug', $post->id)
                                                ->first();
                    if(blank($folderChild)) {
                        $folder = \Botble\Media\Models\MediaFolder::create([
                            'user_id' => 1,
                            'name' => $post->id,
                            'slug' => $post->id,
                            'parent_id' => $folder->id
                        ]);
                    }
                    //Tải và lưu hình ảnh thumbnail
                    if(isset($urlThumbnail) && $urlThumbnail != ''){
                        // Lưu hình ảnh ở local storage
                        $thumbnailName = array_reverse(explode ('/', $urlThumbnail))[0];
                        $extension = 'image/' . array_reverse(explode('.', $thumbnailName))[0];
                        $storagePath = 'news/'.$post->id.'/'.$thumbnailName;
                        // $storagePathThumbnail = 'storage/news/'.$post->id.'/'.$thumbnailName;
                        $this->saveImage($urlThumbnail, $storagePath);
                        // Kết thúc lưu hình ảnh ở local storage

                        $fileUpload = new \Illuminate\Http\UploadedFile(Storage::path($storagePath), $thumbnailName, $extension, null, true);
                        $image = \RvMedia::handleUpload($fileUpload, $folder->id);

                        // Update lại thumbnail bài viết
                        $post->update([
                            'image' => $storagePath
                        ]);
                    }
                }
                if(isset($post)) {
                    foreach($content as $k => $c) {
                        $checkBtn = strpos($c, 'class="div-btn"');
                        $checkImg = strpos($c, '<img');
                        $checkTaga = strpos($c, 'https://go.isclix.com');
                        $checkCenter = strpos($c, 'style="text-align: center;"');
                        $checkMenu = strpos($c, 'id="ftwp-container-outer"');
                        if(!($checkBtn === false)){
                            //Xử lý thẻ a
                            preg_match_all('/<a[^>]+href=([\'"])(?<href>.+?)\1[^>]*>/i', $c, $result);
                            if (!empty($result)) {
                                $href = $result['href'][0];
                                $crawler = $client->request('GET', $href);
                                $baseHref = $crawler->getBaseHref();
                                if(!(strpos($baseHref, 'https://ti.ki') === false)){
                                    $urlAffiliate = '';
                                    $url_components = parse_url($baseHref);
                                    parse_str($url_components['query'], $params);
                                    $urlAffiliate = $params['TIKI_URI'];
                                    $campaignId = '4348614231480407268';
                                }
                                if(!(strpos($baseHref, 'https://shopee.vn/search') === false)){
                                    $urlAffiliate = '';
                                    $url_components = parse_url($baseHref);
                                    parse_str($url_components['query'], $params);
                                    $keyword = urlencode($params['keyword']);
                                    $urlAffiliate = $url_components['scheme'] .'://'. $url_components['host'] . $url_components['path'] . '?keyword=' . $keyword;
                                    $campaignId = '4751584435713464237';
                                }else if(!(strpos($baseHref, 'https://shopee.vn') === false)){
                                    $urlAffiliate = '';
                                    $url_components = parse_url($baseHref);
                                    $urlAffiliate = $url_components['scheme'] .'://'. $url_components['host'] . $url_components['path'];
                                    $campaignId = '4751584435713464237';
                                }
                                if(!(strpos($baseHref, 'lazada.vn') === false)){
                                    $urlAffiliate = '';
                                    $url_components = parse_url($baseHref);
                                    parse_str($url_components['query'], $params);
                                    if(isset($params['url'])){
                                        $url_components1 = parse_url($params['url']);
                                        parse_str($url_components1['query'], $params);
                                        $urlAffiliate = $params['url'];
                                        $campaignId = '5127144557053758578';
                                    }
                                }
                                if(isset($urlAffiliate)){
                                    $response = $this->getUrlAffiliate($urlAffiliate, $campaignId);
                                    if(isset($response) && isset($response['success'])) {
                                        $resultUrlAffiliate = $response['data']['product_success_link'][0]['short_url'];
                                        $c = preg_replace("/(?<=href=(\"|'))[^\"']+(?=(\"|'))/", $resultUrlAffiliate, $c);
                                        $c = preg_replace('/style=".*?"/', '', $c);
                                        $data[] = $c;
                                    }
                                }else {
                                    $data[] = $c;
                                }
                            }
                        }else if(!($checkImg === false)){
                            //Xử lý thẻ img
                            $doc = new \DOMDocument();
                            libxml_use_internal_errors(true);
                            $doc->loadHTML('<?xml encoding="UTF-8">' . $c, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
                            $imageTags = $doc->getElementsByTagName('img');

                            foreach($imageTags as $tag) {
                                $dataSrc = $tag->getAttribute('data-src');
                                $imgExists = $this->remoteFileExists($dataSrc);
                                if (!$imgExists) {
                                    $dataSrc = $tag->getAttribute('src');
                                }

                                if(isset(parse_url($dataSrc)['query'])) {
                                    $dataSrc = parse_url($dataSrc)['scheme'].'://'.parse_url($dataSrc)['host'].parse_url($dataSrc)['path'];
                                }
                                if(isset($dataSrc)) {
                                    // Lưu hình ảnh ở local storage
                                    $imgName = array_reverse(explode ('/', $dataSrc))[0];
                                    $imgExtension = 'image/' . array_reverse(explode('.', $imgName))[0];
                                    $imgStoragePath = 'news/'.$post->id.'/'.$imgName;
                                    // $storagePathThumbnail = 'storage/news/'.$post->id.'/'.$imgName;
                                    $this->saveImage($dataSrc, $imgStoragePath);
                                    // Kết thúc lưu hình ảnh ở local storage

                                    $fileUpload = new \Illuminate\Http\UploadedFile(Storage::path($imgStoragePath), $imgName, $imgExtension, null, true);
                                    \RvMedia::handleUpload($fileUpload, $folder->id);
                                }
                                $tag->setAttribute('loading', 'lazy');
                                $tag->setAttribute('src', get_image_url($imgStoragePath));
                                $tag->setAttribute('data-src', get_image_url($imgStoragePath));
                            }
                            $dom = $doc->saveHTML();
                            $dom = preg_replace('/style=".*?"/', '', $dom);
                            $data[] = preg_replace('/class=".*?"/', '', $dom);
                        }else if(!($checkTaga === false)) {
                            //Xử lý thẻ a
                            preg_match_all('/<a[^>]+href=([\'"])(?<href>.+?)\1[^>]*>/i', $c, $resultTaga);
                            if (!empty($resultTaga)) {
                                $href = $resultTaga['href'][0];
                                $crawler = $client->request('GET', $href);
                                $baseHref = $crawler->getBaseHref();
                                if(!(strpos($baseHref, 'https://ti.ki') === false)){
                                    $urlAffiliate = '';
                                    $url_components = parse_url($baseHref);
                                    parse_str($url_components['query'], $params);
                                    $urlAffiliate = $params['TIKI_URI'];
                                    $campaignId = '4348614231480407268';
                                }
                                if(!(strpos($baseHref, 'https://shopee.vn/search') === false)){
                                    $urlAffiliate = '';
                                    $url_components = parse_url($baseHref);
                                    parse_str($url_components['query'], $params);
                                    $keyword = urlencode($params['keyword']);
                                    $urlAffiliate = $url_components['scheme'] .'://'. $url_components['host'] . $url_components['path'] . '?keyword=' . $keyword;
                                    $campaignId = '4751584435713464237';
                                }else if(!(strpos($baseHref, 'https://shopee.vn') === false)){
                                    $urlAffiliate = '';
                                    $url_components = parse_url($baseHref);
                                    $urlAffiliate = $url_components['scheme'] .'://'. $url_components['host'] . $url_components['path'];
                                    $campaignId = '4751584435713464237';
                                }
                                if(!(strpos($baseHref, 'lazada.vn') === false)){
                                    $urlAffiliate = '';
                                    $url_components = parse_url($baseHref);
                                    parse_str($url_components['query'], $params);
                                    $url_components1 = parse_url($params['url']);
                                    parse_str($url_components1['query'], $params);
                                    $urlAffiliate = $params['url'];
                                    $campaignId = '5127144557053758578';
                                }
                                if(isset($urlAffiliate)){
                                    $response = $this->getUrlAffiliate($urlAffiliate, $campaignId);
                                    if(isset($response) && $response['success']) {
                                        $resultUrlAffiliate = $response['data']['product_success_link'][0]['short_url'];
                                        $c = preg_replace("/(?<=href=(\"|'))[^\"']+(?=(\"|'))/", $resultUrlAffiliate, $c);
                                        // $c = preg_replace('/style=".*?"/', '', $c);
                                        $data[] = $c;
                                    }
                                }else {
                                    $data[] = $c;
                                }
                            }
                        }else if(!($checkCenter === false)){
                            $data[] = preg_replace('/class=".*?"/', '', str_replace(['trustreview.vn', 'trustreview', 'TrustReview', '.html'], ['xoaichua.com', 'xoaichua', 'XoaiChua', ''], $c));
                        }else if(!($checkMenu === false)) {
                            //Xử bài viết có menu khác
                            $docouter = new \DOMDocument();
                            libxml_use_internal_errors(true);
                            $docouter->loadHTML('<?xml encoding="UTF-8">' . $c, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
                            $elements = $docouter->getElementsById('ftwp-container-outer');
                            foreach($elements as $elk => $element) {
                                $outer = $elements->item($elk);
                                $outer->parentNode->removeChild($outer);
                            }
                            $domouter = $docouter->saveHTML();
                            $domouter = preg_replace('/style=".*?"/', '', $domouter);
                            $data[] = preg_replace('/class=".*?"/', '', $domouter);
                        }else {
                            $c = preg_replace('/style=".*?"/', '', $c);
                            $data[] = preg_replace('/class=".*?"/', '', str_replace(['trustreview.vn', 'trustreview', 'TrustReview', '.html'], ['xoaichua.com', 'xoaichua', 'XoaiChua', ''], $c));
                        }
                    }
                    //Lưu vào DB
                    $post->update([
                        'description' => strip_tags($data[0]),
                        'content' => implode('', $data)
                    ]);
                    PostCategory::create([
                        'category_id' => $categoryId,
                        'post_id' => $post->id
                    ]);
                    Slug::create([
                        'key' => $slug_components,
                        'reference_id' => $post->id,
                        'reference_type' => Post::class
                    ]);
                    LanguageMeta::create([
                        'lang_meta_code' => 'vi',
                        'lang_meta_origin' =>  md5($post->id . Post::class . time()),
                        'reference_id' => $post->id,
                        'reference_type' => Post::class
                    ]);
                }
            }else {
                //Lấy nội dung html cần lưu (đã loại trừ những thẻ không cần thiết)
                $content = $crawler->filter('article #tve_editor')
                                    ->children()
                                    ->reduce(function (Crawler $node) {
                                        $check = strpos($node->attr('class'), 'thrv-pricing-table');
                                        return $check === false ? true : false;
                                    })
                                    ->each(function (Crawler $node) {
                                        return $node->outerHtml();
                                    });
                                    $content = preg_replace('/data-ct=".*?"/', '', $content);
                                    $content = preg_replace('/data-ct-name=".*?"/', '', $content);
                                    $content = preg_replace('/data-css=".*?"/', '', $content);
                                    $content = preg_replace('/data-style-d=".*?"/', '', $content);
                                    $content = preg_replace('/data-thickness-d=".*?"/', '', $content);
                                    $content = preg_replace('/data-color-d=".*?"/', '', $content);
                                    $content = preg_replace('/data-button-size-d=".*?"/', '', $content);
                                    $content = preg_replace('/dir=".*?"/', '', $content);
                                    $content = preg_replace('/data-width=".*?"/', '', $content);
                                    $content = preg_replace('/data-height=".*?"/', '', $content);
                                    $content = preg_replace('/data-init-width=".*?"/', '', $content);
                                    $content = preg_replace('/data-init-height=".*?"/', '', $content);
                                    $content = preg_replace('/data-lazyloaded=".*?"/', '', $content);
                                    $content = preg_replace('/data-placeholder-resp=".*?"/', '', $content);
                                    $content = preg_replace('/data-ll-status=".*?"/', '', $content);
                                    $content = preg_replace('/data-sizes=".*?"/', '', $content);
                                    $content = preg_replace('/sizes=".*?"/', '', $content);
                                    $content = preg_replace('/data-srcset=".*?"/', '', $content);
                                    $content = preg_replace('/srcset=".*?"/', '', $content);
                $data = [];
                $slug_components = parse_url($url);
                $slug_components = str_replace('.html', '', explode('/', $slug_components['path'])[1]);
                $slug = Slug::where('Key', $slug_components)
                                            ->where('reference_type', Post::class)
                                            ->first();
                if(blank($slug)) {
                    $post = Post::create([
                        'name' => $title,
                        // 'description' => $data['description'],
                        // 'status' => 'pending',
                        'status' => 'published',
                        'author_id' => 1,
                        'author_type' => User::class,
                        'format_type' => 'default',
                        'website' => 'trustreview.vn'
                    ]);
                    $folder = MediaFolder::where('slug', 'news')->first();
                    $folderChild = MediaFolder::where('parent_id', $folder->id)
                                                ->where('slug', $post->id)
                                                ->first();
                    if(blank($folderChild)) {
                        $folder = \Botble\Media\Models\MediaFolder::create([
                            'user_id' => 1,
                            'name' => $post->id,
                            'slug' => $post->id,
                            'parent_id' => $folder->id
                        ]);
                    }
                    //Tải và lưu hình ảnh thumbnail
                    if(isset($urlThumbnail) && $urlThumbnail != ''){
                        // Lưu hình ảnh ở local storage
                        $thumbnailName = array_reverse(explode ('/', $urlThumbnail))[0];
                        $extension = 'image/' . array_reverse(explode('.', $thumbnailName))[0];
                        $storagePath = 'news/'.$post->id.'/'.$thumbnailName;
                        // $storagePathThumbnail = 'storage/news/'.$post->id.'/'.$thumbnailName;
                        $this->saveImage($urlThumbnail, $storagePath);
                        // Kết thúc lưu hình ảnh ở local storage

                        $fileUpload = new \Illuminate\Http\UploadedFile(Storage::path($storagePath), $thumbnailName, $extension, null, true);
                        $image = \RvMedia::handleUpload($fileUpload, $folder->id);

                        // Update lại thumbnail bài viết
                        $post->update([
                            'image' => $storagePath
                        ]);
                    }
                }
                if(isset($post)) {
                    foreach($content as $k => $c) {
                        $checkImg = strpos($c, '<img');
                        $checkTaga = strpos($c, 'https://go.isclix.com');
                        $checkTaga1 = strpos($c, 'fast.accesstrade.com');
                        $checkMenu = strpos($c, 'id="ftwp-container-outer"');
                        if(!($checkImg === false)){
                            //Xử lý thẻ img
                            $doc = new \DOMDocument();
                            libxml_use_internal_errors(true);
                            $doc->loadHTML('<?xml encoding="UTF-8">' . $c, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
                            $imageTags = $doc->getElementsByTagName('img');

                            foreach($imageTags as $tag) {
                                $dataSrc = $tag->getAttribute('data-src');

                                $imgExists = $this->remoteFileExists($dataSrc);
                                if (!$imgExists) {
                                    $dataSrc = $tag->getAttribute('src');
                                }

                                if(isset(parse_url($dataSrc)['query'])) {
                                    $dataSrc = parse_url($dataSrc)['scheme'].'://'.parse_url($dataSrc)['host'].parse_url($dataSrc)['path'];
                                }
                                if(isset($dataSrc)) {
                                    // Lưu hình ảnh ở local storage
                                    $imgName = array_reverse(explode ('/', $dataSrc))[0];
                                    $imgExtension = 'image/' . array_reverse(explode('.', $imgName))[0];
                                    $imgStoragePath = 'news/'.$post->id.'/'.$imgName;
                                    // $storagePathThumbnail = 'storage/news/'.$post->id.'/'.$imgName;
                                    $this->saveImage($dataSrc, $imgStoragePath);
                                    // Kết thúc lưu hình ảnh ở local storage

                                    $fileUpload = new \Illuminate\Http\UploadedFile(Storage::path($imgStoragePath), $imgName, $imgExtension, null, true);
                                    \RvMedia::handleUpload($fileUpload, $folder->id);
                                }
                                $tag->setAttribute('loading', 'lazy');
                                $tag->setAttribute('src', get_image_url($imgStoragePath));
                                $tag->setAttribute('data-src', get_image_url($imgStoragePath));
                                $tag->parentNode->removeAttribute('class');
                            }
                            $dom = $doc->saveHTML();
                            $dom = preg_replace('/style=".*?"/', '', $dom);
                            $dom = str_replace('wp-caption-text thrv-inline-text', 'd-bloc text-center mt-2', $dom);
                            // $data[] = $dom;

                            $checkTagaInTagImg = strpos($dom, 'https://go.isclix.com');
                            $checkTagaInTagImg1 = strpos($dom, 'fast.accesstrade.com');
                            if(!($checkTagaInTagImg === false) || !($checkTagaInTagImg1 === false)) {
                                //Xử lý thẻ a
                                $docATagsInTagImg = new \DOMDocument();
                                libxml_use_internal_errors(true);
                                $docATagsInTagImg->loadHTML('<?xml encoding="UTF-8">' . $dom, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
                                $aTags = $docATagsInTagImg->getElementsByTagName('a');
                                foreach($aTags as $aTag) {
                                    $aTag->removeAttribute('class');
                                    $aTag->setAttribute('class', 'btn');
                                    $aTag->parentNode->removeAttribute('class');
                                    $aTag->parentNode->setAttribute('class', 'div-btn');
                                }
                                $dom = $docATagsInTagImg->saveHTML();

                                preg_match_all('/<a[^>]+href=([\'"])(?<href>.+?)\1[^>]*>/i', $dom, $resultTaga);
                                if (!empty($resultTaga)) {
                                    $href = $resultTaga['href'][0];
                                    $crawler = $client->request('GET', $href);
                                    $baseHref = $crawler->getBaseHref();
                                    if(!(strpos($baseHref, 'https://ti.ki') === false)){
                                        $urlAffiliate = '';
                                        $url_components = parse_url($baseHref);
                                        parse_str($url_components['query'], $params);
                                        $urlAffiliate = $params['TIKI_URI'];
                                        $campaignId = '4348614231480407268';
                                    }
                                    if(!(strpos($baseHref, 'https://shopee.vn/search') === false)){
                                        $urlAffiliate = '';
                                        $url_components = parse_url($baseHref);
                                        parse_str($url_components['query'], $params);
                                        $keyword = urlencode($params['keyword']);
                                        $urlAffiliate = $url_components['scheme'] .'://'. $url_components['host'] . $url_components['path'] . '?keyword=' . $keyword;
                                        $campaignId = '4751584435713464237';
                                    }else if(!(strpos($baseHref, 'https://shopee.vn') === false)){
                                        $urlAffiliate = '';
                                        $url_components = parse_url($baseHref);
                                        $urlAffiliate = $url_components['scheme'] .'://'. $url_components['host'] . $url_components['path'];
                                        $campaignId = '4751584435713464237';
                                    }
                                    if(!(strpos($baseHref, 'lazada.vn') === false)){
                                        $urlAffiliate = '';
                                        $url_components = parse_url($baseHref);
                                        parse_str($url_components['query'], $params);
                                        $url_components1 = parse_url($params['url']);
                                        parse_str($url_components1['query'], $params);
                                        $urlAffiliate = $params['url'];
                                        $campaignId = '5127144557053758578';
                                    }
                                    if(isset($urlAffiliate)){
                                        $response = $this->getUrlAffiliate($urlAffiliate, $campaignId);
                                        if(isset($response) && $response['success']) {
                                            $resultUrlAffiliate = $response['data']['product_success_link'][0]['short_url'];
                                            $dom = preg_replace("/(?<=href=(\"|'))[^\"']+(?=(\"|'))/", $resultUrlAffiliate, $dom);
                                            // $c = preg_replace('/style=".*?"/', '', $c);
                                            $data[] = $dom;
                                        }
                                    }else {
                                        $data[] = $dom;
                                    }
                                }
                            }else {
                                $data[] = $dom;
                            }
                        }
                        else if(!($checkTaga === false) || !($checkTaga1 === false)) {
                            //Xử lý thẻ a
                            $docATags = new \DOMDocument();
                            libxml_use_internal_errors(true);
                            $docATags->loadHTML('<?xml encoding="UTF-8">' . $c, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
                            $aTags = $docATags->getElementsByTagName('a');
                            foreach($aTags as $aTag) {
                                $aTag->removeAttribute('class');
                                $aTag->setAttribute('class', 'btn');
                                $aTag->parentNode->removeAttribute('class');
                                $aTag->parentNode->setAttribute('class', 'div-btn');
                            }
                            $c = $docATags->saveHTML();

                            preg_match_all('/<a[^>]+href=([\'"])(?<href>.+?)\1[^>]*>/i', $c, $resultTaga);
                            if (!empty($resultTaga)) {
                                $href = $resultTaga['href'][0];
                                $crawler = $client->request('GET', $href);
                                $baseHref = $crawler->getBaseHref();
                                if(!(strpos($baseHref, 'https://ti.ki') === false)){
                                    $urlAffiliate = '';
                                    $url_components = parse_url($baseHref);
                                    parse_str($url_components['query'], $params);
                                    $urlAffiliate = $params['TIKI_URI'];
                                    $campaignId = '4348614231480407268';
                                }
                                if(!(strpos($baseHref, 'https://shopee.vn/search') === false)){
                                    $urlAffiliate = '';
                                    $url_components = parse_url($baseHref);
                                    parse_str($url_components['query'], $params);
                                    $keyword = urlencode($params['keyword']);
                                    $urlAffiliate = $url_components['scheme'] .'://'. $url_components['host'] . $url_components['path'] . '?keyword=' . $keyword;
                                    $campaignId = '4751584435713464237';
                                }else if(!(strpos($baseHref, 'https://shopee.vn') === false)){
                                    $urlAffiliate = '';
                                    $url_components = parse_url($baseHref);
                                    $urlAffiliate = $url_components['scheme'] .'://'. $url_components['host'] . $url_components['path'];
                                    $campaignId = '4751584435713464237';
                                }
                                if(!(strpos($baseHref, 'lazada.vn') === false)){
                                    $urlAffiliate = '';
                                    $url_components = parse_url($baseHref);
                                    parse_str($url_components['query'], $params);
                                    $url_components1 = parse_url($params['url']);
                                    parse_str($url_components1['query'], $params);
                                    $urlAffiliate = $params['url'];
                                    $campaignId = '5127144557053758578';
                                }
                                if(isset($urlAffiliate)){
                                    $response = $this->getUrlAffiliate($urlAffiliate, $campaignId);
                                    if(isset($response) && $response['success']) {
                                        $resultUrlAffiliate = $response['data']['product_success_link'][0]['short_url'];
                                        $c = preg_replace("/(?<=href=(\"|'))[^\"']+(?=(\"|'))/", $resultUrlAffiliate, $c);
                                        // $c = preg_replace('/style=".*?"/', '', $c);
                                        $data[] = $c;
                                    }
                                }else {
                                    $data[] = $c;
                                }
                            }
                        }
                        else if(!($checkMenu === false)) {
                            //Xử bài viết có menu khác
                            $docouter = new \DOMDocument();
                            libxml_use_internal_errors(true);
                            $docouter->loadHTML('<?xml encoding="UTF-8">' . $c, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
                            $elements = $docouter->getElementById('ftwp-container-outer');
                            $elements->parentNode->removeChild($elements);
                            $domouter = $docouter->saveHTML();
                            $domouter = preg_replace('/style=".*?"/', '', $domouter);
                            $domouter = preg_replace('/class=".*?"/', '', $domouter);
                            $data[] = preg_replace('/id=".*?"/', '', $domouter);
                        } else {
                            $c = preg_replace('/style=".*?"/', '', $c);
                            $c = preg_replace('/id=".*?"/', '', $c);
                            $data[] = preg_replace('/class=".*?"/', '', str_replace(['trustreview.vn', 'trustreview', 'TrustReview', '.html'], ['xoaichua.com', 'xoaichua', 'XoaiChua', ''], $c));
                        }
                    }
                    // Lưu vào DB
                    $post->update([
                        'description' => strip_tags($data[0]),
                        'content' => implode('', $data)
                    ]);
                    PostCategory::create([
                        'category_id' => $categoryId,
                        'post_id' => $post->id
                    ]);
                    Slug::create([
                        'key' => $slug_components,
                        'reference_id' => $post->id,
                        'reference_type' => Post::class
                    ]);
                    LanguageMeta::create([
                        'lang_meta_code' => 'vi',
                        'lang_meta_origin' =>  md5($post->id . Post::class . time()),
                        'reference_id' => $post->id,
                        'reference_type' => Post::class
                    ]);
                }
            }

            DB::commit();
            // dump('done');
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

    public function getUrlAffiliate($urlAffiliate, $campaignId)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://pub2-api.accesstrade.vn/v1/product_link/',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS =>'{
                "original_url": [
                    "'. $urlAffiliate .'"
                ],
                "tracking_domain": "https://go.isclix.com",
                "utm_source": "",
                "utm_medium": "",
                "utm_campaign": "",
                "utm_content": "",
                "short_link": "https://shorten.asia",
                "create_shorten": true,
                "sub3": "",
                "campaign_id": "'. $campaignId .'"
            }',
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJleHAiOjE2Nzc1MzI3MDcsImlhdCI6MTY3NzAzMjcwNywibmJmIjoxNjc3MDMyNzA3LCJqdGkiOiIyMDIzLTAyLTIyIDAyOjI1OjA3LjM4NDY3Nl82MTI2NDUxMzAzNzIxMDU5NTY2IiwiaWRlbnRpdHkiOnsiaWQiOiI2MDc5MDY2MzMyMDM3ODc5NjcxIiwic3NvX2lkIjo1NTE5NzMzLCJsb2dpbl9uYW1lIjoiaHVuZ19tdl85NSIsImZvbGxvd2VyIjpudWxsLCJsb2dpbl9uYW1lX3NzbyI6Imh1bmdfbXZfOTUiLCJ0b2tlbl9wcm9maWxlIjoiZGQwNGNhZGEtYjNkZi00ODA4LTk1MjgtNzk0OTIyYjQ0NjRmIiwiZW1haWwiOiJtYXZhbmh1bmcyNzA5OTVAZ21haWwuY29tIiwiZmlyc3RfbmFtZSI6IlZcdTAxMDNuIEhcdTAxYjBuZyIsImxhc3RfbmFtZSI6Ik1cdTAwZTMiLCJkYXRlX2JpcnRoIjoiMTk5NS0wOS0yNyIsImFnZW5jeSI6ZmFsc2UsIl9hdF9pZCI6IjEzODIyMzkiLCJpc0ZyYW1lIjpmYWxzZSwidXNlcm5hbWUiOiJodW5nX212Xzk1IiwicGhvbmUiOiIrODQzNDQyNDI2NzkiLCJhZGRyZXNzIjoiXHUxZWE0cCBQaFx1MDFiMFx1MWVkYmMgVFx1MDBlMm4sIFRcdTAwZTJuIFBoXHUwMWIwXHUxZWRiYywgXHUwMTEwXHUxZWQzbmcgUGhcdTAwZmEsIEJcdTAwZWNuaCBQaFx1MDFiMFx1MWVkYmMiLCJnZW5kZXIiOjEsImN0aW1lIjoiIiwiZGVzY3JpcHRpb24iOiIiLCJhdmF0YXIiOiIiLCJtb2RlbCI6IiJ9fQ.B3fX2ihivT8aUIKDjq0UuXVvy0AMfTadvG20rL55uTc',
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return json_decode($response, true);
    }
}
