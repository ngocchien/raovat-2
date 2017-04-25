<?php

namespace My\Job;

use My\General,
    Sunra\PhpSimple\HtmlDomParser,
    Zend\Dom\Query;

class JobCrawlerContent extends JobAbstract
{
    /*
     * crawler content from KHOAHOC.TV
     */
    public function crawlerKhoahocTv($arrParams = [], $serviceLocator)
    {
        $instanceSearchCategory = new \My\Search\Category();
        $arr_category = $instanceSearchCategory->getList(['cate_status' => 1], [], ['cate_sort' => ['order' => 'asc'], 'cate_id' => ['order' => 'asc']]);
        unset($instanceSearchCategory);
        $instanceSearchContent = new \My\Search\Content();
        $instanceSearchKeyword = new \My\Search\Keyword();
        foreach ($arr_category as $category) {
            try {
                if (empty($category['cate_crawler_url'])) {
                    continue;
                }
                for ($i = 1; $i >= 1; $i--) {
                    $source_url = $category['cate_crawler_url'] . '?p=' . $i;
                    echo \My\General::getColoredString("Crawler page cate = {$source_url} \n", 'green');
                    $page_cate_content = \My\General::crawler($source_url);
                    $page_cate_dom = HtmlDomParser::str_get_html($page_cate_content);
                    try {
                        $item_content_in_cate = $page_cate_dom->find('.listitem');
                    } catch (\Exception $exc) {
                        echo \My\General::getColoredString("Exception url = {$source_url} \n", 'red');
                    }

                    if (empty($item_content_in_cate)) {
                        continue;
                    }

                    foreach ($item_content_in_cate as $item_content) {
                        $arr_data_content = [];
                        $item_content_dom = HtmlDomParser::str_get_html($item_content->outertext);

                        try {
                            $item_content_source = 'http://khoahoc.tv' . $item_content_dom->find('a', 0)->href;
//                            $item_content_source = ' http://khoahoc.tv/my-muon-phat-trien-loai-dan-ma-khi-roi-xuong-dat-chung-se-moc-thanh-cay-77668';
                        } catch (\Exception $exc) {
                            echo \My\General::getColoredString("Exception item cate url = {$source_url} \n", 'red');
                            continue;
                        }

                        echo \My\General::getColoredString("get url = {$item_content_source} \n", 'green');

                        try {
                            $item_content_title = trim($item_content_dom->find('.title', 0)->plaintext);
                        } catch (\Exception $exc) {
                            echo \My\General::getColoredString("Exception cannot get title url = {$item_content_source} \n", 'red');
                            continue;
                        }

                        $arr_data_content['cont_title'] = html_entity_decode($item_content_title);
                        $arr_data_content['cont_slug'] = General::getSlug(html_entity_decode($item_content_title));

                        $arr_detail = $instanceSearchContent->getDetail(['cont_slug' => $arr_data_content['cont_slug'], 'not_cont_status' => -1]);

                        if (!empty($arr_detail)) {
                            continue;
                        }

                        try {
                            $item_content_description = html_entity_decode(trim($item_content_dom->find('.desc', 0)->plaintext));
                        } catch (\Exception $exc) {
                            echo \My\General::getColoredString("Exception cannot get description", 'red');
                        }

                        try {
                            $img_avatar_url = $item_content_dom->find('img', 0)->src;
                        } catch (\Exception $exc) {
                            echo \My\General::getColoredString("Exception image title = {$item_content_title} \n", 'red');
                        }

                        //lấy hình đại diện
                        if (empty($img_avatar_url) || $img_avatar_url == 'http://img.khoahoc.tv/photos/image/blank.png') {
                            $arr_data_content['cont_main_image'] = STATIC_URL . '/f/v1/img/black.png';
                        } else {
                            $extension = end(explode('.', end(explode('/', $img_avatar_url))));
                            $name = $arr_data_content['cont_slug'] . '.' . $extension;
                            file_put_contents(STATIC_PATH . '/uploads/content/' . $name, General::crawler($img_avatar_url));
                            $arr_data_content['cont_main_image'] = STATIC_URL . '/uploads/content/' . $name;
                        }

                        //crawler nội dung bài đọc
                        $content_detail_page_dom = HtmlDomParser::str_get_html(General::crawler($item_content_source));

                        $keyword_kh = $content_detail_page_dom->find('meta[name=keywords]', 0)->content;

                        //add keyword vao table keyword
                        if (!empty($keyword_kh)) {
                            $arr_keyword_kh = explode(',', $keyword_kh);
                            foreach ($arr_keyword_kh as $strkey) {
                                $exits_key = $instanceSearchKeyword->getDetail([
                                    'key_slug' => General::getSlug($strkey)
                                ]);
                                if (empty($exits_key)) {
                                    //search vào gg
                                    $url_gg = 'https://www.google.com.vn/search?sclient=psy-ab&biw=1366&bih=212&espv=2&q=' . rawurlencode($strkey) . '&oq=' . rawurlencode($strkey);

                                    $gg_rp = General::crawler($url_gg);


                                    $gg_rp_dom = new Query($gg_rp);
                                    $results = $gg_rp_dom->execute('.st');
                                    if (count($results)) {
                                        $key_description = '';
                                        foreach ($results as $item) {
                                            empty($key_description) ?
                                                $key_description .= '<p><strong>' . strip_tags($item->textContent) . '</strong></p>' :
                                                $key_description .= '<p>' . strip_tags($item->textContent) . '</p>';
                                        }
                                        $serviceKeyword = $serviceLocator->get('My\Models\Keyword');
                                        $id_key = $serviceKeyword->add([
                                            'key_name' => $strkey,
                                            'key_slug' => General::getSlug($strkey),
                                            'is_crawler' => 0,
                                            'created_date' => time(),
                                            'key_description' => $key_description
                                        ]);
                                        if ($id_key) {
                                            echo \My\General::getColoredString("Insert to tbl_keyword success key_name =  {$strkey} \n", 'green');
                                        } else {
                                            echo \My\General::getColoredString("Insert to tbl_keyword ERROR key_name =  {$strkey} \n", 'red');
                                        }
                                        unset($serviceKeyword, $gg_rp, $gg_rp_dom, $key_description, $id, $results);
                                        self::flush();
                                    }

                                    //random sleep
                                    sleep(rand(4, 10));
                                }
                            }
                            unset($arr_keyword_kh);
                        }

                        //lấy tác giả
                        $auth = strip_tags($content_detail_page_dom->find('.author', 0)->outertext);

                        try {
                            $script = $content_detail_page_dom->find('script');
                        } catch (\Exception $exc) {
                            echo $exc->getMessage();
                            $script = null;
                            echo \My\General::getColoredString("Empty Script", 'red');
                        }
                        if (!empty($script)) {
                            foreach ($content_detail_page_dom->find('script') as $item) {
                                $item->outertext = '';
                            }
                            unset($script);
                        }

                        try {
                            $adbox = $content_detail_page_dom->find('.adbox');
                        } catch (\Exception $exc) {
                            $adbox = null;
                            echo \My\General::getColoredString("Empty adbox", 'red');
                        }

                        if (!empty($adbox)) {
                            foreach ($content_detail_page_dom->find('.adbox') as $item) {
                                $item->outertext = '';
                            }
                            unset($adbox);
                        }

                        try {
                            $content_detail_html = $content_detail_page_dom->find('.content-detail', 0);
                        } catch (\Exception $exc) {
                            echo \My\General::getColoredString("Empty .adbox", 'red');
                            continue;
                        }

                        try {
                            $content_detail_outertext = $content_detail_page_dom->find('.content-detail', 0)->outertext;
                        } catch (\Exception $exc) {
                            echo \My\General::getColoredString("Empty content-detail", 'red');
                            continue;
                        }

                        try {
                            $img_all = $content_detail_html->find("img");
                        } catch (\Exception $exc) {
                            $img_all = [];
                            echo \My\General::getColoredString("Empty images", 'red');
                        }

                        //lấy hình ảnh trong bài
                        if (count($img_all) > 0) {
                            foreach ($img_all as $key => $im) {
                                $extension = end(explode('.', end(explode('/', $im->src))));
                                $name = $arr_data_content['cont_slug'] . '-' . ($key + 1) . '.' . $extension;
                                file_put_contents(STATIC_PATH . '/uploads/content/' . $name, General::crawler($im->src));
                                $content_detail_outertext = str_replace($im->src, STATIC_URL . '/uploads/content/' . $name, $content_detail_outertext);
                            }
                        }

                        //REPLACE ALL HREF TAG  A
                        $content_detail_outertext = str_replace('http://khoahoc.tv', BASE_URL, $content_detail_outertext);
                        $content_detail_outertext = str_replace('khoahoc.tv', 'khampha.tech', $content_detail_outertext);

                        $content_detail_outertext = trim(strip_tags($content_detail_outertext, '<a><div><img><b><p><br><span><br /><strong><h2><h1><h3><h4><table><td><tr><th><tbody><iframe>'));
                        $arr_data_content['cont_detail'] = html_entity_decode($content_detail_outertext);
                        $arr_data_content['created_date'] = time();
                        $arr_data_content['user_created'] = 1;
                        $arr_data_content['cate_id'] = $category['cate_id'];
                        $arr_data_content['cont_description'] = $item_content_description;
                        $arr_data_content['cont_status'] = 1;
                        $arr_data_content['cont_views'] = rand(1, rand(100, 1000));
                        $arr_data_content['method'] = 'crawler';
                        $arr_data_content['from_source'] = $item_content_source;
                        $arr_data_content['meta_keyword'] = empty($keyword_kh) ? str_replace(' ', ', ', $arr_data_content['cont_title']) : $keyword_kh . ', ' . str_replace(' ', ',', $arr_data_content['cont_title']);
                        $arr_data_content['updated_date'] = time();
                        $arr_data_content['cont_detail_text'] = $auth;
                        unset($content_detail_outertext);
                        unset($img_all);
                        unset($img_avatar_url);
                        unset($content_detail_html);
                        unset($content_detail_page_dom);
                        unset($item_content_dom);

                        $serviceContent = $serviceLocator->get('My\Models\Content');
                        $id = $serviceContent->add($arr_data_content);

                        if ($id) {
                            $arr_data_content['cont_id'] = $id;
                            self::postToFb($arr_data_content);
                            echo \My\General::getColoredString("Crawler success 1 post id = {$id} \n", 'green');
                        } else {
                            echo \My\General::getColoredString("Can not insert content db", 'red');
                        }

                        unset($serviceContent);
                        unset($arr_data_content);
                        self::flush();
                        continue;
                    }
                }
            } catch (\Exception $exc) {
                echo '<pre>';
                print_r($exc->getMessage());
                echo '</pre>';
                die();;
            }

        }
        echo \My\General::getColoredString("Crawler to success", 'green');
        return true;
    }

