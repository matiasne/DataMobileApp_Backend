<?php

include '../Config.php';
include '../DB.php';
require '../Clases/auth.php';

$router = new \Slim\Slim();


/*
Que consulta? donde?
Esta función retorna un saldo
*/
function SQLconsultarSaldoAnterior($connEmpresaDB,$id_cliente,$item_id_cereal,$item_str_cosecha,$fechaDesde){

	
	
 	$query ="SELECT SUM(coalesce(DEBE,0)-coalesce(HABER,0)) AS SALDO FROM  CtaCteProductores LEFT OUTER JOIN CartasPorte ON CtaCteProductores.IdTablaOrigen = CartasPorte.CartaPorteId AND CtaCteProductores.TipoMovimientoId = 1 AND CtaCteProductores.NroSec IS NULL LEFT OUTER JOIN  Boletos ON CtaCteProductores.IdTablaOrigen = Boletos.BoletoId AND CtaCteProductores.TipoMovimientoId IN (105, 106) AND  CtaCteProductores.NroSec IS NULL LEFT OUTER JOIN  RT ON CtaCteProductores.IdTablaOrigen = RT.RTId AND CtaCteProductores.TipoMovimientoId = 3  LEFT OUTER JOIN CERTIFICADOSDEPOSITO CD ON CtaCteProductores.IdTablaOrigen = CD.CertificadoDepositoId and CtaCteProductores.TipoMovimientoId = 2  WHERE (CtaCteProductores.Clienteid = '".$id_cliente."') AND ((CtaCteProductores.Debe IS NOT NULL) OR  (CtaCteProductores.Haber IS NOT NULL)) AND ((CartasPorte.ONCCAEspecieId = '".$item_id_cereal."') OR (Boletos.OnccaEspecieId = '".$item_id_cereal."') OR (RT.OnccaEspecieId = '".$item_id_cereal."') or (CD.OnccaEspecieId = '".$item_id_cereal."')) and CtaCteProductores.Fecha < '".$fechaDesde."' and  ((CartasPorte.cosecha = '".$item_str_cosecha."' ) OR (Boletos.cosecha = '".$item_str_cosecha."' ) OR (RT.cosecha = '".$item_str_cosecha."') OR (CD.cosecha = '".$item_str_cosecha."'))";

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

	$row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC);

	if($row['SALDO'] == NULL)
		$row['SALDO'] = 0;



	return $row['SALDO'];
	
}

