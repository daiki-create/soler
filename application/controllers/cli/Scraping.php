<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Scraping extends CI_Controller {

	public function __construct()
	{
		parent::__construct();

		$this->load->helper('file');
		$this->load->library('email');

		$this->load->model('Soler_model');
	}

	public function scrapingCurrentSoler($n)
	{
        // xlsxファイルの取得
		if($this->Soler_model->getXlsx())
		{
        	// csvに変換
			if($this->Soler_model->xlsxToCsv($n))
			{
				// データベースに保存
				if($this->Soler_model->saveCurrentSoler($n))
				{
					log_message('debug', 'success');
					exit;
				}
			}
		}
		log_message('debug', 'failed');
	}

	public function xlsxToCsvSaveCurrentSoler($n)
	{
        // xlsxファイルの取得
		// if($this->Soler_model->getXlsx())
		{
        	// csvに変換
			if($this->Soler_model->xlsxToCsv($n))
			{
				// データベースに保存
				if($this->Soler_model->saveCurrentSoler($n))
				{
					log_message('debug', 'success');
					exit;
				}
			}
		}
		log_message('debug', 'failed');
	}

	public function saveCurrentSoler($n)
	{
        // xlsxファイルの取得
		// if($this->Soler_model->getXlsx())
		{
        	// csvに変換
			// if($this->Soler_model->xlsxToCsv($n))
			{
				// データベースに保存
				if($this->Soler_model->saveCurrentSoler($n))
				{
					log_message('debug', 'success');
					exit;
				}
			}
		}
		log_message('debug', 'failed');
	}

	public function xlsxToCsv($n)
	{
        // xlsxファイルの取得
		// if($this->Soler_model->getXlsx())
		{
        	// csvに変換
			if($this->Soler_model->xlsxToCsv($n))
			{
				// データベースに保存
				// if($this->Soler_model->saveCurrentSoler($n))
				{
					log_message('debug', 'success');
					exit;
				}
			}
		}
		log_message('debug', 'failed');
	}
}
