<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Scraping extends CI_Controller {

	public function __construct()
	{
		parent::__construct();

		$this->load->model('Amedas_model');
		$this->load->model('Amedas_stations_model');
		$this->load->model('Liden_model');
	}

	public function scrapingCurrentAmedas()
	{
		// if ( is_cli() ) 
		{
			log_message('debug', 'scraping current_amedas start.');

			// 気象庁の過去のデータをスクレイピング
			$amedas_data_array = $this->Amedas_model->scrapingCurrentAmedas();

			// 保存
			if($this->Amedas_model->saveAmedas($amedas_data_array))
			{
				log_message('debug', 'scraping current_amedas success.');
				echo 'scraping current_amedas success.';
				exit;
			}
			log_message('debug', 'scraping current_amedas failed.');
			echo 'scraping current_amedas failed.';
		}
	}

	public function scrapingAmedas($start_date,$end_date)
	{
		// if ( is_cli() ) 
		{
			log_message('debug', 'scraping amedas start.');

			// 気象庁の過去のデータをスクレイピング
			$amedas_data_array = $this->Amedas_model->scrapingAmedas($start_date,$end_date);

			// 保存
			if($this->Amedas_model->saveAmedas($amedas_data_array))
			{
				log_message('debug', 'scraping amedas success.');
				echo 'scraping amedas success.';
				exit;
			}
			log_message('debug', 'scraping amedas failed.');
			echo 'scraping amedas failed.';
		}
	}

	public function scrapingCurrentLiden()
	{
		// if ( is_cli() ) 
		{
			log_message('debug', 'scraping current_liden start.');

			// 参考サイトの昨日のデータをスクレイピング
			$liden_data_array = $this->Liden_model->scrapingCurrentLiden();

			// 保存
			if($this->Liden_model->saveLiden($liden_data_array))
			{
				log_message('debug', 'scraping current_liden success.');
				echo 'scraping current_liden success.';
				exit;
			}
			log_message('debug', 'scraping current_liden failed.');
			echo 'scraping current_liden failed.';
		}
	}

	public function scrapingLiden($start_date,$end_date)
	{
		// if ( is_cli() ) 
		{
			log_message('debug', 'scraping liden start.');

			// 参考サイトの指定日付範囲のデータをスクレイピング
			$liden_data_array = $this->Liden_model->scrapingLiden($start_date,$end_date);

			// バリデーション
			if($liden_data_array == "invalid_start_date")
			{
				echo '開始の日付の形式が正しくありません。\n';
			}
			elseif($liden_data_array == "invalid_end_date")
			{
				echo '終了の日付の形式が正しくありません。\n';
			}
			
			elseif($liden_data_array == "too_old")
			{
				echo '開始の日付は2020-10-02以前を選択できません。\n';
			}
			elseif($liden_data_array == "too_new")
			{
				echo '終了の日付は本日以降を選択できません。\n';
			}

			elseif($liden_data_array){
				// 保存
				if($this->Liden_model->saveLiden($liden_data_array))
				{
					log_message('debug', 'scraping liden success.');
					echo 'scraping liden success.';
					exit;
				}
			}
			log_message('debug', 'scraping liden failed.');
			echo 'scraping liden failed.';
		}
	}

	public function scrapingAmedasStations()
	{
		// if ( is_cli() ) 
		{
			log_message('debug', 'scraping amedas_stations start.');

			// 気象庁の過去のデータをスクレイピング
			$amedas_stations_data = $this->Amedas_stations_model->scrapingAmedasStations();

			// 保存
			if($this->Amedas_stations_model->saveAmedasStations($amedas_stations_data))
			{
				log_message('debug', 'scraping amedas_stations success.');
				echo 'scraping amedas_stations success.';
				exit;
			}
			log_message('debug', 'scraping amedas_stations failed.');
			echo 'scraping amedas_stations failed.';
		}
	}

	public function scrapingTestAmedasStation()
	{
		$amedas_station_data = $this->Amedas_stations_model->scrapingTestAmedasStation();
		if($this->Amedas_stations_model->saveAmedasStations($amedas_station_data))
		{
			echo 'scraping saveTestAmedasStation success.';
			exit;
		}
		echo 'scraping saveTestAmedasStation failed.';
	}
}
