<?php

/**
 * Controller Appointment
 * 
 * @author Geovanni Escalante <gescalante@arkho.tech>
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\AppointmentRepository;
use App\Repositories\CalendarRepository;
use App\Response as Resp;
use Validator;

class AppointmentController extends Controller
{
    /**
     * Instancia de AppointmentRepository
     *
     * @var AppointmentRepository
     */
    protected $appointments;

    /**
     * Crea una nueva instancia Controller
     *
     * @param AppointmentRepository  $appointments
     * @return void
     */
    public function __construct(AppointmentRepository $appointments)
    {
        $this->appointments = $appointments;
    }
    
    /**
     * Controller que despliega listado de citas por solicitante
     *
     * @param  \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function listByApplyer(Request $request, $id)
    {  
        $appkey = $request->header('appkey');
        $domain = $request->header('domain');
        $page = $request->input('page', 0);
        $resp = array();
        
        if (!empty($appkey) && !empty($domain)) {
            $appointments = $this->appointments->listAppointmentsByApplyerId($appkey, $domain, $id, $page);
            
            if (isset($appointments['error']) && is_a($appointments['error'], 'Exception')) {
                $resp = Resp::error(500, $appointments['error']->getCode(), '', $appointments['error']);
            } else {
                if (count($appointments['data']) > 0) {                    
                    $appointment['appointments'] = $appointments['data'];
                    $appointment['count'] = $appointments['count'];
                    $resp = Resp::make(200, $appointment);
                } else {
                    $resp = Resp::error(404, 2070);
                }
            }
        } else {
            $resp = Resp::error(400, 1000);
        }
        
        return $resp;
    }
    
    /**
     * Controller que despliega listado de citas por propietario de agenda
     *
     * @param  \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function listByOwner(Request $request, $id)
    {  
        $appkey = $request->header('appkey');
        $domain = $request->header('domain');
        $page = $request->input('page', 0);
        $resp = array();
        
        if (!empty($appkey) && !empty($domain)) {
            $appointments = $this->appointments->listAppointmentsByOwnerId($appkey, $domain, $id, $page);
            
            if (isset($appointments['error']) && is_a($appointments['error'], 'Exception')) {
                $resp = Resp::error(500, $appointments['error']->getCode(), '', $appointments['error']);
            } else {
                if (count($appointments['data']) > 0) {                    
                    $appointment['appointments'] = $appointments['data'];
                    $appointment['count'] = $appointments['count'];
                    $resp = Resp::make(200, $appointment);
                } else {
                    $resp = Resp::error(404, 2070);
                }
            }
        } else {
            $resp = Resp::error(400, 1000);
        }
        
        return $resp;
    }
    
    /**
     * Controller que despliega listado de citas por solicitante
     *
     * @param  \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function listAvailability(Request $request, $id)
    {        
        $appkey = $request->header('appkey');
        $domain = $request->header('domain');
        $date = $request->input('date', null);
        $resp = array();
        
        if (!empty($appkey) && !empty($domain)) {
            $cal = new CalendarRepository();
            $calendars = $cal->listCalendarById($appkey, $domain, $id);
            
            if ($calendars['error'] === null && $calendars['count'] > 0) {                
                $appointments = $this->appointments->listAppointmentsAvailability($appkey, $domain, $id, $date, $calendars['data']);

                if (isset($appointments['error']) && is_a($appointments['error'], 'Exception')) {
                    $resp = Resp::error(500, $appointments['error']->getCode(), '', $appointments['error']);
                } else {
                    if (count($appointments['data']) > 0) {
                        $appointment['owner_name'] = $appointments['owner_name'];
                        $appointment['concurrency'] = $appointments['concurrency'];
                        $appointment['appointmentsavailable'] = $appointments['data'];
                        
                        $resp = Resp::make(200, $appointment);
                    } else {
                        $resp = Resp::error(404, 2070);
                    }
                }
            } else {
                $resp = Resp::error(404, 1010);
            }
        } else {
            $resp = Resp::error(400, 1000);
        }
        
        return $resp;
    }
    
    /**
     * Crea un nuevo registro de tipo appointment
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $resp = array();
        $data = $request->json()->all();
        $appkey = $request->header('appkey');
        $domain = $request->header('domain');
        
        if (!empty($appkey) && !empty($domain)) {
            $validator = Validator::make($data, [
                'applyer_email' => 'bail|required|email|max:80',
                'applyer_id' => 'max:20',
                'applyer_name' => 'max:150',
                'appointment_start_time' => 'bail|required|isodate',
                'calendar_id' => 'bail|required|integer',
                'subject' => 'max:80'
            ]);

            if ($validator->fails()) {
                $messages = $validator->errors();
                $message = '';            
                foreach ($messages->all() as $msg) {
                    $message = $msg;
                    break;
                }

                $resp = Resp::error(400, 1020, $message);
            } else {            
                $validate = $this->appointments->isValidAppointment($appkey, $domain, $data['calendar_id'], $data['appointment_start_time']);
                if (!$validate['is_ok']) {                    
                    return Resp::error(406, $validate['error_code']);
                } else {                    
                    $appointment = $this->appointments->createAppointment($appkey, $domain, $data);
                }
                
                if (isset($appointment['error']) && is_a($appointment['error'], 'Exception')) {                
                    $resp = Resp::error(500, $appointment['error']->getCode(), '', $appointment['error']);
                } else {                
                    $id = isset($appointment['id']) ? (int)$appointment['id'] : 0;
                    $resp = Resp::make(201, array('id' => $id));
                }
            }
        } else {
            return Resp::error(400, 1000);
        }
        
        return $resp;
    }
    
    /**
     * Actualiza un registro de tipo appointment
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $resp = array();
        $data = $request->json()->all();
        $appkey = $request->header('appkey');
        $domain = $request->header('domain');
        
        if (!empty($appkey) && !empty($domain)) {
            if ((int)$id > 0) {
                $validator = Validator::make($data, [
                    'applyer_email' => 'bail|required|email|max:80',
                    'applyer_id' => 'max:20',
                    'applyer_name' => 'max:150',
                    'appointment_start_time' => 'bail|required|isodate',
                    'calendar_id' => 'bail|required|integer',
                    'subject' => 'max:80'
                ]);

                if ($validator->fails()) {
                    $messages = $validator->errors();
                    $message = '';            
                    foreach ($messages->all() as $msg) {
                        $message = $msg;
                        break;
                    }

                    $resp = Resp::error(400, 1020, $message);
                } else {                    
                    $validate = $this->appointments->isValidAppointment($appkey, $domain, $data['calendar_id'], $data['appointment_start_time'], $id);
                    if (!$validate['is_ok']) {                    
                        return Resp::error(406, $validate['error_code']);
                    } else {                    
                        $appointment = $this->appointments->updateAppointment($appkey, $domain, $id, $data);
                    }

                    if (isset($appointment['error']) && is_a($appointment['error'], 'Exception')) {                
                        $resp = Resp::error(500, $appointment['error']->getCode(), '', $appointment['error']);
                    } else {                        
                        $resp = Resp::make(200);
                    }
                }
            }
        } else {
            return Resp::error(400, 1000);
        }
        
        return $resp;
    }
    
    /**
     * Confirma una cita
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function confirm(Request $request, $id)
    {
        $resp = array();
        $data = $request->json()->all();
        $appkey = $request->header('appkey');
        $domain = $request->header('domain');
        $calendar_id = 0;
        $appointment_start_time = '';
        $id = (int)$id;
        
        $appointment = $this->appointments->listAppointmentById($appkey, $domain, $id);        
        if (isset($appointment['data']) && (int)$appointment['count'] > 0) {
            foreach ($appointment['data'] as $a) {            
                $calendar_id = (int)$a->calendar_id;
                $appointment_start_time = $a->appointment_start_time;
            }
            
            if (!empty($appkey) && !empty($domain)) {                
                if ( $calendar_id > 0 && $appointment_start_time) {
                    $validate = $this->appointments->isValidAppointment($appkey, $domain, $calendar_id, $appointment_start_time, $id);

                    if (!$validate['is_ok']) {                    
                        return Resp::error(406, $validate['error_code']);
                    } else {                    
                        $appointment = $this->appointments->confirmAppointment($appkey, $domain, $id, $data);
                    }

                    if (isset($appointment['error']) && is_a($appointment['error'], 'Exception')) {                
                        $resp = Resp::error(500, $appointment['error']->getCode(), '', $appointment['error']);
                    } else {                    
                        $resp = Resp::make(200);
                    }
                }
            } else {
                return Resp::error(400, 1000);
            }
        } else {
            return Resp::error(404, 2070);
        }
        
        return $resp;
    }
    
    /**
     * Cancela una cita
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function cancel(Request $request, $id)
    {
        $resp = array();
        $data = $request->json()->all();
        $appkey = $request->header('appkey');
        $domain = $request->header('domain');
        $time_to_cancel = 0;
        $appointment_start_time = '';
        
        if (!empty($appkey) && !empty($domain)) {
            $validator = Validator::make($data, [
                'user_id_cancel' => 'bail|required|max:20',
                'user_name_cancel' => 'bail|required|max:150'
            ]);

            if ($validator->fails()) {
                $messages = $validator->errors();
                $message = '';            
                foreach ($messages->all() as $msg) {
                    $message = $msg;
                    break;
                }

                $resp = Resp::error(400, 1020, $message);
            } else {
                $appointment = $this->appointments->listCalendarByAppointmentId($appkey, $domain, $id);        
                if (isset($appointment['data'])) {
                    foreach ($appointment['data'] as $a) {            
                        $time_to_cancel = (int)$a->time_cancel_appointment;
                        $appointment_start_time = $a->appointment_start_time;
                    }
                }
                
                if ($appointment_start_time) {
                    $now = new \DateTime(date('Y-m-d H:i:s'));
                    $start_date = new \DateTime($appointment_start_time);
                    $diff = $now->diff($start_date);
                    if ($diff->format('%R%h') >= $time_to_cancel) {                            
                        $appointment = $this->appointments->cancelAppointment($appkey, $domain, $id, $data);

                        if (isset($appointment['error']) && is_a($appointment['error'], 'Exception')) {                
                            $resp = Resp::error(500, $appointment['error']->getCode(), '', $appointment['error']);
                        } else {                    
                            $resp = Resp::make(200);
                        }
                    } else {
                        $resp = Resp::error(406, 2060, 'Can not cancel appointment because time to cancel calendar must be greather or equal to '.$time_to_cancel.' hours');
                    }
                }
            }
        } else {
            return Resp::error(400, 1000);
        }
        
        return $resp;
    }
    
    /**
     * Elimina un registro de tipo dayoff
     * 
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function destroyAppointmentsPendingToConfirm(Request $request)
    {
        $appkey = $request->header('appkey');
        $domain = $request->header('domain');
        $resp = array();
        
        if (!empty($appkey) && !empty($domain)) {            
            $appointments = $this->appointments->deleteAppointmentsPendingToConfirm($appkey, $domain);

            if (isset($appointments['error']) && is_a($appointments['error'], 'Exception')) {                
                $resp = Resp::error(500, $appointments['error']->getCode(), '', $appointments['error']);
            } else {
                $resp = Resp::make(200);
            }
        } else {
            return Resp::error(400, 1000);
        }
        
        return $resp;
    }
    
    /**
     * Actualiza una cita a asistio o no
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function assists(Request $request, $id)
    {
        $resp = array();
        $data = $request->json()->all();
        $appkey = $request->header('appkey');
        $domain = $request->header('domain');
        
        if (!empty($appkey) && !empty($domain)) {
            $validator = Validator::make($data, [
                'applyer_attended' => 'bail|required|boolean'
            ]);

            if ($validator->fails()) {
                $messages = $validator->errors();
                $message = '';            
                foreach ($messages->all() as $msg) {
                    $message = $msg;
                    break;
                }

                $resp = Resp::error(400, 1020, $message);
            } else {
                $appointment = $this->appointments->assistsAppointment($appkey, $domain, $id, $data);

                if (isset($appointment['error']) && is_a($appointment['error'], 'Exception')) {                
                    $resp = Resp::error(500, $appointment['error']->getCode(), '', $appointment['error']);
                } else {                    
                    $resp = Resp::make(200);
                }
            }
        } else {
            return Resp::error(400, 1000);
        }
        
        return $resp;
    }
}
