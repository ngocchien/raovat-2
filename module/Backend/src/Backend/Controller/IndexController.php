<?php

namespace Backend\Controller;

use Facebook\Facebook;
use Facebook\FacebookRequest;
use My\Controller\MyController;
use Sunra\PhpSimple\HtmlDomParser,
    Zend\View\Model\ViewModel,
    My\General;

class IndexController extends MyController
{

    public function __construct()
    {

    }

    public function test($arrParams)
    {
        try {
            $config_fb = General::$config_fb;
            try {
                $fb = new \Facebook\Facebook([
                    'app_id' => $config_fb['appId'],
                    'app_secret' => $config_fb['secret']
                ]);
                $fb->setDefaultAccessToken(General::$face_traffic['mannhi_token']);
//                $fb->setDefaultAccessToken('EAACEdEose0cBAOty2Xj5AdUGjkKTC2B14ZBXNJYrc9lDF6UZBOQhovgQ5JzeF5LyNZBR2POiAL9N7sihK2xfi0B3lcFebGSOSYxsYrNTg7MvnseUsqcclouWgjscVC1YcKtXDMmjOjOivRPOWUEZAfRg2vxpn2DZAvnDljBU75wZDZD');

                $arrGroupId = [
                    '130509333676421'
                ];

                $rp = $fb->get('/me/friends');

                echo '<pre>';
                print_r($rp);
                echo '</pre>';
                die();


                $rp = $fb->post('/519051534870162/feed', [
                    'message' => 'Hello please join to Group'
                ]);
                echo '<pre>';
                print_r($rp);
                echo '</pre>';
                die();

                $rp = $fb->post('/me/feed', ['link' => 'https://www.facebook.com/khampha.tech/posts/216559732133912']);
                echo '<pre>';
                print_r($rp);
                echo '</pre>';
                die();
                echo \My\General::getColoredString(json_decode($rp->getBody(), true), 'green');
                echo \My\General::getColoredString('Share post id ' . $arrParams['post_id'] . ' to facebook ' . $arrParams['name'] . ' SUCCESS', 'green');
                unset($data, $return, $arrParams, $rp, $config_fb);
                return true;
            } catch (\Exception $exc) {
                echo '<pre>';
                print_r([
                    $exc->getCode(),
                    $exc->getMessage()
                ]);
                echo '</pre>';
                die();
                echo \My\General::getColoredString($exc->getMessage(), 'red');
                echo \My\General::getColoredString('Share post id ' . $arrParams['post_id'] . ' to facebook ' . $arrParams['name'] . ' ERROR', 'red');
                return true;
            }

        } catch (Exception $e) {
            echo \My\General::getColoredString($e->getMessage(), 'red');
            return true;
        }
    }

