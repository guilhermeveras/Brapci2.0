<?php
class patents extends CI_model {

    function le($id) {
        $sql = "select * from patent.patent where id_p = $id";
        $rlt = $this -> db -> query($sql);
        $rlt = $rlt -> result_array();
        $line = $rlt[0];

        /* Relacao Agente */
        $sql = "select * from patent.patent_agent_relation
                        INNER JOIN patent.patent_agent ON rl_agent = id_pa
                        WHERE rl_patent = $id order by rl_relation desc, rl_seq";
        $rlt = $this -> db -> query($sql);
        $rlt = $rlt -> result_array();
        $line['relacao'] = $rlt;

        /* Relacao Agente */
        $sql = "select * from patent.patent_despacho
                        INNER JOIN patent.patent_issue ON pd_issue = id_issue
                        LEFT  JOIN patent.patent_section ON pd_section = ps_acronic
                        WHERE pd_patent = $id 
                        order by issue_number desc, pd_section";
        $rlt = $this -> db -> query($sql);
        $rlt = $rlt -> result_array();
        $line['despacho'] = $rlt;

        /* patent_classification */
        $sql = "select * from patent.patent_classification
                        LEFT JOIN patent.patent_class ON cc_class = c_c
                        WHERE c_patent = $id 
                        order by c_c";
        $rlt = $this -> db -> query($sql);
        $rlt = $rlt -> result_array();
        $line['classificacao'] = $rlt;
        
        /* prioritaria */
        $sql = "select * from patent.patent_prioridade
                        WHERE prior_patent = $id 
                        order by prior_seq";
        $rlt = $this -> db -> query($sql);
        $rlt = $rlt -> result_array();
        $line['prioritario'] = $rlt;
                

        return ($line);
    }

