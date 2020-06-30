<?php

include '../Config.php';
include '../DB.php';
require '../Clases/auth.php';

$router = new \Slim\Slim();

$router->post('/', 'verificarPermiso', function ()  use ($conn) {

	$id_cliente = $_POST['id_cliente'];	

	$connEmpresaDB = obtenerConexionBaseDatosEmpresa($_POST['id_empresa'],$conn);

	$query="SELECT OT_OrdenesTrabajo.codigo,fechaingreso,seguncliente,seguntecnico,OT_OrdenesTrabajo.estado,OT_EstadosOrdenes.nombre as nomesta,c.nroctacte as codclie, c.nombre as nomclie,
		v1.nombre as nomresp,v2.nombre as nomtecn,OT_Artefactos.nombre as nomarte,OT_Marcas.nombre as nommarc,OT_Modelos.nombre as nommode,case when coalesce(OT_EstadosOrdenes.final,0)=1 then OT_OrdenesTrabajo.fechaentrega else null end as Entregado
		From OT_OrdenesTrabajo
		inner join OT_Artefactos on  OT_Artefactos.artefactoid=OT_OrdenesTrabajo.artefactoid
		inner join OT_TipoOrdenes on OT_TipoOrdenes.tipoordenid=OT_OrdenesTrabajo.tipoordenid
		inner join OT_EstadosOrdenes on OT_EstadosOrdenes.estadoordenid=OT_OrdenesTrabajo.estadoordenid
		inner join clientes c on c.clienteid=OT_OrdenesTrabajo.clienteid
		inner join vendedores v1 on v1.vendedorid=OT_OrdenesTrabajo.responsableid
		left join vendedores v2 on v2.vendedorid=OT_OrdenesTrabajo.tecnicoid
		left join OT_Marcas on OT_Marcas.marcaid=OT_Artefactos.marcaid
		left join OT_Modelos on OT_Modelos.marcaid=OT_Artefactos.marcaid and OT_Modelos.modeloid=OT_Artefactos.modeloid
		where c.CLIENTEID='".$id_cliente."' order by fechaingreso desc";

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
			'data' => 'No se encontraron ordenes de Trabajo'
		);
	}
	echo json_encode($ret);

});

$router->run();