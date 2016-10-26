<?php

/**
 * Clase Response, formatea el resonse de los servicios  
 * 
 * @author Geovanni Escalante <gescalante@arkho.tech>
 */

namespace App;

use Log;

class Response {
    
    /**
     * Codigo del response
     * 
     * @var int $_code 
     */
    private $_code;
    
    /**
     * Mensaje del response
     * 
     * @var string $_message
     */
    private $_message;
    
    /**
     * Codigo del response
     * 
     * @param int $code
     */
    public function setCode($code)
    {
        $this->_code = $code;
    }
    
    /**
     * Mensaje del response
     * 
     * @param string $message
     */
    public function setMessage($message)
    {
        $this->_message = $message;
    }
    
    /**
     * Retorna el codigo del response
     * 
     * @return int
     */
    public function getCode()
    {
        return $this->_code;
    }
    
    /**
     * Retorna el mensaje del response
     * 
     * @return string
     */
    public function getMessage()
    {
        return $this->_message;
    }
    
    /**
     * Retorna un Objeto de tipo Illuminate/Http/Response solo para status code
     * 200 y 201
     * 
     * @param int $code
     * @param mixed array/null data
     * @return Illuminate/Http/Response
     */
    public static function make($code, $data = null)
    {
        if ($data !== null) {
            $c[] = array(
                'response' => array(
                    'code' => $code,
                    'message' => self::getStatusCode($code)
                )
            );
            array_push($c, $data);        
            $resp = response($c, $code);
        } else {
            $resp = response([
                'response' => array(
                    'code' => $code,
                    'message' => self::getStatusCode($code)
                )], $code
            );
        }
        
        return $resp;
    }
    
    /**
     * Retorna un Objeto de tipo Illuminate/Http/Response
     * 
     * @param int $code
     * @param int $internalCode
     * @param string $customMessage
     * @param mixed Exception/null e
     * @return Illuminate/Http/Response
     */
    public static function error($code, $internalCode, $customMessage = '', $e = null)
    {        
        if ($code === 500) {
            if ($e !== null && is_a($e, 'Exception')) {
                Log::error('code: ' .  $e->getCode() . ' Message: ' . $e->getMessage());
            }
        }
        
        //Mis mensajes de errores personalizados
        if ($internalCode >= 1000) {            
            $resp = response([
                'response' => array(
                    'code' => $internalCode,
                    'message' => !empty($customMessage) ? $customMessage : self::getCustomMessage($internalCode)
                )], $code
            );
        } else {
            //Errores estandar
            $resp = response([
                'response' => array(
                    'code' => $code,
                    'message' => !empty($customMessage) ? $customMessage :  self::getStatusCode($code)
                )], $code
            );
        }
        
        return $resp;
    }
    
    /**
     * Retorna el mensaje del http status code
     * 
     * @param int $code     
     * @return string
     */
    public static function getStatusCode($code)
    {
		$codes = array(

			// Informational 1xx
            100 => 'Continuará',
			101 => 'Protocolos de conmutación',

			// Success 2xx
			200 => 'OK',
			201 => 'Creado',
			202 => 'Aceptado',
			203 => 'Información no autorizada',
			204 => 'Sin contenido',
			205 => 'Contenido reseteado',
			206 => 'Contenido parcial',

			// Redirection 3xx
			300 => 'Multiples opciones',
			301 => 'Se ha movido permanentemente',
			302 => 'Encontrado',  // 1.1
			303 => 'Ver otro',
			304 => 'No modificado',
			305 => 'Usa proxy',
			// 306 is deprecated but reserved
			307 => 'Temporalmente redireccionado',

			// Client Error 4xx
			400 => 'Mala petición',
			401 => 'Desautorizado',
			402 => 'Pago requerido',
			403 => 'Prohibido',
			404 => 'No encontrado',
			405 => 'Método no permitido',
			406 => 'No aceptado',
			407 => 'Se requiere autenticación al proxy',
			408 => 'Petición llegó a su tiempo de espera',
			409 => 'Conflicto',
			410 => 'Ir',
			411 => 'Se requiere longitud',
			412 => 'Falló precondición',
			413 => 'Petición a una entidad muy grande',
			414 => 'Petición a URI muy grande',
			415 => 'Tipo de media no soportado',
			416 => 'Rango de petición no satisface',
			417 => 'Falló lo esperado',

			// Server Error 5xx
			500 => 'Error interno en el servidor',
			501 => 'No implementado',
			502 => 'Puerta de enlace incorrecta',
			503 => 'Servicio no disponible',
			504 => 'Puerta de enlace llegó a su tiempo de espera',
			505 => 'Bersión HTTP no soportada',
			509 => 'Se excedió el límite de ancho de banda'
		);

		$result = (isset($codes[$code])) ? $codes[$code] : 'Código de estado desconocido';

		return $result;
	}
    
    /**
     * Retorna un mensaje personalizado de error
     * 
     * @param int $code     
     * @return string
     */
    public static function getCustomMessage($code)
    {
		$codes = array(
            //Generic
            1000 => 'Parámetros de header appkey y/o dominio no proporcionados',
            1020 => 'Faltan parámetros o petición mal formada',
            1030 => 'Appkey o dominio no existe',
            5000 => 'Appkey no existe',
            
            //Calendar
            1010 => 'Calendario no encontrado',
			1040 => 'El nombre del calendario debe ser único por Appkey y dominio',
            1050 => 'No se puede editar el registro. El calendario tiene citas disponibles',
            1060 => 'No se puede deshabilitar el registro. El calendario tiene citas disponibles',
            
            //DayOff
            1070 => 'No hay días feriados',
			1080 => 'Hay citas disponibles en esta fecha',
            1090 => 'La fecha final debe ser mayor o igual a la fecha actual',
            2000 => 'La appkey no existe',
            2001 => 'La fecha ya ha sido registrada',
            
            //Appointment
            2010 => 'La fecha de la cita debe ser mayor o igual a la fecha actual',
            2020 => 'La fecha de la cita que desea reservar es un día no laboral',
            2030 => 'La fecha de la cita que desea reservar es un horario bloqueado',
            2040 => 'La fecha de cita no corresponde al horario del calendario',
            2050 => 'La fecha de la cita se está cruzando con otra',
            2060 => 'No puede cancelar la cita, tiempo de espera agotado',
            2070 => 'No hay citas',
            2071 => 'No se puede confirmar la cita porque se encuentra cancelada',
            2072 => 'Cita no encontrada',
            
            //BlockSchedule
            2080 => 'La fecha final debe ser mayor a la fecha de inicio',
            2090 => 'La fecha de inicio debe ser mayor o igual a la fecha actual',
            2091 => 'El parámetro range no está bien definido, debe ser un array con elementos',

            //App
            4010 => 'No hay applicaciones',
            4020 => 'Aplicación no editada, campos similares',
            4030 => 'Estado de aplicación no cambió, mismo estado'
        );
        
		$result = (isset($codes[$code])) ? $codes[$code] : 'Código de estado desconocido';

		return $result;
	}
}