    public function videosYoutube($arrParams, $serviceLocator)
    {
        $instanceSearchContent = new \My\Search\Content();
        $google_config = General::$google_config;
        $client = new \Google_Client();
        $client->setDeveloperKey($google_config['key']);

        // Define an object that will be used to make all API requests.
        $youtube = new \Google_Service_YouTube($client);

        try {
            $arr_channel = [
                '28' => [ //Videos Hài Hước
                    'UCwmurIyZ6FHyVPtKppe_2_A', //https://www.youtube.com/channel/UCwmurIyZ6FHyVPtKppe_2_A/videos -- DIEN QUAN Comedy / Hài
                    'UCFMEYTv6N64hIL9FlQ_hxBw', //https://www.youtube.com/channel/UCFMEYTv6N64hIL9FlQ_hxBw -- ĐÔNG TÂY PROMOTION OFFICIAL
                    'UCXarSb1YYXKAtcPQJECVH2Q', //https://www.youtube.com/channel/UCXarSb1YYXKAtcPQJECVH2Q -- Fan Nhã Phương Trường Giang
                    'UCruaM4824Rr_ry7fsD5Jwag', //https://www.youtube.com/channel/UCruaM4824Rr_ry7fsD5Jwag -- THVL Giải Trí
                    'UCQGd-eIAxQV7zMvTT4UmjZA', //https://www.youtube.com/channel/UCQGd-eIAxQV7zMvTT4UmjZA --Bánh Bao Bự
                    'UCPu7cX9LrVOlCDK905T9tVw', //https://www.youtube.com/channel/UCPu7cX9LrVOlCDK905T9tVw -- Kem Xôi TV
                    'UCq6ApdQI0roaprMAY1gZTgw', //https://www.youtube.com/channel/UCq6ApdQI0roaprMAY1gZTgw -- Ghiền Mì Gõ
                    'UCZ72vrOkYZmvs9c0XYQM8cA', //https://www.youtube.com/channel/UCZ72vrOkYZmvs9c0XYQM8cA -- TRƯỜNG GIANG FAN
                    'UC0jDoh3tVXCaqJ6oTve8ebA', //https://www.youtube.com/channel/UC0jDoh3tVXCaqJ6oTve8ebA -- FAP TV
                    'UCsluIbpgt14y6KUcwqCxXbg', //https://www.youtube.com/channel/UCsluIbpgt14y6KUcwqCxXbg -- MCVMedia
                    'UCp-yY0F1wgZ1CUnh3upZLBQ', //https://www.youtube.com/channel/UCp-yY0F1wgZ1CUnh3upZLBQ -- Trắng
                    'UC6K3k5O0Dogk1v00beoGMTw', //https://www.youtube.com/channel/UC6K3k5O0Dogk1v00beoGMTw -- POPSTVVIETNAM
                    'UCmTroavJBDcWhwptGyKkERA' //https://www.youtube.com/channel/UCmTroavJBDcWhwptGyKkERA -- PHIM CẤP 3
                ],
                '29' => [ //videos Trẻ Thơ
                    'UCBzZ9lJmcsPRbiyHPbLYvOA', //Elsa and Spiderman Compilations
                    'UC_lA07JiUMe-aNh-u-TxjHg', // https://www.youtube.com/channel/UC_lA07JiUMe-aNh-u-TxjHg -- TuLi TV
                    'UCMYCx114VbdhQtU_Tz9dndA', //https://www.youtube.com/channel/UCMYCx114VbdhQtU_Tz9dndA -- Come And Play
                    'UCAeYn3nppkt7469wYynZJCg', //https://www.youtube.com/channel/UCAeYn3nppkt7469wYynZJCg -- Spiderman Frozen Elsa & Friends
                    'UC0jJKKA_CD4q4QE5UBMPMrA', //https://www.youtube.com/channel/UC0jJKKA_CD4q4QE5UBMPMrA --ElsaSpidermanIRL
                    'UCIl8mU7DwcDZVgsVPbQ5U8A', //https://www.youtube.com/channel/UCIl8mU7DwcDZVgsVPbQ5U8A -- AnAn ToysReview TV
                    'UCXF15hEQI7y1hpoZK4cZDEQ', //https://www.youtube.com/channel/UCXF15hEQI7y1hpoZK4cZDEQ -- KN Channel
                    'UCKcQ7Jo2VAGHiPMfDwzeRUw', //https://www.youtube.com/channel/UCKcQ7Jo2VAGHiPMfDwzeRUw --ChuChuTV Surprise Eggs Toys
                    'UCCF-1DYSb2deBcsdypD4b-Q', //https://www.youtube.com/channel/UCCF-1DYSb2deBcsdypD4b-Q -- Spiderman Real Life Superhero
                    'UCSJsjCiTl2lourZXnigVCoA', //https://www.youtube.com/channel/UCSJsjCiTl2lourZXnigVCoA -- Thơ Nguyễn
                    'UC5ezaYrzZpyItPSRG27MLpg', //https://www.youtube.com/channel/UC5ezaYrzZpyItPSRG27MLpg -- POPS Kids
                    'UCm4tFSgINIb4yGty_R2by_Q' //https://www.youtube.com/channel/UCm4tFSgINIb4yGty_R2by_Q --- Bi Bi Bi
                ],
                '30' => [ //Videos Khoa Học - Khám Phá
                    'UCiZJtnTQunvoY0xgv-Ce_rg',
                    'UCrZU-Qjgjs75BvKrLIX5pXg', //https://www.youtube.com/channel/UCrZU-Qjgjs75BvKrLIX5pXg -- Làm Gì Đây
                    'UCVC7AzvC0r1iruRyOL5X3JA', //https://www.youtube.com/channel/UCVC7AzvC0r1iruRyOL5X3JA -- Bí Ẩn Lạ Kỳ
                    'UCIBhtklwSSnghOTi4AAtj-g', // https://www.youtube.com/channel/UCIBhtklwSSnghOTi4AAtj-g --CỖ MÁY
                    'UCoOxtz4PmB7AOP3nwPM_bEg', //https://www.youtube.com/channel/UCoOxtz4PmB7AOP3nwPM_bEg -- Săn bắt và hái lượm
                    'UChp7WB42sPXbFtr3e_g9CaA', //https://www.youtube.com/channel/UChp7WB42sPXbFtr3e_g9CaA -- Thế Giới Huyền Bí
                    'UCM4srUfoYLk0n21HqAa5bCA', //https://www.youtube.com/channel/UCM4srUfoYLk0n21HqAa5bCA -- Lê Thành Chung
                    'UC1a6kSwY-zOLpFqxMLImpZw' //https://www.youtube.com/channel/UC1a6kSwY-zOLpFqxMLImpZw -- Thiên nhiên kỳ thú

                ],
                '31' => [ //Videos Hot
                    'UCuSp9h4f73lXjiBE2J85i8g', //https://www.youtube.com/channel/UCuSp9h4f73lXjiBE2J85i8g -- Tin Nóng Trong Ngày
                    'UCID3GTmfIFmydRiCdH_Fjow', //https://www.youtube.com/channel/UCID3GTmfIFmydRiCdH_Fjow --Tổng Hợp
                    'UC92MnB1eOC47kaMPoHckBhA', //https://www.youtube.com/channel/UC92MnB1eOC47kaMPoHckBhA -- Tieu Phong
                    'UCrszDMN3snNmbqu8XHuOmIg', //https://www.youtube.com/channel/UCrszDMN3snNmbqu8XHuOmIg -- Tin Hot 19+
//                    'UCedhIhWwTYnoPvR-o62Oa8g' //https://www.youtube.com/channel/UCedhIhWwTYnoPvR-o62Oa8g -- VẹmTV News

                ],
                '32' => [ // Videos Ca Nhạc
                    'UCZq4u4hadohQDXO6ra8aubQ', //https://www.youtube.com/channel/UCZq4u4hadohQDXO6ra8aubQ -- VIVA Music
                    'UCF5RuEuoGrqGtscvLGLOMew', //https://www.youtube.com/channel/UCF5RuEuoGrqGtscvLGLOMew -- VIVA Shows
                    'UCUgXK2UjZ8G_EM438aYkGrw', //https://www.youtube.com/channel/UCUgXK2UjZ8G_EM438aYkGrw -POPS MUSIC
                    'UCFtat3KL0Z29ATKiYVrnBxw', //https://www.youtube.com/channel/UCFtat3KL0Z29ATKiYVrnBxw -- Hồng Ân Entertainment
                    'UCPtEZo-8wDgZlXIRg1jOoJA' //https://www.youtube.com/channel/UCPtEZo-8wDgZlXIRg1jOoJA -- MP3 Zing Official
                ],
                '33' => [ //Videos Phim
                    'UC5rqnUOQ6tm915MjlwO4G0g', //--Nam Việt TV
                    'UC_VPydo78uJetT1OYk4xbpw', //https://www.youtube.com/channel/UC_VPydo78uJetT1OYk4xbpw/playlists -- Hoàng Dương AOK
                    'UCOjHXJD_ZJbh8nsJIuNcxHw', // https://www.youtube.com/channel/UCOjHXJD_ZJbh8nsJIuNcxHw -- Phim Sắp Ra
                    'UCGk3yw5k_xQUS_KSDCC6Nhw', //https://www.youtube.com/channel/UCGk3yw5k_xQUS_KSDCC6Nhw/videos -- VTV - SUNRISE
                    'UCF3TM1yxDMdFm2p3h11j52Q' //https://www.youtube.com/channel/UCF3TM1yxDMdFm2p3h11j52Q -- Khoảnh khắc kỳ diệu
                ],
                '34' => [ //Videos Thể Thao
                    'UCmHs51bYwsNGMMDH6oMoF_A', //https://www.youtube.com/channel/UCmHs51bYwsNGMMDH6oMoF_A -- Football VN
                    'UCndcERoL9eG-XNljgUk1Gag', //https://www.youtube.com/channel/UCndcERoL9eG-XNljgUk1Gag -- VFF Channel
                    'UCXVmFKJdknhsl-Co6gUAhOA', //https://www.youtube.com/channel/UCXVmFKJdknhsl-Co6gUAhOA -- Captain Football VN
                    'UCZNoTFTsrWXA-dXElRm90bA' //https://www.youtube.com/channel/UCZNoTFTsrWXA-dXElRm90bA -- Hài Bóng Đá
                ],
                '27' => [//gamming
                    'UU2l8G7UE41Vaby59Dfg6r3w' //gamming
                ]
            ];
            foreach ($arr_channel as $cate_id => $channels) {

                foreach ($channels as $channel_id) {
                    $searchResponse = $youtube->search->listSearch(
                        'snippet', array(
                            'channelId' => $channel_id,
                            'maxResults' => 50
                        )
                    );

                    if (empty($searchResponse) || empty($searchResponse->getItems())) {
                        continue;
                    }

                    foreach ($searchResponse->getItems() as $item) {

                        if (empty($item) || empty($item->getSnippet())) {
                            continue;
                        }
                        $id = $item->getId()->getVideoId();

                        if (empty($id)) {
                            continue;
                        }

                        $title = $item->getSnippet()->getTitle();

                        if (empty($title)) {
                            continue;
                        }

                        $description = $item->getSnippet()->getDescription();
                        $main_image = $item->getSnippet()->getThumbnails()->getMedium()->getUrl();

                        $is_exits = $instanceSearchContent->getDetail([
                            'cont_slug' => General::getSlug($title),
                            'status' => 1
                        ]);

                        if (!empty($is_exits)) {
                            echo \My\General::getColoredString("content title = {$title} is exits \n", 'red');
                            continue;
                        }

                        //crawler avatar
                        if (!empty($main_image)) {
                            $extension = end(explode('.', end(explode('/', $main_image))));
                            $name = General::getSlug($title) . '.' . $extension;
                            file_put_contents(STATIC_PATH . '/uploads/content/' . $name, General::crawler($main_image));
                            $main_image = STATIC_URL . '/uploads/content/' . $name;
                        }

                        $arr_data_content = [
                            'cont_title' => $title,
                            'cont_slug' => General::getSlug($title),
                            'cont_main_image' => $main_image,
                            'cont_detail' => html_entity_decode($description),
                            'created_date' => time(),
                            'user_created' => 1,
                            'cate_id' => $cate_id,
                            'cont_description' => $description ? $description : $title,
                            'cont_status' => 1,
                            'cont_views' => 0,
                            'method' => 'crawler',
                            'from_source' => $id,
                            'meta_keyword' => str_replace(' ', ',', $title),
                            'updated_date' => time()
                        ];

                        $serviceContent = $serviceLocator->get('My\Models\Content');
                        $id = $serviceContent->add($arr_data_content);
                        if ($id) {
                            $arr_data_content['cont_id'] = $id;

                            //giảm lượng chia sẻ lên facebook
                            if ($id % 2 == 0) {
                                self::postToFb($arr_data_content);
                            }
                            echo \My\General::getColoredString("Crawler success 1 post id = {$id} \n", 'green');
                        } else {
                            echo \My\General::getColoredString("Can not insert content db", 'red');
                        }
                        unset($serviceContent);
                        unset($arr_data_content);
                        self::flush();
                        continue;
                    }
                }
            }
            return true;
        } catch (\Exception $exc) {
            echo '<pre>';
            print_r($exc->getMessage());
            echo '</pre>';
            return true;
        }
        return true;
    }

