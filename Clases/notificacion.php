<?php
/*
	Para utilizar esta API de clientes siempre es necesario recibir el id_cliente, para relacionar al 
	usuario logueado con un cliente dentro de la base de datos del sistema general.
*/


Class Notificacion {

	private $conn;

	function __construct($conexion){ //Le paso 

		$this->conn = $conexion;

	}

	/*
	Verifica si ya existe una notificacion en la bd de notificaciones para este destinatario y emitente y que ademas sea del tipo registro
	Si existe no envia nada 
	Si no existe entonces envia la notificacion

	*/
	function nuevoUsuario($id_destinatario,$id_remitente,$nombre,$conn){
		

		$texto = "El usuario: ".$nombre." solicita que se le asigne un cliente";
		$tipo = "registro";


		$query = "SELECT * FROM notificaciones WHERE id_destinatario='".$id_destinatario."' AND id_remitente = '".$id_remitente."' AND tipo='".$tipo."'";
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
			//Si ya existe una notificacion de nuevo usuario para este usuario no se vuelve a enviar
		}
		else{
			echo "nada";
			$query = "INSERT INTO notificaciones (id_destinatario,id_remitente,texto,tipo,leida) VALUES ('".$id_destinatario."','".$id_remitente."','".$texto."','".$tipo."','0')";
						
			$result = sqlsrv_query($this->conn, $query);
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
	}

	function noticia($id_destinatario,$id_remitente,$titulo,$texto){

		$query = "INSERT INTO notificaciones (id_destinatario,id_remitente,titulo,texto,tipo,leida) VALUES ('".$id_destinatario."','".$id_remitente."','".$titulo."','".$texto."','noticia','0')";
					
		$result = sqlsrv_query($this->conn, $query);
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
}