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
        if($n)
        {
            $xls_files = glob($this->soler_dir . "/public/xlsx/".$n."#.*.xlsx");
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

    public function saveCurrentSoler($n)
    {
        $csv_files = glob($this->soler_dir . "/public/csv/*.csv");
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
                    'facility_id' => $row[1],
                    'name' => $row[2],
                    'representative_name' => $row[3],
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