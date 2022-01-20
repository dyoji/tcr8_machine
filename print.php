<?php
  //--------------------------------------------------------------------------------------------
  //-----------------------------                                  -----------------------------
  //-----------------------------     CRIADO POR DANIEL MIKAMI     -----------------------------
  //-----------------------------                                  -----------------------------
  //--------------------------------------------------------------------------------------------
  ini_set('display_errors', 1);
  ini_set('display_startup_errors', 1);
  error_reporting(E_ALL);
  // setlocale(LC_ALL, 'pt_BR.utf-8', 'pt_BR.utf-8');
  // date_default_timezone_set("Brazil/East");
  setlocale(LC_TIME, 'pt_BR', 'pt_BR.utf-8', 'pt_BR.utf-8', 'portuguese');
  date_default_timezone_set('America/Sao_Paulo');
  mb_internal_encoding("utf-8"); //IMPORTANTE PARA O ESPAÇAMENTO CORRETO

  require_once 'basic_dan_machine.php';

  tcr8_function();

  function generate_sat(){
    $data = array();
  	try{
  		if( empty($_FILES['data']) ) throw new Exception( 'Não foi encontrado nenhum arquivo texto com dados de variável' );
  		$_DADOS = json_decode(file_get_contents($_FILES['data']['tmp_name']),true);

      $filepath_temp = $_DADOS['SATPHP']['sevenbuilds']['proc_dir'] . "/myText.temp";
      $filepath_txt = $_DADOS['SATPHP']['sevenbuilds']['proc_dir'] . "/" . $_DADOS['nfe_filename'];
      $nfse_content  = $_DADOS['nfe_text'];
      // DEBUG
      // $nfse_content .= "\n".$_DADOS['SATPHP']['sevenbuilds']['proc_dir'];
      // $nfse_content .= "\n".$_DADOS['SATPHP']['sevenbuilds']['xml_dir'];
      // END OF DEBUG s
      $fp = fopen($filepath_temp,"wb");
      fwrite($fp,$nfse_content);
      fclose($fp);
      rename($filepath_temp, $filepath_txt);
      // print_r($files1);
      // print_r($files2);
      // echo "<br>";
      // $fh = fopen('C:/seven/teste.txt','r');
      // while ($line = fgets($fh)) {
      //   // <... Do your work with the line ...>
      //   echo($line);
      // }
      // fclose($fh);
  		$data['success'] = true;
      $data['close_all'] = false;
      $data['reload']    = false;
      $data['message'] = 'NFE via PHP Emitido';
  	} catch (Exception $e){
  	  $data['success'] = false;
  	  $data['message'] = $e->getMessage();
  	}
    $data['msg'] = $data['message'];
  	echo json_encode($data);
  	exit;
  }
?>
