<?php
class altmets extends CI_model
{
	function structure()
	{

	}

	function altmetrics($doi='')
	{
		if ((strlen($doi) == 0) or (substr($doi,0,2) != '10'))
		{
			//echo '==ERRO=='.$doi;
			return("");
		}
		//$doi = '10.1590/2318-08892018000300005';
		$rs = $this->harvested($doi);
		$ar = (array)json_decode($rs);	

		if (isset($ar['images']))
		{
			$url = $ar['details_url'];
			$link = '<a href="'.$url.'" target="_new">';
			$linka = '</a>';
			$img = '<a href="https://www.altmetric.com/">Altmetrics</a><br>'.$link.'<img src="'.$ar['images']->medium.'">'.$linka;			
		} else {
			$img = '';
		}
		return($img);
	}

	function get($doi='')
	{				
		$url = 'https://api.altmetric.com/v1/doi/'.$doi;
		$rs = @file_get_contents($url);
		if (strlen($rs) > 0)
		{
			$this->save_get($doi,$rs,1);
		} else {
			$this->save_get($doi,$rs,-1);
		}
		return($rs);
	}
	function harvested($doi)
	{
		$data = date("Ym");
		$sql = "select * from brapci_altmetrics.altmetrics_query where q_doi = '$doi' and q_data ='$data' ";
		$rlt = $this->db->query($sql);
		$rlt = $rlt->result_array();
		if (count($rlt) == 0)
		{
			return($this->get($doi));
		} else {
			return($rlt[0]['q_result']);
		}
	}
	function save_get($doi,$txt,$status)
	{
		$data = date("Ym");
		$sql = "insert into brapci_altmetrics.altmetrics_query
		(q_doi, q_status, q_result, q_data )
		values
		('$doi','$status','$txt','$data')";
		$this->db->query($sql);
	}
}
?>