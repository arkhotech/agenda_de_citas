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
            100 => 'Continue',
			101 => 'Switching Protocols',

			// Success 2xx
			200 => 'OK',
			201 => 'Created',
			202 => 'Accepted',
			203 => 'Non-Authoritative Information',
			204 => 'No Content',
			205 => 'Reset Content',
			206 => 'Partial Content',

			// Redirection 3xx
			300 => 'Multiple Choices',
			301 => 'Moved Permanently',
			302 => 'Found',  // 1.1
			303 => 'See Other',
			304 => 'Not Modified',
			305 => 'Use Proxy',
			// 306 is deprecated but reserved
			307 => 'Temporary Redirect',

			// Client Error 4xx
			400 => 'Bad Request',
			401 => 'Unauthorized',
			402 => 'Payment Required',
			403 => 'Forbidden',
			404 => 'Not Found',
			405 => 'Method Not Allowed',
			406 => 'Not Acceptable',
			407 => 'Proxy Authentication Required',
			408 => 'Request Timeout',
			409 => 'Conflict',
			410 => 'Gone',
			411 => 'Length Required',
			412 => 'Precondition Failed',
			413 => 'Request Entity Too Large',
			414 => 'Request-URI Too Long',
			415 => 'Unsupported Media Type',
			416 => 'Requested Range Not Satisfiable',
			417 => 'Expectation Failed',

			// Server Error 5xx
			500 => 'Internal Server Error',
			501 => 'Not Implemented',
			502 => 'Bad Gateway',
			503 => 'Service Unavailable',
			504 => 'Gateway Timeout',
			505 => 'HTTP Version Not Supported',
			509 => 'Bandwidth Limit Exceeded'
		);

		$result = (isset($codes[$code])) ? $codes[$code] : 'Unknown Status Code';

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
            1000 => 'headers params appkey and/or domain do not exist',
            1020 => 'Missing params request or malformed',
            1030 => 'Appkey or domain do not exist',            
            
            //Calendar
            1010 => 'No calendar found',            
			1040 => 'Calendar name must be unique by appkey and domain',
            1050 => 'Can not update row. Calendar has appointments availables',
            1060 => 'Can not disable row. Calendar has appointments availables',
            
            //DayOff
            1070 => 'No dayoff found',
			1080 => 'There are available appointments in this date',
            1090 => 'Date must be greater or equal to current date',
            
            //Appointment
            2010 => 'Start date must be greater or equal to current date',
            2020 => 'Appointment time is in non working day',
            2030 => 'Appointment time is in schedule blocked',
            2040 => 'Appointment time is not into calendar schedule',
            2050 => 'Appointment is overlapping with another',
            2060 => 'Can not cancel appointment because of time to cancel',
            2070 => 'No appointment found',
            
            //BlockSchedule
            2080 => 'End date must be greater than start date',
            2090 => 'Start date must be greater or equal to current date',

            //App
            4010 => 'No app found',
            4020 => 'App outdated, same fields',
            4030 => 'Status unchanged, same field'
        );
        
		$result = (isset($codes[$code])) ? $codes[$code] : 'Unknown Status Code';

		return $result;
	}
}
