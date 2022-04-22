<?php
if($_SERVER['HTTP_HOST']=="soler.local.com")
{
    $soler_dir = '/home/noland/src/soler';
}
if($_SERVER['HTTP_HOST']=="weather-info-ss.com")
{
    $soler_dir = '/home/mutsuki2000/weather-info-ss.com/public_html/soler';
}
require ($soler_dir . '/vendor/autoload.php');
 
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as ReaderXlsx;
use PhpOffice\PhpSpreadsheet\Writer\Csv as WriterCsv;
use PhpOffice\PhpSpreadsheet\Reader\Csv as ReaderCsv;

use League\Csv\Reader;
use League\Csv\CharsetConverter;

class Soler_model extends CI_Model
{
    public function __construct()
    {
        if($_SERVER['HTTP_HOST']=="soler.local.com")
        {
            $this->soler_dir = '/home/noland/src/soler';
        }
        if($_SERVER['HTTP_HOST']=="weather-info-ss.com")
        {
            $this->soler_dir = '/home/mutsuki2000/weather-info-ss.com/public_html/soler';
        }
        $this->load->helper('phpquery');
        $this->load->model('tables/Soler_tbl');
    }

    // 最悪手動
    public function getXlsx()
    {
        // $html = file_get_contents("https://www.fit-portal.go.jp/PublicInfo");

        // $spreadsheet = file_get_contents("https://www.fit-portal.go.jp/servlet/servlet.FileDownload?retURL=%2Fapex%2FPublicInfo&file=00P0K000026vFevUAE");
        // $spreadsheet = file_get_contents("http://soler.local.com/top/test");
        // var_dump($spreadsheet);
        exec("https://www.fit-portal.go.jp/servlet/servlet.FileDownload?retURL=%2Fapex%2FPublicInfo&file=00P0K000026vFevUAE");
        exit;

        // $zip = new ZipArchive;
        // $a = $zip->open("/home/noland/src/soler/public/xlsx/aomori.zip");
        // var_dump($a);
        // exit;
        // if ($zip->open("/home/noland/src/soler/public/xlsx/aomori.zip") === TRUE) {
        //     $zip->extractTo("/home/noland/src/soler/public/xlsx/");
        //     $zip->close();
        //     echo '成功';
        //   } else {
        //     echo '失敗';
        //   }
    }

    public function xlsxToCsv($n)
    {
        if(strlen($n) == 2)
        {
            $xls_files = glob($this->soler_dir . "/public/xlsx/".$n.".*.xlsx");
        }
        elseif($n)
        {
            $xls_files = glob($this->soler_dir . "/public/xlsx/".$n."[0-9].*.xlsx");
        }
        else
        {
            $xls_files = glob($this->soler_dir . "/public/xlsx/*.xlsx");
        }

        foreach($xls_files as $xf)
        {
            $reader = new ReaderXlsx();
            $spreadsheet = $reader->load($xf);
            $loadedSheetNames = $spreadsheet->getSheetNames();
            foreach($loadedSheetNames as $sheetIndex => $loadedSheetName) {
                $writer = new WriterCsv($spreadsheet);
                $writer->setSheetIndex($sheetIndex);
                $file_name = basename($xf, '.xlsx');
                $writer->save($this->soler_dir . '/public/csv/'.$file_name.'.csv');
                break;
            }    
        }
        return TRUE;
    }

