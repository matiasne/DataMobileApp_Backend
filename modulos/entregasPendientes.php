<?php

include '../Config.php';
include '../DB.php';
require '../Clases/auth.php';

$router = new \Slim\Slim();

$router->post('/', 'verificarPermiso', function ()  use ($conn) {

	$id_cliente = $_POST['id_cliente'];	

	$connEmpresaDB = obtenerConexionBaseDatosEmpresa($_POST['id_empresa'],$conn);

	$query="Select TIPOOO_D,LETRAA_D,SUCURS_D,NUMERO_D,fecemi_d,NROCTACTE,A.NOMBRE AS NOMCLI,subnum_d, codart_d, b.nombre as nomart,b.codpro,b.codbarra,
	codart_d as codart,PENFAC_d as pendiente,cantid_d as cantid,fecemi_c as fecemi 
	FROM DETFAC INNER JOIN CLIENTES A ON CODCLI_D=A.CLIENTEID 
	INNER JOIN ARTICULOSFAC b ON CODART_D=b.codigo 
	inner join preciosfac c on b.articulofacid=c.articulofacid 
	inner join afipiva d on d.afipivaid=b.afipivaid 
	inner join cabfac on tipooo_c=tipooo_d and letraa_c=letraa_d and sucurs_c=sucurs_d and numero_c=numero_d and fecemi_c=fecemi_d 
	WHERE estado_c<>'B' and PENFAC_D >0 and TIPOOO_D='RS' and b.pendiente<>0 and clienteid='".$id_cliente."'
	order by fecemi_c";

	$result = sqlsrv_query($connEmpresaDB, $query,array(), array("Scrollable"=>"buffered"));
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
		$ret = array(
			'status' => 'succes',
			'code' => '204',
			'data' => 'No se encontraron entregas pendientes'
		);
	}
	echo json_encode($ret);

});

$router->run();