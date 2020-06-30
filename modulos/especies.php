<?php

include '../Config.php';
include '../DB.php';
require '../Clases/auth.php';

$router = new \Slim\Slim();

$router->post('/', 'verificarPermiso', function ()  use ($conn) {	

	$connEmpresaDB = obtenerConexionBaseDatosEmpresa($_POST['id_empresa'],$conn);
	$query = "SELECT ONCCAEspecieid, nombre FROM ONCCAEspecies";	//Desde la base de datos principal obtengo a donde sacar los datos de la emrpesa
	$result = sqlsrv_query($connEmpresaDB, $query);
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

$router->run();