<?php
namespace App\Models\ORM;
 
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class Log extends \Illuminate\Database\Eloquent\Model
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

	public function setMetodo($metodo)
	{
		$this->setAttribute(self::getCampoMetodo(), $metodo);
	}

	public function getMetodo()
	{
		return $this->getAttribute(self::getCampoMetodo());
	}

	public function setRuta($ruta)
	{
		$this->setAttribute(self::getCampoRuta(), $ruta);
	}

	public function getRuta()
	{
		return $this->getAttribute(self::getCampoRuta());
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

	public static function getCampoMetodo()
	{
		return "metodo";
	}

	public static function getCampoFecha()
	{
		return "fecha";
	}

	public static function getCampoRuta()
	{
		return "ruta";
	}

	public static function searchMetodo($metodo)
	{
		$unLog = new Log();

		return $unLog->where(array(self::getCampoMetodo() => $metodo))->get();
	}
}

?>