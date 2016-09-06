<?php

/**
 * Repository Calendar
 * 
 * @author Geovanni Escalante <gescalante@arkho.tech>
 */

namespace App\Repositories;

use Log;
use App\Calendar;
use App\App;
use DB;
use Illuminate\Support\Facades\Cache;
use \Illuminate\Database\QueryException;

class CalendarRepository
{
    /**
     * Obtiene todos los calendarios por una appkey y domain
     * 
     * @param string $appkey
     * @param string $domain
     * @param mixed $page int/null
     * @return Collection
     */
    public function listCalendar($appkey, $domain, $page)
    {
        $res = array();
        $page = (int)$page;
        
        try {            
            $ttl = (int)config('calendar.cache_ttl');
            $cache_id = sha1('cacheCalendarList_'.$appkey.'_'.$domain.'_'.$page);
            $tag = sha1($appkey.'_'.$domain);
            $res = Cache::tags($tag)->get($cache_id);
            
            if ($res === null) {
                if ($page !== 0) {                    
                    $per_page = (int)config('calendar.per_page');            

                    $calendars = Calendar::where('appkey', $appkey)
                            ->where('domain', $domain)
                            ->where('status', 1)
                            ->orderBy('name', 'ASC')
                            ->paginate($per_page);
                    
                    $res['data'] = $calendars->items();
                    $res['count'] = $calendars->total();
                } else {
                    $calendars = Calendar::where('appkey', $appkey)
                            ->where('domain', $domain)
                            ->where('status', 1)
                            ->orderBy('name', 'ASC')->get();
                    
                    $res['data'] = $calendars;
                    $res['count'] = $calendars->count();
                }
                $res['error'] = null;
                
                $cal_array = array();
                $i = 0;
                foreach ($res['data'] as $d) {
                    $cal_array[$i]['id'] = $d->id;
                    $cal_array[$i]['name'] = $d->name;
                    $cal_array[$i]['owner_id'] = $d->owner_id;
                    $cal_array[$i]['owner_name'] = $d->owner_name;
                    $cal_array[$i]['owner_email'] = $d->owner_email;
                    $cal_array[$i]['is_group'] = $d->is_group;
                    $cal_array[$i]['schedule'] = @unserialize($d->schedule);
                    $cal_array[$i]['time_attention'] = $d->time_attention;
                    $cal_array[$i]['concurrency'] = $d->concurrency;
                    $cal_array[$i]['ignore_non_working_days'] = $d->ignore_non_working_days;
                    $cal_array[$i]['time_cancel_appointment'] = $d->time_cancel_appointment;
                    $cal_array[$i]['time_confirm_appointment'] = $d->time_confirm_appointment;
                    $cal_array[$i]['appkey'] = $d->appkey;
                    $cal_array[$i]['domain'] = $d->domain;
                    $i++;
                }

                $res['data'] = $cal_array;                
                Cache::tags([$tag])->put($cache_id, $res, $ttl);
            }                
            
        } catch (QueryException $qe) {
            $res['error'] = $qe;
        } catch (Exception $e) {
            $res['error'] = $e;
        }        
        
        return $res;
    }
    
    /**
     * Obtiene todos los calendarios por nombre de agenda o propietario
     * 
     * @param string $appkey
     * @param string $domain
     * @param int $text
     * @return Collection
     */
    public function searchByName($appkey, $domain, $text)
    {
        $res = array();
        
        try {
            $text = trim(strtolower($text));
            $ttl = (int)config('calendar.cache_ttl');
            $cache_id = sha1('cacheCalendarSearchByName_'.$appkey.'_'.$domain.'_'.$this->sanitizeString($text));
            $tag = sha1($appkey.'_'.$domain);
            $res = Cache::tags($tag)->get($cache_id);
            
            if ($res === null) {
                if (!empty($text)) {                    
                    $calendars = Calendar::where(function ($query) use ($appkey, $domain) {
                        return $query->where('appkey', '=', $appkey)
                              ->Where('domain', '=', $domain)
                              ->where('status', 1);
                    })->where(function ($query) use ($text) {
                        return $query->Where('name', 'LIKE', '%'.$text.'%')
                              ->orWhere('owner_name', 'LIKE', '%'.$text.'%');
                    })->orderBy('name', 'asc')->get();
                    
                    $cal_array = array();
                    $i = 0;
                    foreach ($calendars as $d) {
                        $cal_array[$i]['id'] = $d->id;
                        $cal_array[$i]['name'] = $d->name;
                        $cal_array[$i]['owner_id'] = $d->owner_id;
                        $cal_array[$i]['owner_name'] = $d->owner_name;
                        $cal_array[$i]['owner_email'] = $d->owner_email;
                        $cal_array[$i]['is_group'] = $d->is_group;
                        $cal_array[$i]['schedule'] = @unserialize($d->schedule);
                        $cal_array[$i]['time_attention'] = $d->time_attention;
                        $cal_array[$i]['concurrency'] = $d->concurrency;
                        $cal_array[$i]['ignore_non_working_days'] = $d->ignore_non_working_days;
                        $cal_array[$i]['time_cancel_appointment'] = $d->time_cancel_appointment;
                        $cal_array[$i]['time_confirm_appointment'] = $d->time_confirm_appointment;
                        $cal_array[$i]['appkey'] = $d->appkey;
                        $cal_array[$i]['domain'] = $d->domain;
                        $i++;
                    }

                    $res['data'] = $cal_array;                    
                    $res['count'] = $calendars->count();
                    $res['error'] = null;                    
                    
                    Cache::tags([$tag])->put($cache_id, $res, $ttl);
                }
            }
        } catch (QueryException $qe) {
            $res['error'] = $qe;
        } catch (Exception $e) {
            $res['error'] = $e;
        }        
        
        return $res;
    }
    
