<?php

include '../Config.php';
include '../DB.php';
require '../Clases/auth.php';

$router = new \Slim\Slim();


$router->post('/', 'verificarPermiso', function ()  use ($conn) {

	$id_empresa = $_POST['id_empresa'];

	$fechaDesde = $_POST['fechaDesde'];
	$fechaHasta = $_POST['fechaHasta'];
	$cliente_ctacte = $_POST['cliente_ctacte'];	


	$connEmpresaDB = obtenerConexionBaseDatosEmpresa($_POST['id_empresa'],$conn);

	$query=/*"SELECT DISTINCT  Clientes.NroCtaCte as  CODIGO,Clientes.Nombre as NOMBRE, Clientes.Calle+' '+rtrim(clientes.nro) as DOMICILIO, AfipResponsables.Codigo as TIPORESP, AfipResponsables.Nombre as DESCRI, AFIPDocumentoId  as TIPODOCU, Clientes.CUIT, Clientes.CODPOSTAL, Clientes.LOCALIDAD, Clientes.IngBrutosNro as INGBRUTO,0 as SALDOAN, 0 as  SALDOVDO, CtaCte.DH, CtaCte.Fecha as FECHACARGA,DESDETALLE = CASE WHEN LTRIM(RTRIM(CTACTE.DETALLE)) = ''  OR CTACTE.DETALLE IS NULL THEN TiposComprobantes.Nombre else ctacte.detalle end, Ctacte.NroAsiento as NROTRANSAC, Clientes.NroCtaCte as CLIENTE,0 as Vencido, COMPROBANTE = CASE CtaCte.Letra WHEN 'E' THEN ltrim(str(CtaCte.NRO)) WHEN 'I' THEN ltrim(str(CtaCte.NRO)) ELSE ltrim(coalesce(CtaCte.LETRA,'')) + ' ' + ltrim(STR(CtaCte.Prefijo)) + ' - ' + ltrim(STR(CtaCte.NRO)) END, SALDO2 = COALESCE  ((SELECT     SUM(CASE WHEN MC.DH = 1 THEN  MC.IMPORTE ELSE -MC.IMPORTE END) FROM CtaCte MC  WHERE Clientes.CLIENTEID = MC.CLIENTEID  AND MC.FECHA <   CONVERT(DATETIME, '".$fechaDesde."', 103)), 0), DEBE = COALESCE (CASE WHEN CtaCte.DH = 1 THEN CtaCte.Importe END, 0), CtaCte.FVto as FechaVto, HABER = COALESCE (CASE WHEN DH = 0 THEN CtaCte.Importe END, 0), SUMADEBE = COALESCE ((SELECT     SUM(MC.IMPORTE) FROM CtaCte MC WHERE Clientes.CLIENTEID = MC.CLIENTEID AND MC.DH = 1 AND ((MC.FECHA BETWEEN CONVERT(DATETIME, '".$fechaDesde."', 103) AND  CONVERT(DATETIME, '".$fechaHasta."', 103))  OR (MC.FECHA <  CONVERT(DATETIME, '".$fechaDesde."', 103) AND MC.FVTO > CONVERT(DATETIME, '".$fechaDesde."', 103)) or (MC.FECHA >= CONVERT(DATETIME,'".$fechaDesde."',103) and MC.FVTO < CONVERT(DATETIME,'".$fechaDesde."',103)))), 0), SUMAHABER = COALESCE ((SELECT SUM(MC.IMPORTE) FROM CtaCte MC WHERE Clientes.CLIENTEID = MC.CLIENTEID AND MC.DH = 0 AND ((MC.FECHA BETWEEN CONVERT(DATETIME, '".$fechaDesde."', 103) AND CONVERT(DATETIME, '".$fechaHasta."', 103))  OR (MC.FECHA <  CONVERT(DATETIME, '".$fechaDesde."', 103) AND MC.FVTO > CONVERT(DATETIME, '".$fechaDesde."', 103)) or (MC.FECHA >= CONVERT(DATETIME,'".$fechaDesde."',103) and MC.FVTO < CONVERT(DATETIME, '".$fechaDesde."',103)))), 0), SALDOVENCIDO = COALESCE ((SELECT SUM(CASE WHEN MC.DH = 1 THEN  MC.IMPORTE ELSE -MC.IMPORTE END) FROM CtaCte MC WHERE Clientes.CLIENTEID = MC.CLIENTEID AND  (MC.FECHA <=   CONVERT(DATETIME, '".$fechaHasta."', 103))),  0), SALDOACTUAL = COALESCE ((SELECT SUM(CASE WHEN MC.DH = 1 THEN  MC.IMPORTE ELSE -MC.IMPORTE END) FROM CtaCte MC  WHERE Clientes.CLIENTEID = MC.CLIENTEID AND ((MC.FECHA < CONVERT(DATETIME,'".$fechaDesde."', 103)) OR ((MC.FECHA BETWEEN CONVERT(DATETIME, '".$fechaDesde."', 103) AND CONVERT(DATETIME, '".$fechaHasta."', 103)) OR (MC.FECHA < CONVERT(DATETIME, '".$fechaDesde."', 103) AND MC.FVTO > CONVERT(DATETIME, '".$fechaDesde."', 103)) OR (MC.FECHA >= CONVERT(DATETIME, '".$fechaHasta."', 103) AND MC.FVTO < CONVERT(DATETIME, '".$fechaHasta."', 103))))), 0), SALDOAVENCER = (SELECT SUM(CASE WHEN MC.DH = 1 THEN  MC.IMPORTE ELSE -MC.IMPORTE END) FROM CtaCte MC  WHERE  Clientes.CLIENTEID = MC.CLIENTEID AND ((MC.FECHA < CONVERT(DATETIME,'".$fechaDesde."', 103)) OR ((MC.FECHA BETWEEN CONVERT(DATETIME, '".$fechaDesde."', 103) AND CONVERT(DATETIME, '".$fechaHasta."', 103)) OR (MC.FECHA < CONVERT(DATETIME, '".$fechaDesde."', 103) AND MC.FVTO > CONVERT(DATETIME, '".$fechaDesde."', 103)) OR  (MC.FECHA >= CONVERT(DATETIME, '".$fechaDesde."', 103) AND MC.FVTO < CONVERT(DATETIME, '".$fechaDesde."', 103))))) - (SELECT    SUM(CASE WHEN MC.DH = 1 THEN  MC.IMPORTE ELSE -MC.IMPORTE END) FROM CtaCte MC WHERE Clientes.CLIENTEID = MC.CLIENTEID  AND   (MC.FECHA <=   CONVERT(DATETIME, '".$fechaDesde."', 103))) FROM Clientes Clientes inner join  CtaCte CtaCte on Clientes.ClienteId = CtaCte.ClienteID left join  AfipResponsables AfipResponsables  on Clientes.AfipResponsableId = AfipResponsables.AfipResponsableId left join  TiposComprobantes TiposComprobantes  on CtaCte.TipoComprobanteId = TiposComprobantes.TipoComprobanteId WHERE Clientes.NroCtaCte = '".$cliente_ctacte."' AND ((CtaCte.FECHA BETWEEN CONVERT(DATETIME, '".$fechaDesde."', 103) AND CONVERT(DATETIME,'".$fechaHasta."', 103)) OR (CtaCte.FECHA <  CONVERT(DATETIME, '".$fechaDesde."', 103) AND (CtaCte.FVTO > CONVERT(DATETIME, '".$fechaDesde."', 103) )) or ((CtaCte.FECHA between  CONVERT(DATETIME,'".$fechaDesde."',103) and CONVERT(DATETIME,'".$fechaHasta."',103)) and CtaCte.FVTO < CONVERT(DATETIME, '".$fechaDesde."',103))) ORDER BY Clientes.NroCtaCte, CtaCte.FECHA, CtaCte.NROAsiento";*/

	"SELECT Clientes.NroCtaCte as  CODIGO,Clientes.Nombre as NOMBRE, Clientes.Calle+' '+rtrim(clientes.nro) as DOMICILIO, AfipResponsables.Codigo as TIPORESP,
       AfipResponsables.Nombre as DESCRI, AFIPDocumentoId  as TIPODOCU, Clientes.CUIT, Clientes.CODPOSTAL, Clientes.LOCALIDAD, Clientes.IngBrutosNro as INGBRUTO,
       CtaCte.DH, CtaCte.Fecha as FECHACARGA,DESDETALLE = Case  WHEN LEFT(ctacte.detalle, 15) <> 'NUESTRA ENTREGA' AND LEFT(ctacte.detalle, 10) <> 'SU ENTREGA' AND ctacte.detalle <> '' and ctacte.detalle is not null THEN CTACTE.DETALLE ELSE TIPOSCOMPROBANTES.NOMBRE END,
       Ctacte.NroAsiento as NROTRANSAC, Clientes.NroCtaCte as CLIENTE,0 as Vencido, 
       COMPROBANTE = CASE CtaCte.Letra WHEN 'E' THEN ltrim(str(CtaCte.NRO)) WHEN 'I' THEN ltrim(str(CtaCte.NRO)) ELSE ltrim(coalesce(CtaCte.LETRA,'')) + ' ' + ltrim(STR(CtaCte.Prefijo)) + ' - ' + ltrim(STR(CtaCte.NRO)) END,
       SALDO2 = COALESCE  ((SELECT SUM(CASE WHEN MC.DH = 1 THEN  MC.IMPORTE ELSE -MC.IMPORTE END) FROM CtaCte MC  WHERE Clientes.CLIENTEID = MC.CLIENTEID 
       AND MC.FECHA < CONVERT(DATETIME, '".$fechaDesde."', 103) and (MC.FVTO < CONVERT(DATETIME, '".$fechaDesde."', 103) OR MC.FVTO IS NULL)), 0), 
       DEBE = COALESCE (CASE WHEN CtaCte.DH = 1 THEN CtaCte.Importe END, 0), CtaCte.FVto as FechaVto, 
       HABER = COALESCE (CASE WHEN DH = 0 THEN CtaCte.Importe END, 0),CtaCte.TipoComprobanteId
FROM Clientes   
       inner join CtaCte CtaCte on Clientes.ClienteId = CtaCte.ClienteID 
       left join  AfipResponsables AfipResponsables  on Clientes.AfipResponsableId = AfipResponsables.AfipResponsableId 
       left join  TiposComprobantes TiposComprobantes  on CtaCte.TipoComprobanteId = TiposComprobantes.TipoComprobanteId 
WHERE Clientes.NroCtaCte = '".$cliente_ctacte."' AND 
       ((CtaCte.FECHA BETWEEN CONVERT(DATETIME, '".$fechaDesde."', 103) AND CONVERT(DATETIME,'".$fechaHasta."', 103)) OR 
        (CtaCte.FECHA <  CONVERT(DATETIME, '".$fechaDesde."', 103) AND (CtaCte.FVTO > CONVERT(DATETIME, '".$fechaDesde."', 103) )) OR 
       ((CtaCte.FECHA between  CONVERT(DATETIME,'".$fechaDesde."',103) and CONVERT(DATETIME,'".$fechaHasta."',103)) and 
         CtaCte.FVTO < CONVERT(DATETIME, '".$fechaDesde."',103))) 
ORDER BY Clientes.NroCtaCte, CtaCte.FECHA, CtaCte.NROAsiento";

	



	$result = sqlsrv_query($connEmpresaDB, $query);
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

$router->run(); 