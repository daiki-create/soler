<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Scraping extends CI_Controller {

	public function __construct()
	{
		parent::__construct();

		$this->load->helper('file');
		$this->load->library('email');

		$this->load->model('Amedas_model');
		$this->load->model('Amedas_stations_model');
		$this->load->model('Liden_model');
	}

	// 1分起きに実行
	public function scrapingAmedasCronJob()
	{
		$start_time = microtime(true);

		// ファイルから日付、開始インデックス、バッチNo.を取得
		$txt = read_file('../var/scrapingAmedasCronJob.txt');
		$array = explode(',', $txt);
		$date = $array[0];
		$start_index = $array[1];
		$batch_no = $array[2];

		// exe scrapingAmedas
		if($this->scrapingAmedasForCronJob($start_index, 170, $date))
		{
			// ① 現在のバッチNo.が6の場合・・日付を-1day、開始インデックス=0、バッチNo.=1に更新
			if($batch_no == 6)
			{
				$next_date = date('Y-m-d', strtotime('-1 day', strtotime($date)));
				$next_start_index = 0;
				$next_batch_no = 1;
			}
			// ② それ以外・・開始インデックス+=170、バッチNo.+=1
			else{
				$next_date = $date;
				$next_start_index = $start_index + 170;
				$next_batch_no = $batch_no + 1;
			}
			$data = $next_date.",".$next_start_index.",".$next_batch_no;
			write_file('../var/scrapingAmedasCronJob.txt', $data, 'w');
			log_message('debug', 'cron success!!!!!!!!!!!!!!!!!!!!!!!');
		}
		else{
			// メールで山崎に報告
			$config['protocol'] = 'smtp';
			$config['mailpath'] = '/usr/sbin/sendmail.postfix';
			$config['charset']  = 'iso-8859-1';
			$config['wordWrap'] = true;
			$this->email->initialize($config);

			$this->email->from('info@weather-info-ss.com/', 'CLIMATE SYSTEM');
			$this->email->to('6280ikiad@gmail.com');
			$this->email->subject('アメダスデータスクレイピング失敗');
			$this->email->message('日付：'.$date.'\n開始インデックス：'.$start_index.'\nバッチNo：'.$batch_no);
			$this->email->send();
			log_message('debug', 'cron FAILED!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!');
		}
		$end_time = microtime(true);
		$processing_time = $end_time - $start_time;
		log_message('debug', '実行時間：'.$processing_time);
	}

	// cron用
	public function scrapingAmedasForCronJob($start_index, $batch_sise, $date)
	{
		// if ( is_cli() ) 
		{
			// return TRUE;
			return FALSE;
			log_message('debug', 'scraping current_amedas start.');

			// 気象庁の過去のデータをスクレイピング
			$amedas_data_array = $this->Amedas_model->scrapingAmedas($start_index, $batch_sise, $date);
			if($amedas_data_array == "invalid_date")
			{
				echo 'invalid_date';
			}
			// 保存
			if($this->Amedas_model->saveAmedas($amedas_data_array))
			{
				return True;
			}
			return FALSE;
		}
	}

	// 手動用
	public function scrapingAmedas($start_index, $batch_sise, $date)
	{
		// if ( is_cli() ) 
		{
			log_message('debug', 'scraping current_amedas start.');

			// 気象庁の過去のデータをスクレイピング
			$amedas_data_array = $this->Amedas_model->scrapingAmedas($start_index, $batch_sise, $date);
			if($amedas_data_array == "invalid_date")
			{
				echo 'invalid_date';
			}
			// 保存
			if($this->Amedas_model->saveAmedas($amedas_data_array))
			{
				echo 'scraping current_amedas success.';
				exit;
			}
			echo 'scraping current_amedas failed.';
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

	public function scrapingAmedasStations($start_prec, $end_prec)
	{
		// if ( is_cli() ) 
		{
			log_message('debug', 'scraping amedas_stations start.');

			// 気象庁の過去のデータをスクレイピング
			$amedas_stations_data = $this->Amedas_stations_model->scrapingAmedasStations($start_prec, $end_prec);

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
}
