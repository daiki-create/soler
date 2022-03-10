<?php

class Liden_model extends CI_Model
{
    public function __construct()
    {
        $this->load->helper('phpquery');
        $this->load->model('tables/Liden_tbl');
    }

    public function scrapingCurrentLiden()
    {
        // 配列の初期化
        $liden_data_array = [];

        // 昨日
        $year = date('Y', strtotime('-1 day'));
        $month = date('m', strtotime('-1 day'));
        $day = date('d', strtotime('-1 day'));

        // 落雷データの取得
        $context = stream_context_create([
            'ssl' => [
                'verify_peer'      => false,
                'verify_peer_name' => false
            ]
        ]);
        $html = file_get_contents("https://weather.kakutyoutakaki.com/thander/index.php?year=".$year."&month=".$month."&day=".$day."&hour=24&min=0&kikan=24", false, $context);
        $dom = phpQuery::newDocument($html);
        $script = $dom->find('script:eq(8)');
        if(preg_match('/var\sstr_json\s=\s\[.*?\];/', $script, $matches))
        {
            $m = $matches[0];
            if(preg_match_all('/\"coordinates\":\[.*?\]/', $m, $matches))
            {
                foreach($matches[0] as $m)
                {
                    $coordinates = trim( $m, '"coordinates":[]');
                    $coordinates_array = explode(',',$coordinates);
                    $lon = $coordinates_array[0];
                    $lat = $coordinates_array[1];
                    $liden_data = [
                        'date' => $year."-".$month."-".$day,
                        'lon' => $lon,
                        'lat' => $lat
                    ];
                    // 配列に追加
                    array_push($liden_data_array, $liden_data);
                }
            }
        }

        // 配列をリターン
        return $liden_data_array;
    }

    public function scrapingLiden($start_date,$end_date)
    {
        // 配列の初期化
        $liden_data_array = [];

        $context = stream_context_create([
            'ssl' => [
                'verify_peer'      => false,
                'verify_peer_name' => false
            ]
        ]);

        $yesterday = date('Y-m-d', strtotime('-1 day'));

        // バリデーション
        if(preg_match('/\A[0-9]{4}-[0-9]{1,2}-[0-9]{1,2}\z/', $start_date) == false)
        {
            return 'invalid_start_date';
        }
        if(preg_match('/\A[0-9]{4}-[0-9]{1,2}-[0-9]{1,2}\z/', $end_date) == false)
        {
            return 'invalid_end_date';
        }
        if($start_date < "2020-10-03")
        {
            return "too_old";
        }
        if($end_date > $yesterday)
        {
            return "too_new";
        }

        $start = strtotime($start_date);
        $end = strtotime($end_date);
        // 1日の秒数
        $sec = 60 * 60 * 24;// 60秒 × 60分 × 24時間
        // 日付取得
        $key = 0;
        for ($i = $start ; $i <= $end ; $i += $sec) {
            $dates[$key] = date("Y-m-d", $i);
            $key ++;
        }
        foreach($dates as $date){
            $date_array = explode('-',$date);
            $year = $date_array[0];
            $month = $date_array[1];
            $day = $date_array[2];

            // 落雷データの取得
            $html = file_get_contents("https://weather.kakutyoutakaki.com/thander/index.php?year=".$year."&month=".$month."&day=".$day."&hour=24&min=0&kikan=24", false, $context);
            $dom = phpQuery::newDocument($html);
            $script = $dom->find('script:eq(8)');
            if(preg_match('/var\sstr_json\s=\s\[.*?\];/', $script, $matches))
            {
                $m = $matches[0];
                if(preg_match_all('/\"coordinates\":\[.*?\]/', $m, $matches))
                {
                    foreach($matches[0] as $m)
                    {
                        $coordinates = trim( $m, '"coordinates":[]');
                        $coordinates_array = explode(',',$coordinates);
                        $lon = $coordinates_array[0];
                        $lat = $coordinates_array[1];
                        $liden_data = [
                            'date' => $year."-".$month."-".$day,
                            'lon' => $lon,
                            'lat' => $lat
                        ];
                        // 配列に追加
                        array_push($liden_data_array, $liden_data);
                    }
                }
            }
        }

        // 配列をリターン
        return $liden_data_array;
    }

    public function saveLiden($liden_data_array)
    {
        return $this->Liden_tbl->saveLiden($liden_data_array);
    }

    public function getLiden($request, $lon, $lat)
    {
        return $this->Liden_tbl->getLiden($request, $lon, $lat);
    }
}