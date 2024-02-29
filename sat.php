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

  function make_dirs(){
    $data = array();
    try{
      if( empty($_FILES['data']) ) throw new Exception( 'Não foi encontrado nenhum arquivo texto com dados de variável' );
      $_DADOS = json_decode(file_get_contents($_FILES['data']['tmp_name']),true);
      $data['_DADOS'] = $_DADOS;

      $_DIRS = $_DADOS['dirs'];
      foreach ($_DIRS as $key => $_DIR) {
        $data['dirs'][$_DIR] = new_folder($_DIR);
        // code...
      }

      $data['success'] = true;
      $data['message'] = 'Diretórios criados com sucesso';
    } catch (Exception $e){
      $data['success'] = false;
      $data['message'] = $e->getMessage();
    }
    $data['msg'] = $data['message'];
    echo json_encode($data);
    exit;
  }

  function read_last_line($filepath){
    $line = '';
    $f = fopen($filepath, 'r');
    $cursor = -1;
    fseek($f, $cursor, SEEK_END);
    $char = fgetc($f);
    /**
     * Trim trailing newline chars of the file
     */
    while ($char === "\n" || $char === "\r") {
        fseek($f, $cursor--, SEEK_END);
        $char = fgetc($f);
    }
    /**
     * Read until the start of file or first newline char
     */
    while ($char !== false && $char !== "\n" && $char !== "\r") {
        /**
         * Prepend the new char
         */
        $line = $char . $line;
        fseek($f, $cursor--, SEEK_END);
        $char = fgetc($f);
    }

    fclose($f);
    return $line;
  }

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
      $filepath_txt = "C:/ACBrMonitorPLUS/TXT/IN/teste.txt";
      // $filepath_txt = isset($_DADOS['sat_txtpath']) ? $_DADOS['sat_txtpath'] : "C:\ACBrMonitorPLUS\TXT\IN";
      if(file_exists($filepath_txt)) $filepath_txt = update_file_name($filepath_txt);
      // $data['nfe_filepath'] = $filepath_txt;
      $fp = fopen($filepath_txt,"wb");
      fwrite($fp,$cmd);
      fclose($fp);

      $resposta = false;
    }
    return $resposta;
  }

  function acbrCommandPrintCancel($command,$file_original,$file_cancel,$sc = false){
    extract($GLOBALS);
    $crlf = chr(13).chr(10).chr(46).chr(13).chr(10);
    $cmd = $command.'('.$file_original.','.$file_cancel.')'.$crlf;

    if($sc) {
      $sc->send($cmd);
      $resposta = $sc->recv();
    } else {
      $filepath_txt = "C:/ACBrMonitorPLUS/TXT/IN/teste.txt";
      // $filepath_txt = isset($_DADOS['sat_txtpath']) ? $_DADOS['sat_txtpath'] : "C:\ACBrMonitorPLUS\TXT\IN";
      if(file_exists($filepath_txt)) $filepath_txt = update_file_name($filepath_txt);
      // $data['nfe_filepath'] = $filepath_txt;
      $fp = fopen($filepath_txt,"wb");
      fwrite($fp,$cmd);
      fclose($fp);

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

  function upload_xmls(){
    //é o mais novo organizador 22/07/2022
    extract($GLOBALS);
    $data = array();
    try{
      if( empty($_FILES['data']) ) throw new Exception( 'Não foi encontrado nenhum arquivo texto com dados de variável' );
      $_DADOS = json_decode(file_get_contents($_FILES['data']['tmp_name']),true);

      $data['dirs'] = $_DIRS = $_DADOS['dirs'];
      $data['store'] = $_STORE = $_DADOS['store_obj'];
      $data['satpc_url_xml_upload'] = $satpc_url_xml_upload = $_DADOS['satpc_url_xml_upload'];
      $path_root = $_DIRS['root'];
      $path_cancelamentos = $_DIRS['root'].$_DIRS['xml']."/".$_DIRS['cancelamentos']."/".$_STORE['cnpj']."/";
      $path_cancelamentos_sync = $_DIRS['root'].$_DIRS['xml']."/".$_DIRS['cancelamentos']."/".$_STORE['cnpj']."/sync/";
      $path_enviados = $_DIRS['root'].$_DIRS['xml']."/".$_DIRS['enviados']."/"."/".$_STORE['cnpj']."/";
      $path_enviar = $_DIRS['root'].$_DIRS['xml']."/".$_DIRS['enviar']."/";
      $path_saida = $_DIRS['root'].$_DIRS['xml']."/".$_DIRS['saida']."/";
      $path_vendas = $_DIRS['root'].$_DIRS['xml']."/".$_DIRS['vendas']."/".$_STORE['cnpj']."/";
      $path_sync = $_DIRS['root'].$_DIRS['xml']."/".$_DIRS['vendas']."/".$_STORE['cnpj']."/sync/";
      $path_xml = $_DIRS['root'].$_DIRS['xml']."/";

      $path_IN = $_DIRS['root']."TXT/IN/";
      $path_OU = $_DIRS['root']."TXT/OU/";

      $postData = ['somevar' => 'hello'];
      $file_key = 0;

      $files_arr = get_files_from_folder($path_vendas);
      $files_arr = array_merge($files_arr,get_files_from_folder($path_cancelamentos));
      foreach ($files_arr as $key => $file_path) {
        if($key > 15) continue;

        $file_data = array();
        $path_parts = pathinfo($file_path);
        // echo $path_parts['dirname'], "\n";
        // echo $path_parts['basename'], "\n";
        // echo $path_parts['filename'], "\n"; // desde o PHP 5.2.0
       // 'file' => new CURLFile($_FILES['file']['tmp_name'],$_FILES['file']['type'], $_FILES['file']['name']),
       if(strtolower($path_parts['extension']) == 'xml') {
         $postData['file[' . $file_key . ']'] = curl_file_create(
             realpath($file_path),
             mime_content_type($file_path),
             basename($file_path)
         );
         $file_key++;
         $data['Records'][] = $file_path;
       } else {
         $data['Records_invalid'][] = $file_path;
       }
        // code...
      }
      if(isset($data['Records']) && count($data['Records'])>0){
        $ch = curl_init();
        // curl_setopt($ch, CURLOPT_URL, 'http://localhost/tcr8_sys/actions/nfephp.php?action=xml_upload');
        curl_setopt($ch, CURLOPT_URL, $satpc_url_xml_upload);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10); //86400 = 1 Day Timeout
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_REFERER, $_SERVER['HTTP_HOST']);
  
        $data['teste'] = $response = curl_exec($ch);
        if (curl_errno($ch)) {
           throw new \Exception("$ch Error Processing Request", 1);
        } else {
           $data['uploads'] = json_decode($response,true);
        }
        if(isset($data['uploads']['files'])) {
          foreach ($data['uploads']['files'] as $key => $_UPLOAD) {
            if($_UPLOAD['success']) {
              new_folder($path_sync);
              new_folder($path_cancelamentos_sync);
              if(file_exists( $path_vendas . $_UPLOAD['name'] )) rename($path_vendas . $_UPLOAD['name'],$path_sync . $_UPLOAD['name']);
              if(file_exists( $path_cancelamentos . $_UPLOAD['name'] )) rename($path_cancelamentos . $_UPLOAD['name'],$path_cancelamentos_sync . $_UPLOAD['name']);
            }
          }
        }

        $data['message'] = 'Upload feito com sucesso';
      } else {
        $data['message'] = 'Não tem upload para fazer';
      }


      curl_close($ch);

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

  function acbr_organizer(){
    //é o mais novo organizador 22/07/2022
    extract($GLOBALS);
    $data = array();
    try{
      if( empty($_FILES['data']) ) throw new Exception( 'Não foi encontrado nenhum arquivo texto com dados de variável' );
      $_DADOS = json_decode(file_get_contents($_FILES['data']['tmp_name']),true);

      $data['dirs'] = $_DIRS = $_DADOS['dirs'];
      $data['store'] = $_STORE = $_DADOS['store_obj'];
      $path_root = $_DIRS['root'];
      $path_cancelamentos = $_DIRS['root'].$_DIRS['xml']."/".$_DIRS['cancelamentos']."/".$_STORE['cnpj']."/";
      $path_enviados = $_DIRS['root'].$_DIRS['xml']."/".$_DIRS['enviados']."/"."/".$_STORE['cnpj']."/";
      $path_enviar = $_DIRS['root'].$_DIRS['xml']."/".$_DIRS['enviar']."/";
      $path_saida = $_DIRS['root'].$_DIRS['xml']."/".$_DIRS['saida']."/";
      $path_vendas = $_DIRS['root'].$_DIRS['xml']."/".$_DIRS['vendas']."/".$_STORE['cnpj']."/";
      $path_xml = $_DIRS['root'].$_DIRS['xml']."/";

      $path_IN = $_DIRS['root']."TXT/IN/";
      $path_OU = $_DIRS['root']."TXT/OU/";

      foreach (get_files_from_folder($path_vendas) as $key => $file_path) {
        $file_data = array();
        $path_parts = pathinfo($file_path);
        // echo $path_parts['dirname'], "\n";
        // echo $path_parts['basename'], "\n";
        // echo $path_parts['extension'], "\n";
        // echo $path_parts['filename'], "\n"; // desde o PHP 5.2.0

        $xml = file_get_contents($file_path);
        $xml_nfe = json_decode(json_encode( (array) simplexml_load_string($xml) ), 1);
        $data['xml'][] = $xml_nfe;
        $file_data['xml'] = $xml;
        // $file_data['zip'] = gzencode(base64_encode($xml));
        $file_data['zip'] = base64_encode(gzencode($xml));
        $file_data['basename'] = $path_parts['basename'];
        $file_data['xml'] = $xml_nfe;
        $file_data['chNFe'] = $chNFe = str_replace("CFe", "",($xml_nfe['infCFe']['@attributes']['Id']));
        $file_data['dEmi'] = date_format(date_create_from_format('Ymd', ($xml_nfe['infCFe']['ide']['dEmi'])), 'Y-m-d');

        if( isset($xml_nfe['infCFe']['infAdic']['infCpl']) ){
          $data['infCpl'][] = $infCpl = ($xml_nfe['infCFe']['infAdic']['infCpl']);
          if(strpos($infCpl, 'Pedido') !== false){
            $pedido_crude = get_string_between($infCpl, '[', ']');
            $file_data['sale_id'] = $sale_id = explode(':',$pedido_crude)[1];
            $file_data['cnpj'] = $emit_cnpj = ($xml_nfe['infCFe']['emit']['CNPJ']);

          } else continue;
        } else continue;
        $data['Records'][] = $file_data;
        // code...
      }

      $data['success'] = true;
      $data['close_all'] = false;
      $data['reload']    = false;
      $data['message'] = 'ORGANIZADO COM SUCESSO';
    } catch (Exception $e){
      $data['success'] = false;
      $data['message'] = $e->getMessage();
    }
    $data['msg'] = $data['message'];
    echo json_encode($data);
    exit;
  }

  function upload_xml(){
    $data = array(
       'file' => new CURLFile($_FILES['file']['tmp_name'],$_FILES['file']['type'], $_FILES['file']['name']),
       // 'destination' => 'destination path in which file will be uploaded',
       // 'calling_method' => 'upload_file',
       // 'file_name' => 'file name, you want to give when upload will completed'
    );

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://fl.curumim.tcr8.com.br/actions/nfe.php?action=xml_upload');
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10); // 1 Day Timeout
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_REFERER, $_SERVER['HTTP_HOST']);

    $response = curl_exec($ch);
    if (curl_errno($ch)) {

       $msg = FALSE;
    } else {
       $msg = $response;
    }

    curl_close($ch);
    echo $msg;
  }

  function acbr_after_save_organized(){
    extract($GLOBALS);
    $data = array();
    try{
      if( empty($_FILES['data']) ) throw new Exception( 'Não foi encontrado nenhum arquivo texto com dados de variável' );
      $_DADOS = json_decode(file_get_contents($_FILES['data']['tmp_name']),true);

      $data['dirs'] = $_DIRS = $_DADOS['dirs'];
      $data['store'] = $_STORE = $_DADOS['store_obj'];
      $path_root = $_DIRS['root'];
      $path_cancelamentos = $_DIRS['root'].$_DIRS['xml']."/".$_DIRS['cancelamentos']."/".$_STORE['cnpj']."/";
      $path_enviados = $_DIRS['root'].$_DIRS['xml']."/".$_DIRS['enviados']."/"."/".$_STORE['cnpj']."/";
      $path_enviar = $_DIRS['root'].$_DIRS['xml']."/".$_DIRS['enviar']."/";
      $path_saida = $_DIRS['root'].$_DIRS['xml']."/".$_DIRS['saida']."/";
      $path_vendas = $_DIRS['root'].$_DIRS['xml']."/".$_DIRS['vendas']."/".$_STORE['cnpj']."/";
      $path_xml = $_DIRS['root'].$_DIRS['xml']."/";

      foreach ($_DADOS['organized'] as $key => $_XML) {
        $dEmi = date_create_from_format('Y-m-d', $_XML['dEmi']) ;
        $year_folder = date_format($dEmi, 'Y');
        $move_folder = date_format($dEmi, 'Y')."/".date_format($dEmi, 'm');
        new_folder($path_vendas.$move_folder);

        rename($path_vendas.$_XML['basename'], $path_vendas.$move_folder."/".$_XML['basename']);
        $data['move_dir'][] = array(
          'old' => $path_vendas.$_XML['basename'],
          'new' => $path_vendas.$move_folder."/".$_XML['basename'],
        );
        // $data['deleted'][] = @unlink($path_vendas.$_ORGANIZED['basename']);
        // code...
      }
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

  function acbr_sat_emit(){
    extract($GLOBALS);
    $data = array();
    try{
      if( empty($_FILES['data']) ) throw new Exception( 'Não foi encontrado nenhum arquivo texto com dados de variável' );
      $_DADOS = json_decode(file_get_contents($_FILES['data']['tmp_name']),true);
      $data['_DADOS'] = $_DADOS;
      $acbr_ip = isset($_DADOS['acbr_ip']) ? $_DADOS['acbr_ip'] : "127.0.0.1";
      $acbr_port = isset($_DADOS['acbr_port']) ? $_DADOS['acbr_port'] : "3434";
      $crlf = chr(13).chr(10).chr(46).chr(13).chr(10);

      $acbr_txts = $_DADOS['acbr_txts'];
      $filepath_txt = $_DADOS['nfe_filepath'];

      //INICIALIZAR O SAT
      $path_root = "C:/ACBrMonitorPLUS/";
      $path_IN = $path_root."TXT/IN/";
      $path_OU = $path_root."TXT/OU/";
      // $deleted = unlink($SAT_ENVIAR_filepath_txt);

      $_COMMANDS_INIT = 'SAT.Inicializar();';
      $filepath_SAT_INIT_IN_txt = $path_IN."SAT_INIT.txt";
      $filepath_SAT_INIT_OU_txt = $path_OU."SAT_INIT-resp.txt";
      $deleted = @unlink($filepath_SAT_INIT_OU_txt);

      if(file_exists($filepath_SAT_INIT_IN_txt)) $filepath_SAT_INIT_IN_txt = update_file_name($filepath_SAT_INIT_IN_txt);
      $fp = fopen($filepath_SAT_INIT_IN_txt,"wb");
      fwrite($fp,$_COMMANDS_INIT);
      fclose($fp);

      $x = 0;
      $count = 10;
      do {
        if(!file_exists($filepath_SAT_INIT_OU_txt)){
          $x++;
          if($x >= $count) throw new \Exception("SAT não incializado", 1);
          $data['txtini_resp'][] = "[$x] File still Loading";
          sleep(1);//Delays the program execution for 5seconds before code continues.
        } else {
          $x = $count;
          $data['txtini_resp'][] = "[$x] Ok, Loaded";

          //SAT INICIALIZADO, VAMOS EMITIR O SAT
          foreach ($acbr_txts as $key => $_TXT) {
            $sale_id = $_TXT['sale_id'];
            $SAT_ENVIAR_filepath_IN_txt = $path_IN."SATENVIAR_".$sale_id.".txt";
            $SAT_ENVIAR_filepath_OU_txt = $path_OU."SATENVIAR_".$sale_id."-resp.txt";

            $_TXT['path'] = $filepath_txt.$_TXT['nfe_filename'];
            if(file_exists($_TXT['path'])) $_TXT['path'] = update_file_name($_TXT['path']);
            $nfse_content  = base64_decode($_TXT['nfe_text']);
            $fp = fopen($_TXT['path'],"wb");
            fwrite($fp,$nfse_content);
            fclose($fp);
            $_TXT['message'] = 'TXT salvo localmente e pronto para gerar o SAT';
            $data['Records'][] = $_TXT;
            $_COMMANDS = 'SAT.CriarEnviarCFe('.$_TXT['path'].');'.$crlf;

            if(file_exists($SAT_ENVIAR_filepath_IN_txt)) $SAT_ENVIAR_filepath_IN_txt = update_file_name($SAT_ENVIAR_filepath_IN_txt);
            $fp = fopen($SAT_ENVIAR_filepath_IN_txt,"wb");
            fwrite($fp,$_COMMANDS);
            fclose($fp);

            $x = 0;
            $data['txt_OU'][] = $SAT_ENVIAR_filepath_OU_txt;
            do {
              if(!file_exists($SAT_ENVIAR_filepath_OU_txt)){
                $x++;
                if($x >= $count) throw new \Exception("Arquivo de Saída não encontrado", 1);
                $data['txt_OUT_resp'][] = "[$x] File still Loading";
                sleep(1);//Delays the program execution for 5seconds before code continues.
              } else {
                $x = $count;
                $data['processed'][] = array(
                  'sat_commands_id' => $_TXT['sat_commands_id'],
                  'sale_id' => $sale_id,
                  'retorno' => json_decode( read_last_line($SAT_ENVIAR_filepath_OU_txt) ),
                );
                $data['txt_OUT_resp'][] = "[$x] File Loaded!";
              }
            } while($x < $count); // this kind of regulates how long the loop should last to avoid maximum execution timeout error

          }
        }
      } while($x < $count); // this kind of regulates how long the loop should last to avoid maximum execution timeout error
      // FIM DE INICIALIZAR

      // $x = 0;
      // do {
      //   if(!file_exists($filepath_txt_init)){
      //     $x++;
      //     if($x >= $count) throw new \Exception("Arquivo de Saída não encontrado", 1);
      //     $data['txt_OUT_resp'][] = "[$x] File still Loading";
      //     sleep(1);//Delays the program execution for 5seconds before code continues.
      //   } else {
      //     $x = $count;
      //     $data['retorno'] = base64_encode( file_get_contents($SAT_ENVIAR_filepath_OU_txt) );
      //     $data['txt_OUT_resp'][] = "[$x] File Loaded!";
      //   }
      // } while($x < $count); // this kind of regulates how long the loop should last to avoid maximum execution timeout error



      // $filepath_txt = "C:/ACBrMonitorPLUS/TXT/teste.txt";
      // if(file_exists($filepath_txt)) $filepath_txt = update_file_name($filepath_txt);
      // $fp = fopen($filepath_txt,"wb");
      // fwrite($fp,$_COMMANDS);
      // fclose($fp);


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

      if( empty($_FILES['data']) ) throw new Exception( 'Não foi encontrado nenhum arquivo texto com dados de variável' );
      $_DADOS = json_decode(file_get_contents($_FILES['data']['tmp_name']),true);

      $data['dirs'] = $_DIRS = $_DADOS['dirs'];
      $data['store'] = $_STORE = $_DADOS['store_obj'];
      $path_root = $_DIRS['root'];
      $path_cancelamentos = $_DIRS['root'].$_DIRS['xml']."/".$_DIRS['cancelamentos']."/".$_STORE['cnpj']."/";
      $path_cancelamentos_sync = $_DIRS['root'].$_DIRS['xml']."/".$_DIRS['cancelamentos']."/".$_STORE['cnpj']."/sync/";
      $path_enviados = $_DIRS['root'].$_DIRS['xml']."/".$_DIRS['enviados']."/"."/".$_STORE['cnpj']."/";
      $path_enviar = $_DIRS['root'].$_DIRS['xml']."/".$_DIRS['enviar']."/";
      $path_saida = $_DIRS['root'].$_DIRS['xml']."/".$_DIRS['saida']."/";
      $path_vendas = $_DIRS['root'].$_DIRS['xml']."/".$_DIRS['vendas']."/".$_STORE['cnpj']."/";
      $path_sync = $_DIRS['root'].$_DIRS['xml']."/".$_DIRS['vendas']."/".$_STORE['cnpj']."/sync/";
      $path_xml = $_DIRS['root'].$_DIRS['xml']."/";

      $sat_cmd = $_DADOS['sat_cmd'];
      $nfe_filepath = $_DADOS['nfe_filepath'];
      $path_parts = pathinfo($nfe_filepath);
      $xml_path = $path_sync.$path_parts['basename'];
      $data['xml_path'] = $xml_path;
      $txtcomm = true;
      if(strpos($sat_cmd, "ImprimirExtratoCancelamento") !== false){ //se for cancelamento
        $nfe_cancel_filename = $_DADOS['nfe_cancel_filename'];
        $xml_cancel_path = $path_cancelamentos_sync.$nfe_cancel_filename;
        if($txtcomm) {
          $retorno = acbrCommandPrintCancel($sat_cmd,$xml_path,$xml_cancel_path,false);
        } else {
          $sc = new ClientSocket();
          $sc->open($acbr_ip,$acbr_port);
          $response = $sc->recv();
          $retorno = acbrCommandPrintCancel($sat_cmd,$xml_path,$xml_cancel_path,$sc);
        }
      } else { // se for qualquer outro comando
        if($txtcomm) {
          $retorno = acbrCommandFile($sat_cmd,$xml_path,false);
        } else {
          $sc = new ClientSocket();
          $sc->open($acbr_ip,$acbr_port);
          $response = $sc->recv();
          $retorno = acbrCommandFile($sat_cmd,$xml_path,$sc);
        }
      }

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
