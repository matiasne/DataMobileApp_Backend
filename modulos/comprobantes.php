<?php

include '../Config.php';
include '../DB.php';
require '../Clases/auth.php';

$router = new \Slim\Slim();

$router->post('/', 'verificarPermiso', function ()  use ($conn) {

	$id_cliente = $_POST['id_cliente'];	

	$connEmpresaDB = obtenerConexionBaseDatosEmpresa($_POST['id_empresa'],$conn);

	$query="SELECT CASE cabfac.tipooo_c WHEN 'FC' THEN 'Factura' WHEN 'NC' THEN 'Nota de Crédito' WHEN 'ND' THEN 'Nota de Débito' 
			WHEN 'PD' THEN 'Pedido' WHEN 'RE' THEN 'Remito de Entrada' WHEN 'RS' THEN 'Remito de Salida'  WHEN 'PR' THEN 'Presupuesto' 
			WHEN 'XE' THEN 'Remito X Entrada' WHEN 'XS' THEN 'Remito X Salida' 
			 WHEN 'PS' THEN 'Mov. Int. Salida' WHEN 'PE' THEN 'Mov. Int. Entrada' ELSE 'Otros Comprobantes' END As COMPROBANTE,
			TIPOOO_C,LETRAA_C,SUCURS_C,NUMERO_C,FECEMI_C,
			CASE WHEN TIPOOO_C IN ('FC','NC','ND','PS','PE') THEN CASE WHEN FORMAA_C=2 THEN 'Cta. Cte.' ELSE 'Contado' end ELSE 'S/C' END CONDICION,
			CASE WHEN TIPOOO_C IN ('RS','RE','XE','XS') OR LETRAA_C<>'A' THEN null ELSE TOTALL_T END TGRAVADO,
			CASE WHEN TIPOOO_C IN ('RS','RE','XE','XS') OR LETRAA_C<>'A' THEN null ELSE IVAINS_T END TIVA,
			CASE WHEN TIPOOO_C IN ('RS','RE','XE','XS') THEN null ELSE TOTALL_T END TTOTAL,
			A.CODIGO,A.NOMBRE,CANTID_D,
			CASE WHEN TIPOOO_C IN ('RS','RE','XE','XS') THEN null else totall_d/cantid_d end as DPRECIO,
			CASE WHEN TIPOOO_C IN ('RS','RE','XE','XS') THEN null else totall_d END DTOTAL
			FROM CABFAC 
			INNER JOIN DETFAC ON TIPOOO_C=TIPOOO_D AND LETRAA_C=LETRAA_D AND SUCURS_C=SUCURS_D AND NUMERO_C=NUMERO_D
			INNER JOIN TOTFAC ON TIPOOO_C=TIPOOO_T AND LETRAA_C=LETRAA_T AND SUCURS_C=SUCURS_T AND NUMERO_C=NUMERO_T
			INNER JOIN CLIENTES C ON C.CLIENTEID=CODCLI_C
			INNER JOIN ARTICULOSFAC A ON ARTICULOID_D=ARTICULOFACID
			WHERE C.CLIENTEID='".$id_cliente."'
			ORDER BY FECEMI_D DESC";
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
			'data' => 'No se encontraron comprobantes'
		);
	}
	echo json_encode($ret);

});

$router->run();