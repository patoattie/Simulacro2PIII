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
                    <h2><li>( nota 4)El enunciado será dado el dia del examen.</li></h2>
                    <h2>Atención !!!: si y solo si , hacen el punto 9, y después de hacerlo se corrige los siguientes puntos</h2>
                    <h2>...</h2>
                    <li>(nota 5)Cambios :<br>
                    a-(GET)Listado de compras con fotos.<br>
                    b-(POST)Alta de foto con marca de agua.</li>
                    <li>(nota 6)(PUT)Modificar los datos de un usuario, cambiando el sexo y el password.</li>
                    <li>(nota 7)(GET)Mostrar los datos recopilados en el punto 6 filtrados por el método utilizado.</li>
                    <li>(nota 8)(DELETE)Borrar una compra.</li>
                    <li>(nota 9)(GET) mostrar las ventas filtradas por el nombre del usuario.</li>
                    <li>(nota 10)(GET)<br>
                    a-agregar el campo tipo de pago a la compra(efectivo,tarjeta o mercadopago)<br>
                    b-mostrar las ventas filtradas por un parámetro llamado filtro, que puede ser el nombre del usuario, el nombre del artículo o el tipo de pago,traer todos los datos que coincidan con el dato en cualquiera de los criterios de búsqueda.</li>
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
        $filtroMetodo = isset($args["metodo"]);

        $logs = "";

        if($filtroMetodo)
        {
            //retorna un array de objetos de tipo log con todos los logs de la colección que satisfacen eñ filtro por método
            $logs = Log::searchMetodo($args["metodo"]);
        }
        else
        {
            //retorna un array de objetos de tipo log con todos los logs de la colección
            $logs = Log::all();
        }

        //Devuelvo el estado
        $newResponse = $response->withJson($logs, 200);  
        return $newResponse;
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