    /**
     * Obtiene un calendario por ID
     *      
     * @param string $appkey
     * @param string $domain
     * @param int $id     
     * @return Collection
     */
    public function listCalendarById($appkey, $domain, $id)
    {
        $res = array();
        
        try {            
            $ttl = (int)config('calendar.cache_ttl');
            $cache_id = sha1('cacheCalendarListById_'.$id);
            $tag = sha1($appkey.'_'.$domain);
            $res = Cache::tags($tag)->get($cache_id);
            
            if ($res === null) {
                if ((int)$id > 0) {
                    $calendars = Calendar::where('id', $id)->get();
                    
                    $cal_array = array();
                    $i = 0;
                    foreach ($calendars as $d) {
                        $cal_array[$i]['id'] = $d->id;
                        $cal_array[$i]['name'] = $d->name;
                        $cal_array[$i]['owner_id'] = $d->owner_id;
                        $cal_array[$i]['owner_name'] = $d->owner_name;
                        $cal_array[$i]['owner_email'] = $d->owner_email;
                        $cal_array[$i]['is_group'] = $d->is_group;
                        $cal_array[$i]['schedule'] = @unserialize($d->schedule);
                        $cal_array[$i]['time_attention'] = $d->time_attention;
                        $cal_array[$i]['concurrency'] = $d->concurrency;
                        $cal_array[$i]['ignore_non_working_days'] = $d->ignore_non_working_days;
                        $cal_array[$i]['time_cancel_appointment'] = $d->time_cancel_appointment;
                        $cal_array[$i]['time_confirm_appointment'] = $d->time_confirm_appointment;
                        $cal_array[$i]['appkey'] = $d->appkey;
                        $cal_array[$i]['domain'] = $d->domain;
                        $i++;
                    }

                    $res['data'] = $cal_array;
                    $res['count'] = $calendars->count();
                    $res['error'] = null;                    
                    
                    Cache::tags([$tag])->put($cache_id, $res, $ttl);
                }
            }
        } catch (QueryException $qe) {
            $res['error'] = $qe;
        } catch (Exception $e) {
            $res['error'] = $e;
        }        
        
        return $res;
    }
    
	/**
     * Obtiene un calendario por Owner ID
     *      
     * @param string $appkey
     * @param string $domain
     * @param int $id     
     * @return Collection
     */
    public function listByOwnerId($appkey, $domain, $id)
    {
        $res = array();
        
        try {            
            $ttl = (int)config('calendar.cache_ttl');
            $cache_id = sha1('cacheCalendarListByOwnerId_'.$id);
            $tag = sha1($appkey.'_'.$domain);
            $res = Cache::tags($tag)->get($cache_id);
            
            if ($res === null) {
                if ((int)$id > 0) {
                    $calendars = Calendar::where('owner_id', $id)
                            ->orderBy('name', 'asc')
                            ->get();
                    
                    $cal_array = array();
                    $i = 0;
                    foreach ($calendars as $d) {
                        $cal_array[$i]['id'] = $d->id;
                        $cal_array[$i]['name'] = $d->name;
                        $cal_array[$i]['owner_id'] = $d->owner_id;
                        $cal_array[$i]['owner_name'] = $d->owner_name;
                        $cal_array[$i]['owner_email'] = $d->owner_email;
                        $cal_array[$i]['is_group'] = $d->is_group;
                        $cal_array[$i]['schedule'] = @unserialize($d->schedule);
                        $cal_array[$i]['time_attention'] = $d->time_attention;
                        $cal_array[$i]['concurrency'] = $d->concurrency;
                        $cal_array[$i]['ignore_non_working_days'] = $d->ignore_non_working_days;
                        $cal_array[$i]['time_cancel_appointment'] = $d->time_cancel_appointment;
                        $cal_array[$i]['time_confirm_appointment'] = $d->time_confirm_appointment;
                        $cal_array[$i]['appkey'] = $d->appkey;
                        $cal_array[$i]['domain'] = $d->domain;
                        $i++;
                    }

                    $res['data'] = $cal_array;
                    $res['count'] = 1;
                    $res['error'] = null;                    
                    
                    Cache::tags([$tag])->put($cache_id, $res, $ttl);
                }
            }
        } catch (QueryException $qe) {
            $res['error'] = $qe;
        } catch (Exception $e) {
            $res['error'] = $e;
        }        
        
        return $res;
    }    
    
