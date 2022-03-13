<?php

class Amedas_model extends CI_Model
{
    public function __construct()
    {
        $this->load->helper('phpquery');
        $this->load->model('tables/Amedas_tbl');
        $this->load->model('tables/Amedas_stations_tbl');
    }

    public function scrapingCurrentAmedas()
    {
        // 配列の初期化
        $amedas_data_array = [];

        // 昨日
        $year = date('Y', strtotime('-1 day'));
        $month = date('m', strtotime('-1 day'));
        $day = date('d', strtotime('-1 day'));

        // アメダス観測点をロード
        $amedas_stations = $this->Amedas_stations_tbl->getAmedasStations();
        $k=0;
        foreach($amedas_stations as $amedas_station)
        {
            $k++;
            $prec_no = $amedas_station->prec_no;
            $block_no = $amedas_station->block_no;

            // 降水量、風速データの取得
            if($amedas_station->capital_flag)
            {
                $html = file_get_contents("https://www.data.jma.go.jp/obd/stats/etrn/view/daily_s1.php?prec_no=".$prec_no."&block_no=".$block_no."&year=".$year."&month=".$month);
            }
            else
            {
                $html = file_get_contents("https://www.data.jma.go.jp/obd/stats/etrn/view/daily_a1.php?prec_no=".$prec_no."&block_no=".$block_no."&year=".$year."&month=".$month);
            }

            $dom = phpQuery::newDocument($html);

            $i = 0;
            foreach($dom['table:eq(5) tr'] as $row)
            {
                $i++;
                if($i < 5)
                {
                    continue;
                }
                $tr_day = pq($row)->find('td:eq(0)')->text();
                if($tr_day == $day)
                {
                    if($amedas_station->capital_flag)
                    {
                        $pricipitation = pq($row)->find('td:eq(3)')->text();
                        $wind_speed = pq($row)->find('td:eq(14)')->text();
                        $wind_direction = pq($row)->find('td:eq(15)')->text();                    
                    }
                    else
                    {
                        $pricipitation = pq($row)->find('td:eq(1)')->text();
                        $wind_speed = pq($row)->find('td:eq(10)')->text();
                        $wind_direction = pq($row)->find('td:eq(11)')->text();                    
                    }
                    $wind_direction = trim($wind_direction, ")]");
                    $amedas_data = [
                        'prec_no' => $prec_no,
                        'block_no' => $block_no,
                        'thander_flag' => 0,
                        'pricipitation' => $pricipitation,
                        'wind_speed' => $wind_speed,
                        'wind_direction' => $wind_direction,
                        'date' => $year."-".$month."-".$day
                    ];
                    break;
                }
            }
            // 現在使用されていない観測所
            if($wind_direction == "///" || $wind_direction == "")
            {
                continue;
            }

            // 雷雲フラグの取得
            if(!$amedas_station->capital_flag)
            {
                $amedas_data['thander_flag'] = 2;
            }
            else
            {
                $html2 = file_get_contents("https://www.data.jma.go.jp/obd/stats/etrn/view/hourly_a1.php?prec_no=".$prec_no."&block_no=".$block_no."&year=".$year."&month=".$month."&day=".$day."&view=p1");
                $dom2 = phpQuery::newDocument($html2);
                $j = 0;
                foreach($dom2['table:eq(4) tr'] as $row)
                {
                    $j++;
                    if($j < 2)
                    {
                        continue;
                    }
                    $alt = pq($row)->find('td:eq(14) img')->attr('alt');
                    if($alt == "雷電")
                    {
                        $amedas_data['thander_flag'] = 1;
                        break;
                    }
                }
            }
            var_dump($amedas_data);

            // 配列に追加
            array_push($amedas_data_array, $amedas_data);
        }

        // 配列をリターン
        return $amedas_data_array;
    }