    function view($id) {
        $line = $this -> le($id);
        echo '<pre>';
        print_r($line);
        echo '</pre>';
        $sx = '<table class="table">';
        for ($r = 0; $r < 10; $r++) {
            $sx .= '<td width="10%">';
        }
        /* 021 */
        $sx .= '<tr class="small">';
        $sx .= '<td colspan=2 align="center">';
        $sx .= msg('nr_pedido');
        $sx .= ' (21)';
        $sx .= '</td>';

        $sx .= '<td colspan=2 align="center">';
        $sx .= msg('data_deposito');
        $sx .= ' (22)';
        $sx .= '</td>';

        $sx .= '<td colspan=2 align="center">';
        $sx .= msg('data_publicacao');
        $sx .= ' (43)';
        $sx .= '</td>';

        $sx .= '<td colspan=2 align="center">';
        $sx .= msg('data_concessao');
        $sx .= ' (47)';
        $sx .= '</td>';
        
        $sx .= '<td colspan=2 align="center">';
        $sx .= msg('p_situacao');
        $sx .= ' (47)';
        $sx .= '</td>';        
        $sx .= '</tr>';

        $sx .= '<tr>';
        $sx .= '<td colspan=2 align="center"  style="border: 1px solid #000000;">';
        $sx .= '<b>' . $line['p_nr'] . '</b>';
        $sx .= '</td>';

        $sx .= '<td colspan=2 align="center"  style="border: 1px solid #000000;">';
        $sx .= '<b>' . stodbr($line['p_dt_deposito']) . '</b>';
        $sx .= '</td>';

        $sx .= '<td colspan=2 align="center"  style="border: 1px solid #000000;">';
        $sx .= '<b>' . stodbr($line['p_dt_publicacao']) . '</b>';
        $sx .= '</td>';

        $sx .= '<td colspan=2 align="center"  style="border: 1px solid #000000;">';
        $sx .= '<b>' . stodbr($line['p_dt_concessao']) . '</b>';
        $sx .= '</td>';
        
        $sx .= '<td colspan=2 align="center"  style="border: 1px solid #000000;">';
        $sx .= '<b>' . stodbr($line['p_situacao']) . '</b>';
        $sx .= '</td>';        

        $sx .= '</tr>';

        /* 022 e 054 */
        $sx .= '<tr class="small">';
        $sx .= '<td colspan=2 align="left">';
        $sx .= msg('patente_titulo');
        $sx .= ' (54)';
        $sx .= '</td>';
        $sx .= '</tr>';

        $sx .= '<tr>';
        $sx .= '<td colspan=10 style="border: 1px solid #000000; font-size: 24px;">';
        if (strlen($line['p_title']) == 0) {
            $sx .= '(sem informação)&nbsp;';
        } else {
            $sx .= '<b>' . $line['p_title'] . '<b>';
        }

        $sx .= '</td>';
        $sx .= '</tr>';

        /* 72 e 71 - Inventor e Depositante */
        $sx .= '<tr class="small">';
        $sx .= '<td colspan=5 align="left">';
        $sx .= msg('inventors');
        $sx .= ' (72)';
        $sx .= '</td>';
        $sx .= '<td colspan=5 align="left">';
        $sx .= msg('depositantes');
        $sx .= ' (71)';
        $sx .= '</td>';
        $sx .= '</tr>';

        $sx .= '<tr>';
        $sx .= '<td colspan=5  style="border: 1px solid #000000;">';
        $inv = 0;
        $dep = 0;
        $sx .= '<ol>';
        for ($r = 0; $r < count($line['relacao']); $r++) {
            $xline = $line['relacao'][$r];
            if ($xline['rl_relation'] == 'I') {
                $sx .= '<li><b>' . $xline['pa_nome'] . '<b> ';

                if (strlen($xline['pa_pais'])) {
                    $sx .= '(';
                    $sx .= $xline['pa_pais'];
                    if (strlen($xline['pa_estado']) > 0) {
                        $sx .= ', ' . $xline['pa_estado'];
                    }
                    $sx .= ')';
                }
                $sx .= '</li>';
            }
        }
        $sx .= '</ol>';
        $sx .= '</td>';

        $sx .= '<td colspan=5  style="border: 1px solid #000000;">';
        $inv = 0;
        $dep = 0;
        $sx .= '<ol>';
        for ($r = 0; $r < count($line['relacao']); $r++) {
            $xline = $line['relacao'][$r];
            if ($xline['rl_relation'] == 'A') {
                $sx .= '<li><b>' . $xline['pa_nome'] . '</b> ';

                if (strlen($xline['pa_pais'])) {
                    $sx .= '(';
                    $sx .= $xline['pa_pais'];
                    if (strlen($xline['pa_estado']) > 0) {
                        $sx .= ', ' . $xline['pa_estado'];
                    }
                    $sx .= ')';
                }
                $sx .= '</li>';
            }
        }
        $sx .= '</ol>';
        $sx .= '</td>';
        $sx .= '</tr>';
        
        /*************** PRIORITARIO *********************************************/
        $desp = $line['prioritario'];
        $sx .= '<tr><td colspan=10><h3>' . msg('prioritario') . '</h3></td></tr>';
        $sx .= '<tr align="center">';
        $sx .= '<th colspan=1 align="center">' . msg('prior_seq') . '</th>';
        $sx .= '<th colspan=3 align="center">' . msg('prior_sigla_pais') . '</th>';
        $sx .= '<th colspan=3 align="center">' . msg('prior_numero_prioridade') . '</th>';
        $sx .= '<th colspan=3 align="center">' . msg('prior_data_prioridade') . '</th>';

        for ($r = 0; $r < count($desp); $r++) {
            $xline = $desp[$r];
            $sx .= '<tr>';
            $sx .= '<td align="center" colspan=1 align="center"  style="border: 1px solid #000000;">';
            $sx .=  $xline['prior_seq'];
            $sx .= '</td>';
            $sx .= '<td align="center" colspan=3  style="border: 1px solid #000000;">';
            $sx .= $xline['prior_sigla_pais'];
            $sx .= '</td>';

            $sx .= '<td align="center" colspan=3  style="border: 1px solid #000000;">';
            $sx .= $xline['prior_numero_prioridade'];
            $sx .= '</td>';

            $sx .= '<td align="center" colspan=3 style="border: 1px solid #000000;">';
            $sx .= stodbr($xline['prior_data_prioridade']);
            $sx .= '</td>';
            $sx .= '</tr>';            
        }
        

        /*************** CLASSIFICACAO *********************************************/
        $desp = $line['classificacao'];
        $sx .= '<tr><td colspan=10><h3>' . msg('classificacao') . '</h3></td></tr>';
        $sx .= '<tr align="center">';
        $sx .= '<th colspan=2>' . msg('class') . '</th>';
        $sx .= '<th colspan=8>' . msg('descricao') . '</th>';

        for ($r = 0; $r < count($desp); $r++) {
            $xline = $desp[$r];
            $link = '<a href="https://www.uspto.gov/web/patents/classification/cpc/html/cpc-' . $xline['c_class'] . '.html" target="_new_' . $xline['c_class'] . '"">';
            $linka = '</a>';
            $l1 = strzero($xline['cc_c4'], 4);
            $l2 = strzero($xline['cc_c4'], 2);
            $l2 .= '0000';
            $link = '<a href="http://ipc.inpi.gov.br/ipcpub?notion=scheme&version=20190101&symbol=' . $xline['c_class'] . $l1 . $l2 . '" target="_new_' . $xline['c_class'] . '"">';
            $sx .= '<tr>';
            $sx .= '<td align="center" colspan=2  style="border: 1px solid #000000;">';
            $sx .= $link . $xline['cc_class'] . $linka;
            $sx .= '</td>';
            $sx .= '<td align="center" colspan=8  style="border: 1px solid #000000;">';
            $sx .= $xline['cc_name'];
            $sx .= '</td>';
            $sx .= '</tr>';
        }

        /*************** DESPACHO */
        $desp = $line['despacho'];
        $sx .= '<tr><td colspan=10><h3>' . msg('despachos') . '</h3></td></tr>';
        $sx .= '<tr align="center">';
        $sx .= '<th colspan=1>' . msg('issue') . '</th>';
        $sx .= '<th colspan=1>' . msg('data') . '</th>';
        $sx .= '<th colspan=1>' . msg('despacho') . '</th>';
        $sx .= '<th colspan=7>' . msg('comentario') . '</th>';

        for ($r = 0; $r < count($desp); $r++) {
            $xline = $desp[$r];
            $sx .= '<tr>';
            $sx .= '<td align="center" colspan=1  style="border: 1px solid #000000;">';
            $sx .= $xline['issue_number'];
            $sx .= '</td>';
            $sx .= '<td align="center" colspan=1  style="border: 1px solid #000000;">';
            $sx .= stodbr($xline['issue_published']);
            $sx .= '</td>';
            $sx .= '<td colspan=1 align="center"  style="border: 1px solid #000000;">';
            $sx .= $xline['pd_section'];
            $sx .= '</td>';
            $sx .= '<td colspan=7  style="border: 1px solid #000000;">';
            if (strlen($xline['pd_comentario']) == 0) {
                $sx .= $xline['ps_name'];
            } else {
                $sx .= $xline['ps_name'];
                $sx .= '<br>' . '<span style="color: #0000ff">' . $xline['pd_comentario'] . '</span>';
            }

            $sx .= '</td>';
            $sx .= '</tr>';
        }

        $sx .= '</table>';
        return ($sx);
    }