    public function indexAction()
    {
        try {
            return;

            $config_fb = General::$config_fb;
            $face_token = General::$face_traffic;

            $appsecret_proof= hash_hmac('sha256', 'EAACEdEose0cBAAiZCgZBQ9mJT5XtBxFdIacMRaJurd3vOUVeD51UmFxwpUoCXZCpYU9dIHjsm2oZBYDK5unYAJYZC2NbCaeIKgan9MRDHcXSX8JPKMGATe6LuxCuIDCOellwGquZAiZAzMSiu5X5IkguPQBlyd3SPLlHT8EaqPxzwZDZD', $config_fb['secret']);

            $fb = new \Facebook\Facebook([
                'app_id' => $config_fb['appId'],
                'app_secret' => $config_fb['secret'],
//                'default_graph_version' => 'v2.3',
//                'appsecret_proof' => $appsecret_proof,
                'default_access_token' => 'EAAJTaWjIMUkBABm5MhcAaWVd4fOzECqtlenyDuZCP5iKYHjtSltoiymLqiALv54vJFZCZBlF9M9nj4DTKHZCrzvKLxZAfnzAT904MVFOL1wCAH0YreIOq6NEJGQyPsrOEFXF4p0UVOlBkkxzVT1OGweDZAnovE4WrMqUZA9NvIyFwZDZD'
            ]);

            $rp = $fb->get('/230301240759761/likes');

            echo '<pre>';
            print_r($rp);
            echo '</pre>';
            die();

            //$appsecret_proof= hash_hmac('sha256', $app_access_token, $app_secret);

//            $session = ($fb_access_token);
//            $request = new FacebookRequest( $session, 'GET', '/me' );
//            $response = $request->execute();
            // get response
//            $graphObject = $response->getGraphObject();

//            echo '<pre>';
//            print_r($appsecret_proof);
//            echo '</pre>';
//            die();
//            $fb->ap
//            $fb->
//            $fb->setDefaultAccessToken('EAACEdEose0cBAAiZCgZBQ9mJT5XtBxFdIacMRaJurd3vOUVeD51UmFxwpUoCXZCpYU9dIHjsm2oZBYDK5unYAJYZC2NbCaeIKgan9MRDHcXSX8JPKMGATe6LuxCuIDCOellwGquZAiZAzMSiu5X5IkguPQBlyd3SPLlHT8EaqPxzwZDZD');
//            /{user-id}/groups

            $rp = $fb->get('/me/groups');

            echo '<pre>';
            print_r($rp);
            echo '</pre>';
            die();
            $config_fb = General::$config_fb;
            $fb = new \Facebook\Facebook([
                'app_id' => $config_fb['appId'],
                'app_secret' => $config_fb['secret'],
                'default_graph_version' => 'v2.3',
            ]);

            $appsecret_proof= hash_hmac('sha256', 'EAACEdEose0cBAAacemBZBvboyIhozZBkocpupo9Uj1vgGzC4tT39ZBZAZAIZACPVxME7TqNZCzc0z81ZBUH7xauQHaVZCGNtEZCmHbgYJfMdh5f3nlHBJhkV1tjNo6iz7vBxaDcw6wXYOTHXjOCMunKvmDCBRoJ4Ssfkty9L6HGTIiqAZDZD', $config_fb['secret']);
//            $fb->
            $fb->setDefaultAccessToken('EAACEdEose0cBAAacemBZBvboyIhozZBkocpupo9Uj1vgGzC4tT39ZBZAZAIZACPVxME7TqNZCzc0z81ZBUH7xauQHaVZCGNtEZCmHbgYJfMdh5f3nlHBJhkV1tjNo6iz7vBxaDcw6wXYOTHXjOCMunKvmDCBRoJ4Ssfkty9L6HGTIiqAZDZD');
            $rp = $fb->get('/me/groups');
//        230106094112609
            echo '<pre>';
            print_r($rp);
            echo '</pre>';
            die();
        } catch (\Exception $ex) {
            echo '<pre>';
            print_r($ex->getMessage());
            echo '</pre>';
            die();
        }


        try {
            $config_fb = General::$config_fb;
            $fb = new \Facebook\Facebook([
                'app_id' => $config_fb['appId'],
                'app_secret' => $config_fb['secret']
            ]);
            $face_token = General::$face_traffic;

            $fb->setDefaultAccessToken('EAAJTaWjIMUkBAJIWO1vYfuSEz94BvdgvpKKZAAGDiBv4lBZClDV78vWbizfP5C9STQTATBqrFaoafFLkEQAEcuwqNWTTyyB7D0TIdmUy5CDvipNr2bfv5Nm8kBRZBtwOO6QhTUAaxFnPLlBDWEBgfZBcgqNx9X0ZD');
//            /{user-id}/groups

            $rp = $fb->get('/me/groups?fields=id,name');

            echo '<pre>';
            print_r($rp);
            echo '</pre>';
            die();
        } catch (\Exception $ex) {
            echo '<pre>';
            print_r($ex->getMessage());
            echo '</pre>';
            die();
        }

        return;
        //$this->test();
        $config_fb = General::$config_fb;
        $fb = new \Facebook\Facebook([
            'app_id' => $config_fb['appId'],
            'app_secret' => $config_fb['secret']
        ]);
        $fb->setDefaultAccessToken(General::$face_traffic['mannhi_token']);


        return;
        try {
            $hr = 'http://www.youtube.com/get_video_info?&video_id=kjOyslGKq0A&asv=3&el=detailpage&hl=en_US';

            $rp = General::crawler($hr);
            $thumbnail_url = $title = $url_encoded_fmt_stream_map = $type = $url = '';
            parse_str($rp);
            $my_formats_array = explode(',', $url_encoded_fmt_stream_map);
//            if(empty($url_encoded_fmt_stream_map)) {
//
//            }

            $avail_formats[] = '';
            $i = 0;
            $ipbits = $ip = $itag = $sig = $quality = '';
            $expire = time();
            foreach ($my_formats_array as $format) {
                parse_str($format);
                $avail_formats[$i]['itag'] = $itag;
                $avail_formats[$i]['quality'] = $quality;
                $type = explode(';', $type);
                $avail_formats[$i]['type'] = $type[0];
                $avail_formats[$i]['url'] = urldecode($url) . '&signature=' . $sig;
                parse_str(urldecode($url));
                $avail_formats[$i]['expires'] = date("G:i:s T", $expire);
                $avail_formats[$i]['ipbits'] = $ipbits;
                $avail_formats[$i]['ip'] = $ip;
                $i++;
            }
            echo '<pre>';
            print_r($avail_formats);
            echo '</pre>';
            die();

            $channel_id = 'UCxqZzTt5waSiUo2Huh_9MLQ';
            $google_config = \My\General::$google_config;
            $client = new \Google_Client();
            $client->setDeveloperKey($google_config['key']);

            // Define an object that will be used to make all API requests.
            $youtube = new \Google_Service_YouTube($client);
            $searchResponse = $youtube->search->listSearch(
                'snippet', array(
                    'channelId' => $channel_id,
                    'maxResults' => 50,
                    'order' => 'date'
                )
            );

            if (empty($searchResponse) || empty($searchResponse->getItems())) {
                return;
            }

            foreach ($searchResponse->getItems() as $key => $item) {
                if (empty($item) || empty($item->getSnippet())) {
                    continue;
                }
                $id = $item->getId()->getVideoId();

//                get source
                $url = 'http://www.youtube.com/get_video_info?&video_id=kjOyslGKq0A&asv=3&el=detailpage&hl=en_US';
                echo '<pre>';
                print_r($id);
                echo '</pre>';
                die();


                if (empty($id)) {
                    continue;
                }

                $title = $item->getSnippet()->getTitle();

                if (empty($title)) {
                    continue;
                }

                $description = $item->getSnippet()->getDescription();
                $main_image = $item->getSnippet()->getThumbnails()->getMedium()->getUrl();

                //
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

                $serviceContent = $this->serviceLocator->get('My\Models\Content');
                $id = $serviceContent->add($arr_data_content);
                if ($id) {
                    $arr_data_content['cont_id'] = $id;
                    $this->postToFb($arr_data_content);
                    echo \My\General::getColoredString("Crawler success 1 post id = {$id} \n", 'green');
                } else {
                    echo \My\General::getColoredString("Can not insert content db", 'red');
                }
                unset($serviceContent);
                unset($arr_data_content);
                $this->flush();
                slep(1);
                continue;
            }
            echo '<pre>';
            print_r($searchResponse);
            echo '</pre>';
            die();

            Utils::writeLog($fileNameSuccess, $arrParam);
        } catch (\Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }

        return;
//        $arr_cate_yahoo = [
//            '28' => [
//                'https://vn.answers.yahoo.com/dir/index?sid=396545401',
//                'https://vn.answers.yahoo.com/dir/index?sid=396545469',
//                'https://vn.answers.yahoo.com/dir/index?sid=396545433',
//                'https://vn.answers.yahoo.com/dir/index?sid=396545015',
//                'https://vn.answers.yahoo.com/dir/index?sid=396545016',
//                'https://vn.answers.yahoo.com/dir/index?sid=396545013',
//                'https://vn.answers.yahoo.com/dir/index?sid=396545451',
//                'https://vn.answers.yahoo.com/dir/index?sid=396545394',
//                'https://vn.answers.yahoo.com/dir/index?sid=396545327',
//                'https://vn.answers.yahoo.com/dir/index?sid=396545018',
//                'https://vn.answers.yahoo.com/dir/index?sid=396546046',
//                'https://vn.answers.yahoo.com/dir/index?sid=396545213',
//                'https://vn.answers.yahoo.com/dir/index?sid=396545444',
//                'https://vn.answers.yahoo.com/dir/index?sid=396545439',
//                'https://vn.answers.yahoo.com/dir/index?sid=396545019',
//                'https://vn.answers.yahoo.com/dir/index?sid=396545454',
//                'https://vn.answers.yahoo.com/dir/index?sid=396545012',
//                'https://vn.answers.yahoo.com/dir/index?sid=396545443',
//                'https://vn.answers.yahoo.com/dir/index?sid=396545144'
//            ],
//            '27' => [
//                'https://vn.answers.yahoo.com/dir/index?sid=396545122',
//                'https://vn.answers.yahoo.com/dir/index?sid=396545301',
//                'https://vn.answers.yahoo.com/dir/index?sid=396545660',
//            ]
//        ];
//
//        $arr_cate_no = [
//            'https://www.thegioididong.com/hoi-dap',
//            ''
//        ];
//
//        foreach ($arr_cate_yahoo as $key => $data){
//            foreach ($data as $url){
//                $response = General::crawler($url);
//                echo '<pre>';
//                print_r($response);
//                echo '</pre>';
//                die();
//            }
//            echo '<pre>';
//            print_r($data);
//            echo '</pre>';
//            die();
//        }
//
//        return;
        $instanceSearchContent = new \My\Search\Content();
        $google_config = General::$google_config;
        $client = new \Google_Client();
        $client->setDeveloperKey($google_config['key']);

        $videoPath = '/var/source/video/my_file.mp4';

        // Define an object that will be used to make all API requests.
        $youtube = new \Google_Service_YouTube($client);

        try {
//            https://www.googleapis.com/youtube/v3/videos?part=contentDetails&chart=mostPopular&regionCode=IN&maxResults=25&key=API_KEY
            //get channel of user
//            $searchResponse = $youtube->channels->listChannels(
//                'snippet', array(
//                    'forUsername' => 'HongAnEntertainment',
//                    'maxResults' => 50
//                )
//            );

//                                $youtube->playlistItems->listPlaylistItems('snippet', array(
//                                'channelId' => $channel_id,
//                                'maxResults' => 50
//                            ));

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
                    $token_page = null;
                    for ($i = 0; $i <= 1000; $i++) {
                        if ($i == 0) {
                            $searchResponse = $youtube->search->listSearch(
                                'snippet', array(
                                    'channelId' => $channel_id,
                                    'maxResults' => 50
                                )
                            );
                        } else {
                            if (empty($token_page)) {
                                break;
                            }
                            $searchResponse = $youtube->search->listSearch(
                                'snippet', array(
                                    'channelId' => $channel_id,
                                    'maxResults' => 50,
                                    'pageToken' => $token_page
                                )
                            );
                        }

                        if (empty($searchResponse) || empty($searchResponse->getItems())) {
                            break;
                        }

                        $token_page = $searchResponse->getNextPageToken();

                        foreach ($searchResponse->getItems() as $item) {
                            $id = $item->getId()->getVideoId();
                            $title = $item->getSnippet()->getTitle();
                            $description = $item->getSnippet()->getDescription();
                            $main_image = $item->getSnippet()->getThumbnails()->getMedium()->getUrl();

                            //
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

                            $serviceContent = $this->serviceLocator->get('My\Models\Content');
                            $id = $serviceContent->add($arr_data_content);
                            if ($id) {
                                $arr_data_content['cont_id'] = $id;
                                //$this->postToFb($arr_data_content);
                                echo \My\General::getColoredString("Crawler success 1 post id = {$id} \n", 'green');
                            } else {
                                echo \My\General::getColoredString("Can not insert content db", 'red');
                            }
                            unset($serviceContent);
                            unset($arr_data_content);
                            $this->flush();
                            continue;
                        }
                    }
                }
            }

        } catch (\Exception $exc) {
            echo '<pre>';
            print_r($exc->getMessage());
            echo '</pre>';
            die();
        }
        die('DONE');
        echo '<pre>';
        print_r($searchResponse);
        echo '</pre>';
        die();

        return;
        $arr_cate_yahoo = [
            '28' => [
                'https://vn.answers.yahoo.com/dir/index?sid=396545401',
                'https://vn.answers.yahoo.com/dir/index?sid=396545469',
                'https://vn.answers.yahoo.com/dir/index?sid=396545433',
                'https://vn.answers.yahoo.com/dir/index?sid=396545015',
                'https://vn.answers.yahoo.com/dir/index?sid=396545016',
                'https://vn.answers.yahoo.com/dir/index?sid=396545013',
                'https://vn.answers.yahoo.com/dir/index?sid=396545451',
                'https://vn.answers.yahoo.com/dir/index?sid=396545394',
                'https://vn.answers.yahoo.com/dir/index?sid=396545327',
                'https://vn.answers.yahoo.com/dir/index?sid=396545018',
                'https://vn.answers.yahoo.com/dir/index?sid=396546046',
                'https://vn.answers.yahoo.com/dir/index?sid=396545213',
                'https://vn.answers.yahoo.com/dir/index?sid=396545444',
                'https://vn.answers.yahoo.com/dir/index?sid=396545439',
                'https://vn.answers.yahoo.com/dir/index?sid=396545019',
                'https://vn.answers.yahoo.com/dir/index?sid=396545454',
                'https://vn.answers.yahoo.com/dir/index?sid=396545012',
                'https://vn.answers.yahoo.com/dir/index?sid=396545443',
                'https://vn.answers.yahoo.com/dir/index?sid=396545144'
            ],
            '27' => [
                'https://vn.answers.yahoo.com/dir/index?sid=396545122',
                'https://vn.answers.yahoo.com/dir/index?sid=396545301',
                'https://vn.answers.yahoo.com/dir/index?sid=396545660',
            ]
        ];

        $arr_cate_no = [
            'https://www.thegioididong.com/hoi-dap',
            ''
        ];

        return;
        $current_date = date('Y-m-d');
        $instanceSearchKeyWord = new \My\Search\Keyword();
        for ($i = 0; $i <= 10000; $i++) {
            $date = strtotime('-' . $i . ' day', strtotime($current_date));
            $date = date('Ymd', $date);
            $href = 'https://www.google.com/trends/hottrends/hotItems?ajax=1&pn=p28&htd=' . $date . '&htv=l';
            $responseCurl = General::crawler($href);
            $arrData = json_decode($responseCurl, true);
            foreach ($arrData['trendsByDateList'] as $data) {
                foreach ($data['trendsList'] as $data1) {
                    $arr_key[] = $data1['title'];
                    if (!empty($data1['relatedSearchesList'])) {
                        $arr_key = array_merge($arr_key, $data1['relatedSearchesList']);
                    }
                    $strDescription = '';
                    if (!empty($data1['newsArticlesList'])) {
                        foreach ($data1['newsArticlesList'] as $val) {
                            $strDescription .= $val['snippet'] . ',';
                        }

                    }

                    foreach ($arr_key as $val) {
                        $is_exits = $instanceSearchKeyWord->getDetail(['key_slug' => trim(General::getSlug($val))]);

                        if ($is_exits) {
                            echo \My\General::getColoredString("đã tồn tại {$val}", 'yellow') . '<br/>';
                            continue;
                        }

                        $arr_data = [
                            'key_name' => $val,
                            'key_slug' => General::getSlug($val),
                            'is_crawler' => 0,
                            'created_date' => time()
                        ];

                        $serviceKeyword = $this->serviceLocator->get('My\Models\Keyword');
                        $int_result = $serviceKeyword->add($arr_data);
                        unset($serviceKeyword);
                        if ($int_result) {
                            echo \My\General::getColoredString("Insert success 1 row with id = {$int_result}", 'yellow');
                        }
                        $this->flush();
                    }
                    $this->flush();
                }
                $this->flush();
            }
            $this->flush();
        }
        die('eee');

        return;
        $instanceSearchContent = new \My\Search\Content();
        $arr_content = $instanceSearchContent->getDetail([
            'cont_id' => 59339
        ]);
        $this->test($arr_content);
        return;
        $instanceSearchCategory = new \My\Search\Category();
        $arr_category = $instanceSearchCategory->getList(['cate_status' => 1], [], ['cate_sort' => ['order' => 'asc'], 'cate_id' => ['order' => 'asc']]);
        $instanceSearchContent = new \My\Search\Content();
        foreach ($arr_category as $category) {
            if (empty($category['cate_crawler_url'])) {
                continue;
            }
            for ($i = 290; $i >= 1; $i--) {
                $source_url = $category['cate_crawler_url'] . '?p=' . $i;
                $page_cate_content = General::crawler($source_url);
                $page_cate_dom = HtmlDomParser::str_get_html($page_cate_content);
                try {
                    $item_content_in_cate = $page_cate_dom->find('.listitem');
                } catch (\Exception $exc) {
                    continue;
                }
                if (empty($item_content_in_cate)) {
                    continue;
                }

                foreach ($item_content_in_cate as $item_content) {
                    $arr_data_content = [];
                    $item_content_dom = HtmlDomParser::str_get_html($item_content->outertext);
                    $item_content_source = 'http://khoahoc.tv' . $item_content_dom->find('a', 0)->href;
                    $item_content_title = trim($item_content_dom->find('.title', 0)->plaintext);
                    $arr_data_content['cont_title'] = html_entity_decode($item_content_title);
                    $arr_data_content['cont_slug'] = General::getSlug(html_entity_decode($item_content_title));

                    $item_content_description = html_entity_decode(trim($item_content_dom->find('.desc', 0)->plaintext));
                    $img_avatar_url = $item_content_dom->find('img', 0)->src;
                    $arr_detail = $instanceSearchContent->getDetail(['cont_slug' => $arr_data_content['cont_slug'], 'not_cont_status' => -1]);

                    if (!empty($arr_detail)) {
                        continue;
                    }

//lấy hình đại diện
                    if ($img_avatar_url == 'http://img.khoahoc.tv/photos/image/blank.png') {
                        $arr_data_content['cont_main_image'] = STATIC_URL . '/f/v1/img/black.png';
                    } else {
                        $extension = end(explode('.', end(explode('/', $img_avatar_url))));
                        $name = $arr_data_content['cont_slug'] . '.' . $extension;
                        file_put_contents(STATIC_PATH . '/uploads/content/' . $name, General::crawler($img_avatar_url));
                        $arr_data_content['cont_main_image'] = STATIC_URL . '/uploads/content/' . $name;
                    }

//crawler nội dung bài đọc
                    $content_detail_page_dom = HtmlDomParser::str_get_html(General::crawler($item_content_source));
                    foreach ($content_detail_page_dom->find('script') as $item) {
                        $item->outertext = '';
                    }
                    foreach ($content_detail_page_dom->find('.adbox') as $item) {
                        $item->outertext = '';
                    }
                    $content_detail_html = $content_detail_page_dom->find('.content-detail', 0);
                    $content_detail_outertext = $content_detail_page_dom->find('.content-detail', 0)->outertext;
                    $img_all = $content_detail_html->find("img");

//lấy hình ảnh trong bài
                    if (count($img_all) > 0) {
                        foreach ($img_all as $key => $im) {
                            $extension = end(explode('.', end(explode('/', $im->src))));
                            $name = $arr_data_content['cont_slug'] . '-' . ($key + 1) . '.' . $extension;
                            file_put_contents(STATIC_PATH . '/uploads/content/' . $name, General::crawler($im->src));
                            $content_detail_outertext = str_replace($im->src, STATIC_URL . '/uploads/content/' . $name, $content_detail_outertext);
                        }
                    }

                    $content_detail_outertext = trim(strip_tags($content_detail_outertext, '<a>
    <div><img><b>
            <p><br><span><br/><strong><h2><h1><h3><h4><table><td><tr><th><tbody>'));
                    $arr_data_content['cont_detail'] = html_entity_decode($content_detail_outertext);
                    $arr_data_content['created_date'] = time();
                    $arr_data_content['user_created'] = 1;
                    $arr_data_content['cate_id'] = $category['cate_id'];
                    $arr_data_content['cont_description'] = $item_content_description;
                    $arr_data_content['cont_status'] = 1;
                    $arr_data_content['cont_views'] = rand(1, rand(100, 1000));
                    $arr_data_content['method'] = 'crawler';
                    $arr_data_content['from_source'] = $item_content_source;
                    $arr_data_content['meta_keyword'] = str_replace(' ', ',', $arr_data_content['cont_title']);
                    $arr_data_content['updated_date'] = time();
                    unset($content_detail_outertext);
                    unset($img_all);
                    unset($img_avatar_url);
                    unset($content_detail_html);
                    unset($content_detail_page_dom);
                    unset($item_content_dom);

                    $serviceContent = $this->serviceLocator->get('My\Models\Content');
                    $id = $serviceContent->add($arr_data_content);

                    if ($id) {
                        echo \My\General::getColoredString("Crawler success 1 post id = {$id} \n", 'green');
                    } else {
                        echo \My\General::getColoredString("Can not insert content db", 'red');
                    }

                    unset($serviceContent);
                    unset($arr_data_content);
                    $this->flush();
                    continue;
                }
            }
        }
        return;

        $arr_key_start = [
            'tin tuc', 'tin tuc quoc te', 'tin tuc trong nuoc', 'the thao', 'tin the thao', 'cup c1', 'ngoai hang anh', 'laliga', 'champion league',
            'giai vo dich quoc gia', 'vleague', 'tennis', 'quan vot', 'xe cong thuc 1', 'dua ngua', 'tin tuc thoi su', 'tin hot', 'europa league', 'world cup',
            'ronaldo', 'messi', 'robben', 'rooney', 'nguyen cong phuong', 'nguoi dep', 'gioi tre', 'guong mat tre', 'thoi trang', 'lam dep', 'mac dep', 'trang diem',
            'ca si', 'showbiz', 'manchester united', 'liverpool', 'real marrid', 'barcelona', 'sexy', 'khoe hang', 'son tung mtp', 'hot girl', 'giai tri', 'sao viet', 'sao chau a', 'sao hollyword',
            'truyen cuoi', 'anh hai huoc', 'truyen tieu lam', 'tin nong', 'tin giat gan', 'hai huoc', 'cong nghe', 'do choi cong nghe',
            'dien thoai', 'smart phone', 'dien thoai thong minh', 'internet', 'kham pha cong nghe', 'cong nghe so', 'thu thuat',
            'thoi trang sao', 'goc hai huoc', 'cong dong mang', 'su kien'
        ];

        $serviceKeyword = $this->serviceLocator->get('My\Models\Keyword');

        foreach ($arr_key_start as $key_word) {
            $arr_data = [
                'key_name' => $key_word,
                'key_slug' => General::getSlug($key_word)
            ];
            $id = $serviceKeyword->add($arr_data);
        }
        die('done');

        $arr = [
            ' a', ' b', ' c', ' d', ' e'
        ];
        $keyword = 'bien tan';

        foreach ($arr as $key => $v) {
            $keytemp = $keyword . $v;
            $url = 'http://www.google.com/complete/search?output=search&client=chrome&q=' . rawurlencode($keytemp) . '&hl=vi&gl=vn';
            $content = General::crawler($url);
            echo '<pre>';
            print_r(json_decode($content));
            echo '</pre>';
//            die();
        }
        die();

        //http://www.google.com/complete/search?output=search&client=chrome&q=etec&hl=vi&gl=vn
        $url = 'http://www.google.com/complete/search?output=search&client=chrome&q=' . rawurlencode($key) . '&hl=vi&gl=vn';
//        echo '<pre>';
//        print_r($url);
//        echo '</pre>';
//        die();

        $content = General::crawler($url);

        echo '<pre>';
        print_r(json_decode($content));
        echo '</pre>';
        die();
        foreach ($arr as $v) {

        }
        echo '<pre>';
        print_r(json_decode('["etec a",["etec associates","etec arabia","etec at","etec adalah","etec albert einstein","etec auckland","etec antibiotics","etec americana","etec artes","stec and ehec","etec araraquara","etec aristóteles ferreira","etec agency","etec alberto santos dumont","etec aruja","etec australia","etec atibaia","etec araçatuba","etech antivirus","etec avare"],["","","","","","","","","","","","","","","","","","","",""],[],{"google:clientdata":{"bpc":false,"tlw":false},"google:suggestrelevance":[601,600,567,566,565,564,563,562,561,560,559,558,557,556,555,554,553,552,551,550],"google:suggesttype":["QUERY","QUERY","QUERY","QUERY","QUERY","QUERY","QUERY","QUERY","QUERY","QUERY","QUERY","QUERY","QUERY","QUERY","QUERY","QUERY","QUERY","QUERY","QUERY","QUERY"],"google:verbatimrelevance":851}]'));
        echo '</pre>';
        die();
        return;
        include PUBLIC_PATH . '/simple_html_dom.php';
//        http://2sao.vn/p0c1005/hoi-dap/trang-50.vnn
        $arr_cate = [
            24 => 'http://2sao.vn/p0c1005/hoi-dap/trang-'
        ];

        foreach ($arr_cate as $cate_id => $strURL) {
            echo \My\General::getColoredString("Start crawler url {$strURL} \n", 'red');

            for ($i = 100; $i >= 1; $i--) {
                $sourceURL = $strURL . $i . '.vnn';

                echo \My\General::getColoredString("Start crawler url {$sourceURL} \n", 'green');

                $content = General::crawler($sourceURL);
                $dom = str_get_html($content);

                $results = $dom->find('.span85 .nav1 li.lilist .divnav2 a');

                if (count($results) <= 0) {
                    continue;
                }
//                ksort($results);

                foreach ($results as $item) {
                    if (strpos($item->href, 'clip')) {
                        echo \My\General::getColoredString("Khong lay clip url {$item->href} \n", 'red');
                        continue;
                    }
                    $arr_data = [];
                    $arr_data['cont_title'] = trim($item->plaintext);
                    $arr_data['cont_slug'] = General::getSlug($arr_data['cont_title']);

                    //find in db with
                    $instanceSearchContent = new \My\Search\Content();
                    $arr_content_detail = $instanceSearchContent->getDetail(['cont_slug' => $arr_data['cont_slug'], 'not_cont_status' => -1, 'cate_id' => $cate_id]);

                    if ($arr_content_detail) {
                        echo \My\General::getColoredString("Continue with exits title", 'red');
                        continue;
                    }

                    $content = General::crawler('http://2sao.vn' . $item->href);

                    if ($content == false) {
                        continue;
                    }

                    $html = str_get_html($content);

                    $arr_data['cont_desciption'] = trim($html->find('.fixfont', 0)->plaintext);

                    $cont_detail = $html->find('.2saodetial', 0)->outertext;
                    $img = $html->find(".2saodetial img");

                    if (count($img) > 0) {
                        $arr_data['cont_image'] = [];
                        foreach ($img as $key => $im) {
                            $extension = end(explode('.', end(explode('/', $im->src))));
                            $name = $arr_data['cont_slug'] . '-' . ($key + 1) . '.' . $extension;
                            file_put_contents(STATIC_PATH . '/uploads/content/' . $name, General::crawler($im->src));
                            $cont_detail = str_replace($im->src, STATIC_URL . '/uploads/content/' . $name, $cont_detail);
                            if ($key == 0) {
                                $arr_data['cont_main_image'] = STATIC_URL . '/uploads/content/' . $name;
                                $images = General::resizeImages('content', STATIC_PATH . '/uploads/content/' . $name, $name);
                                if ($images != false) {
                                    $arr_data['cont_image'] = json_encode($images);
                                }
                            }
                        }
                    }

                    $cont_detail = trim(strip_tags($cont_detail, '<img><b>
            <p><br><span><br/><strong><table><td><tr><th><tbody>'));
                    $cont_detail = str_replace('class="Normal"', 'class="content"', $cont_detail);
                    $arr_data['cont_detail'] = $cont_detail;

                    unset($cont_detail);
                    unset($content);
                    unset($html);
                    unset($img);

                    $arr_data['cont_detail_text'] = trim(strip_tags($arr_data['cont_detail']));
                    $arr_data['created_date'] = time();
                    $arr_data['updated_date'] = time();
                    $arr_data['cate_id'] = $cate_id;
                    $arr_data['method'] = 'crawler';
                    $arr_data['from_source'] = '2sao.vn';
                    $arr_data['cont_views'] = 0;
                    $arr_data['meta_keyword'] = str_replace(' ', ',', $arr_data['cont_title']);
                    $arr_data['cont_status'] = 1;

                    $serviceContent = $this->serviceLocator->get('My\Models\Content');
                    $id = $serviceContent->add($arr_data);

                    if ($id) {
                        echo \My\General::getColoredString("Crawler success 1 post from 2sao.vn id = {$id} \n", 'green');
                    } else {
                        echo \My\General::getColoredString("Can not insert content db", 'red');
                    }
                    unset($serviceContent);
                    unset($arr_data);

                    $this->flush();
                }
            }
            echo \My\General::getColoredString("Crawler 2SAO.VN success {$strURL} \n", 'green');
        }
        echo \My\General::getColoredString("Crawler success 2SAO.VN", 'green');
        return true;
    }

    public function coverStr($str)
    {
        $arrPatent = [
            'mọi người',
            'tận nhà',
            'mobi',
            'vina',
            'https://web.facebook.com/',
            'https://facebook.com/',
            'Đ/C',
            'A.',
            'bạn',
            'lh',
            'v/c',
            'Tôi',
            'tôi',
            'nhà mới',
            'dt',
            'thue',
            'nha nguyen can',
            'QL1A',
            'Binh Dinh',
            'DT',
            'Tiện',
            'tiện',
            'ai cần',
            'LH',
            'Tuyển nhân viên',
        ];
        $arrReplace = [
            'tất cả mọi người',
            'tận nơi',
            'mobiphone',
            'vinaphone',
            'http://fb.com/',
            'http://fb.com/',
            'địa chỉ',
            'anh',
            'anh chị',
            'liên hệ',
            'vợ/chồng',
            'Mình',
            'mình',
            'nhà mới xây',
            'diện tích',
            'thuê',
            'nhà nguyên căn',
            'Quốc lộ 1A',
            'Bình Định',
            'diện tích',
            'Thuận tiện',
            'thuận tiện',
            'ai có nhu cầu',
            'liên hệ',
            'Cần tuyển nhân viên',
        ];

        $strRt = str_replace($arrPatent, $arrReplace, $str);
        return $strRt;
    }

    private function flush()
    {
        ob_end_flush();
        ob_flush();
        flush();
    }

    public function __khoahocTV()
    {
        $current_date = date('Y-m-d');
        $instanceSearchKeyWord = new \My\Search\Keyword();
        for ($i = 0; $i <= 2; $i++) {
            $date = strtotime('-' . $i . ' days', strtotime($current_date));
            $date = date('Ymd', $date);
            echo \My\General::getColoredString("Date = {$date}", 'cyan');
            $href = 'https://www.google.com/trends/hottrends/hotItems?ajax=1&pn=p28&htd=' . $date . '&htv=l';
            $responseCurl = General::crawler($href);
            $arrData = json_decode($responseCurl, true);

            foreach ($arrData['trendsByDateList'] as $data) {
                foreach ($data['trendsList'] as $data1) {
                    $arr_key[] = $data1['title'];
                    if (!empty($data1['relatedSearchesList'])) {
                        foreach ($data1['relatedSearchesList'] as $arr_temp) {
                            if (!empty($arr_temp['query'])) {
                                array_push($arr_key, $arr_temp['query']);
                            }
                        }
                    }

                    foreach ($arr_key as $val) {
                        $is_exits = $instanceSearchKeyWord->getDetail(['key_slug' => trim(General::getSlug($val))]);

                        if ($is_exits) {
                            echo \My\General::getColoredString("exist {$val}", 'red') . '<br/>';
                            continue;
                        }

                        //search vào gg
                        $gg_rp = General::crawler('https://www.google.com.vn/search?q=' . rawurlencode($val));
                        $gg_rp_dom = HtmlDomParser::str_get_html($gg_rp);
                        $key_description = '';
                        foreach ($gg_rp_dom->find('.srg .st') as $item) {
                            empty($key_description) ?
                                $key_description .= '<p><strong>' . strip_tags($item->outertext) . '</strong></p>' :
                                $key_description .= '<p>' . strip_tags($item->outertext) . '</p>';
                        }
                        $serviceKeyword = $this->serviceLocator->get('My\Models\Keyword');
                        $id_key = $serviceKeyword->add([
                            'key_name' => $val,
                            'key_slug' => General::getSlug($val),
                            'is_crawler' => 0,
                            'created_date' => time(),
                            'key_description' => $key_description
                        ]);
                        if ($id_key) {
                            echo \My\General::getColoredString("Insert to tbl_keyword success key_name =  {$val} \n", 'green');
                        } else {
                            echo \My\General::getColoredString("Insert to tbl_keyword ERROR key_name =  {$val} \n", 'red');
                        }
                        unset($serviceKeyword, $gg_rp, $gg_rp_dom, $key_description, $id);
                        $this->flush();
                    }
                    $this->flush();
                }
                $this->flush();
            }
            $this->flush();
        }
        die('eee');
    }

    public function uploadVideoAction()
    {
        try {
            $google_config = General::$google_config;
            $client = new \Google_Client();
            $client->setDeveloperKey($google_config['key']);

            $videoPath = '/var/source/video/my_file.mp4';

            // Define an object that will be used to make all API requests.
            $youtube = new \Google_Service_YouTube($client);
            $snippet = new \Google_Service_YouTube_VideoSnippet();
            $snippet->setTitle("Test title");
            $snippet->setDescription("Test description");
            $snippet->setTags(array("tag1", "tag2"));
            $snippet->setCategoryId("22");

            //status
            $status = new \Google_Service_YouTube_VideoStatus();
            $status->privacyStatus = "public";

            //videos
            $video = new \Google_Service_YouTube_Video();
            $video->setSnippet($snippet);
            $video->setStatus($status);

            $chunkSizeBytes = 1 * 1024 * 1024;
            $client->setDefer(true);

            $insertRequest = $youtube->videos->insert("status,snippet", $video);

            // Create a MediaFileUpload object for resumable uploads.
            $media = new \Google_Http_MediaFileUpload(
                $client,
                $insertRequest,
                'video/*',
                null,
                true,
                $chunkSizeBytes
            );
            $media->setFileSize(filesize($videoPath));

            $status = false;
            $handle = fopen($videoPath, "rb");
            while (!$status && !feof($handle)) {
                $chunk = fread($handle, $chunkSizeBytes);
                $status = $media->nextChunk($chunk);
            }

            fclose($handle);

            // If you want to make other calls after the file upload, set setDefer back to false
            $client->setDefer(false);

            echo '<pre>';
            print_r($status);
            echo '</pre>';
            die();
        } catch (\Exception $exc) {
            echo '<pre>';
            print_r([
                'code' => $exc->getCode(),
                'message' => $exc->getMessage()
            ]);
            echo '</pre>';
            die();
        }
    }
}