    static function flush()
    {
        ob_end_flush();
        ob_flush();
        flush();
    }

    static function postToFb($arrParams)
    {
        $config_fb = General::$config_fb;
        $url_content = 'http://khampha.tech/bai-viet/' . $arrParams['cont_slug'] . '-' . $arrParams['cont_id'] . '.html';
        $data = array(
            "access_token" => $config_fb['access_token'],
            "message" => $arrParams['cont_description'],
            "link" => $url_content,
            "picture" => $arrParams['cont_main_image'],
            "name" => $arrParams['cont_title'],
            "caption" => "khampha.tech",
            "description" => $arrParams['cont_description']
        );
        $post_url = 'https://graph.facebook.com/' . $config_fb['fb_id'] . '/feed';

        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $post_url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $return = curl_exec($ch);
            curl_close($ch);
            echo \My\General::getColoredString($return, 'green');
            unset($ch);
            if (!empty($return)) {
                $post_id = explode('_', json_decode($return, true)['id'])[1];
                foreach (General::$face_traffic as $key => $value) {
                    self::shareFb([
                        'post_id' => $post_id,
                        'access_token' => $value,
                        'name' => $key
                    ]);
                }
            }
            echo \My\General::getColoredString("Post 1 content to facebook success cont_id = {$arrParams['cont_id']}", 'green');
            unset($ch, $return, $post_id, $data, $post_url, $url_content, $config_fb, $arrParams);
            self::flush();
            return true;
        } catch (Exception $e) {
            echo \My\General::getColoredString($e->getMessage(), 'red');
            return true;
        }
    }

    static function shareFb($arrParams)
    {
        $config_fb = General::$config_fb;
        try {
            $fb = new \Facebook\Facebook([
                'app_id' => $config_fb['appId'],
                'app_secret' => $config_fb['secret']
            ]);
            $fb->setDefaultAccessToken($arrParams['access_token']);
            $rp = $fb->post('/me/feed', ['link' => 'https://facebook.com/khampha.tech/posts/' . $arrParams['post_id']]);
            echo \My\General::getColoredString(json_decode($rp->getBody(), true), 'green');
            echo \My\General::getColoredString('Share post id ' . $arrParams['post_id'] . ' to facebook ' . $arrParams['name'] . ' SUCCESS', 'green');
            unset($data, $return, $arrParams, $rp, $config_fb);
            return true;
        } catch (\Exception $exc) {
            echo \My\General::getColoredString($exc->getMessage(), 'red');
            echo \My\General::getColoredString('Share post id ' . $arrParams['post_id'] . ' to facebook ' . $arrParams['name'] . ' ERROR', 'red');
            return true;
        }
    }
}
