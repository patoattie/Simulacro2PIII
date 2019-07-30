<?php
namespace App\Models\ORM;
 
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class Compra extends \Illuminate\Database\Eloquent\Model
{ 
	//public $timestamps = false; 
	
	//carga los valores de los atributos contenidos en el array pasado por parámetro
	public function setParams($parametros)
	{
        //cargo los atributos a ingresar en el objeto
        foreach ($parametros as $key => $value)
        {
            $this->setAttribute($key, $value);
        }
	}

	public function setID($id)
	{
		$this->setAttribute($this->getCampoID(), $id);
	}

	public function getID()
	{
		return $this->getAttribute($this->getCampoID());
	}

	public function calculaID()
	{
		return $this->getIncrementing();
	}

	public function setUsuario($usuario)
	{
		$this->setAttribute(self::getCampoUsuario(), $usuario);
	}

	public function getUsuario()
	{
		return $this->getAttribute(self::getCampoUsuario());
	}

	public function setArticulo($articulo)
	{
		$this->setAttribute(self::getCampoArticulo(), $articulo);
	}

	public function getArticulo()
	{
		return $this->getAttribute(self::getCampoArticulo());
	}

	public function setPrecio($precio)
	{
		$this->setAttribute(self::getCampoPrecio(), $precio);
	}

	public function getPrecio()
	{
		return $this->getAttribute(self::getCampoPrecio());
	}

	public function setFecha($fecha)
	{
		$this->setAttribute(self::getCampoFecha(), $fecha);
	}

	public function getFecha()
	{
		return $this->getAttribute(self::getCampoFecha());
	}

	public function getCampoID()
	{
		return $this->getKeyName();
	}

	public static function getCampoUsuario()
	{
		return "id_usuario";
	}

	public static function getCampoArticulo()
	{
		return "articulo";
	}

	public static function getCampoFecha()
	{
		return "fecha";
	}

	public static function getCampoPrecio()
	{
		return "precio";
	}

	public static function searchUsuario($usuario)
	{
		return (new Compra())->where(array(self::getCampoUsuario() => $usuario))->get();
	}
}

?>