    function issue($dta) {
        $year = $dta['year'];
        $num = $dta['num'];
        $jid = $dta['jid'];
        $pub = $dta['pusblished'];
        $year = $dta['year'];

        $sql = "select * from patent.patent_issue
                        where issue_source = $jid
                            AND issue_year = '$year'
                            AND issue_number = '$num'
            ";
        $rlt = $this -> db -> query($sql);
        $rlt = $rlt -> result_array();
        if (count($rlt) == 0) {
            $sqli = "insert into patent.patent_issue
                                (issue_source, issue_year, issue_number, issue_published)
                                values
                                ('$jid','$year','$num','$pub')";
            $rlti = $this -> db -> query($sqli);
            $rlt = $this -> db -> query($sql);
            $rlt = $rlt -> result_array();
        }
        $id_issue = $rlt[0]['id_issue'];
        return ($id_issue);
    }

    function method_inpi($file) {
        $cnt = file_get_contents($file);
        $cnt = troca($cnt, '-', '_');
        $xml = simplexml_load_string($cnt);
        $sessions = $this -> sessions();
        $class = $this -> classes();

        $rst = $this -> xml_read($xml, '');
        if (isset($rst['numero'])) {
            $id = $rst['numero'];
            $dt['year'] = substr(sonumero($rst['dataPublicacao']), 4, 4);
            $dt['num'] = sonumero($rst['numero']);
            $dt['vol'] = '';
            $dt['pusblished'] = brtos($rst['dataPublicacao']);
            $dt['jid'] = '1';
            $issue = $this -> issue($dt);
            $data = $dt['pusblished'];

        } else {
            return ("ERROR");
        }
        $is = $xml;
        /* despachos */
        $debug = 0;
        foreach ($is as $key => $value) {
            $p = array();
            $p['section'] = trim((string)$value -> codigo);
            $p['section_title'] = (string)$value -> titulo;
            $sect = $p['section'];
            if (!isset($sessions[$sect])) {
                $this -> sessions($sect, $p['section_title']);
                $sessions[$sect] = $p['section_title'];
            }

            /************************************************************* PROCESSO PATENTE *****/
            if ($debug == 1) { echo '<br>Processo Patente';
            }
            $pp = $value -> processo_patente;
            $num = $this -> xml_read($pp -> numero);

            $p['patent_nr'] = troca($num[0], '_', '-');
            if (isset($num['kindcode'])) {
                $p['patent_nr_kindcode'] = (string)$num['kindcode'];
            }
            $p['patent_nr_inid'] = (string)$num['inid'];

            /* Título da Patente */
            if ($debug == 1) { echo '<br>Título da Patente';
            }
            if (isset($pp -> titulo)) {
                $dt = $this -> xml_read($pp -> titulo);
                $p['patent_titulo'] = troca($dt[0], '_', '-');
                $p['patent_titulo_inid'] = $dt['inid'];
            }

            /* Data do depósito */
            if ($debug == 1) { echo '<br>Data depósito';
            }
            if (isset($pp -> data_deposito)) {
                $dt = $this -> xml_read($pp -> data_deposito);
                $p['patent_nr_deposit_date'] = $dt[0];
                $p['patent_nr_deposit_date_inid'] = $dt['inid'];
            }

            /* Data fase nacional */
            if ($debug == 1) { echo '<br>Data fase nacional';
            }
            if (isset($pp -> data_fase_nacional)) {
                $dt = $this -> xml_read($pp -> data_fase_nacional);
                $p['patent_fase_nacional'] = $dt[0];
                $p['patent_fase_nacional_inid'] = $dt['inid'];
            }

            /* Pedido Internacional */
            if ($debug == 1) { echo '<br>Pedido Internacional';
            }
            if (isset($pp -> pedido_internacional)) {
                $dt = $this -> xml_read($pp -> pedido_internacional);
                $p['pedido_internacional_inid'] = $dt['inid'];
                $p['pedido_internacional_numero_pct'] = (string)$pp -> pedido_internacional -> numero_pct;
                $p['pedido_internacional_numero_pct_data'] = (string)$pp -> pedido_internacional -> data_pct;
            }

            /* Publicação Internacional */
            if ($debug == 1) { echo '<br>Publicação Internacional';
            }
            if (isset($pp -> publicacao_internacional)) {
                $dt = $this -> xml_read($pp -> publicacao_internacional);
                $p['publicacao_internacional_inid'] = $dt['inid'];
                $p['publicacao_internacional_numero_ompi'] = (string)$pp -> publicacao_internacional -> numero_ompi;
                $p['publicacao_internacional_numero_ompi_data'] = (string)$pp -> publicacao_internacional -> data_ompi;
            }

            /* Classificação Internacional */
            $classes = array();
            if ($debug == 1) { echo '<br>Classificação Internacional';
            }
            if (isset($pp -> classificacao_internacional_lista)) {
                $dt = $pp -> classificacao_internacional_lista -> classificacao_internacional;
                for ($q = 0; $q < count($dt); $q++) {
                    $dta = $this -> xml_read($dt[$q]);
                    $dtt = array();
                    $dtt['cip_inid'] = $dta['inid'];
                    $dtt['cip_seq'] = $dta['sequencia'];
                    $dtt['cip_ano'] = $dta['ano'];
                    $dtt['cip_classe'] = (string)$dt[$q][0];
                    $cod = $dtt['cip_ano'];
                    $c = $dtt['cip_classe'];
                    if (!isset($class[$c])) {
                        $this -> classes($c, '', $cod);
                    }

                    array_push($classes, $dtt);
                }
                $p['classificacao_internacional'] = $classes;
            }

            /** prioridade-unionista-lista */
            if ($debug == 1) { echo '<br>Prioridade Unionista ';
            }
            if (isset($pp -> prioridade_unionista_lista)) {
                $dt = $pp -> prioridade_unionista_lista;

                for ($q = 0; $q < count($dt -> prioridade_unionista); $q++) {

                    $dtx = $dt -> prioridade_unionista[$q];
                    $dta = $this -> xml_read($dtx);

                    $dtt = array();
                    $dtt['prior_inid'] = $dta['inid'];
                    $dtt['prior_seq'] = $dta['sequencia'];

                    $dta = $this -> xml_read($dt -> prioridade_unionista[$q] -> sigla_pais);
                    $dtt['prior_sigla_pais'] = $dta[0];
                    $dtt['prior_sigla_pais_inid'] = $dta['inid'];

                    $dta = $this -> xml_read($dt -> prioridade_unionista[$q] -> numero_prioridade);
                    $dtt['prior_numero_prioridade'] = $dta[0];
                    $dtt['prior_numero_prioridade_inid'] = $dta['inid'];

                    $dta = $this -> xml_read($dt -> prioridade_unionista[$q] -> data_prioridade);
                    $dtt['prior_data_prioridade'] = $dta[0];
                    $dtt['prior_data_prioridade_inid'] = $dta['inid'];
                    $p['prioridade_unionista'][$q] = $dtt;
                }
            }

            /* Divisao Pedido */
            if ($debug == 1) { echo '<br>Divisao Pedido';
            }
            if (isset($pp_divisao_pedido)) {
                $dt = $this -> xml_read($pp -> divisao_pedido);
                $p['patent_nr_divisao_pedido_inid'] = $dt['inid'];
                $dt = $this -> xml_read($pp -> divisao_pedido -> data_deposito);
                $p['patent_nr_divisao_pedido_deposito_data'] = $dt[0];
                $dt = $this -> xml_read($pp -> divisao_pedido -> numero);
                $p['patent_nr_divisao_pedido_numero'] = $dt[0];
            }

            /* titulares */
            if ($debug == 1) { echo '<br>Titulares';
            }
            $titular = array();
            if (isset($value -> processo_patente -> titular_lista)) {
                $dt = $value -> processo_patente -> titular_lista;
                for ($r = 0; $r < count($dt); $r++) {
                    $dd = $dt[$r];
                    $seq = $this -> xml_read($dd -> titular);
                    $titular[$r]['nome_seq'] = $seq['sequencia'];
                    $titular[$r]['nome_inid'] = $seq['inid'];

                    $titular[$r]['nome'] = (string)$dd -> titular -> nome_completo;
                    if (isset($dd -> titular -> endereco -> pais -> sigla)) {
                        $titular[$r]['nome_pais'] = (string)$dd -> titular -> endereco -> pais -> sigla;
                    }
                    if (isset($dd -> titular -> endereco -> uf)) {
                        $titular[$r]['nome_endereco_uf'] = (string)$dd -> titular -> endereco -> uf;
                    }
                }
            }
            $p['titular'] = $titular;

            /* Inventores */
            if ($debug == 1) { echo '<br>Inventor';
            }
            $inventor = array();
            if (isset($value -> processo_patente -> inventor_lista)) {
                $dt = $value -> processo_patente -> inventor_lista;
                for ($r = 0; $r < count($dt); $r++) {
                    $dd = $dt[$r];
                    $seq = $this -> xml_read($dd -> inventor);
                    $inventor[$r]['nome_seq'] = $seq['sequencia'];
                    $inventor[$r]['nome_inid'] = $seq['inid'];
                    $inventor[$r]['nome'] = (string)$dd -> inventor -> nome_completo;
                    //$titular[$r]['nome_pais'] = (string)$dd -> inventor -> endereco -> pais -> sigla;
                }
            }
            $p['inventor'] = $inventor;

            /* comentarios */
            if (isset($value -> comentario)) {
                $dt = $this -> xml_read($value -> comentario);
                $p['comentario'] = $dt[0];
                $p['comentario_indi'] = $dt['inid'];
            }
            echo $this -> process($p, $issue);
        }
        return ("");
    }

