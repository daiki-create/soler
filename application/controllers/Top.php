<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Top extends CI_Controller {

	public function __construct()
	{
		parent::__construct();

		$this->load->model('Amedas_model');
		$this->load->model('Amedas_stations_model');
		$this->load->model('Liden_model');
	}

	public function index()
	{
		$this->load->view('top/index.php');
	}

	public function api()
	{
		$request = json_decode(file_get_contents("php://input"), true);

		// 検索地点の緯度経度を求める
        $query = $request['area'];
        $query = urlencode($query);
        $url = "https://www.geocoding.jp/api/";
        $url.= "?v=1.1&q=".$query;
        $line='';

        // $fp = fopen($url, "r");
        // while(!feof($fp)) {
        // $line.= fgets($fp);
        // }
        // fclose($fp);

		 $context = stream_context_create([
            'ssl' => [
                'verify_peer'      => false,
                'verify_peer_name' => false
            ]
        ]);
		$content = file_get_contents($url, false, $context);
		$rows = explode("\n", $content);
		foreach ($rows as $row) {
			$line.= $row;
		}

        $xml = simplexml_load_string($line);
        $lon = $xml->coordinate->lng;
        $lat = $xml->coordinate->lat;

		// 条件に一致するアメダスデータを取得
		$amedas_data_array = $this->Amedas_model->getAmedas($request, $lon, $lat);

		// 検索地点付近の指定期間内の落雷データを取得
		if($request['thander'] !='なし')
		{
			$liden_data_array = $this->Liden_model->getLiden($request, $lon, $lat);
		}
		else
		{
			$liden_data_array = "no_thander";
		}

		$result =[
			"amedas_data_array" => $amedas_data_array,
			"liden_data_array" => $liden_data_array,
			"center_lon" => $lon,
			"center_lat" => $lat
		];
		$json = json_encode($result, JSON_UNESCAPED_UNICODE);
		header("Content-Type: application/json; charset=UTF-8");
		echo $json;
		exit;	
	}

	public function api_test($rest_flag)
    {
		$context = stream_context_create([
            'ssl' => [
                'verify_peer'      => false,
                'verify_peer_name' => false
            ]
        ]);
        $html = file_get_contents("https://weather.kakutyoutakaki.com/thander/index.php?year=2022&month=03&day=10&hour=24&min=0&kikan=24", false, $context);
        $dom = phpQuery::newDocument($html);
        $script = $dom->find('script:eq(8)');
		var_dump($script);
		exit;
		$request = [
			// 'area'=>'北海道札幌市',
			'area'=>'東京都世田谷区',
			'start_date'=>'2021-03-01',
			'end_date'=>'2022-03-25',
			'thander'=>'指定なし',
			'precipitation'=>0,
			'wind_speed'=>0,
			'wind_direction'=>'指定なし'
		];

		// 検索地点の緯度経度を求める
		if($rest_flag)
		{
			$query = $request['area'];
			$query = urlencode($query);
			$url = "http://www.geocoding.jp/api/";
			$url.= "?v=1.1&q=".$query;
			$line='';

			// $fp = fopen($url, "r");
			// while(!feof($fp)) {
			// 	$line.= fgets($fp);
			// }
			// fclose($fp);

			$context = stream_context_create([
				'ssl' => [
					'verify_peer'      => false,
					'verify_peer_name' => false
				]
			]);
			$content = file_get_contents($url, false, $context);
			$rows = explode("\n", $content);
			foreach ($rows as $row) {
				$line.= $row;
			}
			echo("line");
			var_dump($line);
			exit;
			
			// $ch = curl_init();
			// curl_setopt($ch, CURLOPT_URL, $url);
			// curl_setopt($ch, CURLOPT_HEADER, false);
			// curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			// curl_setopt($ch, CURLOPT_TIMEOUT, 60);
			// $json = curl_exec($ch);
			// curl_close($ch);
			// $content = json_decode($json, true);
			// var_dump($content);
			// exit;

			$xml = simplexml_load_string($line);
			var_dump($xml);
			exit;
			$lon = $xml->coordinate->lng;
			$lat = $xml->coordinate->lat;
			echo('rest_success.lat:');
			echo($lat);
			echo('\\n');
		}
        else{
			$lon = "145.581";
			$lat = "38.5";
		}

		$amedas_data_array = $this->Amedas_model->getAmedas($request, $lon, $lat);
		echo('アメダス：');
		var_dump($amedas_data_array);
		echo('\\n');

		// 検索地点付近の指定期間内の落雷データを取得
		if($request['thander'] !='なし')
		{
			$liden_data_array = $this->Liden_model->getLiden($request, $lon, $lat);
			echo("ライデン");
			var_dump($liden_data_array);
			echo('\\n');
		}
		else
		{
			$liden_data_array = FALSE;
		}

		$result =[
			"amedas_data_array" => $amedas_data_array,
			"liden_data_array" => $liden_data_array,
			"center_lon" => $lon,
			"center_lat" => $lat
		];
		echo("Result");
		var_dump($result);
    }
}
