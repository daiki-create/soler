<?php

class Liden_tbl extends CI_Model
{
    public function __construct()
    {
        $this->load->database();
    }

    public function saveLiden($liden_data_array)
    {
        if(
            $this
            ->db
            ->insert_batch(
                'liden',
                $liden_data_array
            )
        )
        {
            return TRUE;
        }
        return FALSE;
    }

    public function getLiden($request, $lon, $lat)
    {
        // 検索開始日付 & 終了日付
        $start_date = $request['start_date'];
        $end_date = $request['end_date'];

        $left_lon = $lon - 0.069;
        $right_lon = $lon + 0.069;
        $bottom_lat = $lat - 0.0565;
        $top_lat = $lat +0.0565;

        return $this
        ->db
        ->where("lon BETWEEN '$left_lon' AND '$right_lon'")
        ->where("lat BETWEEN '$bottom_lat' AND '$top_lat'")
        ->where("date BETWEEN '$start_date' AND '$end_date'")
        ->get('liden')
        ->result();
    }

    public function getLidenForAmedas($start_date, $end_date, $lat, $lon)
    {
        $left_lon = $lon - 0.069;
        $right_lon = $lon + 0.069;
        $bottom_lat = $lat - 0.0565;
        $top_lat = $lat +0.0565;

        return $this
        ->db
        ->where("lon BETWEEN '$left_lon' AND '$right_lon'")
        ->where("lat BETWEEN '$bottom_lat' AND '$top_lat'")
        ->where("date BETWEEN '$start_date' AND '$end_date'")
        ->get('liden')
        ->result();
    }
}