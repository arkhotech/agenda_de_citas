<?php

/**
 * Clase MailService, Envia correos electronicos mediante un webservice
 * 
 * @author Geovanni Escalante <gescalante@arkho.tech>
 */

namespace App;

use Log;
use App\Repositories\AppRepository;
use App\Repositories\AppointmentRepository;
use Illuminate\Support\Facades\Cache;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use \Httpful\Request;

class MailService { 
    
    /**
     * configura todos los parametros para enviar el correo electronico
     * 
     * @param string $appkey
     * @param string $domain
     * @param int $appointment_id
     * @param string $template_type
     * @return array
     */
    public function setEmail($appkey, $domain, $appointment_id, $template_type)
    {
        $res['error'] = false;
        $res['errorMessage'] = '';
        
        if (!empty($appkey) && !empty($domain) && (int)$appointment_id > 0 && 
            !empty($template_type)) {
            try {
                $app_obj = new AppRepository();
                $app = $app_obj->listApp($appkey, $domain);
                
                if (isset($app['error']) && $app['error'] !== null) {
                    $res['error'] = true;
                    $res['errorMessage'] = 'Hubo un error al obtener información de la app. Descripción: ' . $app['error']->getMessage();
                } else {                    
                    if (isset($app['data']) && (int)$app['count'] > 0) {
                        foreach ($app['data'] as $_app) {            
                            $nombre_app = $_app->name;
                            $from_email = $_app->from_email;
                            
                            switch ($template_type) {
                                case 'confirmation':
                                    $html_applyer = $_app->html_confirmation_email;
                                    $html_owner = $_app->html_confirmation_email;
                                    $subject = base64_encode(config('calendar.subject_confirmation_email'));
                                    $html_text = 'confirmación';
                                    break;
                                case 'modify':
                                    $html_applyer = $_app->html_modify_email;
                                    $html_owner = $_app->html_modify_email;
                                    $subject = base64_encode(config('calendar.subject_modify_email'));
                                    $html_text = 'modificación';
                                    break;
                                case 'cancel':
                                    $html_applyer = $_app->html_cancel_email;
                                    $html_owner = $_app->html_cancel_email;
                                    $subject = base64_encode(config('calendar.subject_cancel_email'));
                                    $html_text = 'cancelación';
                                    break;
                                default :               
                            }
                        } 
                        
                        if (empty($html_applyer)) {
                            $res['error'] = true;
                            $res['errorMessage'] = 'Debe configurar la plantilla de correo electrónico de ' . $html_text;
                        } else {    
                            $appointment = new AppointmentRepository();
                            $appointments = $appointment->listAppointmentById($appkey, $domain, $appointment_id);

                            if (isset($appointments['error']) && $appointments['error'] !== null) {
                                $res['error'] = true;
                                $res['errorMessage'] = 'Hubo un error al obtener información de la cita. Descripción: ' . $appointments['error']->getMessage();
                            } else {
                                if (isset($appointments['data']) && (int)$appointments['count'] > 0) {
                                    foreach ($appointments['data'] as $a) { 
                                        $date = new \DateTime($a->appointment_start_time);
                                        $dia = $date->format('d');
                                        $mes = $this->monthToSpanish($date->format('M'));
                                        $ano = $date->format('Y');
                                        $hora = $date->format('H:i');
                                        $fecha_cita = $dia . ' de ' . $mes . ' de ' . $ano . ' a las ' . $hora;
                                        $nombre_ciudadano = trim($a->applyer_name);
                                        $nombre_funcionario = trim($a->owner_name);
                                        $nombre_tramite = 'TRÁMITE';
                                        $nombre_agenda = trim($a->name);
                                        $motivo_cancelacion = !empty($a->cancelation_cause) ? trim($a->cancelation_cause) : 'No indicado';
                                        $applyer_email = trim($a->applyer_email);
                                        $owner_email =  trim($a->owner_email);

                                        if (!empty($a->metadata)) {
                                            $metadata = json_decode($a->metadata, true);
                                            if (isset($metadata['nombre_tramite']) && !empty($metadata['nombre_tramite'])) {
                                                $nombre_tramite = trim($metadata['nombre_tramite']);
                                            }
                                        }

                                        $search = array(
                                            '{{nombre_app}}' ,
                                            '{{nombre}}',
                                            '{{dia}}',
                                            '{{mes}}',
                                            '{{ano}}',
                                            '{{hora}}',
                                            '{{fecha_cita}}',                                        
                                            '{{nombre_tramite}}',
                                            '{{nombre_agenda}}',
                                            '{{motivo_cancelacion}}',
                                        );
                                        $owner_replace = array(
                                            $nombre_app,
                                            $nombre_funcionario,
                                            $dia,
                                            $mes,
                                            $ano,
                                            $hora,
                                            $fecha_cita,
                                            $nombre_tramite,
                                            $nombre_agenda,
                                            $motivo_cancelacion,
                                        );
                                        $body = base64_encode(str_replace($search, $owner_replace, $html_owner));

                                        $send_mail_owner = $this->sendMail($from_email, $subject, $body, array($owner_email));
                                        if (!$send_mail_owner['error']) {
                                            $applyer_replace = array(
                                                $nombre_app,
                                                $nombre_ciudadano,
                                                $dia,
                                                $mes,
                                                $ano,
                                                $hora,
                                                $fecha_cita,
                                                $nombre_tramite,
                                                $nombre_agenda,
                                                $motivo_cancelacion,
                                            );
                                            $body = base64_encode(str_replace($search, $applyer_replace, $html_applyer));
                                            $send_mail_applyer = $this->sendMail($from_email, $subject, $body, array($applyer_email));
                                            if ($send_mail_applyer['error']) {
                                                $res['error'] = true;
                                                $res['errorMessage'] = $send_mail_applyer['errorMessage'];
                                            }
                                        } else {
                                            $res['error'] = true;
                                            $res['errorMessage'] = $send_mail_owner['errorMessage'];
                                        }
                                    }
                                } else {
                                    $res['error'] = true;
                                    $res['errorMessage'] = 'El Id de la cita enviada no se encontró en la base de datos';
                                }
                            }
                        }
                    } else {
                        $res['error'] = true;
                        $res['errorMessage'] = 'La appkey y/o domain no se encontraron en la base de datos';
                    }
                }
            } catch (Exception $ex) {
                $res['error'] = true;
                $res['errorMessage'] = 'code: ' .  $ex->getCode() . ' Message: ' . $ex->getMessage();
            }            
        } else {
            $res['error'] = true;
            $res['errorMessage'] = 'Faltan parámetros o tipo de dato incorrecto';
        }
        
        if ($res['error']) {
            Log::error($res['errorMessage']);
        }
        
        return $res;
    }
    
