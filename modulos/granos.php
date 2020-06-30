 <?php
/*
	Para utilizar esta API de clientes siempre es necesario recibir el id_cliente, para relacionar al 
	usuario logueado con un cliente dentro de la base de datos del sistema general.
*/

include '../Config.php';
include '../DB.php';
require '../Clases/auth.php';

$router = new \Slim\Slim();

$router->post('/', 'verificarPermiso', function ()  use ($conn) {	

	
	$id_empresa = $_POST['id_empresa'];

	
	$query = "SELECT * FROM granos WHERE id_empresa ='".$id_empresa."'";	//Desde la base de datos principal obtengo a donde sacar los datos de la emrpesa
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
			'code' => '204',
			'data' => 'Na hay granos'
		);
	}
	echo json_encode($ret);	   
  	

	   
});


$router->post('/add', 'verificarPermiso', function ()  use ($conn) {	

	$id_empresa = $_POST['id_empresa'];
	$nombre = $_POST['nombre']; 

	$query = "INSERT INTO granos (id_empresa,nombre) VALUES  ('".$id_empresa."','".$nombre."')";
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

$router->post('/remove', 'verificarPermiso', function ()  use ($conn) {
	
	$id = $_POST['id'];

  	$ret = array(
		'status' => 'error',
		'code' => 404,
		'message' => 'Error al realizar la consulta'
	); 

	$query = "DELETE FROM granos WHERE id='".$id."'";
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
});

$router->run();