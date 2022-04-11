<?php
// require ('/home/noland/src/soler/vendor/autoload.php');
// require ('/home/noland/src/soler/vendor/autoload.php');
 
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

    public function xlsxToCsv()
    {
        $xls_files = glob("/home/noland/src/soler/public/xlsx/*.xlsx");

        foreach($xls_files as $xf)
        {
            $reader = new ReaderXlsx();
            $spreadsheet = $reader->load($xf);
            $loadedSheetNames = $spreadsheet->getSheetNames();
            foreach($loadedSheetNames as $sheetIndex => $loadedSheetName) {
                $writer = new WriterCsv($spreadsheet);
                $writer->setSheetIndex($sheetIndex);
                $writer->save('/home/noland/src/soler/public/csv/'.$loadedSheetName.'.csv');
                break;
            }    
        }
        return TRUE;
    }

    public function saveCurrentSoler()
    {
        $csv_files = glob("/home/noland/src/soler/public/csv/*.csv");
        foreach($csv_files as $cf)
        {
            // 読み込むCSVファイルを指定
            $reader = Reader::createFromPath($cf, 'r');
            
            // 文字エンコードを指定(SJIS-win -> UTF-8)
            // CharsetConverter::addTo($reader, 'SJIS', 'UTF-8');
            
            // レコード件数を取得
            // echo $reader->count();
            // exit;
            
            // データ読み込み
            $records = $reader->getRecords();

            $soler_data_array = [];
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
        $prefectures = ["全国","北海道","青森"];
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