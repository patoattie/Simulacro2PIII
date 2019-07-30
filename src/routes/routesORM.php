<?php

use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;
use App\Models\ORM\usuario;
use App\Models\ORM\usuarioControler;
use App\Models\ORM\compra;
use App\Models\ORM\compraControler;
use App\Models\ORM\log;
use App\Models\ORM\logControler;
use App\Models\API\MWparaAutentificar;
use App\Models\API\AutentificadorJWT;

include_once __DIR__ . '/../../src/app/models/ORM/usuario.php';
include_once __DIR__ . '/../../src/app/models/ORM/usuarioControler.php';
include_once __DIR__ . '/../../src/app/models/ORM/compra.php';
include_once __DIR__ . '/../../src/app/models/ORM/compraControler.php';
include_once __DIR__ . '/../../src/app/models/ORM/log.php';
include_once __DIR__ . '/../../src/app/models/ORM/logControler.php';
include_once __DIR__ . '/../../src/app/models/API/MWparaAutentificar.php';
include_once __DIR__ . '/../../src/app/models/API/AutentificadorJWT.php';

return function (App $app) {

	$app->group('', function () //Agrupamiento para las funciones que trabajan con JWT
	{
		$container = $this->getContainer();

		$this->get('/usuario[/]', function (Request $request, Response $response, array $args) use ($container)
		{
			return (new usuarioControler())->TraerTodos($request, $response, $args);
	  	})->add(MWparaAutentificar::class . ':ExclusivoAdmin');

		$this->post('/usuarioNuevo[/]', function (Request $request, Response $response, array $args) use ($container)
		{
			return (new usuarioControler())->CargarUno($request, $response, $args);
	  	})->add(MWparaAutentificar::class . ':ExclusivoAdmin');     

		$this->post('/compra[/]', function (Request $request, Response $response, array $args) use ($container)
		{
			return (new compraControler())->CargarUno($request, $response, $args);
	  	});

		$this->put('/usuario[/]', function (Request $request, Response $response, array $args) use ($container)
		{
			return (new usuarioControler())->ModificarUno($request, $response, $args);
	  	});

		$this->get('/compra[/]', function (Request $request, Response $response, array $args) use ($container)
		{
			return (new compraControler())->TraerTodos($request, $response, $args);
	  	})->add(MWparaAutentificar::class . ':FiltrarCompras')->add(MWparaAutentificar::class . ':FormatearSalidaCompras');

		$this->delete('/compra[/]', function (Request $request, Response $response, array $args) use ($container)
		{
			return (new compraControler())->BorrarUno($request, $response, $args);
	  	})->add(MWparaAutentificar::class . ':ExclusivoAdmin');
	})->add(MWparaAutentificar::class . ':FiltrarCamposReservados')->add(MWparaAutentificar::class . ':GuardarLog')->add(MWparaAutentificar::class . ':VerificarUsuario');

	$app->group('', function () //Agrupamiento para las funciones que NO trabajan con JWT
	{
		$container = $this->getContainer();

		$this->any('/usuarios[/]', function (Request $request, Response $response, array $args) use ($container)
		{
			return (new usuarioControler())->Bienvenida($request, $response, $args);
	  	});     

		$this->post('/usuario[/]', function (Request $request, Response $response, array $args) use ($container)
		{
			return (new usuarioControler())->CargarUno($request, $response, $args);
	  	})->add(function($request, $response, $next) //middleware
			{
				$request = $request->withParsedBody(array(Usuario::getCampoUsuario() => $request->getParsedBodyParam(Usuario::getCampoUsuario()), Usuario::getCampoClave() => $request->getParsedBodyParam(Usuario::getCampoClave()), Usuario::getCampoPerfil() => "usuario", Usuario::getCampoSexo() => $request->getParsedBodyParam(Usuario::getCampoSexo()), "id" => $request->getParsedBodyParam("id")));

				$response = $next($request, $response);

				return $response;
			}
		);     

		$this->post('/login[/]', function (Request $request, Response $response, array $args) use ($container)
		{
			return (new usuarioControler())->Login($request, $response, $args);
	  	});     

		/*$this->post('/usuario/altaAdminPorDefecto[/]', function (Request $request, Response $response, array $args) use ($container)
		{
			echo (new usuarioControler())->CargarUno($request, $response, $args);
	  	})->add(function($request, $response, $next) //middleware
			{
				$request = $request->withParsedBody(array(Usuario::getCampoUsuario() => "admin", Usuario::getCampoClave() => "admin", Usuario::getCampoPerfil() => "admin", Usuario::getCampoSexo() => "femenino", "id" => "1"));

				$response = $next($request, $response);

				return $response;
			});*/
	});

	$container = $app->getContainer();

	$app->get('/listaCompras[/]', function (Request $request, Response $response, array $args) use ($container)
	{
		return (new compraControler())->TraerTodos($request, $response, $args);
  	})->add(MWparaAutentificar::class . ':FiltrarCompras')->add(MWparaAutentificar::class . ':FormatearSalidaCompras')->add(MWparaAutentificar::class . ':GuardarLog')->add(MWparaAutentificar::class . ':FormatearSalidaComprasHTML')->add(MWparaAutentificar::class . ':VerificarUsuario');

	$app->get('/logs[/{metodo}]', function (Request $request, Response $response, array $args) use ($container)
	{
		return (new logControler())->TraerUno($request, $response, $args);
  	})->add(MWparaAutentificar::class . ':ExclusivoAdmin')->add(MWparaAutentificar::class . ':FormatearSalidaLogs')->add(MWparaAutentificar::class . ':FiltrarCamposReservados')->add(MWparaAutentificar::class . ':VerificarUsuario');

};

?>