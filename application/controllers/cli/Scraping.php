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

	// public function scrapingCurrentSoler($n)
	// {
    //     // xlsxファイルの取得
	// 	if($this->Soler_model->getXlsx())
	// 	{
    //     	// csvに変換
	// 		if($this->Soler_model->xlsxToCsv($n))
	// 		{
	// 			// データベースに保存
	// 			if($this->Soler_model->saveCurrentSoler($n))
	// 			{
	// 				log_message('debug', 'success');
	// 				exit;
	// 			}
	// 		}
	// 	}
	// 	log_message('debug', 'failed');
	// }

	// public function xlsxToCsvSaveCurrentSoler($n)
	// {
    //     // xlsxファイルの取得
	// 	// if($this->Soler_model->getXlsx())
	// 	{
    //     	// csvに変換
	// 		if($this->Soler_model->xlsxToCsv($n))
	// 		{
	// 			// データベースに保存
	// 			if($this->Soler_model->saveCurrentSoler($n))
	// 			{
	// 				log_message('debug', 'success');
	// 				exit;
	// 			}
	// 		}
	// 	}
	// 	log_message('debug', 'failed');
	// }

	public function saveCurrentSoler()
	{
		// データベースに保存
		if($this->Soler_model->saveCurrentSoler())
		{
			log_message('debug', 'success');
			exit;
		}
		echo('failed');
		log_message('debug', 'failed');
	}

	public function saveCurrentBlankSoler()
	{
		// データベースに保存
		if($this->Soler_model->saveCurrentBlankSoler())
		{
			log_message('debug', 'success');
			exit;
		}
		echo('failed');
		log_message('debug', 'failed');
	}

	public function xlsxToCsv($n)
	{
		// csvに変換
		if($this->Soler_model->xlsxToCsv($n))
		{
			log_message('debug', 'success');
			exit;
		}
		echo('failed');
		log_message('debug', 'failed');
	}

	public function addPrecToAdress()
	{
		if($this->Soler_model->addPrecToAdress())
		{
			log_message('debug', 'success');
			exit;
		}
		echo('failed');
	}
}
