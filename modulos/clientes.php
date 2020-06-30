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

	$id_usuario = $_POST['id_usuario'];

	$connEmpresaDB = obtenerConexionBaseDatosEmpresa($_POST['id_empresa'],$conn);

	$query = "SELECT id_cliente FROM usuario_empresa_cliente WHERE usuario_empresa_cliente.id_usuario = '".$id_usuario."'";

	//$query = "SELECT * FROM clientes WHERE clienteid ='".$id_cliente."'";	//Desde la base de datos principal obtengo a donde sacar los datos de la emrpesa
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

	$resultadoFinal = array();
	$num_rows = sqlsrv_num_rows($result);
	if($num_rows > 0){
		
		do {
		     while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {

		     	if($row['id_cliente'] != null){		     	
		     	
			     	$query = "SELECT * FROM Clientes WHERE ClienteId ='".$row['id_cliente']."'";

			     	$resultCliente = sqlsrv_query($connEmpresaDB, $query,array(), array("Scrollable"=>"buffered"));
					if(!$resultCliente){
						if( ($errors = sqlsrv_errors() ) != null) {
				        	foreach( $errors as $error ) {
					            echo "SQLSTATE: ".$error[ 'SQLSTATE']."<br />";
					            echo "code: ".$error[ 'code']."<br />";
					            echo "message: ".$error[ 'message']."<br />";
				        	}
				        	die();
				    	}
					} 

					do {
					     while ($rowCliente = sqlsrv_fetch_array($resultCliente, SQLSRV_FETCH_ASSOC)) { //Parametrizar la linea	     	
							$resultadoFinal[] = $rowCliente;     	

					     	
					    }
					} while ( sqlsrv_next_result($resultCliente) ); //Siguinte resultado
				}

		     }
		} while ( sqlsrv_next_result($result) );

		$ret = array(
			'status' => 'succes',
			'code' => '200',
			'data' => $resultadoFinal
		);
	}
	else{
		$ret = array(
			'status' => 'succes',
			'code' => '204',
			'data' => 'No contiene clientes asignados'
		);
	}
	echo json_encode($ret);	    
});


$router->post('/datos', 'verificarPermiso', function ()  use ($conn) {	

	$id_cliente = $_POST['id_cliente'];

	$connEmpresaDB = obtenerConexionBaseDatosEmpresa($_POST['id_empresa'],$conn);

	$query = "SELECT * FROM Clientes WHERE ClienteId = '".$id_cliente."'";

	$result = sqlsrv_query($connEmpresaDB, $query,array(), array("Scrollable"=>"buffered"));
	if(!$result){
		if( ($errors = sqlsrv_errors()) != null) {
        	foreach( $errors as $error ) {
	            echo "SQLSTATE: ".$error[ 'SQLSTATE']."<br />";
	            echo "code: ".$error[ 'code']."<br />";
	            echo "message: ".$error[ 'message']."<br />";
        	}
        	die();
    	}
	}

	$resultadoFinal = array();
	$num_rows = sqlsrv_num_rows($result);
	if($num_rows > 0){
		
		do {
		     while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
		     	$resultadoFinal[] = $row;  	   	

		     }
		} while ( sqlsrv_next_result($result) );

		$ret = array(
			'status' => 'succes',
			'code' => '200',
			'data' => $resultadoFinal
		);
	}
	else{
		$ret = array(
			'status' => 'succes',
			'code' => '204',
			'data' => 'No contiene clientes asignados'
		);
	}
	echo json_encode($ret);	    
});

$router->post('/all', 'verificarPermiso', function ()  use ($conn) {	

	$connEmpresaDB = obtenerConexionBaseDatosEmpresa($_POST['id_empresa'],$conn);

	$query = "SELECT ClienteId,Nombre FROM Clientes WHERE categoriaId IN (0,1) AND baja <> 1 ORDER BY Nombre";

	$result = sqlsrv_query($connEmpresaDB, $query,array(), array("Scrollable"=>"buffered"));
	if(!$result){
		if( ($errors = sqlsrv_errors()) != null) {
        	foreach( $errors as $error ) {
	            echo "SQLSTATE: ".$error[ 'SQLSTATE']."<br />";
	            echo "code: ".$error[ 'code']."<br />";
	            echo "message: ".$error[ 'message']."<br />";
        	}
        	die();
    	}
	}

	$resultadoFinal = array();
	$num_rows = sqlsrv_num_rows($result);
	if($num_rows > 0){
		
		do {
		     while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
		     	$resultadoFinal[] = $row;  	   	

		     }
		} while ( sqlsrv_next_result($result) );

		$ret = array(
			'status' => 'succes',
			'code' => '200',
			'data' => $resultadoFinal
		);
	}
	else{
		$ret = array(
			'status' => 'succes',
			'code' => '204',
			'data' => 'No contiene clientes asignados'
		);
	}
	echo json_encode($ret);	    
});

/*
Este proceso carga en la base de relaciones de cada usuario la empresa a la que puede acceder y el cliente 
una vez que a un usuario se le han asignado empresa y cliente recien allí puede comenzar a utiliar el sistema!!!
*/

$router->post('/asignarCliente', 'verificarPermiso', function ()  use ($conn) {	

	$id_empresa = $_POST['id_empresa'];
	$id_usuario = $_POST['id_usuario'];
	$id_cliente = $_POST['id_cliente'];	

	$query = "INSERT INTO usuario_empresa_cliente (id_usuario,id_empresa,id_cliente) VALUES  ('".$id_usuario."','".$id_empresa."','".$id_cliente."')";

	$result = sqlsrv_query($conn, $query,array(), array("Scrollable"=>"buffered"));
	if(!$result){
		if( ($errors = sqlsrv_errors()) != null) {
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
		'data' => 'Asignado'
	);
	echo json_encode($ret);
	    
});

$router->post('/borrarCliente', 'verificarPermiso', function ()  use ($conn) {	

	$id_empresa = $_POST['id_empresa'];
	$id_usuario = $_POST['id_usuario'];
	$id_cliente = $_POST['id_cliente'];	

	$query = $query = "DELETE FROM usuario_empresa_cliente WHERE id_usuario='".$id_usuario."' AND id_cliente='".$id_cliente."' AND id_empresa='".$id_empresa."'";;

	$result = sqlsrv_query($conn, $query,array(), array("Scrollable"=>"buffered"));
	if(!$result){
		if( ($errors = sqlsrv_errors()) != null) {
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
		'data' => 'Borrado'
	);
	echo json_encode($ret);
	    
});

$router->run();