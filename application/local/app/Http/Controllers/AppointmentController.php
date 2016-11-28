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
     * Controller que despliega una cita por ID
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function findById(Request $request, $id)
    {
        $resp = array();
        $appkey = $request->header('appkey');
        $domain = $request->header('domain');
        
        if (!empty($appkey) && !empty($domain)) {
            if ((int)$id > 0) {
                $appointments = $this->appointments->listAppointmentById($appkey, $domain, $id);

                if (isset($appointments['error']) && is_a($appointments['error'], 'Exception')) {
                    $resp = Resp::error(500, $appointments['error']->getCode(), '', $appointments['error']);
                } else {
                    if (count($appointments['data']) > 0) {
                        $appointment['appointments'] = $appointments['data'];
                        $appointment['count'] = $appointments['count'];
                        $resp = Resp::make(200, $appointment);
                    } else {
                        $resp = Resp::error(404, 2072);
                    }
                }
            } else {
                $resp = Resp::error(400, 1020, 'appointment_id debe ser mayor a cero');
            }
        } else {
            $resp = Resp::error(400, 1000);
        }
        
        return $resp;
    }
    
    /**
     * Controller que despliega listado de citas por calendario
     *
     * @param  \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function listByCalendar(Request $request, $id)
    {
        $appkey = $request->header('appkey');
        $domain = $request->header('domain');
        $page = $request->input('page', 0);
        $records = $request->input('records', 0);
        $resp = array();
        
        if (!empty($appkey) && !empty($domain)) {
            $appointments = $this->appointments->listAppointmentsByCalendarId($appkey, $domain, $id, $page, $records);
            
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
    public function listByApplier(Request $request, $id)
    {
        $appkey = $request->header('appkey');
        $domain = $request->header('domain');
        $page = $request->input('page', 0);
        $records = $request->input('records', 0);
        $resp = array();
        
        if (!empty($appkey) && !empty($domain)) {
            $appointments = $this->appointments->listAppointmentsByApplierId($appkey, $domain, $id, $page, $records);
            
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
        $records = $request->input('records', 0);
        $resp = array();
        
        if (!empty($appkey) && !empty($domain)) {
            $appointments = $this->appointments->listAppointmentsByOwnerId($appkey, $domain, $id, $page, $records);
            
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
     * Controller que despliega listado de citas por calendario
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
     * Controller que despliega listado de citas por propietario
     *
     * @param  \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function listAvailabilityByOwner(Request $request, $id)
    {
        $appkey = $request->header('appkey');
        $domain = $request->header('domain');
        $date = $request->input('date', null);
        $resp = array();
        
        if (!empty($appkey) && !empty($domain)) {
            $cal = new CalendarRepository();
            $calendars = $cal->listByOwnerId($appkey, $domain, $id, 0, 0);

            if ($calendars['error'] === null && $calendars['count'] > 0) {                
                $appointments = $this->appointments->listAppointmentsAvailabilityByOwner($appkey, $domain, $id, $date, $calendars['data']);

                if (isset($appointments['error']) && is_a($appointments['error'], 'Exception')) {
                    $resp = Resp::error(500, $appointments['error']->getCode(), '', $appointments['error']);
                } else {
                    if (count($appointments) > 0) {                        
                        $resp = Resp::make(200, $appointments);
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
                'applier_email' => 'bail|required|email|max:80',
                'applier_id' => 'bail|required|max:20',
                'applier_name' => 'bail|required|max:150',
                'appointment_start_time' => 'bail|required|isodate',
                'calendar_id' => 'bail|required|integer',
                'subject' => 'max:80',
                'metadata' => 'max:255'
            ]);
            
            if (isset($data['metadata'])) {
                $data['metadata'] = json_encode($data['metadata']);
            } else {
                return Resp::error(400, 1020, 'El campo metadata debe tener un json válido');
            }
        
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
                    $isOverlapping = $this->appointments->isOverlappingAppointmentByUser($appkey, $domain, $data['calendar_id'], $data['applier_id'], $data['appointment_start_time']);
                    
                    if ($isOverlapping) {
                        return Resp::error(400, 1020, 'Ya tiene una cita reservada para este día');
                    } else {
                        $appointment = $this->appointments->createAppointment($appkey, $domain, $data);
                    }
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
                    'applier_email' => 'bail|required|email|max:80',
                    'applier_id' => 'max:20',
                    'applier_name' => 'max:150',
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
     * Confirma citas masivamente
     *
     * @param  \Illuminate\Http\Request $request     
     * @return \Illuminate\Http\Response
     */
    public function bulkConfirm(Request $request)
    {        
        $resp = array();
        $data = $request->json()->all();
        $appkey = $request->header('appkey');
        $domain = $request->header('domain');
        $ids = $data['ids'];
        $calendar_id = 0;
        $appointment_start_time = '';
        
        if (!empty($appkey) && !empty($domain)) {
        
            $validator = Validator::make($data, [
                'ids' => 'bail|required|array'
            ]);

            if ($validator->fails()) {                        
                $messages = $validator->errors();
                $message = '';            
                foreach ($messages->all() as $key => $msg) {
                    $message = $msg;
                    break;
                }

                $resp = Resp::error(400, 1020, $message);
            } else {
            
                foreach ($ids as $id) {
                    // Se validan las citas                
                    $appointment = $this->appointments->listAppointmentById($appkey, $domain, $id);
                    if (isset($appointment['data']) && (int)$appointment['count'] > 0) {
                        foreach ($appointment['data'] as $a) {            
                            $calendar_id = (int)$a->calendar_id;
                            $appointment_start_time = $a->appointment_start_time;
                        }

                        if ( $calendar_id > 0 && $appointment_start_time) {
                            $validate = $this->appointments->isValidAppointment($appkey, $domain, $calendar_id, $appointment_start_time, $id);

                            if (!$validate['is_ok']) {                    
                                return Resp::error(406, $validate['error_code'], Resp::getCustomMessage($validate['error_code']) . ';' . $id);
                            }
                        }                    
                    } else {            
                        return Resp::error(404, 2070, Resp::getCustomMessage(2070) . ';' . $id);
                    }
                }

                // Se confirman las citas
                $appointment = $this->appointments->bulkConfirmAppointment($appkey, $domain, $ids);

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
                    $now = strtotime(date('Y-m-d H:i:s'));
                    $start_date = strtotime($appointment_start_time);
                    $diff = ($start_date - $now)/60;
                    if (floor($diff) >= floor($time_to_cancel*60)) {
                        $appointment = $this->appointments->cancelAppointment($appkey, $domain, $id, $data);

                        if (isset($appointment['error']) && is_a($appointment['error'], 'Exception')) {                
                            $resp = Resp::error(500, $appointment['error']->getCode(), '', $appointment['error']);
                        } else {                    
                            $resp = Resp::make(200);
                        }
                    } else {
                        $resp = Resp::error(406, 2060, 'No se puede cancelar la cita porque expiró el tiempo máximo. '.$time_to_cancel.' horas de anticipación');
                    }
                }
            }
        } else {
            return Resp::error(400, 1000);
        }
        
        return $resp;
    }
    
    /**
     * Elimina todas las citas reservadas pendientes por confirmar
     *      
     * @return \Illuminate\Http\Response
     */
    public function destroyAppointmentsPendingToConfirm()
    {        
        $resp = array();
        
        $appointments = $this->appointments->deleteAppointmentsPendingToConfirm();

        if (isset($appointments['error']) && is_a($appointments['error'], 'Exception')) {                
            $resp = Resp::error(500, $appointments['error']->getCode(), '', $appointments['error']);
        } else {
            $resp = Resp::make(200);
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
                'applier_attended' => 'bail|required|boolean'
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
