<?php

use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;
use App\Models\ORM\usuario;
use App\Models\ORM\usuarioControler;
use App\Models\ORM\compra;
use App\Models\ORM\compraControler;
use App\Models\API\MWparaAutentificar;
use App\Models\API\AutentificadorJWT;

include_once __DIR__ . '/../../src/app/models/ORM/usuario.php';
include_once __DIR__ . '/../../src/app/models/ORM/usuarioControler.php';
include_once __DIR__ . '/../../src/app/models/ORM/compra.php';
include_once __DIR__ . '/../../src/app/models/ORM/compraControler.php';
include_once __DIR__ . '/../../src/app/models/API/MWparaAutentificar.php';
include_once __DIR__ . '/../../src/app/models/API/AutentificadorJWT.php';

return function (App $app) {

	$app->group('/usuarios', function ()
	{
		$container = $this->getContainer();

		$this->any('[/]', function (Request $request, Response $response, array $args) use ($container)
		{
			return (new usuarioControler())->Bienvenida($request, $response, $args);
	  	});     
	});

	$app->group('/usuario', function ()
	{
		$container = $this->getContainer();

		$this->get('[/]', function (Request $request, Response $response, array $args) use ($container)
		{
			return (new usuarioControler())->TraerTodos($request, $response, $args);
	  	})->add(MWparaAutentificar::class . ':ExclusivoAdmin')->add(MWparaAutentificar::class . ':VerificarUsuario');

		$this->post('[/]', function (Request $request, Response $response, array $args) use ($container)
		{
			return (new usuarioControler())->CargarUno($request, $response, $args);
	  	})->add(function($request, $response, $next) //middleware
			{
				$request = $request->withParsedBody(array(Usuario::getCampoUsuario() => $request->getParsedBodyParam(Usuario::getCampoUsuario()), Usuario::getCampoClave() => $request->getParsedBodyParam(Usuario::getCampoClave()), Usuario::getCampoPerfil() => "usuario", Usuario::getCampoSexo() => $request->getParsedBodyParam(Usuario::getCampoSexo()), "id" => $request->getParsedBodyParam("id")));

				$response = $next($request, $response);

				return $response;
			}
		);     

		/*$this->post('/altaAdminPorDefecto[/]', function (Request $request, Response $response, array $args) use ($container)
		{
			echo (new usuarioControler())->CargarUno($request, $response, $args);
	  	})->add(function($request, $response, $next) //middleware
			{
				$request = $request->withParsedBody(array(Usuario::getCampoUsuario() => "admin", Usuario::getCampoClave() => "admin", Usuario::getCampoPerfil() => "admin", Usuario::getCampoSexo() => "femenino", "id" => "1"));

				$response = $next($request, $response);

				return $response;
			});*/
	});

	$app->group('/login', function ()
	{
		$container = $this->getContainer();

		$this->post('[/]', function (Request $request, Response $response, array $args) use ($container)
		{
			return (new usuarioControler())->Login($request, $response, $args);
	  	});     
	});

	$app->group('/compra', function ()
	{
		$container = $this->getContainer();

		$this->post('[/]', function (Request $request, Response $response, array $args) use ($container)
		{
			return (new compraControler())->CargarUno($request, $response, $args);
	  	});

		$this->get('[/]', function (Request $request, Response $response, array $args) use ($container)
		{
			return (new compraControler())->TraerTodos($request, $response, $args);
	  	})->add(MWparaAutentificar::class . ':FiltrarCompras');
	})->add(MWparaAutentificar::class . ':VerificarUsuario');
};

?>