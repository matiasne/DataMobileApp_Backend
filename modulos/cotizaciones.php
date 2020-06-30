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

	
	$query = "SELECT * FROM cotizaciones WHERE id_empresa ='".$id_empresa."' ORDER BY cotizaciones.fecha";	//Desde la base de datos principal obtengo a donde sacar los datos de la emrpesa
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
			'data' => 'Na hay cotizaciones'
		);
	}
	echo json_encode($ret);	   
  	

	   
});

$router->post('/ultimoDia', 'verificarPermiso', function ()  use ($conn) {	

	
	$id_empresa = $_POST['id_empresa'];

	
	$query = "SELECT     granos.nombre, cotizaciones.fecha, cotizaciones.valor
FROM         cotizaciones INNER JOIN
                      granos ON cotizaciones.id_empresa = granos.id_empresa AND cotizaciones.id_grano = granos.id
WHERE     (LTRIM(RTRIM(STR(YEAR(cotizaciones.fecha)))) + LTRIM(RTRIM(STR(MONTH(cotizaciones.fecha)))) + LTRIM(RTRIM(STR(DAY(cotizaciones.fecha)))) =
                          (SELECT     TOP (1) LTRIM(RTRIM(STR(YEAR(cotizaciones_1.fecha)))) + LTRIM(RTRIM(STR(MONTH(cotizaciones_1.fecha)))) 
                                                   + LTRIM(RTRIM(STR(DAY(cotizaciones_1.fecha)))) AS Expr1
                            FROM          cotizaciones AS cotizaciones_1 
                            ORDER BY fecha DESC))";	//Desde la base de datos principal obtengo a donde sacar los datos de la emrpesa
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
			'data' => 'Na hay cotizaciones'
		);
	}
	echo json_encode($ret);	   
  	

	   
});


$router->post('/add', 'verificarPermiso', function ()  use ($conn) {	

	$id_empresa = $_POST['id_empresa'];
	$id_grano = $_POST['id_grano'];
	$fecha = $_POST['fecha'];
	$valor = $_POST['valor'];  

	$query = "INSERT INTO cotizaciones (id_empresa,id_grano,fecha,valor) VALUES  ('".$id_empresa."','".$id_grano."','".$fecha."','".$valor."')";
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

$router->run();