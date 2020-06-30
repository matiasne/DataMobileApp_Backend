<?php

include 'Config.php';
include 'DB.php';
require './Clases/auth.php';

require __DIR__ . '/vendor/autoload.php';

$router = new \Slim\Slim();

$router->post('/', 'verificarPermiso', function ()  use ($conn) {	

  	$ret = array(
	'status' => 'error',
	'code' => 404,
	'message' => 'Error al realizar la consulta'
	); 

	$query = "SELECT * FROM modulos";
	$result = sqlsrv_query($conn, $query);
	if(!$result){
		if( ($errors = sqlsrv_errors() ) != null) {
        	foreach( $errors as $error ) {
	            echo "SQLSTATE: ".$error[ 'SQLSTATE']."<br />";
	            echo "code: ".$error[ 'code']."<br />";
	            echo "message: ".$error[ 'message']."<br />";
        	}
        	die();
    	}
	}

	$json = array();
	do {
	     while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
	     	$json[] = $row;
	     }
	} while ( sqlsrv_next_result($result) );

	$ret = array(
		'status' => 'succes',
		'code' => '200',
		'data' => $json
	);
	echo json_encode($ret);	   
});

$router->post('/byempresa', 'verificarPermiso', function ()  use ($conn) {	

	$id_empresa = $_POST['id_empresa'];
  	$ret = array(
	'status' => 'error',
	'code' => 404,
	'message' => 'Error al realizar la consulta'
	); 

	$query = "SELECT * FROM modulos WHERE id_empresa='".$id_empresa."'";
	$result = sqlsrv_query($conn, $query,array(), array("Scrollable"=>"buffered"));
	if(!$result){
		if( ($errors = sqlsrv_errors() ) != null) {
        	foreach( $errors as $error ) {
	            echo "SQLSTATE: ".$error[ 'SQLSTATE']."<br />";
	            echo "code: ".$error[ 'code']."<br />";
	            echo "message: ".$error[ 'message']."<br />";
        	}
        	die();
    	}
	}
	$num_rows = sqlsrv_num_rows($result);
	if($num_rows > 0){
		$json = array();
		do {
		     while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
		     	$json[] = $row;
		     }
		} while ( sqlsrv_next_result($result) );

		$ret = array(
			'status' => 'succes',
			'code' => '200',
			'data' => $json
		);
	}
	else{
		$ret = array(
			'status' => 'succes',
			'code' => '200',
			'data' => null
		);
	}
	echo json_encode($ret);	   
});

$router->post('/byusuario', 'verificarPermiso', function ()  use ($conn) {	

	$id_user = $_POST['id_user'];

  	$ret = array(
	'status' => 'error',
	'code' => 404,
	'message' => 'Error al realizar la consulta'
	); 

	$query = "SELECT * FROM clientes WHERE id='".$id_user."'";
	$result = sqlsrv_query($conn, $query);
	$row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC);

	if($row['isSuper'] == "1"){
		$query = "SELECT * FROM modulos";
		$result = sqlsrv_query($conn, $query);
	}
	else{
		$query = "SELECT * FROM modulos WHERE id_empresa='".$row['id_empresa']."' AND vistaAdmin ='".$row['isAdmin']."'";
		$result = sqlsrv_query($conn, $query);
	}

	if(!$result){
		if( ($errors = sqlsrv_errors() ) != null) {
        	foreach( $errors as $error ) {
	            echo "SQLSTATE: ".$error[ 'SQLSTATE']."<br />";
	            echo "code: ".$error[ 'code']."<br />";
	            echo "message: ".$error[ 'message']."<br />";
        	}
        	die();
    	}
	}

	$json = array();
	do {
	     while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
	     	$json[] = $row;
	     }
	} while ( sqlsrv_next_result($result) );

	$ret = array(
		'status' => 'succes',
		'code' => '200',
		'data' => $json
	);
	echo json_encode($ret);	    
});



$router->post('/add', 'verificarPermiso', function ()  use ($conn) {
	
	$id_empresa = $_POST['id_empresa'];
	$nombre = $_POST['nombre'];
	$url = $_POST['url'];
	$vistaAdmin = $_POST['vistaAdmin'];
	$vistaCliente = $_POST['vistaCliente'];

  	$ret = array(
	'status' => 'error',
	'code' => 404,
	'message' => 'Error al realizar la consulta'
	); 

	$query = "INSERT INTO modulos (id_empresa, nombre,url, vistaAdmin, vistaCliente) VALUES  ('".$id_empresa."','".$nombre."','".$url."','".$vistaAdmin."','".$vistaCliente."')";
	$result = sqlsrv_query($conn, $query);	
	if(!$result){
		if( ($errors = sqlsrv_errors() ) != null) {
        	foreach( $errors as $error ) {
	            echo "SQLSTATE: ".$error[ 'SQLSTATE']."<br />";
	            echo "code: ".$error[ 'code']."<br />";
	            echo "message: ".$error[ 'message']."<br />";
        	}
        	die();
    	}
	}

	$ret = array(
		'status' => 'succes',
		'code' => '200',
		'data' => $id_empresa
	);
	echo json_encode($ret);	  
});

$router->post('/borrar', 'verificarPermiso', function ()  use ($conn) {
	
	$id_modulo = $_POST['id_modulo'];

  	$ret = array(
	'status' => 'error',
	'code' => 404,
	'message' => 'Error al realizar la consulta'
	); 

	$query = "DELETE FROM modulos WHERE id='".$id_modulo."'";
	$result = sqlsrv_query($conn, $query);	
	if(!$result){
		if( ($errors = sqlsrv_errors() ) != null) {
        	foreach( $errors as $error ) {
	            echo "SQLSTATE: ".$error[ 'SQLSTATE']."<br />";
	            echo "code: ".$error[ 'code']."<br />";
	            echo "message: ".$error[ 'message']."<br />";
        	}
        	die();
    	}
	}

	$ret = array(
		'status' => 'succes',
		'code' => '200',
		'data' => $id_modulo
	);
	echo json_encode($ret);	   
});



$router->run();