    function harvest_patent($id) {
        /*********** pdf ************************************************/
        $file = '_repository_patent/inpi/pdf/Patent-' . strzero($id, 5) . 'pdf';
        if (!file_exists($file)) {
            $url = "http://revistas.inpi.gov.br/pdf/Patentes" . $id . ".pdf";
            $rcn = file_get_contents($url);

            /* Save */
            $rsc = fopen($file, 'w+');
            fwrite($rsc, $rcn);
            fclose($rsc);
        }

        /*********** pdf ************************************************/
        $file = '_repository_patent/inpi/zip/Patent-' . strzero($id, 5) . '.zip';
        if (!file_exists($file)) {
            $url = "http://revistas.inpi.gov.br/txt/P" . $id . ".zip";
            $rcn = file_get_contents($url);

            /* Save */

            $rsc = fopen($file, 'w+');
            fwrite($rsc, $rcn);
            fclose($rsc);
        }

        $zip = new ZipArchive;
        if ($zip -> open($file) === TRUE) {
            $zip -> extractTo($file = '_repository_patent/inpi/txt');
            $zip -> close();
            echo 'UNZIP ' . $file . ' success!' . cr();
        } else {
            echo 'failed';
        }

        return ("");
    }

    function check_diretory() {
        $dir = '_repository_patent';
        if (!is_dir($dir)) {
            mkdir($dir);
        }

        $dir .= '/inpi';
        if (!is_dir($dir)) {
            mkdir($dir);
        }

        $dira = $dir . '/pdf';
        if (!is_dir($dira)) {
            mkdir($dira);
        }

        $dira = $dir . '/zip';
        if (!is_dir($dira)) {
            mkdir($dira);
        }
    }