    public function addPrecToAdress()
    {
        $csv_files = glob($this->soler_dir . "/public/csv/*.csv");

        // 都道府県・市区町村リスト（json）
        $prefecture_city_list_json = file_get_contents($this->soler_dir . "/public/json/prefectureCity.json");
        $prefecture_city_list_array = json_decode($prefecture_city_list_json, true);
        foreach($csv_files as $cf)
        {
            // ファイル名取得
            preg_match('/[0-9].*\.csv/i' , $cf, $matches);
            $file_name = $matches[0];

            // 読み込むCSVファイルを指定
            $reader = Reader::createFromPath($cf, 'r');
             // データ読み込み
            $records = $reader->getRecords();
            $i = 0;

            // 新しいCSVの中身を初期化
            $csv_content = "";

            foreach($records as $row) 
            {
                $i++;
                if($i < 5)
                {
                    continue;
                }
                $adress = $row[4];

                // 空白の場合はパス
                if(!$adress)
                {
                    continue;
                }
                // 正規表現で都道府県を取得
                preg_match('/(北海道|青森県|岩手県|宮城県|秋田県|山形県|福島県|茨城県|栃木県|群馬県|埼玉県|千葉県|東京都|神奈川県|新潟県|富山県|石川県|福井県|山梨県|長野県|岐阜県|静岡県|愛知県|三重県|滋賀県|京都府|大阪府|兵庫県|奈良県|和歌山県|鳥取県|島根県|岡山県|広島県|山口県|徳島県|香川県|愛媛県|高知県|福岡県|佐賀県|長崎県|熊本県|大分県|宮崎県|鹿児島県|沖縄県)/', $adress, $matches2);
                if($matches2)
                {
                    $prec = $matches2[0];
                }
                else
                {
                    $city_add_flag = 0;

                    // 都道府県を取得できない場合、正規表現で市区町村を取得
                    if(preg_match('/郡.*?(市|区|町|村)/', $adress))
                    {
                        preg_match('/郡.*?(市|区|町|村)/', $adress, $matches4);
                    }
                    else
                    {
                        preg_match('/.*?(市|区|町|村)/', $adress, $matches4);
                    }
                    
                    if($matches4)
                    {
                        if(preg_match('/郡山/', $adress))
                        {
                            $city = '郡山市';
                        }
                        else
                        {
                            $city = str_replace('郡', '', $matches4[0]);
                        }

                        // 「市」の入った市
                        if($city == '野々市' or $city == '四日市' or $city == '廿日市')
                        {
                            $city = $city . "市";
                        }
                        if(preg_match('/市川市/', $adress))
                        {
                            $city = '市川市';
                        }
                        if(preg_match('/市原市/', $adress))
                        {
                            $city = '市原市';
                        }
                        // 「町」の入った市
                        if(preg_match('/町田市/', $adress))
                        {
                            $city = "町田市";
                        }
                        if(preg_match('/十日町市/', $adress))
                        {
                            $city = "十日町市";
                        }
                        if(preg_match('/大町市/', $adress))
                        {
                            $city = "大町市";
                        }
                        // 「村」の入った市
                        if(preg_match('/村山市/', $adress))
                        {
                            $city = '村山市';
                        }
                        if(preg_match('/田村市/', $adress))
                        {
                            $city = '田村市';
                        }
                        if(preg_match('/東村山市/', $adress))
                        {
                            $city = '東村山市';
                        }
                        if(preg_match('/武蔵村山市/', $adress))
                        {
                            $city = '武蔵村山市';
                        }
                        if(preg_match('/羽村市/', $adress))
                        {
                            $city = '羽村市';
                        }
                        if(preg_match('/村上市/', $adress))
                        {
                            $city = '村上市';
                        }
                        if(preg_match('/大村市/', $adress))
                        {
                            $city = '大村市';
                        }

                        // 「村」を含む町村・区・郡・都道府県
                        if(preg_match('/村田町/', $adress))
                        {
                            $city = '村田町';
                        }
                        if(preg_match('/玉村町/', $adress))
                        {
                            $city = '玉村町';
                        }

                        // 「市」を含む町村・区・郡・都道府県
                        if(preg_match('/余市町/', $adress))
                        {
                            $city = '余市町';
                        }
                        if(preg_match('/市貝町/', $adress))
                        {
                            $city = '市貝町';
                        }
                        if(preg_match('/上市町/', $adress))
                        {
                            $city = '上市町';
                        }
                        if(preg_match('/市川三郷町/', $adress))
                        {
                            $city = '市川三郷町';
                        }
                        if(preg_match('/市川町/', $adress))
                        {
                            $city = '市川町';
                        }
                        if(preg_match('/下市町/', $adress))
                        {
                            $city = '下市町';
                        }

                        // 「町」を含む町村・区・郡・都道府県
                        if($city == '大町')
                        {
                            $city = $city . "町";
                        }

                        // その他
                        // if(preg_match('/塩釜市/', $adress))
                        // {
                        //     $city = '塩竈市';
                        // }
                        // if(preg_match('/岩舟町/', $adress))
                        // {
                        //     $city = "栃木市";
                        //     $city_add_flag = 1;
                        // }
                        // if(preg_match('/東証町/', $adress))
                        // {
                        //     $city = "東庄町";
                        // }
                        // if(preg_match('/八丈町/', $adress))
                        // {
                        //     $city = "八丈町";
                        // }
                        // if(preg_match('/稲代市/', $adress))
                        // {
                        //     $city = "稲城市";
                        // }
                        // if(preg_match('/相模原中央/', $adress))
                        // {
                        //     $city = "相模原市";
                        // }

                        $city = mb_substr($city, 0, -1);
                        // 都道府県・市区町村リストの中から一致する市区町村を見つけ、都道府県を取得
                        $city_in_json_flag = 0;
                        foreach($prefecture_city_list_array as $pcla)
                        {
                            if(preg_match('/'. $city .'/', $pcla['cityName']))
                            // if($pcla['cityName'] == $city)
                            {
                                $prec = $pcla['prefectureName'];
                                // 都道府県を$adressの先頭に追加
                                $adress = $prec . $adress;
                                if($city_add_flag)
                                {
                                    $adress = $prec . $city . $adress;
                                }
                                $city_in_json_flag = 1;
                                break;
                            }
                        }
                        if(!$city_in_json_flag)
                        {
                            echo('jsonから市区町村が見つかりませんでした。<br>');
                            echo($adress."<br>");
                            echo($city."<br>");

                            if(preg_match('/番地の/', $adress, $matches4))
                            {
                                $banchino = $matches4[0];
                                $adress = str_replace($banchino, "ー", $adress);
                                echo($adress."<br>");
                            }
                            if(preg_match('/一丁目/', $adress, $matches4))
                            {
                                $kanji = $matches4[0];
                                $adress = str_replace($kanji, "1丁目", $adress);
                                echo($adress."<br>");
                            }
                            if(preg_match('/二丁目/', $adress, $matches4))
                            {
                                $kanji = $matches4[0];
                                $adress = str_replace($kanji, "2丁目", $adress);
                                echo($adress."<br>");
                            }
                            if(preg_match('/三丁目/', $adress, $matches4))
                            {
                                $kanji = $matches4[0];
                                $adress = str_replace($kanji, "3丁目", $adress);
                                echo($adress."<br>");
                            }
                            if(preg_match('/四丁目/', $adress, $matches4))
                            {
                                $kanji = $matches4[0];
                                $adress = str_replace($kanji, "4丁目", $adress);
                                echo($adress."<br>");
                            }
                            if(preg_match('/五丁目/', $adress, $matches4))
                            {
                                $kanji = $matches4[0];
                                $adress = str_replace($kanji, "5丁目", $adress);
                                echo($adress."<br>");
                            }
                            if(preg_match('/六丁目/', $adress, $matches4))
                            {
                                $kanji = $matches4[0];
                                $adress = str_replace($kanji, "6丁目", $adress);
                                echo($adress."<br>");
                            }
                            if(preg_match('/七丁目/', $adress, $matches4))
                            {
                                $kanji = $matches4[0];
                                $adress = str_replace($kanji, "7丁目", $adress);
                                echo($adress."<br>");
                            }
                            if(preg_match('/八丁目/', $adress, $matches4))
                            {
                                $kanji = $matches4[0];
                                $adress = str_replace($kanji, "8丁目", $adress);
                                echo($adress."<br>");
                            }
                            if(preg_match('/九丁目/', $adress, $matches4))
                            {
                                $kanji = $matches4[0];
                                $adress = str_replace($kanji, "9丁目", $adress);
                                echo($adress."<br>");
                            }

                            // geocoodingで緯度経度を求める
                            $context = stream_context_create(
                                [
                                    "http"=>
                                    [
                                        "ignore_errors"=>true
                                    ]
                                ]
                            );
                            $myKey = "AIzaSyD9JxYPovcgDD23Cr4H7iDJvAeZQB9j66w";
                            $url = "https://maps.googleapis.com/maps/api/geocode/json?address=" . urlencode($adress) . "+CA&key=" . $myKey . "&sensor=false";

                            $contents= file_get_contents($url);
                            $jsonData = json_decode($contents,true);
                            
                            $lat = $jsonData["results"][0]["geometry"]["location"]["lat"];
                            $lon = $jsonData["results"][0]["geometry"]["location"]["lng"];

                            // 逆geocoodingで住所を求める
                            $latlng = $lat . ',' . $lon;
                            $url2 = "https://maps.google.com/maps/api/geocode/json?latlng=" . $latlng . "&CA&key=" . $myKey . "&sensor=false&language=ja";
                            $contents2 = file_get_contents($url2, false, $context);
                            $jsonData2 = json_decode($contents2,true);
                            $full_adress = $jsonData2['results'][0]["formatted_address"];
                            $prec = $jsonData2['results'][0]['address_components'][5]['long_name'];

                            if(preg_match('/^日本、〒[0-9]{3}-[0-9]{4}/', $full_adress, $matches5))
                            {
                                $trim_string = $matches5[0];
                                $adress = str_replace($trim_string, "", $full_adress);
                                $adress = trim($adress);
                            }
                        }
                    }
                    // 例外
                    else
                    {
                        if(preg_match('/流通センター/', $adress))
                        {
                            $prec = "山形県";
                            $city = "山形市";
                            $adress = $prec . $city . $adress;
                        }
                        // elseif(preg_match('/杵築/', $adress))
                        // {
                        //     $prec = "大分県";
                        //     $city = "杵築市";
                        //     $adress = $prec . $city . str_replace('杵築', '', $adress);
                        // }
                        // elseif(preg_match('/高萩/', $adress))
                        // {
                        //     $prec = "茨城県";
                        //     $city = "高萩市";
                        //     $adress = $prec . $city . str_replace('高萩', '', $adress);
                        // }
                        // elseif(preg_match('/平荒田目/', $adress))
                        // {
                        //     $prec = "福島県";
                        //     $city = "いわき市";
                        //     $adress = $prec . $city . $adress;
                        // }
                        // elseif(preg_match('/境下渕名/', $adress))
                        // {
                        //     $prec = "群馬県";
                        //     $city = "伊勢崎市";
                        //     $adress = $prec . $city . $adress;
                        // }
                        // elseif(preg_match('/芦生田/', $adress))
                        // {
                        //     $prec = "群馬県";
                        //     $city = "嬬恋村";
                        //     $adress = $prec . $city . $adress;
                        // }
                        
                        // elseif(preg_match('/(北海道|青森|岩手|宮城|秋田|山形|福島|茨城|栃木|群馬|埼玉|千葉|東京|神奈川|新潟|富山|石川|福井|山梨|長野|岐阜|静岡|愛知|三重|滋賀|[^東]京都|大阪|兵庫|奈良|和歌山|鳥取|島根|岡山|広島|山口|徳島|香川|愛媛|高知|福岡|佐賀|長崎|熊本|大分|宮崎|鹿児島|沖縄)/', $adress, $matches5))
                        // {
                        //     $prec = $matches5[0];
                        //     if($prec == "大阪" or $prec == "京都")
                        //     {
                        //         $prec = $prec . "府";
                        //     }
                        //     elseif($prec == "東京")
                        //     {
                        //         $prec = $prec . "都";
                        //     }
                        //     elseif($prec == "北海道")
                        //     {
                        //         $prec = $prec;
                        //     }
                        //     else
                        //     {
                        //         $prec = $prec . "県";
                        //     }
                        //     $adress = $prec . $adress;
                        // }
                        else
                        {
                            echo('市区町村が見つかりませんでした。'."<br>");
                            echo($adress."<br>");

                            if(preg_match('/番地の/', $adress, $matches4))
                            {
                                $banchino = $matches4[0];
                                $adress = str_replace($banchino, "ー", $adress);
                                echo($adress."<br>");
                            }
                            if(preg_match('/一丁目/', $adress, $matches4))
                            {
                                $kanji = $matches4[0];
                                $adress = str_replace($kanji, "1丁目", $adress);
                                echo($adress."<br>");
                            }
                            if(preg_match('/二丁目/', $adress, $matches4))
                            {
                                $kanji = $matches4[0];
                                $adress = str_replace($kanji, "2丁目", $adress);
                                echo($adress."<br>");
                            }
                            if(preg_match('/三丁目/', $adress, $matches4))
                            {
                                $kanji = $matches4[0];
                                $adress = str_replace($kanji, "3丁目", $adress);
                                echo($adress."<br>");
                            }
                            if(preg_match('/四丁目/', $adress, $matches4))
                            {
                                $kanji = $matches4[0];
                                $adress = str_replace($kanji, "4丁目", $adress);
                                echo($adress."<br>");
                            }
                            if(preg_match('/五丁目/', $adress, $matches4))
                            {
                                $kanji = $matches4[0];
                                $adress = str_replace($kanji, "5丁目", $adress);
                                echo($adress."<br>");
                            }
                            if(preg_match('/六丁目/', $adress, $matches4))
                            {
                                $kanji = $matches4[0];
                                $adress = str_replace($kanji, "6丁目", $adress);
                                echo($adress."<br>");
                            }
                            if(preg_match('/七丁目/', $adress, $matches4))
                            {
                                $kanji = $matches4[0];
                                $adress = str_replace($kanji, "7丁目", $adress);
                                echo($adress."<br>");
                            }
                            if(preg_match('/八丁目/', $adress, $matches4))
                            {
                                $kanji = $matches4[0];
                                $adress = str_replace($kanji, "8丁目", $adress);
                                echo($adress."<br>");
                            }
                            if(preg_match('/九丁目/', $adress, $matches4))
                            {
                                $kanji = $matches4[0];
                                $adress = str_replace($kanji, "9丁目", $adress);
                                echo($adress."<br>");
                            }

                            // geocoodingで緯度経度を求める
                            $context = stream_context_create(
                                [
                                    "http"=>
                                    [
                                        "ignore_errors"=>true
                                    ]
                                ]
                            );
                            $myKey = "AIzaSyD9JxYPovcgDD23Cr4H7iDJvAeZQB9j66w";
                            $url = "https://maps.googleapis.com/maps/api/geocode/json?address=" . urlencode($adress) . "+CA&key=" . $myKey . "&sensor=false";

                            $contents= file_get_contents($url);
                            $jsonData = json_decode($contents,true);
                            
                            $lat = $jsonData["results"][0]["geometry"]["location"]["lat"];
                            $lon = $jsonData["results"][0]["geometry"]["location"]["lng"];

                            // 逆geocoodingで住所を求める
                            $latlng = $lat . ',' . $lon;
                            $url2 = "https://maps.google.com/maps/api/geocode/json?latlng=" . $latlng . "&CA&key=" . $myKey . "&sensor=false&language=ja";
                            $contents2 = file_get_contents($url2, false, $context);
                            $jsonData2 = json_decode($contents2,true);
                            $full_adress = $jsonData2['results'][0]["formatted_address"];
                            $prec = $jsonData2['results'][0]['address_components'][5]['long_name'];

                            if(preg_match('/^日本、〒[0-9]{3}-[0-9]{4}/', $full_adress, $matches5))
                            {
                                $trim_string = $matches5[0];
                                $adress = str_replace($trim_string, "", $full_adress);
                                $adress = trim($adress);
                            }
                        }
                    }
                }
                // 都道府県の数字を取得
                $prec_no_array  = array('1'=>'北海道','2'=>'青森県','3'=>'岩手県','4'=>'宮城県','5'=>'秋田県','6'=>'山形県','7'=>'福島県','8'=>'茨城県','9'=>'栃木県','10'=>'群馬県','11'=>'埼玉県','12'=>'千葉県','13'=>'東京都','14'=>'神奈川県','15'=>'新潟県','16'=>'富山県','17'=>'石川県','18'=>'福井県','19'=>'山梨県','20'=>'長野県','21'=>'岐阜県','22'=>'静岡県','23'=>'愛知県','24'=>'三重県','25'=>'滋賀県','26'=>'京都府','27'=>'大阪府','28'=>'兵庫県','29'=>'奈良県','30'=>'和歌山県','31'=>'鳥取県','32'=>'島根県','33'=>'岡山県','34'=>'広島県','35'=>'山口県','36'=>'徳島県','37'=>'香川県','38'=>'愛媛県','39'=>'高知県','40'=>'福岡県','41'=>'佐賀県','42'=>'長崎県','43'=>'熊本県','44'=>'大分県','45'=>'宮崎県','46'=>'鹿児島県','47'=>'沖縄県');
                foreach($prec_no_array as $key => $value)
                {
                    if($value == $prec)
                    {
                        $prec_no = $key;
                        break;
                    }
                }

                // 新しいCSVに追加するレコードを定義
                $new_row = '"'. $row[1] .'","'. $row[2] .'","'. $row[3] .'","'. $prec_no .'","'. $adress .'","'. $row[5] .'","'. $row[6] .'","'. $row[7] .'","'. $row[8] .'","'. $row[9] .'","'. $row[10] .'"'. "\n";
                $csv_content = $csv_content . $new_row;
            }
            // csvに書き込み保存
            file_put_contents($this->soler_dir . "/public/csv_add_prec_to_adress/" . $file_name, $csv_content);
        }
        return TRUE;
    }

