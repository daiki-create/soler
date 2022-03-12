<?php

class Amedas_stations_model extends CI_Model
{
    public function __construct()
    {
        $this->load->helper('phpquery');
        $this->load->model('tables/Amedas_stations_tbl');
    }

    public function scrapingAmedasStations()
    {
        // 配列の初期化
        $amedas_stations_data = [];

        // 全国マップからそれぞれの県ナンバーのURLに遷移
        $html = file_get_contents("https://www.data.jma.go.jp/obd/stats/etrn/select/prefecture00.php?prec_no=91&block_no=1147&year=2022&month=01&day=24&view=p1");
        $dom = phpQuery::newDocument($html);
        $area_array = $dom['area'];
        foreach($area_array as $area) {
            $url = pq($area)->attr('href').PHP_EOL;

            // 県Noを取得
            $prec_no = "";
            $pos_prec_no = strpos($url, 'prec_no=');
            if(preg_match('/prec_no=.*?\&/', $url, $matches))
            {
                $m = $matches[0];
                $prec_no = trim( $m, 'prec_no=&');
            }

            // 各県全体のサイトをスクレイピング
            $html2 = file_get_contents("https://www.data.jma.go.jp/obd/stats/etrn/select/".$url);
            $dom = phpQuery::newDocument($html2);
            $area_array2 = $dom['area'];
            $i++;
            foreach($area_array2 as $area2) {
                // 重複は不要
                $i++;
                if($i%2 == 0)
                {
                    continue;
                }
                if($prec_no == "99")
                {
                    // 南極データは不要
                    continue;
                }
                $m = $matches[0];
                $m = trim( $m, '()' );
                $view_point = explode(',', $m);
                $lon = trim($view_point[6], '\'');
                $lat = trim($view_point[5], '\'');

                // アメダスデータ・・便宜上block_noだけ変数に格納
                $block_no = trim($view_point[1], '\'');
                $amedas_station_data = [
                    'st_name' => trim($view_point[2], '\''),
                    'capital_flag' => 0,
                    'prec_no' => $prec_no,
                    'block_no' => $block_no,
                    'lon' => trim($view_point[6], '\'') + trim($view_point[7], '\'')/60,
                    'lat' => trim($view_point[4], '\'') + trim($view_point[5], '\'')/60
                ];

                // 過去の気象検索画面から都市かどうか判定
                $html3 = file_get_contents("https://www.data.jma.go.jp/obd/stats/etrn/index.php?prec_no=".$prec_no."&block_no=".$block_no."&year=2022&month=02&day=28&view=p1");
                $dom = phpQuery::newDocument($html3);                
                $href = $dom->find('table:eq(20) a')->attr('href');
                if(preg_match('/daily_.*?1/', $href, $matches))
                {
                    if($matches[0] == 'daily_s1')
                    {
                        $amedas_station_data['capital_flag'] = 1;
                    }
                }
                var_dump($amedas_station_data);

                // 配列に追加
                array_push($amedas_stations_data, $amedas_station_data);
            }
        }

        // 配列をリターン
        return $amedas_stations_data;
    }

    public function saveAmedasStations($amedas_stations_data)
    {
        return $this->Amedas_stations_tbl->saveAmedasStations($amedas_stations_data);
    }

    public function scrapingTestAmedasStation()
    {
        // 配列の初期化
        $amedas_stations_data = [];

        // 全国マップからそれぞれの県ナンバーのURLに遷移
        $html = file_get_contents("https://www.data.jma.go.jp/obd/stats/etrn/select/prefecture00.php?prec_no=91&block_no=1147&year=2022&month=01&day=24&view=p1");
        $dom = phpQuery::newDocument($html);
        $area_array = $dom['area'];
        foreach($area_array as $area) {
            $url = pq($area)->attr('href').PHP_EOL;

            // 県Noを取得
            $prec_no = "";
            $pos_prec_no = strpos($url, 'prec_no=');
            if(preg_match('/prec_no=.*?\&/', $url, $matches))
            {
                $m = $matches[0];
                $prec_no = trim( $m, 'prec_no=&');
            }

            // 各県全体のサイトをスクレイピング
            $html2 = file_get_contents("https://www.data.jma.go.jp/obd/stats/etrn/select/".$url);
            $dom = phpQuery::newDocument($html2);
            $area_array2 = $dom['area'];
            $i=0;
            foreach($area_array2 as $area2) {
                $i++;
                if($i%2 == 0)
                {
                    continue;
                }
                $onmouseover = pq($area2)->attr('onmouseover').PHP_EOL;
                if($prec_no == "99")
                {
                    // 南極データは不要
                    continue;
                }
                $m = $matches[0];
                $m = trim( $m, '()' );
                $view_point = explode(',', $m);
                $lon = trim($view_point[6], '\'');
                $lat = trim($view_point[5], '\'');

                // アメダスデータ・・便宜上block_noだけ変数に格納
                $block_no = trim($view_point[1], '\'');
                $amedas_station_data = [
                    'st_name' => trim($view_point[2], '\''),
                    'capital_flag' => 0,
                    'prec_no' => $prec_no,
                    'block_no' => $block_no,
                    'lon' => trim($view_point[6], '\'') + trim($view_point[7], '\'')/60,
                    'lat' => trim($view_point[4], '\'') + trim($view_point[5], '\'')/60
                ];

                // 過去の気象検索画面から都市かどうか判定
                $html3 = file_get_contents("https://www.data.jma.go.jp/obd/stats/etrn/index.php?prec_no=".$prec_no."&block_no=".$block_no."&year=2022&month=02&day=28&view=p1");
                $dom = phpQuery::newDocument($html3);                
                $href = $dom->find('table:eq(20) a')->attr('href');
                if(preg_match('/daily_.*?1/', $href, $matches))
                {
                    if($matches[0] == 'daily_s1')
                    {
                        $amedas_station_data['capital_flag'] = 1;
                    }
                }
                var_dump($amedas_station_data);

                // 配列に追加
                array_push($amedas_stations_data, $amedas_station_data);
                break;
            }
            break;
        }

        // 配列をリターン
        return $amedas_stations_data;
    }
}