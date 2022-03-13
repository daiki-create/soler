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
        $url = "http://www.geocoding.jp/api/";
        $url.= "?v=1.1&q=".$query;
        $line='';
        $fp = fopen($url, "r");
        while(!feof($fp)) {
        $line.= fgets($fp);
        }
        fclose($fp);
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
		echo("Request:");
		var_dump($request);
		echo('\\n');

		// 検索地点の緯度経度を求める
		if($rest_flag)
		{
			$query = $request['area'];
			$query = urlencode($query);
			$url = "http://www.geocoding.jp/api/";
			die("ok.");
			$url.= "?v=1.1&q=".$query;
			$line='';
			$fp = fopen($url, "r");
			while(!feof($fp)) {
			$line.= fgets($fp);
			}
			fclose($fp);
			$xml = simplexml_load_string($line);
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