    function classes($class = '', $desc = '', $cod = '') {
        if (strlen($class) == 0) {
            $sql = "select * from patent.patent_class";
            $rlt = $this -> db -> query($sql);
            $rlt = $rlt -> result_array();
            $secs = array();
            for ($r = 0; $r < count($rlt); $r++) {
                $line = $rlt[$r];
                $s = trim($line['cc_class']);
                $desc = trim($line['cc_name']);
                $secs[$s] = $desc;
            }
            return ($secs);
        } else {
            $sql = "select * from patent.patent_class where cc_class = '$class' and cc_cod = '$cod'";
            $rlt = $this -> db -> query($sql);
            $rlt = $rlt -> result_array();
        }
        if (count($rlt) == 0) {
            $ca1 = substr($class, 0, 1);
            $ca2 = substr($class, 1, 2);
            $ca3 = substr($class, 3, 1);
            $cs = trim(substr($class, 5, 10));
            $ca4 = substr($cs, 0, strpos($cs, '/'));
            $ca5 = substr($cs, strpos($cs, '/') + 1, strlen($cs));
            $sql = "insert into patent.patent_class
                                        (cc_name, cc_class, cc_description,cc_language,cc_cod,
                                        cc_c1,cc_c2,cc_c3,cc_c4,cc_c5)
                                        values
                                        ('$desc','$class','','pt','$cod',
                                        '$ca1','$ca2','$ca3',$ca4,$ca5)";
            $rlt = $this -> db -> query($sql);
            return ("");
        }
    }

