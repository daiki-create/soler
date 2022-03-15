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
        mb_language("Japanese");//文字コードの設定
		mb_internal_encoding("UTF-8");

		$myKey = "AIzaSyD9JxYPovcgDD23Cr4H7iDJvAeZQB9j66w";
		$url = "https://maps.googleapis.com/maps/api/geocode/json?address=" . urlencode($request['area']) . "+CA&key=" . $myKey ;
		$contents= file_get_contents($url);
		$jsonData = json_decode($contents,true);
		
		$lat = $jsonData["results"][0]["geometry"]["location"]["lat"];
		$lon = $jsonData["results"][0]["geometry"]["location"]["lng"];

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
			'start_date'=>'2020-09-30',
			'end_date'=>'2022-03-25',
			'thander'=>'あり',
			'precipitation'=>0,
			'wind_speed'=>0,
			'wind_direction'=>'指定なし'
		];

		// 検索地点の緯度経度を求める
		if($rest_flag)
		{
			mb_language("Japanese");//文字コードの設定
			mb_internal_encoding("UTF-8");

			$myKey = "AIzaSyD9JxYPovcgDD23Cr4H7iDJvAeZQB9j66w";
			$url = "https://maps.googleapis.com/maps/api/geocode/json?address=" . urlencode($request['area']) . "+CA&key=" . $myKey ;
			$contents= file_get_contents($url);
			$jsonData = json_decode($contents,true);
			
			$lat = $jsonData["results"][0]["geometry"]["location"]["lat"];
			$lon = $jsonData["results"][0]["geometry"]["location"]["lng"];
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
			echo("ライデン：");
			var_dump($liden_data_array);
			echo('\\n');
		}
		else
		{
			$liden_data_array = FALSE;
		}

		$result =[
			"amedas_data_array" => $amedas_data_array,
			// "liden_data_array" => $liden_data_array,
			// "center_lon" => $lon,
			// "center_lat" => $lat
		];
		echo("Result");
		var_dump($result);
    }
}
