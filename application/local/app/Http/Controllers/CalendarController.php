<?php

/**
 * Controller Calendar
 * 
 * @author Geovanni Escalante <gescalante@arkho.tech>
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\CalendarRepository;
use App\Response as Resp;
use Validator;

class CalendarController extends Controller
{
    /**
     * Instancia de CalendarRepository
     *
     * @var CalendarRepository
     */
    protected $calendars;

    /**
     * Crea una nueva instancia Controller
     *
     * @param CalendarRepository  $calendars
     * @return void
     */
    public function __construct(CalendarRepository $calendars)
    {
        $this->calendars = $calendars;
    }
    
    /**
     * Controller que despliega listado de calendarios
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {  
        $appkey = $request->header('appkey');
        $domain = $request->header('domain');
        $page = $request->input('page', 0);
        $resp = array();
        
        if (!empty($appkey) && !empty($domain)) {
            $calendars = $this->calendars->listCalendar($appkey, $domain, $page);
            
            if (isset($calendars['error']) && is_a($calendars['error'], 'Exception')) {
                $resp = Resp::error(500, $calendars['error']->getCode(), '', $calendars['error']);
            } else {
                if (count($calendars['data']) > 0) {                    
                    $calendar['calendars'] = $calendars['data'];
                    $calendar['count'] = $calendars['count'];
                    $resp = Resp::make(200, $calendar);
                } else {
                    $resp = Resp::error(404, 1010);
                }
            }
        } else {
            $resp = Resp::error(400, 1000);
        }
        
        return $resp;
    }
    
    /**
     * Controller que despliega un calendario por ID
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
                $calendars = $this->calendars->listCalendarById($appkey, $domain, $id);

                if (isset($calendars['error']) && is_a($calendars['error'], 'Exception')) {
                    $resp = Resp::error(500, $calendars['error']->getCode(), '', $calendars['error']);
                } else {
                    if (count($calendars['data']) > 0) {
                        $calendar['calendars'] = $calendars['data'];
                        $calendar['count'] = $calendars['count'];
                        $resp = Resp::make(200, $calendar);
                    } else {
                        $resp = Resp::error(404, 1010);
                    }
                }
            } else {
                $resp = Resp::error(400, 1020, 'calendar_id param must be greater than zero');
            }
        } else {
            $resp = Resp::error(400, 1000);
        }
        
        return $resp;
    }
    
    /**
     * Controller que despliega un calendario por coincidencia de nombre
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function searchByName(Request $request)
    {        
        $appkey = $request->header('appkey');
        $domain = $request->header('domain');
        $text = $request->input('text', '');
        $resp = array();
        
        if (!empty($appkey) && !empty($domain)) {
            if (!empty($text)) {
                $calendars = $this->calendars->searchByName($appkey, $domain, $text);

                if (isset($calendars['error']) && is_a($calendars['error'], 'Exception')) {
                    $resp = Resp::error(500, $calendars['error']->getCode(), '', $calendars['error']);
                } else {
                    if (count($calendars['data']) > 0) {
                        $calendar['calendars'] = $calendars['data'];
                        $calendar['count'] = $calendars['count'];
                        $resp = Resp::make(200, $calendar);
                    } else {
                        $resp = Resp::error(404, 1010);
                    }
                }
            } else {
                $resp = Resp::error(400, 1020, 'text param do not exist or is empty');
            }
        } else {
            $resp = Resp::error(400, 1000);
        }
        
        return $resp;
    }

    /**
     * Controller que despliega los calendarios por Owner Id
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function listByOwner(Request $request, $id)
    {
        $resp = array();
        $appkey = $request->header('appkey');
        $domain = $request->header('domain');
        
        if (!empty($appkey) && !empty($domain)) {
            if ((int)$id > 0) {
                $calendars = $this->calendars->listByOwnerId($appkey, $domain, $id);

                if (isset($calendars['error']) && is_a($calendars['error'], 'Exception')) {
                    $resp = Resp::error(500, $calendars['error']->getCode(), '', $calendars['error']);
                } else {
                    if (count($calendars['data']) > 0) {
                        $calendar['calendars'] = $calendars['data'];
                        $calendar['count'] = $calendars['count'];
                        $resp = Resp::make(200, $calendar);
                    } else {
                        $resp = Resp::error(404, 1010);
                    }
                }
            } else {
                $resp = Resp::error(400, 1020, 'owner_id param must be greater than zero');
            }
        } else {
            $resp = Resp::error(400, 1000);
        }
        
        return $resp;
    }
    
    /**
     * Crea un nuevo registro de tipo calendario
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
        $data['appkey'] = $appkey;
        $data['domain'] = $domain;
        
        if (!empty($appkey) && !empty($domain)) {
            $validator = Validator::make($data, [
                'name' => 'bail|required|max:80',
                'owner_id' => 'bail|required|max:20',
                'owner_name' => 'bail|required|max:150',
                'owner_email' => 'bail|required|email|max:150',
                'is_group' => 'required|boolean',
                'schedule' => 'required',
                'time_attention' => 'bail|required|integer',
                'concurrency' => 'bail|required|integer',
                'ignore_non_working_days' => 'required|boolean',
                'time_cancel_appointment' => 'required|integer',
                'time_confirm_appointment' => 'required|integer',                
                'appkey' => 'bail|required|max:15',
                'domain' => 'bail|required|max:150'
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
                $validate = $this->validateSchedule($data['schedule']);
                if (!$validate) {
                    return Resp::error(400, 1020, 'input param schedule missing or malformed');
                }

                $data['schedule'] = serialize($data['schedule']);
                $calendars = $this->calendars->createCalendar($appkey, $domain, $data);

                if (isset($calendars['error']) && is_a($calendars['error'], 'Exception')) {                
                    $resp = Resp::error(500, $calendars['error']->getCode(), '', $calendars['error']);
                } else {
                    $id = isset($calendars['id']) ? (int)$calendars['id'] : 0;
                    $resp = Resp::make(201, array('id' => $id));
                }
            }
        } else {
            return Resp::error(400, 1000);
        }
            
        return $resp;
    }

    /**
     * Actualiza un registro de tipo calendario.
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
            $validator = Validator::make($data, [
                'name' => 'bail|required|max:80',
                'owner_id' => 'bail|required|max:20',
                'owner_name' => 'bail|required|max:150',
                'is_group' => 'required|boolean',
                'schedule' => 'required',
                'time_attention' => 'bail|required|integer',
                'concurrency' => 'bail|required|integer',
                'ignore_non_working_days' => 'required|boolean',
                'time_cancel_appointment' => 'required|integer',
                'time_confirm_appointment' => 'required|integer'
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
                $validate = $this->validateSchedule($data['schedule']);
                if (!$validate) {
                    return Resp::error(400, 1020, 'input param schedule missing or malformed');
                }

                $data['schedule'] = serialize($data['schedule']);
                $calendars = $this->calendars->updateCalendar($appkey, $domain, $data, $id);

                if (isset($calendars['error']) && is_a($calendars['error'], 'Exception')) {                
                    $resp = Resp::error(500, $calendars['error']->getCode(), '', $calendars['error']);
                } else {
                    $id = isset($calendars['id']) ? (int)$calendars['id'] : 0;
                    $resp = Resp::make(200);
                }
            }
        } else {
            return Resp::error(400, 1000);
        }
        
        return $resp;
    }

    /**
     * Deshabilita un registro de tipo calendar
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function disable(Request $request, $id)
    {
        $appkey = $request->header('appkey');
        $domain = $request->header('domain');        
        $resp = array();
        
        if (!empty($appkey) && !empty($domain)) {
            if ((int)$id <= 0) {
                $resp = Resp::error(400, 1020, 'calendar_id param must be greather than zero');
            } else {
                $calendars = $this->calendars->disableCalendar($appkey, $domain, $id);

                if (isset($calendars['error']) && is_a($calendars['error'], 'Exception')) {                
                    $resp = Resp::error(500, $calendars['error']->getCode(), '', $calendars['error']);
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
     * Valida si el campo schedule es valido
     * 
     * @param array $schedule
     * @return boolean
     */
    public function validateSchedule($schedule)
    {                     
        $days = array('lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado', 'domingo');

        foreach ($schedule as $key => $value) {

            //Verifico que sea un dia valido
            if (!in_array($key, $days))
                return false;

            foreach ($value as $k => $time) {                    
                $val = explode('-', $time);
                $count = is_array($val) ? count($val) : 0;

                //Verifico que venga un rango de horas
                if ($count == 2) {
                    $start = preg_match('#([0-1]{1}[0-9]{1}|[2]{1}[0-3]{1}):[0-5]{1}[0-9]{1}#', $val[0]);
                    $end = preg_match('#([0-1]{1}[0-9]{1}|[2]{1}[0-3]{1}):[0-5]{1}[0-9]{1}#', $val[1]);
                    
                    $diff = date('H:i:s', strtotime('00:00:00') + strtotime($val[1]) - strtotime($val[0]));
                    if ((int)$diff > (int)config('calendar.time_max_schedule')) {
                        return false; 
                    }
                    
                    //Verifico que sea una hora valida ej: 18:00
                    if ($start !== 1 || $end !== 1) {
                        return false;
                    }
                } else {
                    return false;
                }
            }
        }
        
        return true;
    }
}
