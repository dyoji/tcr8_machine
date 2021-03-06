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

  $view = false;
  if($view){
    $_PRINT = [
      'esc' => "<br><br>",
      'codeutf8' => "",
      'cortaTotal'		=> "\n<div style='color:red;'>".mb_str_pad("",48,"-",STR_PAD_BOTH).'</div><br><br>',
      'cortaParcial'		=> "\n<div style='color:red;'>".mb_str_pad("||",48,"-",STR_PAD_BOTH).'</div><br><br>',
      'avancaPapel'		=> "",
      'diminuiLineSpace'	=> "",

      'n_on'		=> "<b>",
      'n_off'		=> "</b>",

      's_on'		=> "<u>",
      's_off'		=> "</u>",

      'i_on' => "<i>",
      'i_off' => "</i>",

      'emphase' => "",

      'center' => "<div style='align: center;'>",
      'center_off' => '</div>', //Apenas para Debug
      'left'   => "",
      'left_off' => '', //Apenas para Debug
      'right'   => "",
      'right_off' => '', //Apenas para Debug

      'tamanhoDuploOn'		=> "<div style='transform: scale(1,1.5);'>",
      'tamanhoDuploOff'	=> "</div>",

      'melhoraQualidade'	=> "",
      'diminuiQualidade'	=> "",
      'print_works' => '',
    ];
  } else {
    $_PRINT = [
      'esc'	 		=> "\x0", // toda impressão deve começar com esse caracter de escape;
      'codeutf8' => "\x1B\x74\x08",
      'cortaTotal'		=> "\x1B\x69",
      'cortaParcial'		=> "\x1B\x6D",
      'avancaPapel'		=> "\x0C",
      'diminuiLineSpace'	=> "\x1B\x33\x12",

      'n_on'		=> "\x1B\x45",
      'n_off'		=> "\x1B\x46",

      's_on'		=> "\x1B\x2D\x01",
      's_off'		=> "\x1B\x2D\x00",

      'i_on' => "\x1B\x34",
      'i_off' => "\x1B\x35",

      'emphase' => "\x1B\x56",

      'center' => "\x1B\x61\x01",
      'center_off' => '', //Apenas para Debug
      'left'   => "\x1B\x61\x00",
      'left_off' => '', //Apenas para Debug
      'right'   => "\x1B\x61\x02",
      'right_off' => '', //Apenas para Debug

      'tamanhoDuploOn'		=> "\x1b\x64\x01",
      'tamanhoDuploOff'	=> "\x1b\x64\x00",

      'melhoraQualidade'	=> "\x1D\xF9\x2D\x01",
      'diminuiQualidade'	=> "\x1D\xF9\x2D\x00",
      'print_works' => '',
    ];
  }

  $impressora_endereco_na_rede = @$_REQUEST['ip'];
  $impressora_porta_padrao = @$_REQUEST['port']; // Porta em que a impressora fica escutando por padrão
  // http://localhost/tcr8_machine/print.php?action=mp4200&ip=192.168.0.99&port=9100&text=oi
  // echo "$impressora_endereco_na_rede <br> $impressora_porta_padrao <br>";

  function exec_print($print){
  	extract($GLOBALS);
  	$data = array();
  	try{
      $print = base64_decode($print);
      $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
      if ($socket === false) throw new Exception( "socket_create() falha: motivo: " . socket_strerror(socket_last_error()) );
      $result = socket_connect($socket, $impressora_endereco_na_rede, $impressora_porta_padrao);
      if ($result === false) throw new Exception( "socket_connect() falha: motivo: ($result) " . socket_strerror(socket_last_error($socket)) );

      socket_write($socket, $print, strlen($print));
      socket_close($socket);

  		$data['Result'] = 'OK';
      $data['success'] = true;
      $data['message'] = 'Impressão Concluída';
  		$data['type'] = 'success';
    }catch (Exception $e){
      $data['success'] = false;
      $data['message'] = $e->getMessage();
    }
  	$data['printer'] = $impressora_endereco_na_rede;
  	$data['port']    = $impressora_porta_padrao;
    $data['msg'] = $data['message'];
    return $data;
    exit;
  }

  function exec_sprint($header='',$body='',$footer='',$corte_total = true){ //simple print
  	extract($GLOBALS);
  	$data = array();
  	try{
  		$corte = $corte_total ? $_PRINT['cortaTotal'] : $_PRINT['cortaParcial'];
  		$init      = $_PRINT['esc'] . $_PRINT['codeutf8'] . $_PRINT['melhoraQualidade'] . $_PRINT['diminuiLineSpace']  ;
  		$impressao = $init . $header . $body . $footer . $corte;

  		if(!$view){
  			$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
  			if ($socket === false) throw new Exception( "socket_create() falha: motivo: " . socket_strerror(socket_last_error()) );
  			$result = socket_connect($socket, $impressora_endereco_na_rede, $impressora_porta_padrao);
  			if ($result === false) throw new Exception( "socket_connect() falha: motivo: ($result) " . socket_strerror(socket_last_error($socket)) );

  			socket_write($socket, $impressao, strlen($impressao));
  			socket_close($socket);
  		} else {
  			$html_debug = "<div class='teste' style='width: 100%;margin: 0 auto; background:white'><pre>".$impressao."</pre></div>";
  			$data['html'] = '<pre><div style="margin: 0 auto;">' . $html_debug . '</div></pre>';
  		}

  		$data['Result'] = 'OK';
      $data['success'] = true;
      $data['message'] = 'Impressão Concluída';
  		$data['type'] = 'success';
    }catch (Exception $e){
      $data['success'] = false;
      $data['message'] = $e->getMessage();
    }
    $data['msg'] = $data['message'];
    return $data;
    // echo $data['html'];
    exit;
  }

  tcr8_function();

  function mp4200(){
    $data = array();
    extract($GLOBALS);
    try{
      if( empty($_FILES['data']) ) throw new Exception( 'Não foi encontrado nenhum arquivo texto com dados de variável' );
      $_DADOS = json_decode(file_get_contents($_FILES['data']['tmp_name']),true);
      $_CUPOM = $_DADOS['print']['cupom'];
      exec_print($_CUPOM['print']);
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
  function mp4200_teste(){
    // http://10.19.31.190/tcr8_machine/print.php?action=mp4200_teste&ip=10.19.31.197&port=9100&text=teste
    $data = array();
    extract($GLOBALS);
    try{
      // if( empty($_FILES['data']) ) throw new Exception( 'Não foi encontrado nenhum arquivo texto com dados de variável' );
      // $_DADOS = json_decode(file_get_contents($_FILES['data']['tmp_name']),true);

      $header = $body = $footer = "";

  		// $text = $_REQUEST['text'];
      $text = "Este é um uma mensagem de Teste, significa que a impressora está conectada com o sistema de forma via PHP";
  		$text = preg_split('/<br[^>]*>/i', $text);
      $body .= "\n";
      $body .= "\n";

  		$body.= $_PRINT['tamanhoDuploOn'].$_PRINT['n_on'];

  		foreach ($text as $linha) {
  			$body .= wordwrap($linha, 48, "\n");
  			$body .= "\n";
  		}
  		$body.= $_PRINT['tamanhoDuploOff'].$_PRINT['n_off'];

  		$body .= "\n";
  		$body .= "\n";

      $data['print']   = exec_sprint($header,$body,$footer);


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

  function organize_sat_locally(){
    $data = array();
  	try{
  		if( empty($_FILES['data']) ) throw new Exception( 'Não foi encontrado nenhum arquivo texto com dados de variável' );
  		$_DADOS = json_decode(file_get_contents($_FILES['data']['tmp_name']),true);

      $data['_DADOS'] = $_DADOS;
      $cnpj = (isset( $_DADOS['cnpj'] ) ? $_DADOS['cnpj'] : false);
      if(!$cnpj) throw new \Exception("Existem campos necessário que não foram informados", 1);

      $dirpath_xml = "{$_DADOS['SATPHP']['sevenbuilds']['xml_dir']}";

      $files = get_files_from_folder($dirpath_xml,'xml');
      if(count($files)>0){
        $conn_id       =  ftp_server_conn();
        $xml_path_server = "/nfe/$cnpj/files/SAT/";
        $folder_xml    = "/tcr8.com.br/public_html/tcr8_local".$xml_path_server;
        ftp_chdir($conn_id, $folder_xml);
        foreach ($files as $key => $file) {
          // if($key > 0) return;
          $file_parts = pathinfo($file);
          $basename  = $file_parts['basename'];
          $filename  = $file_parts['filename'];
          $extension = $file_parts['extension'];
          $dirname   = $file_parts['dirname'];
          $sale_id = $filename;

          $filepath_temp = "{$_DADOS['SATPHP']['sevenbuilds']['proc_dir']}/myText.temp";
          $filepath_txt = "{$_DADOS['SATPHP']['sevenbuilds']['proc_dir']}/{$sale_id}.txt";

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

          @ftp_mkdir($conn_id, $server_year_folder);
          @ftp_mkdir($conn_id, $server_move_folder);

          // $server_year_folder  = "1900";
          // $server_month_folder = "01";
          // $server_move_folder  = "1900/01";

          $chave        = recursive_array_search('infCFe',$xml_nfe);
          $chave        = array_get_by_array($xml_nfe,$chave)['@attributes']['Id'];
          $chave        = preg_replace("/[^0-9]/","",$chave);

          $chCanc       = recursive_array_search('chCanc',$xml_nfe);

          $data['files_debug'][] = array(
            'file' => $file,
          );
          if($chCanc !== null) {
            // $data['Cancelados']
          } else {
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

  function organize_xml_byphp($xml_dir,$cnpj) {
    $files = get_files_from_folder($xml_dir,'xml');
    if(count($files)>0){
      $conn_id       =  ftp_server_conn();
      $cnpj          = '24593673000160';
      $folder_xml    = "/tcr8.com.br/public_html/tcr8_local/nfe/$cnpj/files/SAT";
      ftp_chdir($conn_id, $folder_xml);
      foreach ($files as $key => $file) {
        // if($key > 0) return;
        $file_parts = pathinfo($file);
        $basename  = $file_parts['basename'];
        $filename  = $file_parts['filename'];
        $extension = $file_parts['extension'];
        $dirname   = $file_parts['dirname'];

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

        $server_year_folder  = "1900";
        $server_month_folder = "01";
        $server_move_folder  = "1900/01";

        $paths = [
          $xml_dir."/".$server_year_folder,
          $xml_dir."/".$server_move_folder,
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

        $chave        = recursive_array_search('infCFe',$xml_nfe);
        $chave        = array_get_by_array($xml_nfe,$chave)['@attributes']['Id'];
        $chave        = preg_replace("/[^0-9]/","",$chave);

        if (ftp_put($conn_id, $server_move_folder."/".$basename, $file, FTP_ASCII)) {
          rename($file, $dirname."/".$server_move_folder."/".$basename);

         // echo "successfully uploaded $file\n";
        } else {
         // echo "There was a problem while uploading $file\n";
        }
        // echo "<pre>";
        // print_r($xml_nfe);
        // echo "</pre>";    // code...
      }
    }
  }

?>
