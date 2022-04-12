<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Top extends CI_Controller {

	public function __construct()
	{
		parent::__construct();

		$this->load->model('Soler_model');
	}

	public function index()
	{
		// $soler_aggregated_array = $this->Soler_model->getAggregatedSoler();
		// $data['soler_aggregated_array'] = $soler_aggregated_array;
		$this->load->view('top/index.php');
	}
	
	public function api()
	{
		$request = json_decode(file_get_contents("php://input"), true);

		// 条件に一致する太陽王データを取得
		$soler_data_array = $this->Soler_model->getSoler($request);

		$result =[
			"soler_data_array" => $soler_data_array,
		];
		$json = json_encode($result, JSON_UNESCAPED_UNICODE);
		header("Content-Type: application/json; charset=UTF-8");
		echo $json;
		exit;	
	}
}