    /**
     * Envia un correo electronico
     * 
     * @param string $from
     * @param string $subject
     * @param string $body
     * @param array $to
     * @return response
     */
    public function sendMail($from, $subject, $body, $to)
    {
        $res['error'] = false;
        $res['errorMessage'] = false;
        
        try {
            
            $client_id = config('calendar.client_id_send_mail');
            $client_secret = config('calendar.client_secret_send_mail');            
            $urlAccessToken = config('calendar.endpoint_service_get_token_sendmail');
            $token_app = config('calendar.token_app_send_mail');
            $path = config('calendar.path_send_email_test');
            $urlService = config('calendar.endpoint_service_sendmail');
            $urlSendMail = $urlService . $path;
            $client = new Client();        
            $cache_id = 'cacheGetTokenSendMail';            
            $response_token = Cache::get($cache_id);
            $resp = array();
            $access_token = '';
            
            if ($response_token === null) {                
                
                // Obtiene el access token
                $response_token = $client->request('POST', $urlAccessToken, [
                    'form_params' => [
                        'client_id' => $client_id,
                        'client_secret' => $client_secret,
                        'scope' => 'sendmail',
                        'grant_type' => 'client_credentials'
                    ]
                ]);
                
                $resp = json_decode($response_token->getBody(), true);
                if ($response_token->getStatusCode() == 200) {                    
                    $ttl = (int)$resp['expires_in']/60;
                    Cache::put($cache_id, $resp, $ttl);
                    $access_token = $resp['access_token'];
                }
            } else {                
                $access_token = $response_token['access_token'];                
            }            
            
            if (!empty($access_token)) {
                
                // Envio el correo
                $body = array(
                    'from' => $from,
                    'subject' => $subject,
                    'body' => $body,
                    'to' => $to,
                    'token_app' => $token_app,
                    'test_email_receiver' => array('gescalante@arkho.tech')
                );
                
                $response = Request::post($urlSendMail)
                    ->addHeaders(array('Authorization' => 'Bearer ' . $access_token))
                    ->expectsJson()
                    ->sendsJson()
                    ->body(json_encode($body))
                    ->send();
                
                if ($response->code == 401 && isset($response->body->error) &&
                        $response->body->error == 'invalid_token') {                    
                    Cache::forget($cache_id);
                    $this->sendMail($from, $subject, $body, $to);
                } else if ($response->code != 200) {
                    $res['error'] = true;
                    $res['errorMessage'] = isset($response->body->error_description) ? $response->body->error_description : $response->body->error;
                }
            } else {
                $res['error'] = true;
                $res['errorMessage'] = $resp['error_description'];
            }
        } catch (ClientException $ce) {
            $res['error'] = true;
            $res['errorMessage'] = $ce->getMessage();
        }
        catch (Exception $ex) {
            $res['error'] = true;
            $res['errorMessage'] = $ex->getMessage();
        }
        
        return $res;
    }
    
    /**
     * Devuelve el mes en espanol
     * 
     * @param string $month
     * @return mixed string/boolean
     */
    private function monthToSpanish($month)
    {
        $m = array(
            'Jan' => 'Ene',
            'Feb' => 'Feb',
            'Mar' => 'Mar',
            'Apr' => 'Abr',
            'May' => 'May',
            'Jun' => 'Jun',
            'Jul' => 'Jul',
            'Aug' => 'Ago',
            'Sep' => 'Sep',
            'Oct' => 'Oct',
            'Nov' => 'Nov',
            'Dec' => 'Dic',
        );
        
        return isset($m[$month]) ? $m[$month] : false;
    }
}
