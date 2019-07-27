<?php
namespace App\Models\API;

use Exception;

class ManejadorArchivos
{
	/*public static function cambiarNombre($archivoOrigen, $nombreNuevo)
	{
		$archivoDestino = pathinfo($archivoOrigen, PATHINFO_DIRNAME) . "/" . $nombreNuevo . "." . pathinfo($archivoOrigen, PATHINFO_EXTENSION);
		return $archivoDestino;
	}*/
	public function cargarImagenPorNombre($nombreArchivo, $nombre, $carpetaDestino )
	{
		//INDICO CUAL SERA EL DESTINO DEL ARCHIVO SUBIDO
		$destino = $carpetaDestino . $nombreArchivo->getClientFilename();
		if (isset($nombre))
		{
			$tipoArchivo = pathinfo($destino, PATHINFO_EXTENSION);
			$destino = $carpetaDestino . $nombre . ".$tipoArchivo";
		}
		$uploadOk = TRUE;

		//PATHINFO RETORNA UN ARRAY CON INFORMACION DEL PATH
		//RETORNA : NOMBRE DEL DIRECTORIO; NOMBRE DEL ARCHIVO; EXTENSION DEL ARCHIVO
		//PATHINFO_DIRNAME - retorna solo nombre del directorio
		//PATHINFO_BASENAME - retorna solo el nombre del archivo (con la extension)
		//PATHINFO_EXTENSION - retorna solo extension
		//PATHINFO_FILENAME - retorna solo el nombre del archivo (sin la extension)
		$tipoArchivo = pathinfo($destino, PATHINFO_EXTENSION);

		//VERIFICO QUE EL ARCHIVO NO EXISTA
		if (file_exists($destino))
		{
			$uploadOk = ManejadorArchivo::moverArchivoBackup($nombreArchivo->getClientFilename(), $nombre, $destino);
		}

		//VERIFICO EL TAMAÑO MAXIMO QUE PERMITO SUBIR
		if ($nombreArchivo->getSize() > 5000000)
		{
			echo "El archivo es demasiado grande. Verifique!!!";
			$uploadOk = FALSE;
		}

		//VERIFICO SI ES UNA IMAGEN O NO
		//OBTIENE EL TAMAÑO DE UNA IMAGEN, SI EL ARCHIVO NO ES UNA IMAGEN, RETORNA FALSE
		$esImagen = getimagesize($nombreArchivo->getStream()->getMetaData()["uri"]);

		if ($esImagen === FALSE)
		{
			//NO ES UNA IMAGEN (SOLO PERMITO CIERTAS EXTENSIONES)
			if ($tipoArchivo != "doc" && $tipoArchivo != "txt" && $tipoArchivo != "rar")
			{
				echo "Solo son permitidos archivos con extension DOC, TXT o RAR.";
				$uploadOk = FALSE;
			}
		}
		else
		{
			// ES UNA IMAGEN (SOLO PERMITO CIERTAS EXTENSIONES)
			if ($tipoArchivo != "jpg" && $tipoArchivo != "jpeg" && $tipoArchivo != "gif" && $tipoArchivo != "png")
			{
				echo "Solo son permitidas imagenes con extension JPG, JPEG, PNG o GIF.";
				$uploadOk = FALSE;
			}
		}

		//VERIFICO SI HUBO ALGUN ERROR, CHEQUEANDO $uploadOk
		if ($uploadOk === FALSE)
		{
			echo "<br/>NO SE PUDO SUBIR EL ARCHIVO.";
		}
		else
		{
			//MUEVO EL ARCHIVO DEL TEMPORAL AL DESTINO FINAL
			try
			{
				$nombreArchivo->moveTo($destino);
				//MarcadeAgua::hacerMarca($destino, "./firma.png");
			}
			catch(Exception $e)
			{
				echo $e->getMessage();
			}
		}
	}
	
	public static function hacerBackup($archivo)
	{
		copy($archivo, "backup/" . pathinfo($archivo, PATHINFO_FILENAME) . "_" . date("YmdHis") . "." . pathinfo($archivo, PATHINFO_EXTENSION));
	}
	public static function agregarMarcaAgua($archivo, $marca)
	{
		$im = imagecreatefrompng($archivo);
		$estampa = imagecreatefrompng($marca);
		// Establecer los márgenes para la estampa y obtener el alto/ancho de la imagen de la estampa
		$margen_dcho = 10;
		$margen_inf = 10;
		$sx = imagesx($estampa);
		$sy = imagesy($estampa);
		// Copiar la imagen de la estampa sobre nuestra foto usando los índices de márgen y el
		// ancho de la foto para calcular la posición de la estampa. 
		imagecopy($im, $estampa, imagesx($im) - $sx - $margen_dcho, imagesy($im) - $sy - $margen_inf, 0, 0, imagesx($estampa), imagesy($estampa));
		// Imprimir y liberar memoria
		header('Content-type: image/png');
		imagepng($im, $archivo);
		imagedestroy($im);
	}
}
?>