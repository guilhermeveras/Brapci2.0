<?php
class pdfs extends CI_model {
	function harvesting_pdf($id) {
		$this -> harvesting_pdf_curl($id);
		echo '<script> 	window.opener.location.reload(); close();  </script>';
	}

	function journals_files()
		{
			$sx = '';
			$dd1 = get("dd1");
			$dd2 = round(get("dd2"));
			if (strlen($dd1) == 0)
			{
				$sql ="select * from source_source where jnl_active = 1 order by jnl_name";
				$rlt = $this->db->query($sql);
				$rlt = $rlt->result_array();
				$sx .= '<ul>';
				for ($r=0;$r < count($rlt);$r++)
				{
					$line = $rlt[$r];
					$link = '<a href="'.base_url(PATH.'tools/pdf_check_article/?dd1='.$line['id_jnl']).'">';
					$sx .= '<li>'.$link.$line['jnl_name'].'</a>'.'</li>';
				}
				$sx .= '</ul>';
			} else {
				$sql ="select * from source_source where id_jnl = $dd1 order by jnl_name";
				$rlt = $this->db->query($sql);
				$rlt = $rlt->result_array();			
				$idj = $rlt[0]['jnl_frbr'];
				//'hasFileStorage'

				$sql = "select * from (
					    SELECT d1.d_r1 as art FROM `rdf_data` as d1
							INNER JOIN rdf_class ON d_p = id_c and c_class = 'isPubishIn'
							where d1.d_r2 = $idj
					    ) as tabela
					    LEFT JOIN rdf_data as d2 ON art = d2.d_r1 and d_p = 76
					     where d_r1 IS NULL and art > $dd2
					     order by art
					     ";

			     $rlt = $this->db->query($sql);
			     $rlt = $rlt->result_array();
			     $sx .= 'Total de '.count($rlt).' arquivos PDF para coletar<hr>';
			     $sx .= '<ul>';
			     for ($r=0;$r < count($rlt);$r++)
			     {
			     		$line = $rlt[$r];
			     		$sx .= '<li>'.$line['art'].'</li>';
			     }
				 $sx .= '</ul>';
				 $xart = $rlt[0]['art'];
				 echo '<meta http-equiv="refresh" content="5;' . base_url(PATH . 'tools/pdf_check_article/?dd1='.$dd1.'&dd2='.$xart) . '">';	     		 
	     		 $sx .= $this -> pdfs -> harvesting_pdf($xart);
			     
			}
			return($sx);
		}

	function upload($id = '') {
		$data = $this -> frbr_core -> le_data($id);
		for ($r = 0; $r < count($data); $r++) {
			$attr = trim($data[$r]['c_class']);
			$vlr = trim($data[$r]['n_name']);

			if ($attr == 'prefLabel') {
				$file = trim($vlr);
				$file = troca($file, '/', '_');
				$file = troca($file, '.', '_');
				$file = troca($file, ':', '_');
			}
		}

		$sx = '
			<h1>' . msg('upload_file') . '</h1>
			<form method="post" enctype="multipart/form-data">
			    Select image to upload:
			    <input type="file" name="fileToUpload" id="fileToUpload">
			    <input type="submit" value="Upload Image" name="submit">
			</form>
			';

		// Check if image file is a actual image or fake image
		if (isset($_POST["submit"])) {
			$target_dir = "uploads/";
			$target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
			$uploadOk = 1;
			$imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
			$check = (lowercase(substr($imageFileType, strlen($imageFileType) - 3, 3)) == 'pdf');
			if ($check !== false) {
				$namef = $_FILES["fileToUpload"]["tmp_name"];
				$txt = '';

				$myfile = fopen($namef, "r") or die("Unable to open file!");
				$txt .= fread($myfile, filesize($namef));
				fclose($myfile);

				$this -> file_pdf($file, $txt, $id);
				$uploadOk = 1;
				$sx .= '<script> wclose(); </script>';
			} else {
				echo "File is not an image.";
				$uploadOk = 0;
			}
		}

		return ($sx);
	}

	function harvesting_next($p = '', $rs = '1') {
		$s = '';
		if ($p == '') {
			$p = 0;
		}
		$prop1 = $this -> frbr_core -> find_class('hasUrl');
        $prop3 = $this -> frbr_core -> find_class('hasRegisterId');
		$prop2 = $this -> frbr_core -> find_class('hasFileStorage');
		$sql = "
				select count(*) as total from rdf_data AS R1
					left JOIN rdf_data AS R2 ON R1.d_r1 = R2.d_r1 and R2.d_p = $prop2
				  where (R1.d_p = $prop1 OR R1.d_p = $prop3) and R2.d_p is null ";
		$s = 'P1:'.date("d/m/Y H:i:s").'<br>';				  
		$rlt = $this -> db -> query($sql);
		$s .= 'P2:'.date("d/m/Y H:i:s").'<br>';
		$rlt = $rlt -> result_array();
		$total = $rlt[0]['total'];
		$date = date("Y-m-d");
		$sql = "
				select R1.d_r1 as d_r1 from rdf_data AS R1
					left JOIN __file_temp on d_r1 = fl_id
					left JOIN rdf_data AS R2 ON R1.d_r1 = R2.d_r1 and R2.d_p = $prop2
				  where R1.d_p = $prop1 and R2.d_p is null and R1.d_r1 > $p
				  and fl_id is null
				  order by R1.d_r1 desc
				limit 1			
			";

		$rlt = $this -> db -> query($sql);
		$rlt = $rlt -> result_array();
		$s .= 'P3:'.date("d/m/Y H:i:s").'<br>';
		/***************************/
		if (count($rlt) == 0)
			{
				$sql = "TRUNCATE __file_temp";
				$rrr = $this->db->query($sql);
			}
		if (count($rlt) > 0) {
			$line = $rlt[0];
			$id = $line['d_r1'];

			/* */
			$sql = "insert into __file_temp (fl_id, fl_data) values ($id,'" . $date . "');";
			$rrr = $this -> db -> query($sql);

			$sx = msg('Article') . ' ' . $id;
			$sx .= ', ' . msg('left') . ' ' . $total . ' files';
			if ($rs == '1') {
				//echo '<meta http-equiv="refresh" content="1;' . base_url(PATH . 'tools/pdf_import/' . (round($id))) . '">';
				echo '<meta http-equiv="refresh" content="30;' . base_url(PATH . 'tools/pdf_import/') . '">';
			}
			$s .= 'CURL: '.date("d/m/Y H:i:s").'<br>';
			$sx .= ' ' . $this -> harvesting_pdf_curl($id);
			$s .= 'CURL-STOP: '. date("d/m/Y H:i:s").'<br>';
			return ($sx.'<hr>'.$s);
		} else {
			return ("Fim da coleta");
		}
	}

	function harvesting_pdf_curl($id) {
		$links = array();
		$links2 = array();
        $dd = $this->frbr_core->le($id);
        if ($dd['c_class'] == 'Journal')
            {
                return("JOURNAL - ".$id);
            }
		$data = $this -> frbr_core -> le_data($id);

		for ($r = 0; $r < count($data); $r++) {
			$attr = trim($data[$r]['c_class']);
			$vlr = trim($data[$r]['n_name']);

			if ($attr === 'isPubishIn') {
				$jnl = $data[$r]['d_r2'];
			}

			if ($attr == 'prefLabel') {
				$file = trim($vlr);
				$file = troca($file, '/', '_');
				$file = troca($file, '.', '_');
				$file = troca($file, ':', '_');
			}

			if ($attr == 'hasUrl') {
				if (strpos(' '.$vlr,'http') > 0) {
				    $vlr = substr($vlr,strpos($vlr,'http'),strlen($vlr));
					array_push($links, $vlr);
				}
			}
			if ($attr == 'hasRegisterId'){
                if (substr($vlr, 0, 4) == 'http') {
                    array_push($links2, $vlr);
                }			    
			}
            
		}
		
        echo '<h1>'.$id.'</h1>';

        if ((count($links) == 0) and (count($links2) > 0))
            {
            	echo "OK";
                for ($r=0;$r < count($links2);$r++)
                    {
                        $link = $links2[$r];
                        $rsp = load_page($link);
                        $txt = $rsp['content'];

                        if (strpos($txt,'citation_pdf_url') > 0)
                            {
                                /*****************************/
                                $d = 'citation_pdf_url';
                                $pos = strpos($txt,$d)+strlen($d);
                                $txt = substr($txt,$pos,1000);
                                
                                /*****************************/
                                $d = 'content="';
                                $pos = strpos($txt,$d)+strlen($d);
                                $txt = substr($txt,$pos,1000);
                                $txt = substr($txt,0,strpos($txt,'"'));                                
                                if (strlen($txt) > 0)
                                    {
                                        array_push($links, $txt);
                                    }
                            }
                        if (strpos($txt,'frame src="') > 0)
                            {
                                /*****************************/
                                $d = 'frame src="';
                                $pos = strpos($txt,$d)+strlen($d);
                                $txt = substr($txt,$pos,1000);
                                
                                /*****************************/
                                $d = '" frameborder';
                                $pos = strpos($txt,$d)+strlen($d);
                                $txt = substr($txt,0,$pos);
                                $txt = substr($txt,0,strpos($txt,'"')); 
                                if ((strlen($txt) > 0) and (substr($txt,0,4) == 'http'))
                                    {
                                        array_push($links, $txt);
                                    }
                            }                                                   
                    }
            }
        
		/************************ IDENTIFICAÇÃO DOS MÉTODOS *************/
		$method = 0;
		$link = '';
		for ($r = 0; $r < count($links); $r++) {
			$method == 0;
			$link = $links[$r];
			if ((strpos($link, '/view/')) or (strpos($link, '/viewFile/')) or (strpos($link, '/viewArticle/')) or (strpos($link, '/download/'))) {
				$method = 1;
			}         
            
            if (strpos($link,'scielo.php') > 0)
                {
                    $method=1; 
                    /* Base do Scielo */
                    $txt = file_get_contents($link);
                    $txt = substr($txt,strpos($txt,'citation_pdf_url'),1024);
                    $txt = substr($txt,strpos($txt,'http'),strlen($txt));
                    $link = substr($txt,0,strpos($txt,'"'));
                }

			switch($method) {
				case '1' :
					$link = $this -> method_1($link, $file, $id);
					echo '<br>' . ($r+1) . '. ' . $link;
					try {
						$rsp = load_page($link);
						$txt = $rsp['content'];
						$type = $rsp['content_type'];
						/* save pdf */
						
						/**************** Correções de regras de download ************************/
						if (strpos($link,'revista.arquivonacional.gov.br') > 0)
                            {
                                $type = 'application/pdf';
                            }
                        
						if (strpos($type, ';') > 0) {
							$type = substr($type, 0, strpos($type, ';'));
						}
                        
                        /******************** Tipos de arquivos recebidos ************************/
						switch($type) {
							case 'application/pdf' :
								$this -> file_pdf($file, $txt, $id, $jnl);
                                return("pdf");
								//echo ' - ' . msg('save_pdf');
								break;
                            case 'application/octet-stream' :
                                $this -> file_pdf($file, $txt, $id, $jnl);
                                return("pdf");
                                break;
                            case 'application/save-as' :
                                $this -> file_pdf($file, $txt, $id, $jnl);
                                return("pdf");
                                break;                                                                
							case 'application/zip' :
								$this -> file_save($file, $txt, $id, 'ZIP', $jnl);
								//echo ' - ' . msg('save_pdf');
								break;
							case 'application/word' :
								$this -> file_save($file, $txt, $id, 'WRD', $jnl);
								break;
							case 'application/word' :
								$this -> file_save($file, $txt, $id, 'WRD', $jnl);
								break;
							case 'text/save' :
								$this -> file_save($file, $txt, $id, 'SAV', $jnl);
								break;
							case 'text/html' :
								$this -> file_save($file, $txt, $id, 'HTM', $jnl);
								break;
							case 'image/gif' :
								$this -> file_save($file, $txt, $id, 'GIF', $jnl);
								break;
							case 'image/jpeg' :
								$this -> file_save($file, $txt, $id, 'JPG', $jnl);
								break;
							case 'application/msword':
								$this -> file_save($file, $txt, $id, 'DOC', $jnl);
								break;
							default :
                                echo $link.'<br>';
                                echo 'ID:'.$id.'<br>';
								echo '===><pre>[' . $type . ']</pre>';
                                echo '<hr><pre>';
                                print_r($rsp);
                                echo '</pre>';
								exit ;
						}

					} catch (Exception $e) {
						echo 'Caught exception: ', $e -> getMessage(), "\n";
					}
					break;
				default :
					echo '<br>===Erro de método ';
					echo '<br>' . $links[$r];
					break;
			}
		}
		exit;
		return (msg("Harvesting"));
	}

	function download($d1) {
		$data = $this -> frbr_core -> le_data($d1);
		$size = 0;
		$name = 'File';
		$type = '';
		$file = '';
		$size = 0;
		for ($r = 0; $r < count($data); $r++) {
			$attr = $data[$r]['c_class'];
			$vlr = $data[$r]['n_name'];
			switch ($attr) {
				case 'hasFileType' :
					$type = $vlr;
					break;
				case 'prefLabel' :
					$file = $vlr;
					break;
				default :
					break;
			}
		}

		if ($type == 'PDF') {
			header('Content-type: application/pdf');
			readfile($file);
		}
		if ($type == 'TXT') {
			header('Content-type: text/html');
			if (file_exists($file)) {
				readfile($file);
			} else {
				echo 'File not found - ' . $file;
			}

		}
	}

	function directories($journal = 0) {
		/* Prepara o nome do arquivo */
		$filename = '_repository';
		check_dir($filename);
		$filename .= '/' . $journal;
		check_dir($filename);
		$filename .= '/' . date("Y");
		check_dir($filename);
		$filename .= '/' . date("m");
		check_dir($filename);
		return ($filename);
	}

	function file_pdf($file, $content, $id, $journal) {

		$filename = $this -> directories($journal);
		$filename .= '/' . $file . '.pdf';

		$fld = fopen($filename, 'w+');
		fwrite($fld, $content);
		fclose($fld);

		$size = filesize($filename);
		if ($size > 0) {
			/********** cria objeto do arquivo ****************************************/
			$r2 = $this -> frbr_core -> rdf_concept_create('FileStorage', $filename, 'en', '');

			/* TIPO DO ARQUIVO */
			$r3 = $this -> frbr_core -> rdf_concept_create('FileType', 'PDF', 'pt-BR', '');
			$prop = 'hasFileType';
			$this -> frbr_core -> set_propriety($r2, $prop, $r3, 0);

			/* Tamanho do Arquivo */
			$prop = 'hasFileSize';
			$id_size = $this -> frbr_core -> frbr_name($size, 'pt-BR');
			$this -> frbr_core -> set_propriety($r2, $prop, 0, $id_size);

			/* DATA DA COLETA DO ARQUIVO */
			$prop = 'hasDateTime';
			$idd = $this -> frbr_core -> rdf_concept_create('Date', DATE("Y-m-d"));
			$this -> frbr_core -> set_propriety($r2, $prop, $idd, 0);

			$prop = 'hasFileStorage';
			$this -> frbr_core -> set_propriety($id, $prop, $r2, 0);
		}
		return (1);
	}

	function file_save($file, $content, $id, $type, $journal) {
		$type = UpperCase($type);
		$filename = $this -> directories($journal);
		$filename .= '/' . $file . '.'.lowercase($type);

		$fld = fopen($filename, 'w+');
		fwrite($fld, $content);
		fclose($fld);

		$size = filesize($filename);
		if ($size > 0) {
			/********** cria objeto do arquivo ****************************************/
			$r2 = $this -> frbr_core -> rdf_concept_create('File', $filename, 'en', '');

			/* TIPO DO ARQUIVO */
			$r3 = $this -> frbr_core -> rdf_concept_create('FileType', $type, 'pt-BR', '');
			$prop = 'hasFileType';
			$this -> frbr_core -> set_propriety($r2, $prop, $r3, 0);

			/* Tamanho do Arquivo */
			$prop = 'hasFileSize';
			$id_size = $this -> frbr_core -> frbr_name($size, 'pt-BR');
			$this -> frbr_core -> set_propriety($r2, $prop, 0, $id_size);

			/* DATA DA COLETA DO ARQUIVO */
			$prop = 'hasDateTime';
			$idd = $this -> frbr_core -> rdf_concept_create('Date', DATE("Y-m-d"));
			$this -> frbr_core -> set_propriety($r2, $prop, $idd, 0);

			$prop = 'hasFileStorage';
			$this -> frbr_core -> set_propriety($id, $prop, $r2, 0);
		}
		return (1);
	}

	function method_1($link, $file) {
		if (!(strpos($link, '/download/'))) {
			$link = troca($link, '/view/', '/download/');
		}
		return ($link);
	}

	function create_coversheet() {
		$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

		// set document information
		$pdf -> SetCreator(PDF_CREATOR);
		$pdf -> SetAuthor('Nicola Asuni');
		$pdf -> SetTitle('TCPDF Example 001');
		$pdf -> SetSubject('TCPDF Tutorial');
		$pdf -> SetKeywords('TCPDF, PDF, example, test, guide');

		// set default header data
		$pdf -> SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE . ' 001', PDF_HEADER_STRING, array(0, 64, 255), array(0, 64, 128));
		$pdf -> setFooterData(array(0, 64, 0), array(0, 64, 128));

		// set header and footer fonts
		$pdf -> setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
		$pdf -> setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

		// set default monospaced font
		$pdf -> SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

		// set margins
		$pdf -> SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
		$pdf -> SetHeaderMargin(PDF_MARGIN_HEADER);
		$pdf -> SetFooterMargin(PDF_MARGIN_FOOTER);

		// set auto page breaks
		$pdf -> SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

		// set image scale factor
		$pdf -> setImageScale(PDF_IMAGE_SCALE_RATIO);

		$pdf -> AddPage();

		// set some language-dependent strings (optional)
		if (@file_exists(dirname(__FILE__) . '/lang/eng.php')) {
			require_once (dirname(__FILE__) . '/lang/eng.php');
			$pdf -> setLanguageArray($l);
		}

		// ---------------------------------------------------------

		// set default font subsetting mode
		$pdf -> setFontSubsetting(true);

		// Set font
		// dejavusans is a UTF-8 Unicode font, if you only need to
		// print standard ASCII chars, you can use core fonts like
		// helvetica or times to reduce file size.
		$pdf -> SetFont('dejavusans', '', 14, '', true);

		// Add a page
		// This method has several options, check the source code documentation for more information.
		$pdf -> AddPage();

		// set text shadow effect
		$pdf -> setTextShadow(array('enabled' => true, 'depth_w' => 0.2, 'depth_h' => 0.2, 'color' => array(196, 196, 196), 'opacity' => 1, 'blend_mode' => 'Normal'));

		// Set some content to print
		$html = <<<EOD