    public function saveCurrentSoler()
    {
        $csv_files = glob($this->soler_dir . "/public/csv_add_prec_to_adress/*.csv");
        $soler_data_array = [];
        foreach($csv_files as $cf)
        {
            // 読み込むCSVファイルを指定
            $reader = Reader::createFromPath($cf, 'r');
             // データ読み込み
            $records = $reader->getRecords();
            $i = 0;
            foreach($records as $row) {
                $i++;
                if($i < 5)
                {
                    continue;
                }
                $soler_data = [
                    'facility_id' => $row[0],
                    'name' => $row[1],
                    'representative_name' => $row[2],
                    'prec_no' => $row[3],
                    'adress' => $row[4],
                    'tel' => $row[5],
                    'type' => $row[6],
                    'output' => $row[7],
                    'facility_adress' => $row[8],
                    'total_output' => $row[10]
                ];
                array_push($soler_data_array, $soler_data);
            }
        }
        return $this->Soler_tbl->saveCurrentSoler($soler_data_array);
    }

    public function getAggregatedSoler()
    {
        $soler_aggregated_array = [];
        $prefectures = ['全国','北海道','青森県','岩手県','宮城県','秋田県','山形県','福島県','茨城県','栃木県','群馬県','埼玉県','千葉県','東京都','神奈川県','新潟県','富山県','石川県','福井県','山梨県','長野県','岐阜県','静岡県','愛知県','三重県','滋賀県','京都府','大阪府','兵庫県','奈良県','和歌山県','鳥取県','島根県','岡山県','広島県','山口県','徳島県','香川県','愛媛県','高知県','福岡県','佐賀県','長崎県','熊本県','大分県','宮崎県','鹿児島県','沖縄県'];
        foreach($prefectures as $prefecture)
        {
            $soler = $this->Soler_tbl->getAllSoler($prefecture);
            $soler_unique_address = $this->Soler_tbl->getSolerUniqueAddress($prefecture);
            $soler_under_50 = $this->Soler_tbl->getSolerUnder50($prefecture);
            $soler_under_50_unique_address = $this->Soler_tbl->getSolerUnder50UniqueAddress($prefecture);
            $soler_under_50_unique_address_not_blank = $this->Soler_tbl->getSolerUnder50UniqueAddressNotBlank($prefecture);

            $soler_aggregated = [
                'prefecture' => $prefecture,
                'n_soler' => count($soler),
                'n_soler_unique_address' => count($soler_unique_address),
                'n_soler_under_50' => count($soler_under_50),
                'n_soler_under_50_unique_address' => count($soler_under_50_unique_address),
                'n_soler_under_50_unique_address_not_blank' => count($soler_under_50_unique_address_not_blank),
            ];
            array_push($soler_aggregated_array, $soler_aggregated);
        }
        return $soler_aggregated_array;
    }

    public function getSoler($request)
    {
        return $this->Soler_tbl->getSoler($request);
    }
}