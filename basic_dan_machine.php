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


?>
