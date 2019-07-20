<?php
namespace App\Models\API;

use App\Models\ORM\usuario;
use App\Models\ORM\compra;
use Slim\Http\Request;
use Slim\Http\Response;
use Exception;

require_once "AutentificadorJWT.php";
include_once __DIR__ . '/../ORM/usuario.php';
include_once __DIR__ . '/../ORM/compra.php';

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

				if(!$request->isPost() && !$request->isGet()) // el post y el get sirven para todos los logueados
				{
		  			$perfil = Usuario::getCampoPerfil();

					// PUT y DELETE sirve para solamente para los logueados y admin
					if($payload->$perfil !== Usuario::getPerfilAdmin())
					{	
						$objDelaRespuesta->esValido = false;
						$objDelaRespuesta->respuesta = "Solo Administradores";
					}
				}		          
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
}