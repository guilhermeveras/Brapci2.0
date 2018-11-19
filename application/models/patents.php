<?php
/* http://revistas.inpi.gov.br/txt/P2490.zip */
class patents extends CI_model {

    function import() {
        $sx = '<div class="container">' . cr();
        $sx .= '<div class="row">' . cr();
        $sx .= '<h3>Harvesting</h3>';
        $sx .= $this -> process();
        $sx .= '</div>';
        $sx .= '</div>';
        return ($sx);
    }

    function process($file = '') {
        $cnt = $this -> content($file);
        $cnt = troca($cnt, '-', '_');

        $xml = simplexml_load_string($cnt);

        foreach ($xml->attributes() as $a => $b) {
            switch($a) {
                case 'dataPublicacao' :
                    $dt_now = $b;
                    break;
                case 'numero' :
                    $journal_nr = $b;
            }
        }
        /***************************************** JOURNAL *******/
        $setName = 'Revista da Propriedade Industrial';
        $class = 'Journal';
        $id_jnl = $this -> frbr_core -> rdf_concept_create($class, $setName);

        $dt_now = brtos($dt_now);
        $dt_now = substr($dt_now, 0, 4) . '-' . substr($dt_now, 4, 2) . '-' . substr($dt_now, 6, 2);
        $id_date_now = $this -> frbr_core -> rdf_concept_create('Date', $dt_now);
        $year = substr($dt_now, 0, 4);

        /***************************************** ISSUE *********/
        $setName = 'Revista RPI, n.' . $journal_nr . ', ' . $year;
        $class = 'Issue';
        $id_issue = $this -> frbr_core -> rdf_concept_create($class, $setName);

        $prop = 'hasIssue';
        $this -> frbr_core -> set_propriety($id_jnl, $prop, $id_issue, 0);

        $prop = 'hasDateDispatch';
        $this -> frbr_core -> set_propriety($id_issue, $prop, $id_date_now, 0);

        $prop = 'dateOfPublication';
        $id_date_year = $this -> frbr_core -> rdf_concept_create('Number', substr($dt_now, 0, 4));
        $this -> frbr_core -> set_propriety($id_issue, $prop, $id_date_year, 0);

        /************************************** PUBLICATION NUMBER ******/
        $num = 'n. ' . $journal_nr;
        $class = 'PublicationNumber';
        $id_nr = $this -> frbr_core -> rdf_concept_create($class, $num);

        $prop = 'hasPublicationNumber';
        $this -> frbr_core -> set_propriety($id_issue, $prop, $id_nr, 0);

        /********************************************************************/

        $rr = 0;
        $xsessao = "";
        $sx = '<ul>' . cr();

        foreach ($xml as $key => $value) {
            $sessao = 'RPI.' . trim($value -> codigo);
            $sessao_nome = trim($value -> titulo);

            if ($xsessao != $sessao) {
                /******************************************* SESSION *************************/
                $setName = $sessao;
                $class = 'PatentSection';
                $id_section = $this -> frbr_core -> rdf_concept_create($class, $setName);
                $prop = 'altLabel';
                $lit = $this -> frbr_core -> frbr_name($sessao_nome, 'pt-BR', 1);
                $this -> frbr_core -> set_propriety($id_section, $prop, 0, $lit);
                $xsessao = $sessao;

                $sx .= '<li><b>' . $sessao . ' - ' . $sessao_nome . ' (' . $id_section . ')' . '</b></li>';
            }
            /* */
            $desc = $value -> processo_patente;

            /*******************************************************/
            $NR = '';
            $classInter = array();
            $titulo_patent = $desc -> titulo;
            $autores = array();
            $auth = array();
            $inventor = array();
            $sa = '';
            /********************************************/
            if (isset($desc -> titular_lista -> titular)) {
                foreach ($desc->titular_lista->titular as $tipo => $autores) {
                    $nome = $autores -> nome_completo;
                    $nome = troca($nome,'_','-');
                    $nome = ucase(lowercase($nome));

                    $uf = $autores -> endereco -> uf;
                    $pais = $autores -> endereco -> pais -> sigla;
                    $sa = '<ul>';
                    $sa .= '<li>';
                    if (strlen($uf) > 0) {
                        $sa .= $nome . ' (' . $uf . '/' . $pais . ')';
                    } else {
                        $sa .= $nome . ' (' . $pais . ')';
                    }

                    foreach ($autores->attributes() as $a => $b) {
                        $sx .= '<br>' . $a . '="' . $b . "\"\n";
                    }
                    array_push($auth, array($nome, $uf, $pais));
                    $sx .= '</li>';
                    $sx .= '</ul>';
                }
            }
            
            /********************************************/
            if (isset($desc -> inventor_lista -> inventor)) {
                foreach ($desc->inventor_lista->inventor as $tipo => $autores) {
                    $nome = $autores -> nome_completo;
                    $nome = troca($nome,'_','-');
                    $nome = ucase(lowercase($nome));

                    $uf = $autores -> endereco -> uf;
                    if (isset($autores -> endereco -> pais -> sigla))
                        {
                            $pais = $autores -> endereco -> pais -> sigla;
                        } else {
                            $pais = '';
                        }
                    $sa = '<ul>';
                    $sa .= '<li>';
                    if (strlen($uf) > 0) {
                        $sa .= $nome . ' (' . $uf . '/' . $pais . ')';
                    } else {
                        $sa .= $nome . ' (' . $pais . ')';
                    }

                    foreach ($autores->attributes() as $a => $b) {
                        $sx .= '<br>' . $a . '="' . $b . "\"\n";
                    }
                    array_push($inventor, array($nome, $uf, $pais));
                    $sx .= '</li>';
                    $sx .= '</ul>';
                }
            }            

            $content = troca($value -> comentario, '_', '-');
            $NR = $desc -> numero;
            $NR = troca($NR,'_','-');
            $dt_deposito = $desc -> data_deposito;

            $sx .= '<li><h5>' . $NR . '</h5>';
            $sx .= 'Sessão: '.$sessao.'<br>';
            $sx .= 'Date: ' . $dt_deposito;
            $sx .= $sa;
            $sx .= '<br><i>' . $content . '</i>';
            /*
            foreach ($desc->numero->attributes() as $a => $b) {
                $sx .= '<br>' . $a . '="' . $b . "\"\n";
            }
             */
            $sx .= '</li>';

            /******************************************** pedido_internacional ******/
            $inter_pct = $desc -> pedido_internacional -> numero_pct;
            $inter_pct_data = $desc -> pedido_internacional -> data_pct;

            /******************************************** publicacao_internacional ******/
            $inter_patent = $desc -> publicacao_internacional -> numero_ompi;
            $inter_patent_data = $desc -> publicacao_internacional -> data_ompi;

            /*******************************************classificacao-internacional-lista */
            $classInter = array();
            if (isset($desc -> classificacao_internacional_lista -> classificacao_internacional)) {
                foreach ($desc->classificacao_internacional_lista->classificacao_internacional as $key => $value) {
                    $cl = (string)$value[0];
                    foreach ($value->attributes() as $a => $b) {
                        if ($a == 'sequencia') { $seq = (string)$b;
                        }
                        if ($a == 'ano') { $ano = troca((string)$b, '.', '-');
                        }
                    }
                    array_push($classInter, array($cl, $seq, $ano));
                }
            }
            $classNaci = array();
            if (isset($desc -> classificacao_nacional_lista -> classificacao_nacional)) {
                foreach ($desc->classificacao_nacional_lista->classificacao_nacional as $key => $value) {
                    $cl = (string)$value[0];
                    foreach ($value->attributes() as $a => $b) {
                        if ($a == 'sequencia') { $seq = (string)$b;
                        }
                        if ($a == 'ano') { $ano = troca((string)$b, '.', '-');
                        }
                    }
                    array_push($classNaci, array($cl, $seq, $ano));
                }
            }

            /********************************************************************/
            if (strlen($NR) > 0) {

                /************************************************* registra patent ********/
                $class = "Patent";
                $NR = troca($NR, '_', '-');
                $id_patent = $this -> frbr_core -> rdf_concept_create($class, $NR);
                $prop = 'altLabel';
                $lit = $this -> frbr_core -> frbr_name($sessao_nome, 'pt-BR', 1);
                $this -> frbr_core -> set_propriety($id_section, $prop, 0, $lit);

                if (strlen($titulo_patent) > 0) {
                    $prop = 'hasTitle';
                    $lit = $this -> frbr_core -> frbr_name($titulo_patent, 'pt-BR', 1);
                    $this -> frbr_core -> set_propriety($id_patent, $prop, 0, $lit);
                }

                $dt_deposito = brtos($dt_deposito);
                if ($dt_deposito > 0) {
                    $dt_deposito = substr($dt_deposito, 0, 4) . '-' . substr($dt_deposito, 4, 2) . '-' . substr($dt_deposito, 6, 2);
                    $id_dp = $this -> frbr_core -> rdf_concept_create('Date', $dt_deposito);
                    $prop = 'hasDatePatent';
                    $this -> frbr_core -> set_propriety($id_patent, $prop, $id_dp, 0);
                }

                /*********************************************** autores patent ************/
                for ($r = 0; $r < count($auth); $r++) {
                    $line = $auth[$r];
                    $nome = $line[0];
                    $local = $line[2];
                    if (strlen($line[1]) > 0) {
                        $local = $line[1] . '/' . $line[2];
                    }
                    $class = "Person";
                    $id_auth = $this -> frbr_core -> rdf_concept_create($class, $nome);

                    $class = "Place";
                    $id_place = $this -> frbr_core -> rdf_concept_create($class, $local);

                    $prop = 'hasAffiliation';
                    $this -> frbr_core -> set_propriety($id_auth, $prop, $id_place, 0);

                    $prop = 'hasPatentHolder';
                    $this -> frbr_core -> set_propriety($id_patent, $prop, $id_auth, 0);
                }

                /*********************************************** inventor patent ************/
                for ($r = 0; $r < count($auth); $r++) {
                    $line = $auth[$r];
                    $nome = $line[0];
                    $local = $line[2];
                    if (strlen($line[1]) > 0) {
                        $local = $line[1] . '/' . $line[2];
                    }
                    $class = "Person";
                    $id_auth = $this -> frbr_core -> rdf_concept_create($class, $nome);

                    $class = "Place";
                    $id_place = $this -> frbr_core -> rdf_concept_create($class, $local);

                    $prop = 'hasAffiliation';
                    $this -> frbr_core -> set_propriety($id_auth, $prop, $id_place, 0);

                    $prop = 'hasPatentInventor';
                    $this -> frbr_core -> set_propriety($id_patent, $prop, $id_auth, 0);
                }

                $sx .= 'ID: ' . $id_patent;

                /************************************************** PUBLICACAO **/

                $class = 'PatentDispatch';
                $PPname = strzero($id_issue, 7) . '-' . strzero($id_section, 7) . '-' . strzero($id_patent, 7);
                $id_dispatch = $this -> frbr_core -> rdf_concept_create($class, $PPname);

                $prop = 'hasPatentDispatch';
                $this -> frbr_core -> set_propriety($id_issue, $prop, $id_dispatch, 0);

                $prop = 'hasPatentSession';
                $this -> frbr_core -> set_propriety($id_dispatch, $prop, $id_section, 0);

                $prop = 'hasPatent';
                $this -> frbr_core -> set_propriety($id_dispatch, $prop, $id_patent, 0);

                if (strlen($content) > 0) {
                    $prop = 'hasPatentDispatchComplement';
                    $id_comment = $this -> frbr_core -> frbr_name($content);
                    $this -> frbr_core -> set_propriety($id_dispatch, $prop, 0, $id_comment);
                }

                if (strlen($inter_pct) > 0) {
                    $class = 'PatentPCT';
                    $id_pct = $this -> frbr_core -> rdf_concept_create($class, $inter_pct);

                    $inter_pct_data = brtos($inter_pct_data);
                    $PPname = substr($inter_pct_data, 0, 4) . '-' . substr($inter_pct_data, 4, 2) . '-' . substr($inter_pct_data, 6, 2);

                    $class = 'Date';
                    $id_pct_dt = $this -> frbr_core -> rdf_concept_create($class, $PPname);

                    $prop = 'hasPatentPCTDate';
                    $this -> frbr_core -> set_propriety($id_pct, $prop, $id_pct_dt, 0);

                    $prop = 'hasPatentPCT';
                    $this -> frbr_core -> set_propriety($id_patent, $prop, $id_pct, 0);
                }
                if (strlen($inter_patent) > 0) {
                    $class = 'PatentFamily';
                    $id_pct = $this -> frbr_core -> rdf_concept_create($class, $inter_patent);

                    $inter_patent_data = brtos($inter_patent_data);
                    $PPname = substr($inter_patent_data, 0, 4) . '-' . substr($inter_patent_data, 4, 2) . '-' . substr($inter_patent_data, 6, 2);

                    $class = 'Date';
                    $id_pct_dt = $this -> frbr_core -> rdf_concept_create($class, $PPname);

                    $prop = 'hasPatentFamilyDate';
                    $this -> frbr_core -> set_propriety($id_pct, $prop, $id_pct_dt, 0);

                    $prop = 'hasPatentFamily';
                    $this -> frbr_core -> set_propriety($id_patent, $prop, $id_pct, 0);
                }

            }
            /*********************************************** INSER CLASSIFICAÇÃO ****************/
            if ((count($classInter) > 0) and ($id_patent > 0)) {
                for ($r = 0; $r < count($classInter); $r++) {
                    $cl = $classInter[$r][0];
                    $class = 'PatentClassification';
                    $id_cls = $this -> frbr_core -> rdf_concept_create($class, $cl);

                    $prop = 'hasPatentClassification';
                    $this -> frbr_core -> set_propriety($id_patent, $prop, $id_cls, 0);
                }
            }
            /*********************************************** INSER CLASSIFICAÇÃO NACIONAL ******/
            if ((count($classNaci) > 0) and ($id_patent > 0)) {
                for ($r = 0; $r < count($classNaci); $r++) {
                    $cl = $classNaci[$r][0];
                    $class = 'PatentClassificationNacional';
                    $id_cls = $this -> frbr_core -> rdf_concept_create($class, $cl);

                    $prop = 'hasPatentClassificationNacional';
                    $this -> frbr_core -> set_propriety($id_patent, $prop, $id_cls, 0);
                }
            }

            $rr++;
            if ($rr > 200000) {
                return ($sx);
            }
        }
        $sx .= '</ul>' . cr();
        return ($sx);

        $erro = (string)$xml -> error;
        if (strlen($erro) > 0) {
            return ($dt);
        }

        $despacho = (array)$xml -> revista -> despacho;
        print_r($despacho);

    }

    function content() {
        $id = '2494';
        $file = 'D:/GoogleDrive/Artigos/2019/BasePatentes/'.$id.'/P'.$id.'/Patente_'.$id.'.xml';
        $xml = file_get_contents($file);
        return ($xml);
    }

}
?>