$router->post('/', 'verificarPermiso', function ()  use ($conn) {

	$id_empresa = $_POST['id_empresa'];

	$fechaDesde = $_POST['fechaDesde'];
	$fechaHasta = $_POST['fechaHasta'];
	$id_cliente = $_POST['id_cliente'];	
	$id_cereal = intval($_POST['id_cereal']);
	$str_cosecha = $_POST['str_cosecha'];




	$connEmpresaDB = obtenerConexionBaseDatosEmpresa($_POST['id_empresa'],$conn);

	$filtroCadenaPorIDCereal = "";
	if($id_cereal != 0){ //Si el id es 0 significa todos
		$filtroCadenaPorIDCereal = "AND onccaespecies.onccaespecieid = '".$id_cereal."'";		
	}

	$filtroCadenaPorIDCosecha_cporte = "";	
	$filtroCadenaPorIDCosecha_boletos="";
	$filtroCadenaPorIDCosecha_rt = "";
	$filtroCadenaPorIDCosecha_cd = "";
	if($str_cosecha != ""){
		$filtroCadenaPorIDCosecha_cporte ="AND cartasporte.cosecha = '".$str_cosecha."'";
		$filtroCadenaPorIDCosecha_boletos="AND Boletos.Cosecha = '".$str_cosecha."'";
		$filtroCadenaPorIDCosecha_rt = "AND rt.cosecha = '".$str_cosecha."'";
		$filtroCadenaPorIDCosecha_cd = "AND CD.cosecha ='".$str_cosecha."'";	
	}
	

	$query = "SELECT DISTINCT onccaespecies.*,cosecha FROM onccaespecies INNER JOIN cartasporte ON onccaespecies.onccaespecieid = cartasporte.onccaespecieid WHERE productorid ='".$id_cliente."' 
		".$filtroCadenaPorIDCereal." ".$filtroCadenaPorIDCosecha_cporte."
		union
		SELECT DISTINCT onccaespecies.*,cosecha FROM onccaespecies INNER JOIN RT
		ON onccaespecies.onccaespecieid = RT.onccaespecieid WHERE (DepositanteId ='".$id_cliente."' OR ReceptorId ='".$id_cliente."') 
		".$filtroCadenaPorIDCereal." ".$filtroCadenaPorIDCosecha_rt."
		union
		SELECT DISTINCT onccaespecies.*,cosecha FROM onccaespecies INNER JOIN CertificadosDeposito CD
		ON onccaespecies.onccaespecieid = CD.onccaespecieid WHERE DepositanteId = '".$id_cliente."'
		".$filtroCadenaPorIDCereal." ".$filtroCadenaPorIDCosecha_cd."
		union
		Select distinct onccaespecies.*,cosecha from onccaespecies inner join Boletos CD
		on onccaespecies.onccaespecieid = CD.onccaespecieid where ProductorId = '".$id_cliente."' 
		".$filtroCadenaPorIDCereal." ".$filtroCadenaPorIDCosecha_cd." 
		order by onccaespecies.onccaespecieid,cosecha";	



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

	$jsonCosechas = array();
	$jsonDetalles = array();
	//Por cada una de las especies/cosecha en determinada cosecha debo obtener los datos (cartaporte, etc)
	
	$jsonTotal = array();
	do {  //Por cada cosecha

	    while ($rowc = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) { 
	     	
	     	$jsonCosechas[] = $rowc;

	     	$item_id_cereal = $rowc['ONCCAEspecieId']; 
	     	$item_str_cosecha = $rowc['cosecha'];


	     	

	     	//Debo sacar el saldo anterior de cada una de las consultas que relice.
	     	$saldoAnterior = SQLconsultarSaldoAnterior($connEmpresaDB,$id_cliente,$item_id_cereal,$item_str_cosecha,$fechaDesde);

	     	$query = "SELECT Clientes.NroCtaCte, Clientes.Nombre AS Cliente, CartasPorte.ONCCAEspecieId, ONCCAEspecies.Nombre AS Cereal, CartasPorte.Cosecha AS Cosecha,".$saldoAnterior." AS SaldoAnt, str(CtaCteProductores.TipoMovimientoId)+str(CtaCteProductores.Nro)+ str(COALESCE (CtaCteProductores.NroSec, 0)) AS K,TipoMovEntradas.Nombre AS Tipo, REPLICATE('0', 4 - LEN(LTRIM(STR(CtaCteProductores.Prefijo)))) + LTRIM(STR(CtaCteProductores.Prefijo))+'-'+REPLICATE('0',8 - LEN(LTRIM(STR(CtaCteProductores.Nro)))) + LTRIM(STR(CtaCteProductores.Nro))+ Case when debe = 0 then ' P/A' else '' end AS Nro, CtaCteProductores.Fecha, 'C1116A '+REPLICATE('0', 4 - LEN(LTRIM(STR(CD.Prefijo)))) + LTRIM(STR(CD.Prefijo))+'-'+REPLICATE('0',8 - LEN(LTRIM(STR(CD.Nro)))) + LTRIM(STR(CD.Nro)) AS NroCert, CtaCteProductores.Debe , CtaCteProductores.Haber,0 AS Saldo, CtaCteProductores.Neto, CtaCteProductores.Mermas, CtaCteProductores.Pizarra, CtaCteProductores.Flete, CtaCteProductores.GsAdmin, CtaCteProductores.Comision, CtaCteProductores.Precio, CtaCteProductores.IVACBU, CtaCteProductores.Pago,0 AS NroCtaCteRecibe, '' AS ClienteRecibe ,idtablaorigen FROM   CtaCteProductores INNER JOIN Clientes ON CtaCteProductores.Clienteid = Clientes.ClienteId INNER JOIN TipoMovEntradas ON CtaCteProductores.TipoMovimientoId = TipoMovEntradas.TipoMovEntradaId INNER JOIN CartasPorte ON CtaCteProductores.IdTablaOrigen = CartasPorte.CartaPorteId INNER JOIN ONCCAEspecies ON CartasPorte.ONCCAEspecieId = ONCCAEspecies.ONCCAEspecieId left join CertificadosDeposito cd on CartasPorte.CertificadoDepositoId = CD.CertificadoDepositoId
	     		Where (CtaCteProductores.TipoMovimientoSecId Is Null) And (CtaCteProductores.TipoMovimientoId = 1)  and CtaCteProductores.Fecha BETWEEN '".$fechaDesde."' and '".$fechaHasta."' and CtaCteProductores.ClienteID = '".$id_cliente."' And CartasPorte.onccaespecieid = '".$item_id_cereal."' AND cartasporte.cosecha = '".$item_str_cosecha."'";

	     		


	     	$query.=" Union All
	     		SELECT Clientes.NroCtaCte, Clientes.Nombre AS Cliente, Boletos.OnccaEspecieId, ONCCAEspecies.Nombre AS Cereal, Boletos.Cosecha AS Cosecha,".$saldoAnterior." AS Saldo, str(CtaCteProductores.TipoMovimientoId)+str(CtaCteProductores.Nro)+ str(COALESCE (CtaCteProductores.NroSec, 0)) AS K,TipoMovEntradas.Nombre AS Tipo, REPLICATE('0', 4 - LEN(LTRIM(STR(CtaCteProductores.Prefijo)))) + LTRIM(STR(CtaCteProductores.Prefijo))+'-'+REPLICATE('0',8 - LEN(LTRIM(STR(CtaCteProductores.Nro)))) + LTRIM(STR(CtaCteProductores.Nro)) AS Nro, CtaCteProductores.Fecha, nrocert = Case when cd.tipomovimientoid = 5 then 'C1116B ' when cd.tipomovimientoid = 6 then 'C1116C ' else 'FACTURA ' end +REPLICATE('0', 4 - LEN(LTRIM(STR(CD.Prefijo)))) + LTRIM(STR(CD.Prefijo))+'-'+REPLICATE('0',8 - LEN(LTRIM(STR(CD.Nro)))) + LTRIM(STR(CD.Nro)) ,CtaCteProductores.Debe, CtaCteProductores.Haber,0 AS Saldo, CtaCteProductores.Neto,CtaCteProductores.Mermas, CtaCteProductores.Pizarra, CtaCteProductores.Flete, CtaCteProductores.GsAdmin, CtaCteProductores.Comision, CtaCteProductores.Precio, CtaCteProductores.IVACBU, CtaCteProductores.Pago, 0 AS NroCtaCteRecibe, '' AS ClienteRecibe ,idtablaorigen FROM   CtaCteProductores INNER JOIN Clientes ON CtaCteProductores.Clienteid = Clientes.ClienteId INNER JOIN  TipoMovEntradas ON CtaCteProductores.TipoMovimientoId = TipoMovEntradas.TipoMovEntradaId INNER JOIN  Boletos ON CtaCteProductores.IdTablaOrigen = Boletos.BoletoId INNER JOIN ONCCAEspecies ON Boletos.OnccaEspecieId = ONCCAEspecies.ONCCAEspecieId  left join LiquidacionesCereal cd on Boletos.BoletoId = CD.BoletoId and cd.mandanteid = boletos.productorid  WHERE (CtaCteProductores.TipoMovimientoSecId IS NULL) AND (CtaCteProductores.TipoMovimientoId IN (105, 106))  and CtaCteProductores.Fecha BETWEEN '".$fechaDesde."' and '".$fechaHasta."' and CtaCteProductores.ClienteID = '".$id_cliente."' And Boletos.onccaespecieid = '".$item_id_cereal."' AND Boletos.cosecha = '".$item_str_cosecha."'";
	     	
	     	$query.=" Union All
	     		SELECT Clientes.NroCtaCte, Clientes.Nombre AS Cliente, CD.ONCCAEspecieId, ONCCAEspecies.Nombre AS Cereal, CD.Cosecha AS Cosecha,".$saldoAnterior." AS Saldo, str(CtaCteProductores.TipoMovimientoId)+str(CtaCteProductores.Nro)+ str(COALESCE (CtaCteProductores.NroSec, 0)) AS K,TipoMovEntradas.Nombre AS Tipo, REPLICATE('0', 4 - LEN(LTRIM(STR(CD.Prefijo)))) + LTRIM(STR(CD.Prefijo))+'-'+REPLICATE('0',8 - LEN(LTRIM(STR(CD.Nro)))) + LTRIM(STR(CD.Nro)) AS Nro, CtaCteProductores.Fecha, '' AS NroCert,CtaCteProductores.Debe, CtaCteProductores.Haber,0 AS Saldo, CtaCteProductores.Neto, CtaCteProductores.Mermas, CtaCteProductores.Pizarra, CtaCteProductores.Flete, CtaCteProductores.GsAdmin, CtaCteProductores.Comision, CtaCteProductores.Precio, CtaCteProductores.IVACBU, CtaCteProductores.Pago,0 AS NroCtaCteRecibe, '' AS ClienteRecibe ,idtablaorigen FROM   CtaCteProductores INNER JOIN Clientes ON CtaCteProductores.Clienteid = Clientes.ClienteId INNER JOIN TipoMovEntradas ON CtaCteProductores.TipoMovimientoId = TipoMovEntradas.TipoMovEntradaId INNER JOIN CertificadosDeposito CD ON CtaCteProductores.IdTablaOrigen = CD.CertificadoDepositoId INNER JOIN ONCCAEspecies ON CD.ONCCAEspecieId = ONCCAEspecies.ONCCAEspecieId  Where (CtaCteProductores.TipoMovimientoSecId Is Null) And (CtaCteProductores.TipoMovimientoId = 2)  and CtaCteProductores.Fecha BETWEEN '".$fechaDesde."' and '".$fechaHasta."' and CtaCteProductores.ClienteID = '".$id_cliente."' And CD.onccaespecieid = '".$item_id_cereal."' AND CD.cosecha = '".$item_str_cosecha."'";

	     	$query.= " Union All 
	     		SELECT Clientes.NroCtaCte, Clientes.Nombre AS Cliente, RT.OnccaEspecieId, ONCCAEspecies.Nombre AS Cereal, RT.Cosecha AS Cosecha,".$saldoAnterior." AS Saldo,str(CtaCteProductores.TipoMovimientoId)+str(CtaCteProductores.Nro)+ str(COALESCE (CtaCteProductores.NroSec, 0)) AS K,rtrim(TipoMovEntradas.Nombre)+'-'+case when rt.retira = 1 then 'Retiro' when  ctacteproductores.debe <> 0 then 'Recibe' else 'Transfiere' end  AS Tipo,  REPLICATE('0', 4 - LEN(LTRIM(STR(CtaCteProductores.Prefijo)))) + LTRIM(STR(CtaCteProductores.Prefijo))+'-'+REPLICATE('0',8 - LEN(LTRIM(STR(CtaCteProductores.Nro)))) + LTRIM(STR(CtaCteProductores.Nro)) AS Nro, CtaCteProductores.Fecha, '' AS NroCert,CtaCteProductores.Debe, CtaCteProductores.Haber, 0 AS Saldo,CtaCteProductores.Neto,CtaCteProductores.Mermas, CtaCteProductores.Pizarra, CtaCteProductores.Flete, CtaCteProductores.GsAdmin, CtaCteProductores.Comision, CtaCteProductores.Precio, CtaCteProductores.IVACBU, CtaCteProductores.Pago, clientes_1.NroCtaCte AS NroCtaCteRecibe, Clientes_1.Nombre AS ClienteRecibe ,idtablaorigen FROM ONCCAEspecies INNER JOIN RT ON ONCCAEspecies.ONCCAEspecieId = RT.OnccaEspecieId INNER JOIN CtaCteProductores INNER JOIN Clientes ON CtaCteProductores.Clienteid = Clientes.ClienteId ON RT.RTId = CtaCteProductores.IdTablaOrigen INNER JOIN TipoMovEntradas ON CtaCteProductores.TipoMovimientoId = TipoMovEntradas.TipoMovEntradaId LEFT OUTER JOIN Clientes Clientes_1 ON CtaCteProductores.ClienteSecId = Clientes_1.ClienteId  Where (CtaCteProductores.TipoMovimientoId = 3) and CtaCteProductores.Fecha BETWEEN '".$fechaDesde."' and '".$fechaHasta."' and CtaCteProductores.ClienteID = '".$id_cliente."' And RT.onccaespecieid = '".$item_id_cereal."' AND rt.cosecha = '".$item_str_cosecha."' Order by 1,10";

	     	$resultParcial = sqlsrv_query($connEmpresaDB, $query);
			if(!$resultParcial){
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
				
			     while ($row = sqlsrv_fetch_array($resultParcial, SQLSRV_FETCH_ASSOC)) {
			     	$jsonDetalles[] = $row;
			     	
			     }
			} while ( sqlsrv_next_result($resultParcial) );

			

			    	
			

	     }



			
			
			


			     	
			    

	} while ( sqlsrv_next_result($result) );


	$ret = array(
		'status' => 'succes',
		'code' => '200',
		'detalle' => $jsonDetalles
	);
	echo json_encode($ret);
		
			
				

	   
});

$router->run();