    function sessions($sec = '', $desc = '') {
        if (strlen($sec) == 0) {
            $sql = "select * from patent.patent_section";
            $rlt = $this -> db -> query($sql);
            $rlt = $rlt -> result_array();
            $secs = array();
            for ($r = 0; $r < count($rlt); $r++) {
                $line = $rlt[$r];
                $s = trim($line['ps_acronic']);
                $desc = trim($line['ps_name']);
                $secs[$s] = $desc;
            }
            return ($secs);
        } else {
            $sql = "select * from patent.patent_section where ps_acronic = '$sec'";
            $rlt = $this -> db -> query($sql);
            $rlt = $rlt -> result_array();
        }
        if (count($rlt) == 0) {
            $sql = "insert into patent.patent_section 
                                        (ps_name, ps_acronic, ps_description, ps_source, ps_active)
                                        values
                                        ('$desc','$sec','',1,1)";
            $rlt = $this -> db -> query($sql);
            return ("");
        }
    }

    function harvesting() {

        $id = 2527;

        $this -> check_diretory();
        $this -> harvest_patent($id);

        $sql = "select * from patent.patent_source limit 1";
        $rlt = $this -> db -> query($sql);
        $rlt = $rlt -> result_array();
        $line = $rlt[0];

        $method = $line['ps_method'];
        switch($method) {
            case 'INPI' :
                $file = '_repository_patent/inpi/txt/Patente_' . $id . '.xml';
                $sx = $this -> method_inpi($file);
                return ($sx);
                break;
        }
        return ("KO");
    }

    function repository_list() {

    }

    public function xml_read($x, $vl = '') {
        $v = array();
        if (strlen($vl) == 0) {
            $sr = $x;
        } else {
            $sr = $x -> vl;
        }

        foreach ($sr as $key => $value) {
            $vlr = trim((string)$value);
            if (strlen($vlr) > 0) {
                array_push($v, (string)$value);
            }
            /******************* atributes *************/
        }
        foreach ($sr->attributes() as $a => $b) {
            $v[$a] = (string)$b;
        }
        return ($v);
    }

