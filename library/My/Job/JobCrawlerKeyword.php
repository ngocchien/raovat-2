<?php

namespace My\Job;

use My\General,
    Sunra\PhpSimple\HtmlDomParser,
    Zend\Dom\Query;

class JobCrawlerKeyword extends JobAbstract
{
    /*
     * crawler keyword from google
     */
    public function crawlerKeywordGoogle($arrParams = [], $serviceLocator)
    {
        $file = '/var/www/khampha/html/logs/updateKW.txt';
        $id_begin = exec('tail -n 1 /var/www/khampha/html/logs/updateKW.txt');

        if (strstr($id_begin, 'Cannot query; no document registered') || empty($id_begin)) {
            shell_exec("sed -i '$ d' /var/www/khampha/html/logs/updateKW.txt");
            $id_begin = exec('tail -n 1 /var/www/khampha/html/logs/updateKW.txt');
        }

        try {
            $instanceSearch = new \My\Search\Keyword();
            $arrKeyword = $instanceSearch->getListLimit(
                [
                    'key_id_greater' => $id_begin
                ],
                1,
                50,
                [
                    'key_id' => [
                        'order' => 'asc'
                    ]
                ],
                [
                    'key_id',
                    'key_name',
                    'key_description'
                ]
            );

            if (empty($arrKeyword)) {
                return true;
            }
            foreach ($arrKeyword as $arr) {
                if (empty($arr['key_id']) || !empty($arr['key_description'])) {
                    continue;
                }
                $last_id = $arr['key_id'];

                //search vào gg
                $url_gg = 'https://www.google.com.vn/search?sclient=psy-ab&biw=1366&bih=212&espv=2&q=' . rawurlencode($arr['key_name']) . '&oq=' . rawurlencode($arr['key_name']);

                $gg_rp = General::crawler($url_gg);

                $gg_rp_dom = new Query($gg_rp);
                $results = $gg_rp_dom->execute('.st');
                if (!count($results)) {
                    continue;
                }

                $key_description = '';

                foreach ($results as $item) {
                    empty($key_description) ?
                        $key_description .= '<p><strong>' . strip_tags($item->textContent) . '</strong></p>' :
                        $key_description .= '<p>' . strip_tags($item->textContent) . '</p>';
                }

                $serviceKeyword = $serviceLocator->get('My\Models\Keyword');
                $rs = $serviceKeyword->edit(['key_description' => $key_description], $arr['key_id']);
                if ($rs) {
                    file_put_contents($file, $arr['key_id'] . PHP_EOL, FILE_APPEND);
                } else {
                    file_put_contents($file, 'ERROR ID = ' . $arr['key_id'] . PHP_EOL, FILE_APPEND);
                    continue;
                }
                unset($serviceKeyword, $gg_rp, $gg_rp_dom, $key_description, $id, $url_gg, $results);
                $this->flush();

                //random sleep
                sleep(rand(4, 10));
            }
            $this->flush();
            unset($arrKeyword);
            exec("ps -ef | grep -v grep | grep update-new-key | awk '{ print $2 }'", $PID);

            return shell_exec('php ' . PUBLIC_PATH . '/index.php update-new-key --id=' . $last_id . ' --pid=' . current($PID));

        } catch (\Exception $exc) {
            file_put_contents($file, $exc->getCode() . ' => ' . $exc->getMessage() . PHP_EOL, FILE_APPEND);
            return true;
        }
    }

    static function flush()
    {
        ob_end_flush();
        ob_flush();
        flush();
    }
}
