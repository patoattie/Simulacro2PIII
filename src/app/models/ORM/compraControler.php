<?php
namespace App\Models\ORM;

use App\Models\ORM\compra;
use App\Models\ORM\usuario;
use App\Models\API\IApiControler;
use App\Models\API\ManejadorArchivos;

require_once __DIR__ . '/compra.php';
require_once __DIR__ . '/usuario.php';
include_once __DIR__ . '../../API/IApiControler.php';
include_once __DIR__ . '../../API/ManejadorArchivos.php';

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Http\UploadedFile;

class CompraControler implements IApiControler 
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
        $todasLasCompras=Compra::all();

        $newResponse = $response->withJson($todasLasCompras, 200);  
        return $newResponse;
    }

    public function CargarUno($request, $response, $args) {
     	 //complete el codigo

        $condicion = self::cargarConBody($request);
        $archivos = $request->getUploadedFiles();
        $foto = $archivos["foto"];

        //cargo un objeto de tipo Compra con los parametros ingresados por POST
        $unaCompra = new Compra();

        //cargo los atributos a ingresar en el objeto
        $payload = $request->getAttribute("datosToken");
        $unaCompra->setParams($condicion);
        $unaCompra->setUsuario($payload->id);

        //inserto en la base
        $estado = 0; //OK

        if($unaCompra->calculaID()) //El ID es autoincremental, lo dejo en nulo para que lo calcule la BD.
        {
            $unaCompra->setID(null);

            if($unaCompra->save())
            {
                if ($foto->getError() === UPLOAD_ERR_OK)
                {
                    $nombreFoto = $unaCompra[$unaCompra->getCampoID()] . "_" . $unaCompra[$unaCompra->getCampoArticulo()];
                    ManejadorArchivos::cargarImagenPorNombre($foto, $nombreFoto, __DIR__ . "/../../../../IMGCompras/", $nombreFoto);
                }
            }
        }
        else
        {
            //retorna true si dentro de los parámetros ingresados está el ID. Util para cuando el ID en la BD no es autoincremental y se lo tengo que pasar.
            //$tengoClave = array_key_exists($unaCompra->getCampoID(), $condicion);

            if($unaCompra->getID()) //tengo el ID ingresado dentro de los parámetros del body
            {
                //traigo el id de los parámetros ingresados
                $id = $condicion[$unaCompra->getCampoID()];

                if($unaCompra->find($id)) //Si existe el ID, muestro mensaje al usuario y no ingreso nada
                {
                    $estado = -1; //"La Compra ya se encuentra ingresado"
                }
                else
                {
                    $unaCompra->save();
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
        //modifico en la base
        $estado = 0; //OK

        $condicion = self::cargarConBody($request);

        $tengoArticulo = array_key_exists(Compra::getCampoArticulo(), $condicion);
        $tengoFecha = array_key_exists(Compra::getCampoFecha(), $condicion);
        $tengoPrecio = array_key_exists(Compra::getCampoPrecio(), $condicion);
        $tengoUsuario = array_key_exists('usuario', $condicion);

        if(!$tengoArticulo || !$tengoFecha || !$tengoPrecio || !$tengoUsuario)
        {
            $estado = -3; //"No tengo la clave completa para borrar una compra"
        }
        else
        {
            $usuario = Usuario::searchUsuario($condicion["usuario"]);

            if($usuario)
            {
                //cargo un array de objetos de tipo Compra que satisfagan el id_usuario requerido
                $compras = Compra::searchUsuario($usuario->id);

                if($compras)
                {
                    foreach ($compras as $unaCompra)
                    {
                        if($unaCompra[Compra::getCampoArticulo()] == $condicion[Compra::getCampoArticulo()]
                        && $unaCompra[Compra::getCampoPrecio()] == $condicion[Compra::getCampoPrecio()]
                        && $unaCompra[Compra::getCampoFecha()] == $condicion[Compra::getCampoFecha()])
                        {
                            //Si existe, borro la compra de la BD
                            $borro = $unaCompra->delete();
                            if(!$borro || is_null($borro))
                            {
                                $estado = -2; //"Error al borrar en la BD"
                            }

                            break;
                        }
                    }
                }
                else
                {
                    $estado = -1; //"La Compra no existe"
                }
            }
            else
            {
                $estado = -4; //"El usuario no existe"
            }
        }

        //Devuelvo el estado
        $newResponse = $response->withJson($estado, 200);  
        return $newResponse;
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
            $parametros[$key] = $value;
        }

        return $parametros;
    }
}

?>