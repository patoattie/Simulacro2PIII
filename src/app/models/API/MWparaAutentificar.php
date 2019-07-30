<?php
namespace App\Models\API;

use App\Models\ORM\usuario;
use App\Models\ORM\compra;
use App\Models\ORM\log;
use App\Models\ORM\logControler;
use Slim\Http\Request;
use Slim\Http\Response;
use Exception;

require_once "AutentificadorJWT.php";
include_once __DIR__ . '/../ORM/usuario.php';
include_once __DIR__ . '/../ORM/compra.php';
include_once __DIR__ . '/../ORM/log.php';
include_once __DIR__ . '/../ORM/logControler.php';

class MWparaAutentificar
{
 /**
   * @api {any} /MWparaAutenticar/  Verificar Usuario
   * @apiVersion 0.1.0
   * @apiName VerificarUsuario
   * @apiGroup MIDDLEWARE
   * @apiDescription  Por medio de este MiddleWare verifico las credeciales antes de ingresar al correspondiente metodo 
   *
   * @apiParam {ServerRequestInterface} request  El objeto REQUEST.
 * @apiParam {ResponseInterface} response El objeto RESPONSE.
 * @apiParam {Callable} next  The next middleware callable.
   *
   * @apiExample Como usarlo:
   *    ->add(\MWparaAutenticar::class . ':VerificarUsuario')
   */
	public function VerificarUsuario($request, $response, $next) {
         
		$objDelaRespuesta = new \stdclass();
		$objDelaRespuesta->respuesta = "";
		$newResponse = "";
	   
		/*if($request->isGet())
		{
		// $response->getBody()->write('<p>NO necesita credenciales para los get </p>');
			$response = $next($request, $response);
		}
		else
		{*/
  			$token = $request->getHeader("jwt")[0];
			$objDelaRespuesta->esValido = true; 
			$payload = null;

			try 
			{
				AutentificadorJWT::verificarToken($token);
			}
			catch (Exception $e)
			{      
				//guardar en un log
				$objDelaRespuesta->excepcion = $e->getMessage();
				$objDelaRespuesta->esValido = false;     
			}

			if($objDelaRespuesta->esValido)
			{						
				$payload = AutentificadorJWT::ObtenerData($token);

				/*if(!$request->isPost() && !$request->isGet()) // el post y el get sirven para todos los logueados
				{
		  			$perfil = Usuario::getCampoPerfil();

					// PUT y DELETE sirve para solamente para los logueados y admin
					if($payload->$perfil !== Usuario::getPerfilAdmin())
					{	
						$objDelaRespuesta->esValido = false;
						$objDelaRespuesta->respuesta = "Solo Administradores";
					}
				}*/		          
			}    
			else
			{
				$objDelaRespuesta->respuesta = "Solo usuarios registrados";
			}

			if($objDelaRespuesta->esValido) 
			{
				//Atributo que usarán los demás middleware obtener los datos del token
				$request = $request->withAttribute("datosToken", $payload);

				$response = $next($request, $response);
			}

		//}

		if($objDelaRespuesta->respuesta != "")
		{
			$newResponse = $response->write($response->withJson($objDelaRespuesta->respuesta, 401));  
		}
		else
		{
			$newResponse = $response;
		}
		  
		 //$response->getBody()->write('<p>vuelvo del verificador de credenciales</p>');
		 return $newResponse;   
	}

	public function ExclusivoAdmin($request, $response, $next)
	{
		$newResponse = "";

		$objDelaRespuesta = new \stdclass();
		$objDelaRespuesta->respuesta = "";
	   
		$payload = $request->getAttribute("datosToken");
		$perfil = Usuario::getCampoPerfil();

		if($payload->$perfil === Usuario::getPerfilAdmin())
		{
			$response = $next($request, $response);
		}		           	
		else
		{
			$objDelaRespuesta->respuesta = "hola";
		}

		if($objDelaRespuesta->respuesta != "")
		{
			$newResponse = $response->write($response->withJson($objDelaRespuesta->respuesta, 200));  
		}
		else
		{
			$newResponse = $response;
		}
		  
		return $newResponse;   
	}

	public function FiltrarCompras(Request $request, Response $response, callable $next)
	{
		$payload = $request->getAttribute("datosToken");
		$perfil = Usuario::getCampoPerfil();

		$newResponse = "";
		$response = $next($request, $response);

		if($payload->$perfil === Usuario::getPerfilAdmin())
		{
			$newResponse = $response;
		}
		else
		{
			$compras = array();

			foreach (json_decode($response->getBody(), true) as $unaCompra)
			{
				if($unaCompra[Compra::getCampoUsuario()] == $payload->id)
				{
					array_push($compras, $unaCompra);
				}
			}

			$newResponse = $response->withJson($compras, 200);
		}

		return $newResponse;   
	}