    public function scrapingAmedas($start_date,$end_date)
    {
        // 配列の初期化
        $amedas_data_array = [];

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
        // if($start_date < "2020-10-03")
        // {
        //     return "too_old";
        // }
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

        // アメダス観測点をロード
        $amedas_stations = $this->Amedas_stations_tbl->getAmedasStations();
        $k=0;
        foreach($amedas_stations as $amedas_station)
        {
            $k++;
            $prec_no = $amedas_station->prec_no;
            $block_no = $amedas_station->block_no;
                       
            foreach($dates as $date){
                $date_array = explode('-',$date);
                $year = $date_array[0];
                $month = $date_array[1];
                $day = $date_array[2];

                // 降水量、風速データの取得
                if($amedas_station->capital_flag)
                {
                    $html = file_get_contents("https://www.data.jma.go.jp/obd/stats/etrn/view/daily_s1.php?prec_no=".$prec_no."&block_no=".$block_no."&year=".$year."&month=".$month);
                }
                else
                {
                    $html = file_get_contents("https://www.data.jma.go.jp/obd/stats/etrn/view/daily_a1.php?prec_no=".$prec_no."&block_no=".$block_no."&year=".$year."&month=".$month);
                }                
                $dom = phpQuery::newDocument($html);
                $i = 0;
                foreach($dom['table:eq(5) tr'] as $row)
                {
                    $i++;
                    if($i < 5)
                    {
                        continue;
                    }
                    $tr_day = pq($row)->find('td:eq(0)')->text();
                    
                    if($tr_day == $day)
                    {
                        if($amedas_station->capital_flag)
                        {
                            $pricipitation = pq($row)->find('td:eq(3)')->text();
                            $wind_speed = pq($row)->find('td:eq(14)')->text();
                            $wind_direction = pq($row)->find('td:eq(15)')->text();                    
                        }
                        else
                        {
                            $pricipitation = pq($row)->find('td:eq(1)')->text();
                            $wind_speed = pq($row)->find('td:eq(10)')->text();
                            $wind_direction = pq($row)->find('td:eq(11)')->text();                    
                        }
                        $wind_direction = trim($wind_direction, ")]");
                        $amedas_data = [
                            'prec_no' => $prec_no,
                            'block_no' => $block_no,
                            'thander_flag' => 0,
                            'pricipitation' => $pricipitation,
                            'wind_speed' => $wind_speed,
                            'wind_direction' => $wind_direction,
                            'date' => $year."-".$month."-".$day
                        ];
                        break;
                    }
                }
                // 現在使用されていない観測所
                if($wind_direction == "///" || $wind_direction == "")
                {
                    break;
                }

                // 雷雲フラグの取得
                if(!$amedas_station->capital_flag)
                {
                    $amedas_data['thander_flag'] = 2;
                }
                else
                {
                    $html2 = file_get_contents("https://www.data.jma.go.jp/obd/stats/etrn/view/hourly_a1.php?prec_no=".$prec_no."&block_no=".$block_no."&year=".$year."&month=".$month."&day=".$day."&view=p1");
                    $dom2 = phpQuery::newDocument($html2);
                    $j = 0;
                    foreach($dom2['table:eq(4) tr'] as $row)
                    {
                        $j++;
                        if($j < 2)
                        {
                            continue;
                        }
                        $alt = pq($row)->find('td:eq(14) img')->attr('alt');
                        if($alt == "雷電")
                        {
                            $amedas_data['thander_flag'] = 1;
                            break;
                        }
                    }
                }

                var_dump($amedas_data);
                // 配列に追加
                array_push($amedas_data_array, $amedas_data);
            }
        }

        // 配列をリターン
        return $amedas_data_array;
    }

    public function saveAmedas($amedas_data_array)
    {
        // insertエラーを防ぐために分割してtblに送る
        $batch_sise = 200;
        $len = count($amedas_data_array);
        $quotient = floor($len / $batch_sise);

        // < ではなく <= にすることで端数分までループ
        for($i=0; $i<=$quotient; $i++)
        {
            $amedas_data_array_batch = array_slice($amedas_data_array, $batch_sise * $i, $batch_sise);
            if( !$this->Amedas_tbl->saveAmedas($amedas_data_array_batch))
            {
                return FALSE;
            }
        }
        return TRUE;
    }

    public function getAmedas($request, $lon, $lat)
    {
        // 最寄りの観測点の県NoとブロックNoを求める
        $stations = $this->Amedas_stations_tbl->getNearestStation($lon, $lat);
        $prec_no = $stations[0]->prec_no;
        $block_no = $stations[0]->block_no;

        // 検索開始日付 & 終了日付
        $start_date = $request['start_date'];
        $end_date = $request['end_date'];

        // 落雷指定があるとき、同一県内の都市の観測所データから落雷の有無を取得
        if($request['thander'] != '指定なし')
        {
            // 同一県内の都市の観測所を取得
            $stations = $this->Amedas_stations_tbl->getCapitalAmedasStationsFromSamePrefecture($prec_no);
            $block_no_array = [];
            foreach($stations as $station)
            {
                array_push($block_no_array, $station->block_no);
            }

            // 落雷ありの場合
            if($request['thander'] == 'あり')
            {
                $amedas_data_array = $this->Amedas_tbl->getAmedasFromSamePrefectureCapital($prec_no, $block_no_array, $start_date, $end_date, 1);
            }
            // 落雷なしの場合
            if($request['thander'] == 'なし')
            {
                $amedas_data_array = $this->Amedas_tbl->getAmedasFromSamePrefectureCapital($prec_no, $block_no_array, $start_date, $end_date, 0);
            }
            // 落雷条件を満たす日付を取得
            $date_array = [];
            foreach($amedas_data_array as $amedas_data)
            {
                array_push($date_array, $amedas_data->date);
            }
            $date_array = array_unique($date_array);
        }
        else
        {
            $date_array = FALSE;
        }

        return $this->Amedas_tbl->getAmedas($request, $prec_no, $block_no, $date_array);
    }
}