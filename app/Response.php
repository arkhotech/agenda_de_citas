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
        $c[] = array(
            'response' => array(
                'code' => $code,
                'message' => self::getStatusCode($code)
            )
        );
        array_push($c, $data);
        
        if ($data !== null) {
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
     * @param mixed Exception/null e
     * @return Illuminate/Http/Response
     */
    public static function error($code, Exception $e = null)
    {        
        if ($code === 500) {
            if ($e !== null && is_a($e, 'Exception')) {
                Log::error('code: ' .  $e->getCode() . ' Message: ' . $e->getMessage());
            }
        }
        
        $resp = response([
            'response' => array(
                'code' => $code,
                'message' => self::getStatusCode($code)
            )], $code
        );
        
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
}
