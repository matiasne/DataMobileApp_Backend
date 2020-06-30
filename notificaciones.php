<?php

include 'Config.php';
include 'DB.php';
require './Clases/auth.php';
include './Clases/notificacion.php';

require __DIR__ . '/vendor/autoload.php';

$router = new \Slim\Slim();


$router->post('/enviarNoticia', 'verificarPermiso', function ()  use ($conn) {

		//Aca debe tomar el id como si fuera de la empresa y obtener los id de los usuarios. 
		$id_empresa = $_POST['id_empresa'];
		$id_remitente = $_POST['id_remitente'];
		$titulo = $_POST['titulo'];				
		$texto = $_POST['texto'];	
		
		$query = " SELECT DISTINCT id_usuario,mail FROM usuario_empresa_cliente INNER JOIN usuarios ON usuario_empresa_cliente.id_usuario = usuarios.id WHERE usuario_empresa_cliente.id_empresa = '".$id_empresa."'";

		$result = sqlsrv_query($conn, $query);
		if(!$result){
			if( ($errors = sqlsrv_errors() ) != null) {
	        	foreach($errors as $error) {
		            echo "SQLSTATE: ".$error[ 'SQLSTATE']."<br />";
		            echo "code: ".$error[ 'code']."<br />";
		            echo "message: ".$error[ 'message']."<br />";
	        	}
	        	die();
	    	}
		}

		$json = array();
		do {
		     while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) { //Parametrizar la linea	     	

		     	$id_destinatario = $row['id_usuario'];

		     	$notificacion = new Notificacion($conn);
		     	$notificacion->noticia($id_destinatario,$id_remitente,$titulo,$texto);

		     	
		    }
		} while ( sqlsrv_next_result($result) ); //Siguinte resultado	
		
	
		$ret = array(
			'status' => 'success',
			'code' => 200,
			'data' => 'notificacion enviada'
		); 	
		echo json_encode($ret);	
		exit();
	
	
});


/*
El id de la empresa lo trae debido a que se saca de /welcome/id_empresa
*/
$router->post('/enviarPedidoAsignacion', 'verificarPermiso', function ()  use ($conn) {

	//Aca debe tomar el id_destinatario como de la empresa y buscar todos los usuarios Admin de la misma
	
		$id_remitente = $_POST['id_remitente'];		
		$id_empresa = $_POST['id_empresa'];


		$query = "SELECT nombreCompleto FROM  usuarios  WHERE id = '".$id_remitente."'";

		$result = sqlsrv_query($conn, $query);
		if(!$result){
			if( ($errors = sqlsrv_errors() ) != null) {
	        	foreach($errors as $error) {
		            echo "SQLSTATE: ".$error[ 'SQLSTATE']."<br />";
		            echo "code: ".$error[ 'code']."<br />";
		            echo "message: ".$error[ 'message']."<br />";
	        	}
	        	die();
	    	}
		}
		$row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC);
		$nombre = trim($row['nombreCompleto']);


		
		//Se debe enviar a todos los integrantes de la empresa!!

		$query = "SELECT id_usuario,mail FROM usuario_empresa_cliente INNER JOIN usuarios ON usuario_empresa_cliente.id_usuario = usuarios.id WHERE usuario_empresa_cliente.id_empresa = '".$id_empresa."' AND isAdmin = '1'";

		$result = sqlsrv_query($conn, $query);
		if(!$result){
			if( ($errors = sqlsrv_errors() ) != null) {
	        	foreach($errors as $error) {
		            echo "SQLSTATE: ".$error[ 'SQLSTATE']."<br />";
		            echo "code: ".$error[ 'code']."<br />";
		            echo "message: ".$error[ 'message']."<br />";
	        	}
	        	die();
	    	}
		}


		$json = array();
		do {
		     while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) { //Parametrizar la linea

		     	

				 $id_destinatario = $row['id_usuario'];
				 
				

		     	$notificacion = new Notificacion($conn);
		     	$notificacion->nuevoUsuario($id_destinatario,$id_remitente,$nombre,$conn);

		     	
		    }
		} while ( sqlsrv_next_result($result) ); //Siguinte resultado
		
		
		
		
	
		$ret = array(
			'status' => 'success',
			'code' => 200,
			'data' => 'notificacion enviada'
		); 	
		echo json_encode($ret);	
		exit();	
	
});



$router->post('/enviarAEmpresa', 'verificarPermiso', function ()  use ($conn) {

	//Aca debe tomar el id_destinatario como de la empresa y buscar todos los usuarios Admin de la misma
	
		$mail = $_POST['mail'];		
		$id_empresa = $_POST['id_empresa'];
		$texto = $_POST['texto'];
		$tipo = $_POST['tipo'];
		$titulo = $_POST['titulo'];

		$query = "SELECT id_usuario,mail FROM usuario_empresa_cliente INNER JOIN usuarios ON usuario_empresa_cliente.id_usuario = usuarios.id WHERE usuario_empresa_cliente.id_empresa = '".$id_empresa."' AND isAdmin = 'True'";

		$result = sqlsrv_query($conn, $query);
		if(!$result){
			if( ($errors = sqlsrv_errors() ) != null) {
	        	foreach($errors as $error) {
		            echo "SQLSTATE: ".$error[ 'SQLSTATE']."<br />";
		            echo "code: ".$error[ 'code']."<br />";
		            echo "message: ".$error[ 'message']."<br />";
	        	}
	        	die();
	    	}
		}

		$json = array();
		do {
		     while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) { //Parametrizar la linea

		     	

		     	$id_destinatario = $row['id_usuario'];

		     	$notificacion = new Notificacion($conn);
		     	$notificacion->noticia($id_destinatario,$id_remitente,$titulo,$texto);

		     	
		    }
		} while ( sqlsrv_next_result($result) ); //Siguinte resultado
		
		
		
		
	
		$ret = array(
			'status' => 'success',
			'code' => 200,
			'data' => 'notificacion enviada'
		); 	
		echo json_encode($ret);	
		exit();	
	
});


$router->post('/obtener', /*'verificarPermiso',*/ function ()  use ($conn) {
	
	$id_destinatario = $_POST['id_destinatario'];
		
	$query = "SELECT id,id_remitente,titulo,texto,leida,tipo,leida FROM notificaciones WHERE id_destinatario = '".$id_destinatario."'";
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
		'status' => 'error',
		'code' => 200,
		'data' => $json
	); 
	echo json_encode($ret);
	exit();
	
	
	
});


$router->post('/borrar', 'verificarPermiso', function ()  use ($conn) {
	
	$id = $_POST['id'];

	$query = "DELETE FROM notificaciones WHERE id='".$id."'";
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
		'data' => $id
	);
	echo json_encode($ret);	
	exit();
	
	
	
});
/*
$router->post('/leida', 'verificarPermiso', function ()  use ($conn) {
	
	$id = $_POST['id'];
	$leida = $_POST['leida'];
		
	$query = "UPDATE notificaciones SET  leida='".$leida."'";
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

$router->post('/setLeida', 'verificarPermiso', function ()  use ($conn) {
	
	$id = $_POST['id'];
		
	$query = "DELETE FROM notificaciones WHERE id='".$id."'";
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
	exit();
	
	
	
});*/

$router->run();



