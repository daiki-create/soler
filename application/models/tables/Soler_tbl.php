<?php

class Soler_tbl extends CI_Model
{
    public function __construct()
    {
        $this->load->database();
    }

    public function saveCurrentSoler($soler_data_array)
    {
        $this->db->truncate('soler');
        if(
            $this
            ->db
            ->insert_batch(
                'soler',
                $soler_data_array
            )
        )
        {
            return TRUE;
        }
        return FALSE;
    }

    // 集計
    public function getAllSoler($prefecture)
    {
        if($prefecture == '全国')
        {
            $sql = $this
            ->db
            ->where('type', '太陽光')
            ->get('soler')
            ->result();
        }
        else
        {
            $sql = $this
            ->db
            ->where('type', '太陽光')
            ->like('facility_adress', $prefecture)
            ->get('soler')
            ->result();
        }
     
        return $sql;
    }

    public function getSolerUniqueAddress($prefecture)
    {
        if($prefecture == '全国')
        {
            $sql = $this
            ->db
            ->where('type', '太陽光')
            ->group_by('adress')
            ->get('soler')
            ->result();
        }
        else
        {
            $sql = $this
            ->db
            ->where('type', '太陽光')
            ->like('facility_adress', $prefecture)
            ->group_by('adress')
            ->get('soler')
            ->result();
        }
     
        return $sql;
    }

    public function getSolerUnder50($prefecture)
    {
        if($prefecture == '全国')
        {
            $sql = $this
            ->db
            ->where('type', '太陽光')
            ->where('output <=', 50)
            ->get('soler')
            ->result();
        }
        else
        {
            $sql = $this
            ->db
            ->where('type', '太陽光')
            ->where('output <=', 50)
            ->like('facility_adress', $prefecture)
            ->get('soler')
            ->result();
        }
     
        return $sql;
    }

    public function getSolerUnder50UniqueAddress($prefecture)
    {
        if($prefecture == '全国')
        {
            $sql = $this
            ->db
            ->where('type', '太陽光')
            ->where('output <=', 50)
            ->group_by('adress')
            ->get('soler')
            ->result();
        }
        else
        {
            $sql = $this
            ->db
            ->where('type', '太陽光')
            ->where('output <=', 50)
            ->like('facility_adress', $prefecture)
            ->group_by('adress')
            ->get('soler')
            ->result();
        }
     
        return $sql;
    }

    public function getSolerUnder50UniqueAddressNotBlank($prefecture)
    {
        if($prefecture == '全国')
        {
            $sql = $this
            ->db
            ->where('type', '太陽光')
            ->where('output <=', 50)
            ->where('adress !=', '')
            ->group_by('adress')
            ->get('soler')
            ->result();
        }
        else
        {
            $sql = $this
            ->db
            ->where('type', '太陽光')
            ->where('output <=', 50)
            ->like('facility_adress', $prefecture)
            ->where('adress !=', '')
            ->group_by('adress')
            ->get('soler')
            ->result();
        }
     
        return $sql;
    }

     // 検索
     public function getSoler($request)
     {
         $area =$request['area'];
         $output = $request['output'];
         $adress_blank = $request['adress_blank'];
         $unique = $request['unique'];
 
         $sql = $this->db
        //  ->select('name, representative_name, adress, tel, output, facility_adress')
         ->select('facility_id, name, representative_name, adress, tel, output, facility_adress, total_output')
         ->where('type', '太陽光');

         if($area != '全国')
         {
           $sql = $sql->like('facility_adress', $area);
         }

         if($output)
         {
            $sql = $sql->where('output <=', 50);
         }
         
         if($adress_blank == "blank")
         {
            $sql = $sql->where('adress =', '');
         }
         elseif($adress_blank == "no_blank")
         {
            $sql = $sql->where('adress !=', '');
         }

         if($unique)
         {
            $sql = $sql->group_by('adress');
         }

         $sql = $sql
            ->get('soler')
            ->result();
      
         return $sql;
     }
}