    /**
     * Crea un nuevo registro de tipo calendario
     * 
     * @param string appkey
     * @param string domain
     * @param array $data     
     * @return Collection
     */
    public function createCalendar($appkey, $domain, $data)
    {
        $res = array();
        
        try {            
            $apps = App::where('appkey', $appkey)
                            ->where('domain', $domain)
                            ->where('status', 1)->value('appkey');
            
            if ($apps) {                
                $data['status'] = 1;
                $calendar = Calendar::create($data);
                $res['id'] = $calendar->id;
                $res['error'] = null;
                
                $tag = sha1($appkey.'_'.$domain);
                Cache::tags($tag)->flush();
            } else {
                $res['error'] = new \Exception('', 1030);
            }
        } catch (QueryException $qe) {
            if ($qe->getCode() == 23000) {
                $res['error'] = new \Exception('', 1040);
            } else {
                $res['error'] = $qe;
            }
        } catch (Exception $e) {
            $res['error'] = $e;
        }
        
        return $res;
    }
    
    /**
     * Actualiza un nuevo registro de tipo calendario
     *
     * @param string appkey
     * @param string domain
     * @param array $data
     * @param int $id     
     * @return Collection
     */
    public function updateCalendar($appkey, $domain, $data, $id)
    {
        $res = array();
        
        try {
            
            if (!$this->hasAvailableAppointments($appkey, $domain, $id)) {
                unset($data['status']);

                $calendar = Calendar::where('id', $id)->update($data);
                $res['error'] = $calendar === false ? new \Exception('', 500) : null;
                
                $tag = sha1($appkey.'_'.$domain);
                Cache::tags($tag)->flush();
            } else {
                $res['error'] = new \Exception('', 1050);
            }
        } catch (QueryException $qe) {
            if ($qe->getCode() == 23000) {
                $res['error'] = new \Exception('', 1040);
            } else {
                $res['error'] = $qe;
            }
        } catch (Exception $e) {
            $res['error'] = $e;
        }
        
        return $res;
    }
    
    /**
     * Deshabilita un registro de tipo calendario
     *      
     * @param string appkey
     * @param string domain
     * @param int $id     
     * @return Collection
     */
    public function disableCalendar($appkey, $domain, $id)
    {
        $res = array();
        
        try {
            
            if (!$this->hasAvailableAppointments($appkey, $domain, $id)) {

                $calendar = Calendar::where('id', $id)->update(array('status' => 0));
                $res['error'] = $calendar === false ? new \Exception('', 500) : null;
                
                $tag = sha1($appkey.'_'.$domain);
                Cache::tags($tag)->flush();
            } else {
                $res['error'] = new \Exception('', 1060);
            }
        } catch (QueryException $qe) {            
                $res['error'] = $qe;
        } catch (Exception $e) {
            $res['error'] = $e;
        }
        
        return $res;
    }
    
    /**
     * Retorna un booleano si un calendario tiene citas disponibles
     *
     * @param string appkey
     * @param string domain
     * @param int $id
     * @return boolean
     */
    public function hasAvailableAppointments($appkey, $domain, $id)
    {
        $resp = true;
        try {
            if ((int)$id > 0) {
                $ttl = (int)config('calendar.cache_ttl');
                $cache_id = sha1('cacheCalendarAppointment_'.$id);
                $tag = sha1($appkey.'_'.$domain);
                $resp = Cache::tags($tag)->get($cache_id);
                
                if ($resp === null) {
                    $results = Calendar::find($id)
                            ->appointments()
                            ->where('is_canceled', '<>', 1)
                            ->where('appointment_start_time', '>=', date('Y-m-d H:i:s'))
                            ->orderBy('appointment_start_time', 'ASC')->get();
                    
                    $resp = $results->count() ? true : false;                    
                    
                    Cache::tags([$tag])->put($cache_id, $resp, $ttl);
                }
            }
        } catch (Exception $e) {
            Log::error('code: ' .  $e->getCode() . ' Message: ' . $e->getMessage());
        }        
        
        return $resp;
    }
    
