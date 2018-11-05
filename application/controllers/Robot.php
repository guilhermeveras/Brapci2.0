<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Robot extends CI_Controller {

    function __construct() {

        parent::__construct();
		global $MODO;
		$MODO = 'robot';
        $this -> lang -> load("app", "portuguese");
        $this -> load -> database();
		$this -> load -> helper('url');
        $this -> load -> helper('xml');
        $this -> load -> helper('email');
		$this -> load -> helper('form_sisdoc');
        date_default_timezone_set('America/Sao_Paulo');
    }

    private function cab($data = array()) {
    	echo "OAI-PMH ROBOT Brapci v2.0a".cr();
    }

    private function footer($data = array()) {
    	echo cr();
    }

    public function index() {
    	$verb = get("verb");
    	$id = get("id");
		$id2 = get("id2");
    	
        $this -> load -> model('sources');
        $this -> load -> model('oai_pmh');
        $this -> load -> model('export');
        $this -> load -> model('frbr');
        $this -> load -> model('frbr_core');
		$this -> load -> helper('form_sisdoc');
        $this -> load -> model('Elasticsearch');
        $this -> load -> model('Elasticsearch_brapci20');
        $this -> cab();
        $data['title'] = 'OAI';		
		$html = '';
        switch($verb) {
            case 'GetRecord' :
                $dt = array();
                $idc = $this -> oai_pmh -> getRecord($id);
                if ($idc > 0) {
                    //$dt = $this -> oai_pmh -> getRecordNlM($idc, $dt);
                    $dt = $this -> oai_pmh -> getRecord_oai_dc($idc, $dt);
                    $dt['idc'] = $idc;
                    $html = $this -> sources -> info($id);
                    $html .= $this -> oai_pmh -> process($dt);
                    $html .= '<meta http-equiv="Refresh" content="5">';
                } else {
                    $html = $this -> sources -> info($id);
                    $html .= '<h3>Fim da coleta</h3>'.cr();
                }
                /***************************************************/

                //$html = '';
                //http://www.viaf.org/viaf/AutoSuggest?query=Zen, Ana Maria
                //http://www.viaf.org/processed/search/processed?query=local.personalName+all+"ZEN, Ana Maria Dalla"
                break;
            case 'ListIdentifiers' :
                $html = $this -> sources -> info($id);
                $html .= '<div class="row"><div class="col-12">' . $this -> oai_pmh -> ListIdentifiers($id) . '</div></div>';
                break;
            case 'info' :
                $html = $this -> sources -> info($id);
                break;
            case 'cache' :
                $html = $this -> sources -> info($id);
                $html .= '<div class="col-2">';
                $html .= '<h1>CACHE</h1>';
                $html .= $this -> oai_pmh -> cache_change_to($id, $id2, $id3) . '</div>';
                $html .= '<div class="col-4">' . $this -> oai_pmh -> list_cache($id, $id2) . '</div>';
                break;
            case 'cache_status_to' :
                $html = $this -> sources -> info($id);
                $html .= '<div class="col-2">';
                $html .= '<h1>CACHE ID</h1>';
                $this -> oai_pmh -> cache_reprocess($id3);
                $html .= $this -> oai_pmh -> list_cache($id, $id2, $id3);
                $html .= '</div>';
                break;
            case 'Identify' :
                $html = $this -> sources -> info($id);
                $html .= $this -> oai_pmh -> Identify($id);
                redirect(base_url(PATH.'oai/info/'.$id));
                break;
			case 'pdf_import':
                $this -> load -> model("pdfs");
                $this -> load -> model("frbr");
                $this -> load -> model("frbr_core");

                $txt = $this -> pdfs -> harvesting_next($id,0);
				echo cr().$txt;
				echo cr().date("d/m/Y H:i:s");
				break;
			case 'oai_import':
                $data['title'] = msg('Tools');
				$txt = '';
				$txt = date("d/m/Y H:i:s").cr();
                /* Coleta */
                $this -> load -> model('sources');
                $this -> load -> model('searchs');				
                $this -> load -> model('oai_pmh');
                $this -> load -> model('export');
                $this -> load -> model('frbr');
                $this -> load -> model('frbr_core');
                $this -> load -> model('Elasticsearch');
                $this -> load -> model('Elasticsearch_brapci20');
                $dt = array();
                $idc = $this -> oai_pmh -> getRecord(0);
                if ($idc > 0) {
                    //$dt = $this -> oai_pmh -> getRecordNlM($idc, $dt);
                    $dt = $this -> oai_pmh -> getRecord_oai_dc($idc, $dt);
                    $dt['idc'] = $idc;
                    //$txt = $this -> sources -> info($id);
                    $txt .= $this -> oai_pmh -> process($dt);
                    $html = $txt.cr();
                } else {
                    $txt = $this -> sources -> info($id);
                    $txt .= '<h3>Fim da coleta</h3>'.cr();
                    $txt .= '<br>' . date("d/m/Y H:i:s").cr();
					$html .= $txt.cr();
                }	
				$html .= 'left >>'.$this->oai_pmh->leftHarvesting().cr();			
				break;
            default :
                $html = $this -> oai_pmh -> repository_list($id);
                break;
        }

        echo strip_tags($html);
        $this -> footer();
    }
}