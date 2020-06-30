<?php

require './Clases/auth.php';

include 'Config.php';
include 'DB.php';

require __DIR__ . '/vendor/autoload.php';

$router = new \Slim\Slim();

$router->post('/tokenLogin',  function ()  use ($conn) {		
	
	$mail = $_POST['mail'];
	$contrasena = $_POST['contrasena'];

	$query = "SELECT id,mail,isSuper,isAdmin FROM usuarios WHERE mail = '".$mail."' AND contrasena = '".$contrasena."'";
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
	$res = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC);

	if(!$res){ //Se decide no informar si el mail o la contraseña es incorrecta y hacer algo genérico
		$ret = array(
			'status' => 'error',
			'code' => 204,
			'data' => 'Incorrecto'
		); 
		echo json_encode($ret);
		exit();
	}
	else{

		$token = Auth::SignIn([
	        'id' => $res['id'],
	        'name' => $res['mail'],
	        'isSuper' => $res['isSuper'],
	        'isAdmin' => $res['isAdmin']
	    ]);
				

		$ret = array(
			'status' => 'succes',
			'code' => '200',
			'data' => $token
		);

		echo json_encode($ret);
		exit();
	}	
	
});

$router->post('/tokenInfo',  function ()  use ($conn) {

	$token = $_POST['token'];

		
	try {
			$res = Auth::GetData($token);
			$ret = array(
				'status' => 'succes',
				'code' => '200',
				'data' => $res
			);
		}	
	catch(Exception $e){
			$ret = array(
				'status' => 'succes',
				'code' => '204',
				'data' => 'token no valido'
			);
	}	
		

	

	echo json_encode($ret);
	exit();
});



$router->post('/registrar', function ()  use ($conn) {

	$nombre = $_POST['nombre'];
	$mail = $_POST['mail'];
	$contrasena = $_POST['contrasena'];
	$confirmacionContrasena = $_POST['confirmacionContrasena'];
	$id_empresa = $_POST['id_empresa'];

	//mail ya existe?	
	$query = "SELECT * FROM usuarios WHERE mail='".$mail."'";
	$result = sqlsrv_query($conn, $query);

	$r = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC);

	$row_count = 0;
	if($r != "")
		$row_count = count($r);	

	if ($row_count > 0){
		
		$ret = array(
			'status' => 'error',
			'code' => 204,
			'data' => 'Usuario ya existe'
		); 
		echo json_encode($ret);
		exit();
	}
	else{
		//Carga el nuevo usuario
		$query = "INSERT INTO usuarios (nombreCompleto,mail,contrasena,isAdmin,isSuper) OUTPUT Inserted.id VALUES ('".$nombre."','".$mail."','".$contrasena."','0','0')";
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

	}	
	
	$query = "SELECT id,mail,nombreCompleto,isAdmin,isSuper,telefono,direccion,id_cliente,id_empresa,token FROM usuarios WHERE mail = '".$mail."'";
	$result = sqlsrv_query($conn, $query);
	$res = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC);

	
	
	$token = Auth::SignIn([
		'id' => $res['id'],
		'name' => $res['mail'],
		'isSuper' => $res['isSuper'],
		'isAdmin' => $res['isAdmin']
	]);
			

	$ret = array(
		'status' => 'succes',
		'code' => '200',
		'data' => $token
	);

	echo json_encode($ret);
	exit();
});

/*Este método se utiliza por si se loguean a travéz de gmail o facebook para
obtenet los datos de la base de datos local
*/
$router->post('/propiedades', 'verificarPermiso', function ()  use ($conn) {

	$mail = $_POST['mail'];  	

	$query = "SELECT id,mail,nombreCompleto,isAdmin,isSuper,telefono,direccion,id_cliente,id_empresa,token FROM usuarios WHERE mail='".$mail."'";
	$result = sqlsrv_query($conn, $query, array(), array("Scrollable"=>"buffered"));	
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

	if ($num_rows > 0){

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

		//Si el usuario no existe entonces lo generamos

		$query = "INSERT INTO usuarios (mail,isAdmin,isSuper) OUTPUT Inserted.id VALUES ('".$mail."','False','False')";
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

		$row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC);

		$ret = array(
			'status' => 'succes',
			'code' => '200',
			'data' => $row['id']
		);
	}
	echo json_encode($ret);	   
});

$router->post('/all', /*'verificarPermiso',*/ function ()  use ($conn) {	

  	$ret = array(
	'status' => 'error',
	'code' => 404,
	'message' => 'Error al realizar la consulta'
	); 

	$idEmpresa =  $_POST['id_empresa'];

	$query = "SELECT id,mail,nombreCompleto,isAdmin,isSuper,telefono,direccion,token FROM usuarios INNER JOIN usuario_empresa_cliente ON usuarios.id = usuario_empresa_cliente.id_usuario WHERE usuario_empresa_cliente.id_empresa='".$idEmpresa."' order by usuarios.mail;";
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

$router->post('/usuario', 'verificarPermiso', function ()  use ($conn) {	

	$id_usuario = $_POST['id_usuario'];

  	$ret = array(
	'status' => 'error',
	'code' => 404,
	'message' => 'Error al realizar la consulta'
	); 

	$query = "SELECT id,mail,nombreCompleto,isAdmin,isSuper,telefono,direccion,id_cliente,id_empresa,token FROM usuarios WHERE id='".$id_usuario."'";
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
	     

	$ret = array(
		'status' => 'succes',
		'code' => '200',
		'data' => $row
	);
	echo json_encode($ret);	   
});



$router->post('/borrar', 'verificarPermiso', function ()  use ($conn) {
	
	$id_usuario = $_POST['id_usuario'];

  	$ret = array(
		'status' => 'error',
		'code' => 404,
		'message' => 'Error al realizar la consulta'
	); 

	$query = "DELETE FROM usuarios WHERE id='".$id_usuario."'";
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
		'data' => $id_usuario
	);
	echo json_encode($ret);	   
});


$router->post('/update', 'verificarPermiso', function ()  use ($conn) {	

	$mail = $_POST['mail'];
	$id_empresa = $_POST['id_empresa'];
	$nombreCompleto = $_POST['nombreCompleto'];
	$telefono = $_POST['telefono'];
	$direccion = $_POST['direccion'];
	$id_cliente = $_POST['id_cliente'];

  	$ret = array(
		'status' => 'error',
		'code' => 404,
		'message' => 'Error al realizar la consulta'
	); 

	$query = "UPDATE usuarios SET  mail='".$mail."',id_empresa='".$id_empresa."',nombreCompleto='".$nombreCompleto."',telefono='".$telefono."',direccion='".$direccion."',id_cliente='".$id_cliente."'";
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
		'data' => $mail
	);
	echo json_encode($ret);	  
});


$router->run();