<?php
  header("Access-Control-Allow-Origin: *");
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
  require_once("assets/PHP/ClientSocket.php");

  tcr8_function();

  function acbrCommand($cmd,$sc = false){
    if($sc) {
      $sc->send($cmd);
      $resposta = $sc->recv();
    } else {
      $resposta = false;
    }
    return $resposta;
  }

  function acbrCommandFile($command,$file,$sc = false){
    extract($GLOBALS);
    $crlf = chr(13).chr(10).chr(46).chr(13).chr(10);
    $cmd = $command.'('.$file.')'.$crlf;
    if($sc) {
      $sc->send($cmd);
      $resposta = $sc->recv();
    } else {
      $resposta = false;
    }
    return $resposta;
  }

  function acbr_savetxt(){
    extract($GLOBALS);
    $data = array();
    try{
      if( empty($_FILES['data']) ) throw new Exception( 'Não foi encontrado nenhum arquivo texto com dados de variável' );
      $_DADOS = json_decode(file_get_contents($_FILES['data']['tmp_name']),true);

      $acbr_ip = isset($_DADOS['acbr_ip']) ? $_DADOS['acbr_ip'] : "127.0.0.1";
      $acbr_port = isset($_DADOS['acbr_port']) ? $_DADOS['acbr_port'] : "3434";

      $sale_id = $_DADOS['sale_id'];

      $filepath_txt = $_DADOS['nfe_filepath'];
      if(file_exists($filepath_txt)) $filepath_txt = update_file_name($filepath_txt);
      $data['nfe_filepath'] = $filepath_txt;
      $nfse_content  = base64_decode($_DADOS['nfe_text']);
      $fp = fopen($filepath_txt,"wb");
      fwrite($fp,$nfse_content);
      fclose($fp);

      $data['success'] = true;
      $data['close_all'] = false;
      $data['reload']    = false;
      $data['message'] = '';
    } catch (Exception $e){
      $data['success'] = false;
      $data['message'] = $e->getMessage();
    }
    $data['msg'] = $data['message'];
    echo json_encode($data);
    exit;
  }

  function acbr_organize_sales(){
    $data = array();
    try{
      if( empty($_FILES['data']) ) throw new Exception( 'Não foi encontrado nenhum arquivo texto com dados de variável' );
      $_DADOS = json_decode(file_get_contents($_FILES['data']['tmp_name']),true);

      $data['_DADOS'] = $_DADOS;
      $cnpj = (isset( $_DADOS['cnpj'] ) ? $_DADOS['cnpj'] : false);
      if(!$cnpj) throw new \Exception("Existem campos necessário que não foram informados", 1);

      $_DIRS = $_DADOS['SATACBR']['dirs'];
      $dirpath_xml = $_DIRS['xml'].$_DIRS['vendas']."/".$cnpj;

      $files = get_files_from_folder($dirpath_xml,'xml');
      // echo $dirpath_xml;
      // echo "<pre>";
      // print_r( $files );
      // echo "</pre>";

      if(count($files)>0){
        $conn_id       =  ftp_server_conn();
        $xml_path_server = "/nfe/$cnpj/files/SAT/";
        $folder_xml    = "/tcr8.com.br/public_html/tcr8_local".$xml_path_server;
        @ftp_mkdir($conn_id, $folder_xml);
        ftp_chdir($conn_id, $folder_xml);
        foreach ($files as $key => $file) {
          // if($key > 0) return;
          $file_parts = pathinfo($file);
          $basename  = $file_parts['basename'];
          $filename  = $file_parts['filename'];
          $extension = $file_parts['extension'];
          $dirname   = $file_parts['dirname'];
          $sale_id = $filename;

          $xml_nfe     = json_decode(json_encode( (array) simplexml_load_file($file) ), 1);
          $date        = recursive_array_search('dEmi',$xml_nfe);
          $date        = array_get_by_array($xml_nfe,$date);
          $xml_hour        = recursive_array_search('hEmi',$xml_nfe);
          $xml_hour        = array_get_by_array($xml_nfe,$xml_hour);
          $xml_hour        = recursive_array_search('hEmi',$xml_nfe);
          $xml_hour        = array_get_by_array($xml_nfe,$xml_hour);

          $xml_nfe     = json_decode(json_encode( (array) simplexml_load_file($file) ), 1);
          $date        = recursive_array_search('dEmi',$xml_nfe);
          $date        = array_get_by_array($xml_nfe,$date);
          $xml_hour        = recursive_array_search('hEmi',$xml_nfe);
          $xml_hour        = array_get_by_array($xml_nfe,$xml_hour);
          $xml_hour 					 = substr($xml_hour, 0, 2).":".substr($xml_hour, 2, 2).":".substr($xml_hour, 4, 2);

          $xml_date            = date("Y-m-d",strtotime($date))." ".$xml_hour;

          $server_year_folder  = date("Y", strtotime($date));
          $server_month_folder = date("m", strtotime($date));
          $server_move_folder  = date("Y/m", strtotime($date));


          echo "<pre>";
          print_r( recursive_array_search('infCpl',$xml_nfe) );
          echo "</pre>";

          $infCpl = array_get_by_array($xml_nfe,recursive_array_search('infCpl',$xml_nfe));
          if (strpos($infCpl, '[Pedido:') !== false) {
            $infCpl = explode(':',$infCpl);
            $sale_id = trim(str_replace("]",'',$infCpl[1]));
          } else {
            $sale_id = $filename;
          }

          @ftp_mkdir($conn_id, $server_year_folder);
          @ftp_mkdir($conn_id, $server_move_folder);

          $chave        = recursive_array_search('infCFe',$xml_nfe);
          $chave        = array_get_by_array($xml_nfe,$chave)['@attributes']['Id'];
          $chave        = preg_replace("/[^0-9]/","",$chave);

          // $chCanc       = recursive_array_search('chCanc',$xml_nfe);
          // echo "saleid = $sale_id<br>";
          // echo "chave = $chave<br>";

          $data['files_debug'][] = array(
            'file' => $file,
          );
          continue;
          $xml_total        = recursive_array_search('ICMSTot',$xml_nfe);
          $xml_total        = array_get_by_array($xml_nfe,$xml_total)['vProd'];

          $new_xml_file = $basename;
          $new_xml_path_ftp    = $server_move_folder."/".$new_xml_file;
          if(ftp_size($conn_id,$new_xml_path_ftp) > 0){
            // $data['errors'][] = "Arquivo não existe";
            $new_xml_path_ftp = update_ftp_fname($new_xml_path_ftp,$conn_id);
          }


          if (ftp_put($conn_id, $new_xml_path_ftp, $file, FTP_ASCII)) {
            rename($file, "{$dirname}/{$server_move_folder}{$basename}");
          } else {
            throw new \Exception("Não foi possível enviar ao Servidor", 1);
          }

          $paths = [
            $dirpath_xml."/".$server_year_folder,
            $dirpath_xml."/".$server_move_folder,
          ];
          foreach ($paths as $key => $path) {
            if (!file_exists($path)){
                $old = umask(0);
                mkdir($path, 0777, true);
                @chmod($path, 0777);
                umask($old);
            }
            @ftp_mkdir($conn_id, $server_year_folder);
            @ftp_mkdir($conn_id, $server_move_folder);
          }


          $data['Files'][] = array(
            'xml_path' => $file, $dirname."/".$server_move_folder."/".$basename,
            'chave'    => $chave,
            'sale_id'  => $sale_id,
            'rename' => "{$dirname}/{$server_move_folder}{$basename}",
            'xml_path_server' => "{$xml_path_server}{$basename}",
            'new_xml_path_ftp' => "{$new_xml_path_ftp}",
            'xml_date' => $xml_date,
            'xml_total' => $xml_total,
          );
        }
        $data['message'] = 'NFE organizado via PHP';

      } else {
        $data['message'] = 'NFE já estava organizado';

      }


      $data['success'] = true;
      $data['close_all'] = false;
      $data['reload']    = false;
    } catch (Exception $e){
      $data['success'] = false;
      $data['message'] = $e->getMessage();
    }
    $data['msg'] = $data['message'];
    echo json_encode($data);
    exit;
  }

  function acbr_cmd(){
    extract($GLOBALS);
    $data = array();
    try{
      if( empty($_FILES['data']) ) throw new Exception( 'Não foi encontrado nenhum arquivo texto com dados de variável' );
      $_DADOS = json_decode(file_get_contents($_FILES['data']['tmp_name']),true);

      $acbr_ip = isset($_DADOS['acbr_ip']) ? $_DADOS['acbr_ip'] : "127.0.0.1";
      $acbr_port = isset($_DADOS['acbr_port']) ? $_DADOS['acbr_port'] : "3434";

      $sat_cmd = $_DADOS['sat_cmd'];
      $nfe_filepath = $_DADOS['nfe_filepath'];

      $sc = new ClientSocket();
      $sc->open($acbr_ip,$acbr_port);
      $response = $sc->recv();
      $retorno = acbrCommandFile($sat_cmd,$nfe_filepath,$sc);

      $filename = "log_".date('Ymd_Hms');
      if( preg_match("/CFe\d{44}/", $retorno, $chNFe) ){
        $chNFe = preg_replace('/[^0-9.]+/', '', $chNFe[0]);
        $filename .= "_".$chNFe.".txt";
        // echo "<pre>";
        // print_r( $chNFe );
        // echo "</pre>";
      } else {
        $filename .= ".txt";
      }

      $filepath_txt = 'C:/ACBrMonitorPLUS/XML/'.$filename;
      if(file_exists($filepath_txt)) $filepath_txt = update_file_name($filepath_txt);
      $fp = fopen($filepath_txt,"wb");
      fwrite($fp,$retorno);
      fclose($fp);
      // if(!$retorno) throw new \Exception("Error Processing Request", 1);
      $data['retorno'] = base64_encode($retorno);

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

  function generate_sat(){
    extract($GLOBALS);
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
      // END OF DEBUG
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
