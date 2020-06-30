<?php

$serverName = "USUARIO-F6CL7K1\SQLEXPRESS";
$uid = "bbvSoftware";
$pwd = "bbv*_*123";
$connectionInfo = array( "UID"=>$uid,
"PWD"=>$pwd,
"Database"=>"DataMovilApp",
"CharacterSet" => "UTF-8");
 
$conn = sqlsrv_connect( $serverName, $connectionInfo);
if( $conn === false )
{
	echo "No es posible conectarse al servidor.</br>";
	die( print_r( sqlsrv_errors(), true));
}


/*
Parametros: id de la empresa, conexión a la base de datos de DataMobilApp donde están los datos en común
Retorno: conexión a la base de dato particular de la empresa.
*/
function obtenerConexionBaseDatosEmpresa($id,$conn){ 
//La variable $conn debe pasarse porque siempre se llama est función desde dentro de una ruta de Slim

	$id_empresa = $id;
	
	$query = "SELECT * FROM empresas WHERE id='".$id_empresa."'";	//Desde la base de datos principal obtengo a donde sacar los datos de la emrpesa
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

	$row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC);


	$serverName = "USUARIO-F6CL7K1\SQLEXPRESS";
	$uid = "bbvSoftware";
	$pwd = "bbv*_*123";
	$connectionInfo = array( "UID"=>$uid,
	"PWD"=>$pwd,
	"Database"=>$row['baseDatos'],
	"CharacterSet" => "UTF-8");

	
	 
	$connBDEmpresa = sqlsrv_connect( $serverName, $connectionInfo);
	if( $connBDEmpresa === false )
	{
		echo "No es posible conectarse al servidor.</br>";
		die( print_r( sqlsrv_errors(), true));
	}

	return $connBDEmpresa;
}

function obtenerNombreBDEmpresa($id_empresa,$conn){

	$query = "SELECT * FROM empresas WHERE id='".$id_empresa."'";	
	
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

	$row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC);

	return $row['baseDatos'];
}