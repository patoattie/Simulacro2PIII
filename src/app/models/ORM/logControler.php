<?php
namespace App\Models\ORM;

use App\Models\ORM\log;
use App\Models\API\IApiControler;

require_once __DIR__ . '/log.php';
include_once __DIR__ . '../../API/IApiControler.php';

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;


class LogControler implements IApiControler 
{
 	public function Bienvenida($request, $response, $args) {
		return $response->getBody()->write("<h1>" . $request->getMethod() . " => Simulacro 2do Parcial</h1>
            <h2>ETAPA 1</h2>
                <ol>
                    <li>Ruta “usuario”(POST) , alta de usuario(nombre , clave y sexo).<br>
                    Perfil por defecto “usuario”.<br>
                    Agregar a un usuario en la base con los siguientes datos(nombre:admin, clave: admin , sexo: femenino<br>
                    perfil:admin).</li>
                    <li>Ruta “login” (POST) , retorna el JWT o un error con la información de que esta mal( la clave o el sexo , o el
                    nombre no existe).</li>
                    <h2>ETAPA 2</h2>
                    <li>Ruta “usuario”(GET), retorna la lista de usuarios, solo si sos admin, de lo contrario retorna un “hola”. usar un
                    middleware.</li>
                    <li>Ruta “Compra”(POST), se ingresa un artículo , la fecha y el precio de la compra, solo personas que estén
                    registradas en el sistema.</li>
                    <h2>ETAPA 3</h2>
                    <li>Ruta “Compra”(GET),retorna el listado de compras del usuario pero si es un admin, retorna todas compras.</li>
                    <li>Hacer un middleware para todas las rutas que guarde en la BD los siguientes datos: usuario, metodo,ruta y
                    hora.</li>
                    <h2>ETAPA 4</h2>
                    <li>Modificar la ruta “Compra”(POST), y subir una imagen que se guarde en la carpeta “IMGCompras” con el
                    nombre del id de la compra y el artículo.</li>
                    <li>Ruta “Compra”(GET),retorna el listado de compras del usuario pero si es un admin, retorna todas compras.</li>
                </ol>");
    }
    
    public function TraerTodos($request, $response, $args) {
        //retorna un array de objetos de tipo compra con todas las compras de la colección
        $todosLosLogs=Log::all();

        $newResponse = $response->withJson($todosLosLogs, 200);  
        return $newResponse;
    }

    public function CargarUno($request, $response, $args) {
     	 //complete el codigo

        $condicion = self::cargarConBody($request);

        //cargo un objeto de tipo Log con los parametros ingresados por POST
        $unLog = new Log();

        //cargo los atributos a ingresar en el objeto
        $payload = $request->getAttribute("datosToken");
        $unLog->setParams($condicion);
        $unLog->setUsuario($payload->id);

        //inserto en la base
        $estado = 0; //OK

        if($unLog->calculaID()) //El ID es autoincremental, lo dejo en nulo para que lo calcule la BD.
        {
            $unLog->setID(null);
            $unLog->save();
        }
        else
        {
            //retorna true si dentro de los parámetros ingresados está el ID. Util para cuando el ID en la BD no es autoincremental y se lo tengo que pasar.
            //$tengoClave = array_key_exists($unLog->getCampoID(), $condicion);

            if($unLog->getID()) //tengo el ID ingresado dentro de los parámetros del body
            {
                //traigo el id de los parámetros ingresados
                $id = $condicion[$unLog->getCampoID()];

                if($unLog->find($id)) //Si existe el ID, muestro mensaje al usuario y no ingreso nada
                {
                    $estado = -1; //"El Log ya se encuentra ingresado"
                }
                else
                {
                    $unLog->save();
                }
            }
            else
            {
                $estado = -2; //"Falta pasar el parametro con el ID de la compra a cargar"
            }
        }

        //Devuelvo el estado
        $newResponse = $response->withJson($estado, 200);
        return $newResponse;
    }

    public function TraerUno($request, $response, $args)
    {

    }

    public function BorrarUno($request, $response, $args)
    {

    }

    public function ModificarUno($request, $response, $args)
    {

    }

    private static function cargarConQueryParams($request)
    {
        $parametros = array();

        //recorro los parámetros ingresados
        foreach ($request->getQueryParams() as $key => $value) //Parametros de $_GET
        {
            $parametros[$key] = $value;
        }

        return $parametros;
    }

    private static function cargarConBody($request)
    {
        $parametros = array();

        //recorro los parámetros ingresados
        foreach ($request->getParsedBody() as $key => $value) //Parametros de $_POST
        {
            if($key !== "usuario")
            {
                $parametros[$key] = $value;
            }
        }

        return $parametros;
    }
}

?>