    function despacho($issue, $d, $id) {
        if (strlen($issue) == 0) {
            echo cr() . "<br>OPS, erro de ISSUE";
            exit ;
        }

        if (!isset($d['comentario'])) {
            $d['comentario'] = '';
        }

        $cot = troca($d['comentario'], "'", '´');
        $sec = $d['section'];
        $sql = "select * from patent.patent_despacho 
                                    where pd_patent = $id 
                                        and pd_section = '$sec' 
                                        and pd_comentario    = '$cot' 
                                        and pd_issue = '$issue'";
        $rlt = $this -> db -> query($sql);
        $rlt = $rlt -> result_array();
        if (count($rlt) == 0) {
            $sql = "insert into patent.patent_despacho 
                                (pd_patent, pd_section, pd_comentario, pd_issue, pd_method)
                                values
                                ('$id','$sec','$cot','$issue','INPI')";
            $rlt = $this -> db -> query($sql);
        }
        return (1);
    }

    function relacao_agente_patent($idp, $age, $relacao, $seq) {
        $sql = "select * from patent.patent_agent_relation 
                        WHERE rl_patent = $idp
                        AND rl_agent = $age
                        AND rl_relation = '$relacao' ";
        $rlt = $this -> db -> query($sql);
        $rlt = $rlt -> result_array();
        if (count($rlt) == 0) {
            $sql = "insert into patent.patent_agent_relation
                                (rl_agent, rl_relation, rl_patent,rl_seq)
                                values
                                ('$age','$relacao','$idp',$seq)";
            $rlt = $this -> db -> query($sql);
        }
        return (1);
    }

    function process($d, $issue) {
        $sx = '';
        if (isset($d['patent_nr'])) {
            $id = $this -> patent($d);

            $sx .= cr() . $d['patent_nr'] . ' process (' . $id . ')<br>';

            /************************ History ******************/
            $this -> despacho($issue, $d, $id);

            echo '<pre>';
            print_r($d);
            //exit ;

            /****************** Titulares **********************/
            $tit = $d['titular'];
            for ($r = 0; $r < count($tit); $r++) {
                $line = $tit[$r];
                $pais = '';
                $estado = '';
                $name = $line['nome'];
                $seq = $line['nome_seq'];
                if (isset($line['nome_pais'])) { $pais = $line['nome_pais'];
                }
                if (isset($line['nome_endereco_uf'])) { $estado = $line['nome_endereco_uf'];
                }
                $ida = $this -> agent($name, $pais, $estado);
                $this -> relacao_agente_patent($id, $ida, 'A', $seq);
            }
            /****************** Inventor **********************/
            $tit = $d['inventor'];
            for ($r = 0; $r < count($tit); $r++) {
                $line = $tit[$r];
                $pais = '';
                $estado = '';
                $name = $line['nome'];
                $seq = $line['nome_seq'];
                if (isset($line['pais'])) { $pais = $line['pais'];
                }
                if (isset($line['estado'])) { $estado = $line['pais'];
                }
                $ida = $this -> agent($name, $pais, $estado);
                $this -> relacao_agente_patent($id, $ida, 'I', $seq);
            }
            /****************** Classificacao **********************/
            if (isset($d['classificacao_internacional'])) {
                $class = $d['classificacao_internacional'];
                for ($r = 0; $r < count($class); $r++) {
                    $line = $class[$r];
                    $this -> classification($id, $line);
                }
            }
            /****************** Classificacao **********************/
            if (isset($d['prioridade_unionista'])) {
                $class = $d['prioridade_unionista'];
                for ($r = 0; $r < count($class); $r++) {
                    $line = $class[$r];
                    $this -> prioritario($id, $line);
                }
            }
        }
        return ($sx);
    }

    function classification($id, $l) {
        $cl = $l['cip_classe'];
        $cl1 = troca($cl, ' ', ';');
        $c = splitx(';', $cl1);
        $c1 = $c[0];
        $c2 = $c[1];
        $data = substr($l['cip_ano'], 0, 7);
        $seq = $l['cip_seq'];

        $sql = "select * from patent.patent_classification
                        WHERE c_patent = $id
                            AND c_class = '$c1'
                            AND c_subclass = '$c2'";
        $rlt = $this -> db -> query($sql);
        $rlt = $rlt -> result_array();
        if (count($rlt) == 0) {
            $sql = "insert into patent.patent_classification
                                (c_patent, c_class, c_subclass, c_cod, c_seq, c_c)
                                values
                                ($id,'$c1','$c2','$data','$seq', '$cl')";
            $rlt = $this -> db -> query($sql);
        }
        return (1);
    }