<h1>Welcome to <a href="http://www.tcpdf.org" style="text-decoration:none;background-color:#CC0000;color:black;">&nbsp;<span style="color:black;">TC</span><span style="color:white;">PDF</span>&nbsp;</a>!</h1>
<i>This is the first example of TCPDF library.</i>
<p>This text is printed using the <i>writeHTMLCell()</i> method but you can also use: <i>Multicell(), writeHTML(), Write(), Cell() and Text()</i>.</p>
<p>Please check the source code documentation and other examples for further information.</p>
<p style="color:#CC0000;">TO IMPROVE AND EXPAND TCPDF I NEED YOUR SUPPORT, PLEASE <a href="http://sourceforge.net/donate/index.php?group_id=128076">MAKE A DONATION!</a></p>
EOD;

		// Print text using writeHTMLCell()
		$pdf -> writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);

		// ---------------------------------------------------------

		// Close and output PDF document
		// This method has several options, check the source code documentation for more information.
		$pdf -> Output('example_001.pdf', 'I');
	}

	function check_pdf() {
		$class = 'FileStorage   ';
		$f = $this -> frbr_core -> find_class($class);
		$this -> frbr_core -> check_language();
		$limit = 200;
		$offset = round(get('p'));

		$sql = "select N1.n_name as n_name, N1.n_lang as n_lang, C1.id_cc as id_cc,
                       N2.n_name as n_name_use, N2.n_lang as n_lang_use, C2.id_cc as id_cc_use         
                        FROM rdf_concept as C1
                        INNER JOIN rdf_name as N1 ON C1.cc_pref_term = N1.id_n
                        LEFT JOIN rdf_concept as C2 ON C1.cc_use = C2.id_cc
                        LEFT JOIN rdf_name as N2 ON C2.cc_pref_term = N2.id_n
                        where C1.cc_class = " . $f . "                        
                        ORDER BY N1.n_name
                        limit $limit offset $offset
                        ";
		$rlt = $this -> db -> query($sql);
		$rlt = $rlt -> result_array();
		$sx = '<div class="container">';
		$sx .= '<div class="row">';
		$sx .= '<div class="col-md-12">';
		$sx .= '<ul>';
		$tot = 0;
		for ($r = 0; $r < count($rlt); $r++) {
			$tot++;
			$line = $rlt[$r];
			$file = trim($line['n_name']);
			$id = $line['id_cc'];
			$link = '<a href="' . base_url(PATH . 'v/' . $id) . '">';
			$sx .= '<li>' . $link . $file . '</a>';
			if (file_exists($file)) {
				$sz = filesize($file);
				if ($sz > 10) {
					$sx .= ' - <font color="green"><b>OK</b></font>';
				} else {
					$sx .= ' - <font color="orange"><b>Alert</b></font>';
					$this -> remove_pdf($id);
				}
			} else {
				$sx .= ' - <font color="red"><b>ERROR</b> - ' . $id . '</font>';
				$this -> remove_pdf($id);
			}
			$sx .= '</li>';
			//print_r($line);
			//echo '<hr>';
		}
		$sx .= '</ul>';
		$sx .= '</div>';
		$sx .= '</div>';
		if ($tot >= $limit) {
			$sx .= '<meta http-equiv="refresh" content="15;' . base_url(PATH . 'tools/pdf_check?p=' . ($offset + $limit)) . '">';
		} else {
			$sx .= '<div class="row">';
			$sx .= '<div class="col-md-12">';
			$sx .= bs_alert('success', 'Fim da coleta!');
			$sx .= '</div>';
			$sx .= '</div>';
		}
		$sx .= '</div>';
		return ($sx);
	}

	function remove_pdf($id) {
		$sql = "delete from rdf_data where d_r2 = $id ";
		$rlt = $this -> db -> query($sql);
	}

}
?>