    /**
     * Retorna un booleano si un calendario tiene citas disponibles
     * en una fecha dada
     * 
     * @param mixed $id null/int
     * @param string appkey
     * @param string domain
     * @return boolean
     */
    public function hasAvailableAppointmentByDate($appkey, $domain, $date)
    {
        $resp = true;
        try {
            if (!empty($appkey) && !empty($domain) && !empty($date)) {
                $ttl = (int)config('calendar.cache_ttl');
                $cache_id = sha1('cacheCalendarAppointmentByDate_'.$appkey.'_'.$domain.'_'.$date);
                $tag = sha1($appkey.'_'.$domain);
                $resp = Cache::tags($tag)->get($cache_id);
                
                if ($resp === null) {
                    $results = Calendar::join('appointments', 'calendars.id', '=', 'appointments.calendar_id')
                        ->select('calendars.id', 'appointment_start_time')
                        ->where('appkey', $appkey)
                        ->where('domain', $domain)
                        ->where('ignore_non_working_days', 0)
                        ->where(DB::raw('DATE(appointment_start_time)'), $date)
                        ->orderBy('appointment_start_time', 'ASC')
                        ->get();
                    
                    $resp = $results->count() ? true : false;                    
                    
                    Cache::tags([$tag])->put($cache_id, $resp, $ttl);
                }
            }
        } catch (Exception $e) {
            Log::error('code: ' .  $e->getCode() . ' Message: ' . $e->getMessage());
        }        
        
        return $resp;
    }
    
    /**
     * Retorna un booleano si la fecha esta dentro del horarios del calendario
     * 
     * @param int $calendar_id
     * @param date $start_date
     * @param int $end_date
     * @return boolean
     */
    public function isIntoSchedule($schedule, $start_date, $end_date)
    {
        $resp = false;
        
        if (is_array($schedule) && $start_date && $end_date) {            
            $date_ini = new \DateTime($start_date);
            $dayIniOfWeek = self::dayOfWeeks($date_ini->format('l'));
            $date_end = new \DateTime($end_date);
            $dayEndOfWeek = self::dayOfWeeks($date_end->format('l'));            
            $sw = false;
            
            foreach ($schedule[$dayIniOfWeek] as $sch) {
                $_date = explode('-', $sch);
                if (is_array($_date) && count($_date) == 2) {
                    $dateIni = $date_ini->format('Y-m-d');
                    $timeObjIni = new \DateTime($dateIni.' '.$_date[0].':00');
                    $timeObjIni = $timeObjIni->format('Y-m-d H:i:s');
                    $dateEnd = $date_end->format('Y-m-d');
                    $timeObjEnd = new \DateTime($dateEnd.' '.$_date[1].':00');                    
                    $timeObjEnd = $timeObjEnd->format('Y-m-d H:i:s');
                    
                    if ($timeObjIni <= $date_ini->format('Y-m-d H:i:s') && 
                            $timeObjEnd >= $date_end->format('Y-m-d H:i:s'))
                        return true;
                } else {
                    throw new Exception('Invalid field schedule', 1020);
                }
            }
        }
        
        return $resp;
    }
    
    public function sanitizeString($string)
    {

        $string = trim($string);

        $string = str_replace(
            array('á', 'à', 'ä', 'â', 'ª', 'Á', 'À', 'Â', 'Ä'),
            array('a', 'a', 'a', 'a', 'a', 'A', 'A', 'A', 'A'),
            $string
        );

        $string = str_replace(
            array('é', 'è', 'ë', 'ê', 'É', 'È', 'Ê', 'Ë'),
            array('e', 'e', 'e', 'e', 'E', 'E', 'E', 'E'),
            $string
        );

        $string = str_replace(
            array('í', 'ì', 'ï', 'î', 'Í', 'Ì', 'Ï', 'Î'),
            array('i', 'i', 'i', 'i', 'I', 'I', 'I', 'I'),
            $string
        );

        $string = str_replace(
            array('ó', 'ò', 'ö', 'ô', 'Ó', 'Ò', 'Ö', 'Ô'),
            array('o', 'o', 'o', 'o', 'O', 'O', 'O', 'O'),
            $string
        );

        $string = str_replace(
            array('ú', 'ù', 'ü', 'û', 'Ú', 'Ù', 'Û', 'Ü'),
            array('u', 'u', 'u', 'u', 'U', 'U', 'U', 'U'),
            $string
        );

        $string = str_replace(
            array('ñ', 'Ñ', 'ç', 'Ç'),
            array('n', 'N', 'c', 'C',),
            $string
        );

        return $string;
    }
    
    public static function dayOfWeeks($key)
    {
        $key = trim(strtolower($key));
        $dayOfWeeks = array(
            'monday' => 'lunes',
            'tuesday' => 'martes',
            'wednesday' => 'miercoles',
            'thursday' => 'jueves',
            'friday' => 'viernes',
            'saturday' => 'sabado',
            'sunday' => 'domingo'
        );
        
        return isset($dayOfWeeks[$key]) ? $dayOfWeeks[$key] : false;
    }
}