    function agent($name = '', $country = '', $state = '') {
        $name = troca($name, "'", '´');
        $sql = "select * from patent.patent_agent where pa_nome = '$name'";
        $rlt = $this -> db -> query($sql);
        $rlt = $rlt -> result_array();
        if (count($rlt) == 0) {
            $sqli = "insert into patent.patent_agent
                            (pa_nome, pa_pais, pa_estado)
                            value
                            ('$name','$country','$state')";
            $rlti = $this -> db -> query($sqli);
            $rlt = $this -> db -> query($sql);
            $rlt = $rlt -> result_array();
        }
        $line = $rlt[0];

        echo '==>' . $country;
        /******************* Update ***************************************/
        $set = '';
        if ((strlen($line['pa_pais']) == 0) and (strlen($country) > 0)) {
            $set = " pa_pais = '$country' ";
        }
        if ((strlen($line['pa_estado']) == 0) and (strlen($state) > 0)) {
            if (strlen($set) > 0) { $set .= ', ';
            }
            $set = " pa_estado = '$state' ";
        }
        if (strlen($set) > 0) {
            $sql = "update patent.patent_agent set $set where id_pa = " . $line['id_pa'];
            $rlti = $this -> db -> query($sql);
        }

        $id = $line['id_pa'];
        return ($id);
    }

    function patent($d) {
        /* VARIAVEIS */
        $pat_nr = troca($d['patent_nr'], ' ', '');
        $pat_dd = '0000-00-00';
        $pat_title = '';

        if (isset($d['patent_nr_deposit_date'])) {
            $pat_dd = brtos($d['patent_nr_deposit_date']);
        }
        if (isset($d['patent_titulo'])) {
            $pat_title = utf8_decode($d['patent_titulo']);
            $pat_title = strtolower($pat_title);
            $pat_title = troca($pat_title, '"', '');
            $pat_title = troca($pat_title, "'", '´');
            $pat_title = strtoupper(substr($pat_title, 0, 1)) . substr($pat_title, 1, strlen($pat_title));
            $pat_title = utf8_encode($pat_title);
        }

        /**************** recupera patent ****************************/
        $sql = "select * from patent.patent where p_nr = '" . $pat_nr . "'";
        $rlt = $this -> db -> query($sql);
        $rlt = $rlt -> result_array();
        if (count($rlt) == 0) {
            $sqli = "insert into patent.patent
                                (p_nr, p_dt_deposito)
                                value
                                ('$pat_nr', '$pat_dd')";
            $rlti = $this -> db -> query($sqli);
            $rlt = $this -> db -> query($sql);
            $rlt = $rlt -> result_array();
        }
        $line = $rlt[0];
        $idp = $line['id_p'];

        /* UPDATES */
        $set = '';
        if ((strlen($line['p_title']) == 0) and ($pat_title != '')) {
            if (strlen($set) > 0) { $set .= ', ';
            }
            $set .= 'p_title = "' . $pat_title . '" ';
        }

        if ((strlen($line['p_dt_deposito']) == '0000-00-00') and ($pat_dd != '0000-00-00')) {
            if (strlen($set) > 0) { $set .= ', ';
            }
            $set .= 'p_dt_deposito = "' . $pat_dd . '" ';
        }

        /********************************** SALVA NO BANCO DE DADOS ************/
        if (strlen($set) > 0) {
            $sqli = "update patent.patent set " . $set . " where id_p = " . $idp;
            $rlti = $this -> db -> query($sqli);
            echo $sqli . cr() . '<br>';
        }
        return ($idp);
    }

    function prioritario($idp, $d) {
        $prioc = $d['prior_numero_prioridade'];
        $pais = $d['prior_sigla_pais'];
        $seq = $d['prior_seq'];
        $data = brtos($d['prior_data_prioridade']);
        $sql = "select * from patent.patent_prioridade
                        WHERE prior_patent = $idp
                            AND prior_numero_prioridade = '$prioc'
                            AND prior_sigla_pais = '$pais'";
                            echo $sql;
        $rlt = $this -> db -> query($sql);
        $rlt = $rlt -> result_array();
        if (count($rlt) == 0) {
            $sql = "insert into patent.patent_prioridade
                                (prior_seq, prior_numero_prioridade, prior_sigla_pais, 
                                    prior_data_prioridade,   prior_patent)
                                values
                                ($seq,'$prioc','$pais',
                                '$data',$idp)";
            $rlt = $this -> db -> query($sql);
        }
        return(1);
    }

}
?>