<?php
  //--------------------------------------------------------------------------------------------
  //-----------------------------                                  -----------------------------
  //-----------------------------     CRIADO POR DANIEL MIKAMI     -----------------------------
  //-----------------------------                                  -----------------------------
  //--------------------------------------------------------------------------------------------
  //CONFIG Inicial
  ini_set('display_errors', 1);
  ini_set('display_startup_errors', 1);
  error_reporting(E_ALL);
  setlocale(LC_ALL, 'pt_BR.utf-8', 'pt_BR.utf-8');
  date_default_timezone_set("Brazil/East");
  mb_internal_encoding("utf-8"); //IMPORTANTE PARA O ESPAÇAMENTO CORRETO

  function tcr8_type($action=false){
  	if( isset($_POST['$_tCr8']) && !empty($_POST['$_tCr8'] ) ){
  	  $_tCr8 = $_POST['$_tCr8'];
  	  $type = $_tCr8['action'];
  		return $type;
  	} elseif ( isset($_GET['action']) && !empty($_GET['action'] ) ) {
  	  $type = $_GET['action'];
  		return $type;
  	} elseif ( isset($_POST['type']) && !empty($_POST['type'] ) ) {
  	  $type = $_POST['type'];
  		return $type;
  	} else {
  		if($action){
  			$data = array();
  		  $data['success'] = false;
  		  $data['type'] = 'error';
  		  $data['message'] = 'Nenhuma ação foi escolhida';
  		  $data['time'] = strftime('%A, %d de %B de %Y', strtotime('today'));
  		  print json_encode($data);
  		  exit;
  		} else {
  			return '';
  		}
  	}
  }

  function tcr8_function(){
  	global $mysqli;
  	$function_str = tcr8_type();
  	if( $function_str!="" && function_exists($function_str) ){
  		call_user_func($function_str, $mysqli,@bd_central);
  		return true;
  	} else {
  		return false;
  	}
  }

  function mb_str_pad( $input, $pad_length, $pad_string = ' ', $pad_type = STR_PAD_RIGHT)
  {
  		$input = mb_strlen($input) > $pad_length ? substr($input,0,$pad_length) : $input;

      $diff = strlen( $input ) - mb_strlen( $input );
      return str_pad( $input, $pad_length + $diff, $pad_string, $pad_type );
  }

  function recursive_array_search($needle,$haystack) {
      foreach($haystack as $key=>$value) {
          if($needle===$key) {
              return array($key);
          } else if (is_array($value) && $subkey = recursive_array_search($needle,$value)) {
              array_unshift($subkey, $key);
              return $subkey;
          }
      }
  }

  function array_get_by_array($arr,$fetch){
    foreach ($fetch as $key => $value) {
      $arr = $arr[$value];
    }
    return $arr;
  }

  function get_files_from_folder($dir,$ext='*'){
    $cdir = scandir($dir);
    $return = array();
    foreach ($cdir as $key => $value) {
       if (!in_array($value,array(".",".."))) {
          if (is_dir($dir . DIRECTORY_SEPARATOR . $value)) {
            //é Diretório, não faz nada;
             // $result[$value] = dirToArray($dir . DIRECTORY_SEPARATOR . $value);
          } else {
            $file_parts = pathinfo($value);
            $basename  = $file_parts['basename'];
            $filename  = $file_parts['filename'];
            $extension = $file_parts['extension'];
            $dirname   = $file_parts['dirname'];
            $fullpath = $dir."/".$value;
            if($ext == '*') $return[] = $fullpath;
            elseif ($ext == $extension) $return[] = $fullpath;
          }
       }
    }
    return $return;
  }

  function ftp_server_conn(){
    $ftp_server    = 'tcr8.com.br';
    $ftp_port      = '21';
    $ftp_user_name = 'tcr8';
    $ftp_user_pass = 'daniel1321';
    $ftp_pasv      = true;
    // $folder        = $data['proc_dir'] = cfg['SAT']['sevenbuilds']['proc_dir'];
    // /tcr8.com.br/public_html/tcr8_local/nfe/sats/
    // /tcr8.com.br/public_html/tcr8_local/nfe/24593673000160/files/SAT
    $conn_id = ftp_connect($ftp_server,$ftp_port,10);
    // $conn_id = ftp_connect($ftp_server);
    $login_result = ftp_login($conn_id, $ftp_user_name, $ftp_user_pass);
    ftp_set_option($conn_id, FTP_USEPASVADDRESS, false);
    ftp_pasv($conn_id, $ftp_pasv);

    return $conn_id;
  }

?>
