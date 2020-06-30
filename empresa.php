<?php

include 'Config.php';
include 'DB.php';
require './Clases/auth.php';

require __DIR__ . '/vendor/autoload.php';

$router = new \Slim\Slim();

$router->post('/all', 'verificarPermiso', function ()  use ($conn) {	

  	$ret = array(
	'status' => 'error',
	'code' => 404,
	'message' => 'Error al realizar la consulta'
	); 

	$query = "SELECT * FROM empresas";
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

$router->post('/empresa',  function ()  use ($conn) {	

	$id_empresa = $_POST['id_empresa'];

  	$ret = array(
	'status' => 'error',
	'code' => 404,
	'message' => 'Error al realizar la consulta'
	); 

	$query = "SELECT * FROM empresas WHERE id='".$id_empresa."'";
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

		$row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC);
		     

		$ret = array(
			'status' => 'succes',
			'code' => '200',
			'data' => $row
		);
	}
	else{
		$ret = array(
			'status' => 'succes',
			'code' => '204',
			'data' => 'Empresa inexistente'
		);
	}
	echo json_encode($ret);	   
});

$router->post('/addEmpresa', 'verificarPermiso', function ()  use ($conn) {	

	$nombre = $_POST['nombre'];
	$bd = $_POST['bd'];
	$logo_url = $_POST['logo_url'];
	$color_menu = $_POST['color_menu'];
	$color_background = $_POST['color_background'];

  	$ret = array(
	'status' => 'error',
	'code' => 404,
	'message' => 'Error al realizar la consulta'
	); 

	$query = "INSERT INTO empresas (nombre,baseDatos,logoUrl,colorMenu,colorBackground) VALUES  ('".$nombre."','".$bd."','".$logo_url."','".$color_menu."','".$color_background."')";
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
		'data' => $nombre
	);
	echo json_encode($ret);	  
});

$router->post('/updateEmpresa', 'verificarPermiso', function ()  use ($conn) {	

	$id = $_POST['id'];
	$nombre = $_POST['nombre'];
	$bd = $_POST['bd'];
	$logo_url = $_POST['logo_url'];
	$color_menu = $_POST['color_menu'];
	$background = $_POST['background'];

  	$ret = array(
	'status' => 'error',
	'code' => 404,
	'message' => 'Error al realizar la consulta'
	); 

	$query = "UPDATE empresas SET  nombre='".$nombre."',baseDatos='".$bd."',logoUrl='".$logo_url."',colorMenu='".$color_menu."',background='".$background."' WHERE  id='".$id."'";
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
		'data' => $nombre
	);
	echo json_encode($ret);	  
});




$router->run();