<?php

include '../Config.php';
include '../DB.php';
require '../Clases/auth.php';

$router = new \Slim\Slim();

$router->post('/', 'verificarPermiso', function ()  use ($conn) {

	$id_cliente = $_POST['id_cliente'];	

	$connEmpresaDB = obtenerConexionBaseDatosEmpresa($_POST['id_empresa'],$conn);

	$query="select COALESCE(d.nombre,'VARIOS') as nombre_sec, COALESCE(g.nombre,'VARIOS') as nombre_lin, COALESCE(h.nombre,'VARIOS')  as nombre_rub, COALESCE(ma.nombre,'VARIOS') as nommar, COALESCE(mo.nombre,'VARIOS') nommod,e.nombre as nombre_lis,
f.nombre as nombre_sub,b.codigo as codigo_art,codbarra,codpro,b.nombre as nombre_art,
round(((preunitario * ((i.ivains / 100) + 1)) + coalesce(impint,0)),2) as prevent,i.nombre as nombre_iva
from preciosfac a 
inner join articulosfac b on a.articulofacid=b.articulofacid 
inner join clientes c on c.listaprecioid=a.listaid and c.sublistaprecioid=a.sublistaid
left outer join listas e on a.listaid= e.listaid 
left outer join seccion d on b.seccionid=d.seccionid 
left outer join sublistas f on a.sublistaid= f.sublistaid 
left outer join afipiva i on b.afipivaid=i.afipivaid 
left outer join lineaart g on b.lineaartid=g.lineaartid  
left outer join rubroart h on b.rubroartid=h.rubroartid 
left outer join OT_Marcas ma on b.marcaid=ma.marcaid 
left outer join OT_Modelos mo on b.modeloid=mo.modeloid 
where b.estado<>'B' and b.codigo>=1 and b.codigo<=999000000 and c.clienteid='".$id_cliente."'
order by d.nombre,g.nombre, h.nombre,ma.nombre,mo.nombre,b.nombre";

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
			'data' => 'No se encontraron items'
		);
	}
	echo json_encode($ret);

});

$router->run();