	public function FormatearSalidaCompras(Request $request, Response $response, callable $next)
	{
		$precio = Compra::getCampoPrecio();
		$fecha = Compra::getCampoFecha();
		$usuarioID = Compra::getCampoUsuario();
		$usuarioNombre = "nombre_usuario";

		$newResponse = "";
		$response = $next($request, $response);

		$compras = array();

		foreach (json_decode($response->getBody(), true) as $unaCompra)
		{
            //Redondeo el precio a dos decimales, seteando también separador de decimales coma y separador de miles punto
            $unaCompra[$precio] = number_format($unaCompra[$precio], 2, ",", ".");

            //La fecha en el array vieen como string, para poder formatear primero tengo que convertirla a fecha con la función date_create_from_format() y luego darle el formato requerido con la función date_format()
            $unaCompra[$fecha] = date_format(date_create_from_format("Y-m-d H:i:s", $unaCompra[$fecha]), "d/m/Y H:i:s");

            //Agrego al response el nombre del usuario, utilizando el id para hacer la búsqueda en la BD.
            $unaCompra[$usuarioNombre] = Usuario::searchID($unaCompra[$usuarioID])->getUsuario();

            array_push($compras, $unaCompra);
		}

		$newResponse = $response->withJson($compras, 200);

		return $newResponse;   
	}

	public function FormatearSalidaComprasHTML(Request $request, Response $response, callable $next)
	{
		$response = $next($request, $response);

		$compras = json_decode($response->getBody(), true);

		$salida = "<table>";
		$salida = $salida . "<caption>Compras</caption>";
		$salida = $salida . "<tr>";
		
		$salida = $salida . "<th>imagen</th>";

		foreach (array_keys($compras[0]) as $unaClave)
		{
			$salida = $salida . "<th>$unaClave</th>";
		}

		$salida = $salida . "</tr>";

		foreach ($compras as $unaCompra)
		{
			$salida = $salida . "<tr>";
			$imagen = __DIR__ . "/../../../../IMGCompras/" . $unaCompra["id"] . "_" . $unaCompra[Compra::getCampoArticulo()] . ".png";
			$imagenURL = "http://localhost/Simulacro2P/IMGCompras/" . $unaCompra["id"] . "_" . $unaCompra[Compra::getCampoArticulo()] . ".png";

			if(file_exists($imagen))
			{
				$salida = $salida . "<td><img src = $imagenURL alt = $imagenURL style = 'width:48px; height:48px;>'</td>";
			}
			else
			{
				$salida = $salida . "<td></td>";
			}
	
			foreach ($unaCompra as $valor)
			{
				$salida = $salida . "<td>$valor</td>";
			}

			$salida = $salida . "</tr>";
		}

		$salida = $salida . "</table>";

		//echo $salida;

		$newResponse = $response->withJson($salida, 200, JSON_UNESCAPED_UNICODE + JSON_UNESCAPED_SLASHES);

		return $newResponse;   
	}

	public function FiltrarCamposReservados(Request $request, Response $response, callable $next)
	{
		$newResponse = "";
		$response = $next($request, $response);

		$salida = array();
		$datos = json_decode($response->getBody(), true);

		if(is_null($datos) || !$request->isGet())
		{
			$newResponse = $response;
		}
		else
		{
			foreach ($datos as $unDato)
			{
		        $func = function($key)
		        {
		            return ($key !== "id"
		            	&& substr($key, 0, 3) !== "id_"
		            	&& $key !== Usuario::getCampoClave()
		            	&& $key !== "created_at"
		            	&& $key !== "updated_at");
		        };

	            array_push($salida, array_filter($unDato, $func, ARRAY_FILTER_USE_KEY));
			}
	
			$newResponse = $response->withJson($salida, 200, JSON_UNESCAPED_UNICODE + JSON_UNESCAPED_SLASHES);
		}

		return $newResponse;   
	}

  	public function GuardarLog(Request $request, Response $response, callable $next)
  	{
  		$payload = $request->getAttribute("datosToken");

  		if($payload)
  		{
			$requestLog = $request->withParsedBody(array(Log::getCampoUsuario() => $payload->id, Log::getCampoMetodo() => $request->getMethod(), Log::getCampoRuta() => $request->getUri()->getPath()));

			$unLog = (new logControler)->CargarUno($requestLog, $response, null);
  		}

		$response = $next($request, $response);

		return $response;
  	}

	public function FormatearSalidaLogs(Request $request, Response $response, callable $next)
	{
		$fecha = Log::getCampoFecha();
		$usuarioID = Log::getCampoUsuario();
		$usuarioNombre = "nombre_usuario";

		$newResponse = "";
		$response = $next($request, $response);

		$logs = array();

		foreach (json_decode($response->getBody(), true) as $unLog)
		{
            //La fecha en el array vieen como string, para poder formatear primero tengo que convertirla a fecha con la función date_create_from_format() y luego darle el formato requerido con la función date_format()
            $unLog[$fecha] = date_format(date_create_from_format("Y-m-d H:i:s", $unLog[$fecha]), "d/m/Y H:i:s");

            //Agrego al response el nombre del usuario, utilizando el id para hacer la búsqueda en la BD.
            $unLog[$usuarioNombre] = Usuario::searchID($unLog[$usuarioID])->getUsuario();

            array_push($logs, $unLog);
		}

		$newResponse = $response->withJson($logs, 200, JSON_UNESCAPED_UNICODE + JSON_UNESCAPED_SLASHES);

		return $newResponse;   
	}
}