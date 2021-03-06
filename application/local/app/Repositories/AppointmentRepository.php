<?php

/**
 * Repository Appointment
 * 
 * @author Geovanni Escalante <gescalante@arkho.tech>
 */

namespace App\Repositories;

use Exception;
use DB;
use Log;
use App\Calendar;
use App\Appointment;
use App\MailService;
use App\Repositories\BlockScheduleRepository;
use App\Repositories\CalendarRepository;
use Illuminate\Support\Facades\Cache;
use \Illuminate\Database\QueryException;

class AppointmentRepository
{
    /**
     * Obtiene todas listado de citas por calendario
     * 
     * @param string $appkey
     * @param string $domain
     * @param int $id
     * @param mixed $page int/null
     * @param mixed $records int/null
     * @return Collection
     */
    public function listAppointmentsByCalendarId($appkey, $domain, $id, $page, $records)
    {
        $res = array();
        $page = (int)$page;
        $records = (int)$records;
        
        try {            
            $ttl = (int)config('calendar.cache_ttl');
            $cache_id = sha1('cacheAppointmentListByCalendar_'.$appkey.'_'.$domain.'_'.$id.'_'.$page.'_'.$records);
            $tag = sha1($appkey.'_'.$domain);
            $res = Cache::tags($tag)->get($cache_id);
            
            if ($res === null) {
                if ((int)$id > 0) {
                    $columns = array(
                        DB::raw('appointments.id AS appointment_id'),
                        'subject',
                        'applier_name',
                        'applier_email',
                        'owner_name',
                        'appointment_start_time',
                        'applier_attended',
                        'calendar_id',
                        'metadata'
                    );
                    
                    if ($page !== 0) {
                        if ($records !== 0) {
                            $per_page = $records;
                        } else {
                            $per_page = (int)config('calendar.per_page');
                        }

                        $appointments = Appointment::select($columns)
                                ->join('calendars', 'calendars.id', '=', 'appointments.calendar_id')
                                ->where('appointments.calendar_id', $id)
                                ->where('appkey', $appkey)
                                ->where('domain', $domain)
                                ->where(DB::raw('DATE(appointment_start_time)'), '>=', date('Y-m-d'))
                                ->where('is_canceled', '<>', 1)
                                ->where('is_reserved', 0)->orderBy('appointment_start_time', 'ASC')
                                ->paginate($per_page);
                        
                        $appointments_data = $appointments->items();                        
                        $res['count'] = $appointments->total();
                    } else {
                        $appointments = Appointment::select($columns)
                                ->join('calendars', 'calendars.id', '=', 'appointments.calendar_id')
                                ->where('appkey', $appkey)
                                ->where('domain', $domain)
                                ->where('appointments.calendar_id', $id)
                                ->where(DB::raw('DATE(appointment_start_time)'), '>=', date('Y-m-d'))
                                ->where('is_canceled', '<>', 1)
                                ->where('is_reserved', 0)->orderBy('appointment_start_time', 'ASC')->get();
                        
                        $appointments_data = $appointments;
                        $res['count'] = $appointments->count();
                    }
                    $res['error'] = null;                    
                    
                    $i = 0;
                    $appointments_array = array();                    
                    foreach ($appointments_data as $a) {
                        $date = new \DateTime($a->appointment_start_time);
                        $appointment_time = $date->format('Y-m-d\TH:i:sO');
                        $appointments_array[$i]['appointment_id'] = $a->appointment_id;
                        $appointments_array[$i]['subject'] = $a->subject;
                        $appointments_array[$i]['applier_name'] = $a->applier_name;
                        $appointments_array[$i]['applier_email'] = $a->applier_email;
                        $appointments_array[$i]['owner_name'] = $a->owner_name;
                        $appointments_array[$i]['appointment_time'] = $appointment_time;
                        $appointments_array[$i]['applier_attended'] = $a->applier_attended;
                        $appointments_array[$i]['calendar_id'] = $a->calendar_id;
                        $appointments_array[$i]['metadata'] = $a->metadata;
                        $i++;
                    }
                    $res['data'] = $appointments_array;
                    
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
     * Obtiene todas las citas futuras por solicitante
     * 
     * @param string $appkey
     * @param string $domain
     * @param int $id
     * @param mixed $page int/null
     * @param mixed $records int/null
     * @return Collection
     */
    public function listAppointmentsByApplierId($appkey, $domain, $id, $page, $records)
    {
        $res = array();
        $page = (int)$page;
        $records = (int)$records;
        
        try {            
            $ttl = (int)config('calendar.cache_ttl');
            $cache_id = sha1('cacheAppointmentListByApplier_'.$appkey.'_'.$domain.'_'.$id.'_'.$page.'_'.$records);
            $tag = sha1($appkey.'_'.$domain);
            $res = Cache::tags($tag)->get($cache_id);
            
            if ($res === null) {
                if ((int)$id > 0) {
                    $columns = array(
                        DB::raw('appointments.id AS appointment_id'),
                        'subject',
                        'applier_name',
                        'applier_email',
                        'owner_name',
                        'appointment_start_time',
                        'applier_attended',
                        'calendar_id',
                        'metadata'
                    );
                    
                    if ($page !== 0) {
                        if ($records !== 0) {
                            $per_page = $records;
                        } else {
                            $per_page = (int)config('calendar.per_page');
                        }

                        $appointments = Appointment::select($columns)
                                ->join('calendars', 'calendars.id', '=', 'appointments.calendar_id')
                                ->where('appkey', $appkey)
                                ->where('domain', $domain)
                                ->where('applier_id', $id)
                                ->where('appointment_start_time', '>=', date('Y-m-d H:i:s'))
                                ->where('is_canceled', '<>', 1)
                                ->where('is_reserved', 0)->orderBy('appointment_start_time', 'ASC')
                                ->paginate($per_page);
                        
                        $appointments_data = $appointments->items();                        
                        $res['count'] = $appointments->total();
                    } else {
                        $appointments = Appointment::select($columns)
                                ->join('calendars', 'calendars.id', '=', 'appointments.calendar_id')
                                ->where('applier_id', $id)
                                ->where('appkey', $appkey)
                                ->where('domain', $domain)
                                ->where('appointment_start_time', '>=', date('Y-m-d H:i:s'))
                                ->where('is_canceled', '<>', 1)
                                ->where('is_reserved', 0)->orderBy('appointment_start_time', 'ASC')->get();
                        
                        $appointments_data = $appointments;
                        $res['count'] = $appointments->count();
                    }
                    $res['error'] = null;                    
                    
                    $i = 0;
                    $appointments_array = array();                    
                    foreach ($appointments_data as $a) {
                        $date = new \DateTime($a->appointment_start_time);
                        $appointment_time = $date->format('Y-m-d\TH:i:sO');
                        $appointments_array[$i]['appointment_id'] = $a->appointment_id;
                        $appointments_array[$i]['subject'] = $a->subject;
                        $appointments_array[$i]['applier_name'] = $a->applier_name;
                        $appointments_array[$i]['applier_email'] = $a->applier_email;
                        $appointments_array[$i]['owner_name'] = $a->owner_name;
                        $appointments_array[$i]['appointment_time'] = $appointment_time;
                        $appointments_array[$i]['applier_attended'] = $a->applier_attended;
                        $appointments_array[$i]['calendar_id'] = $a->calendar_id;
                        $appointments_array[$i]['metadata'] = $a->metadata;
                        $i++;
                    }
                    $res['data'] = $appointments_array;
                    
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
     * Obtiene todas las citas futuras por propietario de agenda
     * 
     * @param string $appkey
     * @param string $domain
     * @param int $id
     * @param mixed $page int/null
     * @param mixed $records int/null
     * @return Collection
     */
    public function listAppointmentsByOwnerId($appkey, $domain, $id, $page, $records)
    {
        $res = array();
        $page = (int)$page;
        $records = (int)$records;
        
        try {            
            $ttl = (int)config('calendar.cache_ttl');
            $cache_id = sha1('cacheAppointmentListByOwner_'.$appkey.'_'.$domain.'_'.$id.'_'.$page.'_'.$records);
            $tag = sha1($appkey.'_'.$domain);
            $res = Cache::tags($tag)->get($cache_id);
            
            if ($res === null) {
                if ((int)$id > 0) {
                    $columns = array(
                        DB::raw('appointments.id AS appointment_id'),
                        'subject',
                        'applier_name',
                        'applier_email',
                        'owner_name',
                        'appointment_start_time',
                        'applier_attended',
                        'calendar_id',
                        'metadata'
                    );
                    
                    if ($page !== 0) {
                        if ($records !== 0) {
                            $per_page = $records;
                        } else {
                            $per_page = (int)config('calendar.per_page');
                        }

                        $appointments = Appointment::select($columns)
                                ->join('calendars', 'calendars.id', '=', 'appointments.calendar_id')
                                ->where('owner_id', $id)
                                ->where('appkey', $appkey)
                                ->where('domain', $domain)
                                ->where(DB::raw('DATE(appointment_start_time)'), '>=', date('Y-m-d'))
                                ->where('is_canceled', '<>', 1)
                                ->where('is_reserved', 0)->orderBy('appointment_start_time', 'ASC')
                                ->paginate($per_page);

                        $appointments_data = $appointments->items();
                        $res['count'] = $appointments->total();
                    } else {
                        $appointments = Appointment::select($columns)
                                ->join('calendars', 'calendars.id', '=', 'appointments.calendar_id')
                                ->where('owner_id', $id)
                                ->where('appkey', $appkey)
                                ->where('domain', $domain)
                                ->where(DB::raw('DATE(appointment_start_time)'), '>=', date('Y-m-d'))
                                ->where('is_canceled', '<>', 1)
                                ->where('is_reserved', 0)->orderBy('appointment_start_time', 'ASC')->get();

                        $appointments_data = $appointments;
                        $res['count'] = $appointments->count();
                    }
                    $res['error'] = null;                    
                    
                    $i = 0;
                    $appointments_array = array();                    
                    foreach ($appointments_data as $a) {
                        $date = new \DateTime($a->appointment_start_time);
                        $appointment_time = $date->format('Y-m-d\TH:i:sO');
                        $appointments_array[$i]['appointment_id'] = $a->appointment_id;
                        $appointments_array[$i]['subject'] = $a->subject;
                        $appointments_array[$i]['applier_name'] = $a->applier_name;
                        $appointments_array[$i]['applier_email'] = $a->applier_email;
                        $appointments_array[$i]['owner_name'] = $a->owner_name;
                        $appointments_array[$i]['appointment_time'] = $appointment_time;
                        $appointments_array[$i]['applier_attended'] = $a->applier_attended;
                        $appointments_array[$i]['calendar_id'] = $a->calendar_id;
                        $appointments_array[$i]['metadata'] = $a->metadata;
                        $i++;
                    }
                    $res['data'] = $appointments_array;
                    
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
     * Obtiene todas las citas y su disponibilidad
     * 
     * @param string $appkey
     * @param string $domain
     * @param int $calendar_id
     * @param date $date
     * @param int $calendar_array
     * @return Collection
     */
    public function listAppointmentsAvailability($appkey, $domain, $calendar_id, $date = null, $calendar_array = array())
    {        
        $res = array();
        $date = $date === null ? '' : $date;
        
        try {
            $ttl = (int)config('calendar.cache_ttl');
            $month_max_availability = (int)config('calendar.month_max_appointments');            
            $owner_name = isset($calendar_array[0]['owner_name']) ? $calendar_array[0]['owner_name'] : '';
            $schedule = isset($calendar_array[0]['schedule']) ? $calendar_array[0]['schedule'] : array();
            $time_attention = isset($calendar_array[0]['time_attention']) ? $calendar_array[0]['time_attention'] : 0;
            $concurrency = isset($calendar_array[0]['concurrency']) ? $calendar_array[0]['concurrency'] : 1;
            $cache_id = sha1('cacheAppointmentListAvailability_'.$appkey.'_'.$domain.'_'.$calendar_id.'_'.$date);
            $tag = sha1($appkey.'_'.$domain);
            $res = Cache::tags($tag)->get($cache_id);
            Cache::flush();
            if ($res === null) {
                if ((int)$calendar_id > 0) {
                    $columns = array(
                        DB::raw('appointments.id AS appointment_id'),
                        'subject',
                        'applier_name',
                        'applier_email',
                        'appointment_start_time',
                        'appointment_end_time',
                        'metadata',
                        'schedule',
                        'time_attention'
                    );                    
                    
                    //Citas
                    if (empty($date)) {
                        $months = new \DateTime(date('Y-m-d H:i:s'));
                        $interval = new \DateInterval('P'.$month_max_availability.'M');
                        $max_date_time = $months->add($interval)->format('Y-m-d H:i:s');
                    
                        $appointments = Appointment::select($columns)
                            ->join('calendars', 'appointments.calendar_id', '=', 'calendars.id')
                            ->where('calendar_id', $calendar_id)
                            ->where('appkey', $appkey)
                            ->where('domain', $domain)
                            ->where(DB::raw('DATE(appointment_start_time)'), '>=', date('Y-m-d'))
                            ->where('appointment_start_time', '<=', $max_date_time)
                            ->where('is_canceled', '<>', 1)->orderBy('appointment_start_time', 'ASC')->get();
                    } else {                                                
                        $date_format = explode('-', $date);
                        
                        if (count($date_format) == 2) {
                            $date = $date . '-01';
                            $last_day_month = date('Y-m-t', strtotime($date));
                            $appointment_date = new \DateTime($last_day_month);
                            $appointment_date->add(new \DateInterval('P6D'));
                            $max_date_time = $appointment_date->format('Y-m-d');
                            
                            $appointments = Appointment::select($columns)
                                ->join('calendars', 'appointments.calendar_id', '=', 'calendars.id')
                                ->where('calendar_id', $calendar_id)
                                ->where('appkey', $appkey)
                                ->where('domain', $domain)
                                ->where(DB::raw('YEAR(appointment_start_time)'), (int)$date_format[0])
                                ->where(DB::raw('MONTH(appointment_start_time)'), (int)$date_format[1])
                                ->where('is_canceled', '<>', 1)->get();
                        } else {
                            $appointment_date = new \DateTime($date);
                            $max_date_time = $appointment_date->format('Y-m-d');
                            
                            $appointments = Appointment::select($columns)
                                ->join('calendars', 'appointments.calendar_id', '=', 'calendars.id')
                                ->where('calendar_id', $calendar_id)
                                ->where('appkey', $appkey)
                                ->where('domain', $domain)
                                ->where(DB::raw('DATE(appointment_start_time)'), $appointment_date->format('Y-m-d'))
                                ->where('is_canceled', '<>', 1)->get();
                        }
                    }
                    
                    $appointment_array = array();
                    $i = 0;
                    foreach ($appointments as $appointment) {
                        $date1 = new \DateTime($appointment->appointment_start_time);
                        $date2 = new \DateTime($appointment->appointment_end_time);
                        $appointment_array[$i]['appointment_id'] = $appointment->appointment_id;
                        $appointment_array[$i]['subject'] = $appointment->subject;
                        $appointment_array[$i]['applier_name'] = $appointment->applier_name;
                        $appointment_array[$i]['applier_email'] = $appointment->applier_email;
                        $appointment_array[$i]['appointment_start_time'] = $date1->format('Y-m-d\TH:i:sO');
                        $appointment_array[$i]['appointment_end_time'] = $date2->format('Y-m-d\TH:i:sO');
                        $appointment_array[$i]['metadata'] = $appointment->metadata;
                        $appointment_array[$i]['time'] = '';
                        $appointment_array[$i]['available'] = '';
                        $i++;
                    }
                    $num_appointment = count($appointment_array);

                    //Bloqueos de citas
                    $blockschedule = new BlockScheduleRepository();
                    $blockschedule_rs = $blockschedule->listBlockScheduleByCalendarId($appkey, $domain, $calendar_id);
                    $blockschedules = $blockschedule_rs['error'] === null ? $blockschedule_rs['data'] : array();

                    $num_blocks = count($blockschedules);                    
                    
                    if (empty($date)) {
                        $tmp_date = new \DateTime(date('Y-m-d'));
                    } else {
                        $tmp_date = new \DateTime($date);
                        if (count($date_format) == 2) {
                            $tmp_date->sub(new \DateInterval('P6D'));
                        }
                    }
                    
                    $max_date = new \DateTime($max_date_time);
                    $appointment_availability = array();
                    
                    // Mientras que hoy o fecha ingresada <= Fecha limite de consulta haga
                    while ($tmp_date->format('Y-m-d') <= $max_date->format('Y-m-d')) {
                        
                        //Armo un array por rango de horario
                        $day_of_Week = new \DateTime($tmp_date->format('Y-m-d'));
                        $day_of_Week = CalendarRepository::dayOfWeeks($day_of_Week->format('l'));
                        $times = isset($schedule[$day_of_Week]) ? $schedule[$day_of_Week] : array();
                        
                        foreach ($times as $t) {
                            $_time = explode('-', $t);
                            if (is_array($_time) && count($_time) == 2) {
                                $time_ini = new \DateTime($tmp_date->format('Y-m-d').' '.$_time[0].':00');
                                $time_end = new \DateTime($tmp_date->format('Y-m-d').' '.$_time[1].':00');
                                
                                while ($time_ini->format('Y-m-d H:i:s') < $time_end->format('Y-m-d H:i:s')) {
                                    $time_range = array();
                                    $timeEnd = new \DateTime($time_ini->format('Y-m-d H:i:s'));
                                    $timeEnd->add(new \DateInterval('PT'.$time_attention.'M'));
                                        
                                    for ($k=0; $k<$concurrency; $k++) {
                                        $ind = $this->getIndex($appointment_array, $time_ini->format('Y-m-d H:i:s'), $timeEnd->format('Y-m-d H:i:s'),  'appointment', $num_appointment);
                                        $ind_block = $this->getIndex($blockschedules, $time_ini->format('Y-m-d H:i:s'), $timeEnd->format('Y-m-d H:i:s'), 'blockschedule', $num_blocks);

                                        if ($ind > -1) {
                                            $time_range[$k]['appointment_id'] = $appointment_array[$ind]['appointment_id'];
                                            $time_range[$k]['subject'] = $appointment_array[$ind]['subject'] != null ? $appointment_array[$ind]['subject'] : '';
                                            $time_range[$k]['applier_name'] = $appointment_array[$ind]['applier_name'] != null ? $appointment_array[$ind]['applier_name'] : '';
                                            $time_range[$k]['applier_email'] = $appointment_array[$ind]['applier_email'];
                                            $time_range[$k]['appointment_start_time'] = $appointment_array[$ind]['appointment_start_time'];
                                            $time_range[$k]['appointment_end_time'] = $appointment_array[$ind]['appointment_end_time'];
                                            $time_range[$k]['metadata'] = $appointment_array[$ind]['metadata'];
                                            $time_range[$k]['available'] = 'R';
                                            $time_range[$k]['block_id'] = 0;
                                            unset($appointment_array[$ind]);
                                        } else {
                                            $time_range[$k]['appointment_id'] = '';
                                            $time_range[$k]['subject'] = '';
                                            $time_range[$k]['applier_name'] = '';
                                            $time_range[$k]['applier_email'] = '';
                                            $time_range[$k]['appointment_start_time'] = '';
                                            $time_range[$k]['appointment_end_time'] = '';
                                            $time_range[$k]['metadata'] = '';
                                            if ($ind_block > -1) {
                                                $time_range[$k]['available'] = 'B';
                                                $time_range[$k]['block_id'] = $blockschedules[$ind_block]['id'];
                                            } else {
                                                $time_range[$k]['available'] = 'D';
                                                $time_range[$k]['block_id'] = 0;
                                            }
                                        }
                                    }
                                    
                                    $appointment_availability[$tmp_date->format('Y-m-d')][$time_ini->format('H:i')] = $time_range;
                                    $time_ini->add(new \DateInterval('PT'.$time_attention.'M'));
                                }
                            }
                        }                        
                        
                        //Armo el array por dias que tendra el array de rango de horarios
                        $tmp_date->add(new \DateInterval('P1D'));
                    }
                    
                    $res['data'] = $appointment_availability;
                    $res['owner_name'] = $owner_name;
                    $res['concurrency'] = $concurrency;
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
     * Obtiene todas las citas y su disponibilidad por propietario
     * 
     * @param string $appkey
     * @param string $domain
     * @param int $owner_id
     * @param date $date
     * @param int $calendar_array
     * @return Collection
     */
    public function listAppointmentsAvailabilityByOwner($appkey, $domain, $owner_id, $date = null, $calendar_array = array())
    {        
        $response = array();
        try {
            
            foreach ($calendar_array as $calendar) {
                $calendar_id = isset($calendar['id']) ? $calendar['id'] : 0;
                $cal_obj = new CalendarRepository();
                $cal_info = $cal_obj->listCalendarById($appkey, $domain, $calendar_id);
                        
                if ($cal_info['error'] !== null || (isset($cal_info['count']) && (int)$cal_info['count'] == 0)) {
                    $res['error'] = new Exception('', 1030);
                    return $res;
                }

                $res = array();

                $ttl = (int)config('calendar.cache_ttl');
                $month_max_availability = (int)config('calendar.month_max_appointments');                
                $calendar_name = isset($calendar['name']) ? $calendar['name'] : '';
                $owner_name = isset($calendar['owner_name']) ? $calendar['owner_name'] : '';
                $schedule = isset($calendar['schedule']) ? $calendar['schedule'] : array();
                $time_attention = isset($calendar['time_attention']) ? $calendar['time_attention'] : 0;
                $concurrency = isset($calendar['concurrency']) ? $calendar['concurrency'] : 1;
                $cache_id = sha1('cacheAppointmentListAvailability_'.$appkey.'_'.$domain.'_'.$owner_id);
                $tag = sha1($appkey.'_'.$domain);
                $cache = Cache::tags($tag)->get($cache_id);
                
                if ($cache === null) {
                    if ((int)$owner_id > 0) {
                        $columns = array(
                            DB::raw('appointments.id AS appointment_id'),
                            'subject',
                            'applier_name',
                            'applier_email',
                            'appointment_start_time',
                            'appointment_end_time',
                            'metadata',
                            'schedule',
                            'time_attention'
                        );                    
                        
                        //Citas
                        if ($date === null) {
                            $months = new \DateTime(date('Y-m-d H:i:s'));
                            $interval = new \DateInterval('P'.$month_max_availability.'M');
                            $max_date_time = $months->add($interval)->format('Y-m-d H:i:s');
                            
                            $appointments = Appointment::select($columns)
                                ->join('calendars', 'appointments.calendar_id', '=', 'calendars.id')
                                ->where('owner_id', $owner_id)
                                ->where('appkey', $appkey)
                                ->where('domain', $domain)
                                ->where('calendar_id', $calendar_id)
                                ->where(DB::raw('DATE(appointment_start_time)'), '>=', date('Y-m-d'))
                                ->where('appointment_start_time', '<=', $max_date_time)
                                ->where('is_canceled', '<>', 1)                            
                                ->where('is_reserved', 0)->orderBy('appointment_start_time', 'ASC')->get();                            
                        } else {
                            $appointment_date = new \DateTime($date);
                            $max_date_time = $appointment_date->format('Y-m-d');                        
                            
                            $appointments = Appointment::select($columns)
                                ->join('calendars', 'appointments.calendar_id', '=', 'calendars.id')
                                ->where('appkey', $appkey)
                                ->where('domain', $domain)
                                ->where('owner_id', $owner_id)
                                ->where('calendar_id', $calendar_id)
                                ->where(DB::raw('DATE(appointment_start_time)'), $appointment_date->format('Y-m-d'))
                                ->where('is_canceled', '<>', 1)                            
                                ->where('is_reserved', 0)->get();
                        }
                        
                        $appointment_array = array();
                        $i = 0;
                        foreach ($appointments as $appointment) {
                            $date1 = new \DateTime($appointment->appointment_start_time);
                            $date2 = new \DateTime($appointment->appointment_end_time);
                            $appointment_array[$i]['appointment_id'] = $appointment->appointment_id;
                            $appointment_array[$i]['subject'] = $appointment->subject;
                            $appointment_array[$i]['applier_name'] = $appointment->applier_name;
                            $appointment_array[$i]['applier_email'] = $appointment->applier_email;
                            $appointment_array[$i]['appointment_start_time'] = $date1->format('Y-m-d\TH:i:sO');
                            $appointment_array[$i]['appointment_end_time'] = $date2->format('Y-m-d\TH:i:sO');
                            $appointment_array[$i]['metadata'] = $appointment->metadata;
                            $appointment_array[$i]['time'] = '';
                            $appointment_array[$i]['available'] = '';
                            $i++;
                        }
                        $num_appointment = count($appointment_array);
                        
                        //Bloqueos de citas
                        $blockschedule = new BlockScheduleRepository();
                        $blockschedule_rs = $blockschedule->listBlockScheduleByUserIdBlock($appkey, $domain, $owner_id);
                        $blockschedules = $blockschedule_rs['error'] === null ? $blockschedule_rs['data'] : array();

                        $num_blocks = count($blockschedules);
                        
                        if ($date === null) {
                            $tmp_date = new \DateTime(date('Y-m-d'));
                        } else {
                            $tmp_date = new \DateTime($date);
                        }
                        
                        $max_date = new \DateTime($max_date_time);
                        $appointment_availability = array();
                        
                        while ($tmp_date->format('Y-m-d') <= $max_date->format('Y-m-d')) {
                            
                            //Armo un array por rango de horario
                            $day_of_Week = new \DateTime($tmp_date->format('Y-m-d'));
                            $day_of_Week = CalendarRepository::dayOfWeeks($day_of_Week->format('l'));
                            $times = isset($schedule[$day_of_Week]) ? $schedule[$day_of_Week] : array();
                            
                            foreach ($times as $t) {
                                $_time = explode('-', $t);
                                if (is_array($_time) && count($_time) == 2) {
                                    $time_ini = new \DateTime($tmp_date->format('Y-m-d').' '.$_time[0].':00');
                                    $time_end = new \DateTime($tmp_date->format('Y-m-d').' '.$_time[1].':00');
                                    
                                    while ($time_ini->format('Y-m-d H:i:s') < $time_end->format('Y-m-d H:i:s')) {
                                        $time_range = array();
                                        $timeEnd = new \DateTime($time_ini->format('Y-m-d H:i:s'));
                                        $timeEnd->add(new \DateInterval('PT'.$time_attention.'M'));
                                            
                                        for ($k=0; $k<$concurrency; $k++) {
                                            $ind = $this->getIndex($appointment_array, $time_ini->format('Y-m-d H:i:s'), $timeEnd->format('Y-m-d H:i:s'),  'appointment', $num_appointment);
                                            $ind_block = $this->getIndex($blockschedules, $time_ini->format('Y-m-d H:i:s'), $timeEnd->format('Y-m-d H:i:s'), 'blockschedule', $num_blocks);

                                            if ($ind > -1) {
                                                $time_range[$k]['appointment_id'] = $appointment_array[$ind]['appointment_id'];
                                                $time_range[$k]['subject'] = $appointment_array[$ind]['subject'] != null ? $appointment_array[$ind]['subject'] : '';
                                                $time_range[$k]['applier_name'] = $appointment_array[$ind]['applier_name'] != null ? $appointment_array[$ind]['applier_name'] : '';
                                                $time_range[$k]['applier_email'] = $appointment_array[$ind]['applier_email'];
                                                $time_range[$k]['appointment_start_time'] = $appointment_array[$ind]['appointment_start_time'];
                                                $time_range[$k]['appointment_end_time'] = $appointment_array[$ind]['appointment_end_time'];
                                                $time_range[$k]['metadata'] = $appointment_array[$ind]['metadata'];
                                                $time_range[$k]['available'] = 'R';
                                                $time_range[$k]['block_id'] = 0;
                                                unset($appointment_array[$ind]);
                                            } else {
                                                $time_range[$k]['appointment_id'] = '';
                                                $time_range[$k]['subject'] = '';
                                                $time_range[$k]['applier_name'] = '';
                                                $time_range[$k]['applier_email'] = '';
                                                $time_range[$k]['appointment_start_time'] = '';
                                                $time_range[$k]['appointment_end_time'] = '';
                                                $time_range[$k]['metadata'] = '';
                                                if ($ind_block > -1) {
                                                    $time_range[$k]['available'] = 'B';
                                                    $time_range[$k]['block_id'] = $blockschedules[$ind_block]['id'];
                                                } else {
                                                    $time_range[$k]['available'] = 'D';
                                                    $time_range[$k]['block_id'] = 0;
                                                }
                                            }
                                        }
                                        
                                        $appointment_availability[$tmp_date->format('Y-m-d')][$time_ini->format('H:i')] = $time_range;
                                        $time_ini->add(new \DateInterval('PT'.$time_attention.'M'));
                                    }
                                }
                            } // End foreach times
                            
                            //Armo el array por dias que tendra el array de rango de horarios
                            $tmp_date->add(new \DateInterval('P1D'));

                        } // End while
                        
                        $res['calendar_id'] = $calendar_id;
                        $res['name'] = $calendar_name;
                        $res['owner_name'] = $owner_name;
                        $res['concurrency'] = $concurrency;
                        $res['appointmentsavailable'] = $appointment_availability;                        
                        
                        Cache::tags([$tag])->put($cache_id, $cache, $ttl);
                    } // End if owner_id > 0
                } // End if res === null
                array_push($response, $res);                
                //$response['error'] = null;
            } // End foreach calendar_array
            
        } catch (QueryException $qe) {
            $res['error'] = $qe;
        } catch (Exception $e) {
            $res['error'] = $e;
        }        
        
        return $response;
    }
    
    /**
     * Crea un nuevo registro de tipo cita
     *
     * @param string $appkey
     * @param string $domain 
     * @param array $data     
     * @return Collection
     */
    public function createAppointment($appkey, $domain, $data)
    {
        $res = array();
        
        try {            
            $calendar = Calendar::where('id', $data['calendar_id'])->get();
            $time_attention = 0;
            
            if ($calendar->count() > 0) {
                foreach ($calendar as $cal) {
                    $time_attention = (int)$cal->time_attention;
                }
                
                $date = new \DateTime($data['appointment_start_time']);
                $start_date = $date->format('Y-m-d H:i:s');
                $end_date = $date->add(new \DateInterval('PT' . $time_attention . 'M'))->format('Y-m-d H:i:s');
                
                $data['appointment_start_time'] = $start_date;
                $data['appointment_end_time'] = $end_date;
                $data['is_reserved'] = 1;
                $data['reservation_date'] = date('Y-m-d H:i:s');
                $data['is_canceled'] = 0;
                $data['applier_attended'] = -1;
                
                $appointment = Appointment::create($data);
                $res['id'] = $appointment->id;
                $res['error'] = null;
                
                $tag = sha1($appkey.'_'.$domain);
                Cache::tags($tag)->flush();
            } else {
                $res['error'] = new Exception('', 1010);
            }
        } catch (QueryException $qe) {
            $res['error'] = $qe;
        } catch (Exception $e) {
            $res['error'] = $e;
        }
        
        return $res;
    }
    
    /**
     * Actualiza un nuevo registro de tipo cita
     *
     * @param string $appkey
     * @param string $domain 
     * @param int $id 
     * @param array $data
     * @return Collection
     */
    public function updateAppointment($appkey, $domain, $id, $data)
    {
        $res = array();
        
        try {            
            $calendar = Calendar::where('id', $data['calendar_id'])->get();
            $time_attention = 0;
            $email_owner = '';
            
            if ($calendar->count() > 0) {
                foreach ($calendar as $cal) {
                    $time_attention = (int)$cal->time_attention;
                    $email_owner = $cal->owner_email;
                }
                
                $date = new \DateTime($data['appointment_start_time']);
                $start_date = $date->format('Y-m-d H:i:s');
                $end_date = $date->add(new \DateInterval('PT' . $time_attention . 'M'))->format('Y-m-d H:i:s');
                
                $data['appointment_start_time'] = $start_date;
                $data['appointment_end_time'] = $end_date;
                
                $appointment = Appointment::where('id', $id)->update($data);
                $appo = Appointment::where('id', $id)->first();
                
                $resp_mail['error'] = false;
                if (!$appo->is_reserved && $appo->confirmation_date) {
                    $mail = new MailService();
                    $resp_mail = $mail->setEmail($appkey, $domain, $id, 'modify');
                }
                
                if ($resp_mail['error']) {
                    Log::error('Message: ' . $resp_mail['errorMessage']);
                    $res['error'] = null;
                    //$res['error'] = new Exception($resp_mail['errorMessage']);
                } else {
                    $res['error'] = null;
                }
                
                $tag = sha1($appkey.'_'.$domain);
                Cache::tags($tag)->flush();
            } else {
                $res['error'] = new Exception('', 1010);
            }
        } catch (QueryException $qe) {
                $res['error'] = $qe;
        } catch (Exception $e) {
            $res['error'] = $e;
        }
        
        return $res;
    }
    
    /**
     * Obtiene una cita por ID
     *      
     * @param string $appkey
     * @param string $domain
     * @param int $id
     * @param bool $cache
     * @return Collection
     */
    public function listAppointmentById($appkey, $domain, $id, $cache = true)
    {
        $res = array();
        
        try {            
            $ttl = (int)config('calendar.cache_ttl');
            $cache_id = sha1('cacheAppointmentListById_'.$id);
            $tag = sha1($appkey.'_'.$domain);
            $res = $cache === true ? Cache::tags($tag)->get($cache_id) : null;
            
            if ($res === null) {                
                if ((int)$id > 0) {                    
                    $appointments = Appointment::select(array('appointments.*', 'calendars.name', 'calendars.owner_name', 'calendars.owner_email'))
                                ->join('calendars', 'appointments.calendar_id', '=', 'calendars.id')
                                ->where('appointments.id', $id)->get();
                    
                    $res['data'] = $appointments;
                    $res['count'] = $appointments->count();
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
     * Obtiene todos los datos de un calendario por ID de cita
     *      
     * @param string $appkey
     * @param string $domain
     * @param int $id     
     * @return Collection
     */
    public function listCalendarByAppointmentId($appkey, $domain, $id)
    {
        $res = array();
        
        try {            
            $ttl = (int)config('calendar.cache_ttl');
            $cache_id = sha1('cacheCalendarByAppointmentId_'.$id);
            $tag = sha1($appkey.'_'.$domain);
            $res = Cache::tags($tag)->get($cache_id);
            
            if ($res === null) {
                if ((int)$id > 0) {
                    $appointments = Appointment::join('calendars', 'appointments.calendar_id', '=', 'calendars.id')
                            ->select('calendars.*', 'appointments.appointment_start_time', 'appointments.metadata')
                            ->where('appointments.id', $id)->get();

                    $res['data'] = $appointments;
                    $res['count'] = $appointments->count();
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
     * Confirma una cita
     *
     * @param string $appkey
     * @param string $domain 
     * @param int $id
     * @return Collection
     */
    public function confirmAppointment($appkey, $domain, $id)
    {
        $res = array();
        
        try {
            
            if ((int)$id > 0) {
                $appo = Appointment::where('id', $id)->first();
                if (!$appo->is_canceled) {
                    $data['confirmation_date'] = date('Y-m-d H:i:s');
                    $data['is_reserved'] = 0;
                    $appointment = Appointment::where('id', $id)->update($data);
                    $mail = new MailService();
                    $resp_mail = $mail->setEmail($appkey, $domain, $id, 'confirmation');

                    if ($resp_mail['error']) {
                        Log::error('Message: ' . $resp_mail['errorMessage']);
                        $res['error'] = null;
                        //$res['error'] = new Exception($resp_mail['errorMessage']);
                    } else {
                        $res['error'] = null;
                    }

                    $tag = sha1($appkey.'_'.$domain);
                    Cache::tags($tag)->flush();
                } else {
                    $res['error'] = new Exception('', 2071);
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
     * Confirma citas masivamente
     *
     * @param string $appkey
     * @param string $domain 
     * @param array $ids
     * @return Collection
     */
    public function bulkConfirmAppointment($appkey, $domain, $ids)
    {
        $res = array();
        
        try {
            DB::beginTransaction();
            
            foreach ($ids as $id) {
                if ((int)$id > 0) {
                    $appo = Appointment::where('id', $id)->first();
                    if (!$appo->is_canceled) {
                        $data['confirmation_date'] = date('Y-m-d H:i:s');
                        $data['is_reserved'] = 0;
                        $appointment = Appointment::where('id', $id)->update($data);                                                
                    } else {
                        $res['error'] = new Exception('ID cita: ' . $id . ' Cita cancelada', 2071);
                        break;
                    }
                } else {
                    $res['error'] = new Exception('ID cita: ' . $id . ' Cita no encontrada', 2072);
                    break;
                }
            }
            
            if (!isset($res['error']) || $res['error'] === null) {
                DB::commit();
                
                $tag = sha1($appkey.'_'.$domain);
                Cache::tags($tag)->flush();
            
                // Se envian los correos electronicos
                foreach ($ids as $id) {
                    $mail = new MailService();
                    $resp_mail = $mail->setEmail($appkey, $domain, $id, 'confirmation');

                    if ($resp_mail['error']) {
                        Log::error('Message: ' . $resp_mail['errorMessage'] . ' ID cita: ' . $id);
                    }
                }                
            } else {
                DB::rollBack();
            }
        } catch (QueryException $qe) {
            DB::rollBack();
            $res['error'] = $qe;
        } catch (Exception $e) {
            DB::rollBack();
            $res['error'] = $e;
        }
        
        return $res;
    }
    
    /**
     * Cancela una cita
     *
     * @param string $appkey
     * @param string $domain 
     * @param int $id
     * @param array $data
     * @return Collection
     */
    public function cancelAppointment($appkey, $domain, $id, $data)
    {
        $res = array();
        
        try {
            
            if ((int)$id > 0) {
                $columns['user_id_cancel'] = $data['user_id_cancel'];
                $columns['user_name_cancel'] = $data['user_name_cancel'];                
                if (isset($data['cancelation_cause']))
                    $columns['cancelation_cause'] = $data['cancelation_cause'];
                $columns['cancelation_date'] = date('Y-m-d H:i:s');
                $columns['is_canceled'] = 1;
                $appointment = Appointment::where('id', $id)->update($columns);
                $mail = new MailService();
                $resp_mail = $mail->setEmail($appkey, $domain, $id, 'cancel');
                
                if ($resp_mail['error']) {
                    Log::error('Message: ' . $resp_mail['errorMessage']);
                    $res['error'] = null;
                    //$res['error'] = new Exception($resp_mail['errorMessage']);
                } else {
                    $res['error'] = null;
                }               
                
                $tag = sha1($appkey.'_'.$domain);
                Cache::tags($tag)->flush();
            }
        } catch (QueryException $qe) {
            $res['error'] = $qe;
        } catch (Exception $e) {
            $res['error'] = $e;
        }
        
        return $res;
    }  
    
    /**
     * Define una cita a si asistio o no el usuario
     *
     * @param string $appkey
     * @param string $domain 
     * @param int $id
     * @param array $data
     * @return Collection
     */
    public function assistsAppointment($appkey, $domain, $id, $data)
    {
        $res = array();
        
        try {
            
            if ((int)$id > 0) {
                $columns['applier_attended'] = $data['applier_attended'];
                Appointment::where('id', $id)->update($columns);
                $res['error'] = null;
                
                $tag = sha1($appkey.'_'.$domain);
                Cache::tags($tag)->flush();
            }
        } catch (QueryException $qe) {
                $res['error'] = $qe;
        } catch (Exception $e) {
            $res['error'] = $e;
        }
        
        return $res;
    }
    
    /**
     * Elimina un registro de tipo Appointment
     * 
     * @param string $appkey
     * @param string $domain
     * @param int $id
     * @return bool
     */
    public function destroyAppointment($appkey, $domain, $id)
    {
        $res = array();
        
        try {
            $appointment = Appointment::destroy($id);
            $res['error'] = $appointment === false ? new Exception('', 500) : null;
            
            $tag = sha1($appkey.'_'.$domain);
            Cache::tags($tag)->flush();
        } catch (QueryException $qe) {            
                $res['error'] = $qe;
        } catch (Exception $e) {
            $res['error'] = $e;
        }
        
        return $res;
    }
    
    /**
     * Elimina citas cuya reserva haya pasado cierto tiempo
     *     
     * @return Collection
     */
    public function deleteAppointmentsPendingToConfirm()
    {        
        $res = array();
        
        try {
            $columns = array(
                'appointments.id',
                'reservation_date',
                'time_confirm_appointment',
                'appkey',
                'domain'
            );
            $now = strtotime(date('Y-m-d H:i:s'));

            $appointments = Appointment::select($columns)
                    ->join('calendars', 'appointments.calendar_id', '=', 'calendars.id')
                    ->where('appointments.is_reserved', 1)
                    ->where('appointments.is_canceled', 0)->get();

            foreach ($appointments as $appointment) {
                $time_to_confirm = (int)$appointment->time_confirm_appointment;                
                $reservation_date = strtotime($appointment->reservation_date);                
                $diff = ($now - $reservation_date)/60;
                if (floor($diff) > floor(($time_to_confirm))) {
                    $this->destroyAppointment($appointment->appkey, $appointment->domain, (int)$appointment->id);
                }
            }                
            
            Cache::flush();
            $res['error'] = null;            
        } catch (QueryException $qe) {
                $res['error'] = $qe;
        } catch (Exception $e) {
            $res['error'] = $e;
        }
        
        return $res;
    }
    
    /**
     * Verifica si una cita puede agendarse para una fecha determinada
     * 
     * @param string $appkey
     * @param string $domain
     * @param int $calendar_id
     * @param string $start_date
     * @param int $id
     * @return boolean
     */
    public function isValidAppointment($appkey, $domain, $calendar_id, $start_date, $id = 0)
    {
        $val = true;        
        $code = 0;
        
        //Obtengo el calendario por ID y valido que exista
        $calendar = new CalendarRepository();
        $cal_data = $calendar->listCalendarById($appkey, $domain, $calendar_id);
        
        $time_attention = 0;
        $concurrency = 0;
        $ignore_non_working_days = 0;
        $calendar_schedule = '';
        
        if ($cal_data['count'] > 0) {
            foreach ($cal_data['data'] as $cal) {
                $calendar_schedule = $cal['schedule'];
                $time_attention = (int)$cal['time_attention'];
                $concurrency = (int)$cal['concurrency'];
                $ignore_non_working_days = (int)$cal['ignore_non_working_days'];
            }
            
            $date = new \DateTime($start_date);
            $start_date = $date->format('Y-m-d H:i:s');
            $end_date = $date->add(new \DateInterval('PT' . $time_attention . 'M'))->format('Y-m-d H:i:s');
            
            //Valido que la fecha inicial sea mayor o igual a fecha actual
            if ($start_date < date('Y-m-d H:i:s')) {
                $val = false;
                $code = 2010;
            } else {
                
                //valido si la fecha esta en un dia no laboral
                $day_off = false;
                if (!(bool)$ignore_non_working_days) {
                    $dayoff = new DayOffRepository();
                    $day_off = $dayoff->isDayOff($appkey, $start_date, $end_date);
                }
                
                if ($day_off) {
                    $val = false;
                    $code = 2020;                    
                } else {
                    
                    //Valido bloqueo de horario
                    $block = new BlockScheduleRepository();
                    $block_appointment = $block->validateBlock($appkey, $domain, $calendar_id, $start_date, $end_date);                    
                    if ($block_appointment) {
                        $val = false;
                        $code = 2030;
                    } else {
                        
                        //Valido que la cita este dentro del horario del calendario
                        $cal = $calendar->isIntoSchedule($calendar_schedule, $start_date, $end_date);
                        if (!$cal) {
                            $val = false;
                            $code = 2040;
                        } else {
                            
                            //Valido que no haya cruce de citas
                            $appointment = $this->validateOverlappingAppointment($appkey, $domain, $calendar_id, $concurrency, $start_date, $end_date, $id);
                            if ($appointment) {
                                $val = false;
                                $code = 2050;
                            }
                        }
                    }
                }
            }
        } else {
            $val = false;
            $code = 1010;
        }
        
        $result = array(
            'is_ok' => $val, 
            'error_code' => $val ? 0 : $code
        );
        
        return $result;
    }
    
    /**
     * Valido que no haya cruce de horarios entre citas
     * 
     * @param string $appkey
     * @param string $domain 
     * @param int $calendar_id
     * @param int $concurrency
     * @param date $start_date
     * @param date $end_date
     * @param int $id
     * @return boolean
     */
    public function validateOverlappingAppointment($appkey, $domain, $calendar_id, $concurrency, $start_date, $end_date, $id)
    {
        $res = true;        
        
        try {
            if ((int)$calendar_id > 0 && $start_date && $end_date) {
                $start_date = new \DateTime($start_date);
                $start_date = $start_date->format('Y-m-d H:i:s');
                $end_date = new \DateTime($end_date);
                $end_date = $end_date->format('Y-m-d H:i:s');
            
                $ttl = (int)config('calendar.cache_ttl');
                $cache_id = sha1('cacheIsOverlappingAppointment_'.$id.'_'.$calendar_id.'_'.$concurrency.'_'.$start_date.'_'.$end_date);
                $tag = sha1($appkey.'_'.$domain);
                $res = Cache::tags($tag)->get($cache_id);
                
                if ($res === null) {
                    if ((int)$id > 0) {
                    $appointment = Appointment::where('appointment_end_time', '>=', date('Y-m-d H:i:s'))
                          ->where('appointment_start_time', '<', $end_date)
                          ->Where('appointment_end_time', '>', $start_date)
                          ->where('calendar_id', $calendar_id)
                          ->where('id', '<>', $id)
                          ->where('is_canceled', '<>', 1)
                          ->orderBy('appointment_start_time', 'ASC')->get();
                    } else {
                        $appointment = Appointment::where('appointment_end_time', '>=', date('Y-m-d H:i:s'))
                          ->where('appointment_start_time', '<', $end_date)
                          ->Where('appointment_end_time', '>', $start_date)
                          ->where('calendar_id', $calendar_id)                          
                          ->where('is_canceled', '<>', 1)
                          ->orderBy('appointment_start_time', 'ASC')->get();
                    }
                    
                    $appointments = $appointment->count();
                    if ($appointments == 0) {
                        $res = false;
                    } else {
                        if ($concurrency > 1) {
                            if ($appointments >= $concurrency) {
                                $res = true; 
                            } else {
                                $res = false;
                            }
                        } else {
                            $res = true;
                        }
                    }                    
                    
                    Cache::tags([$tag])->put($cache_id, $res, $ttl);
                }
            }
        } catch (QueryException $qe) {
            Log::error('code: ' .  $qe->getCode() . ' Message: ' . $qe->getMessage());
        } catch (Exception $e) {
            Log::error('code: ' .  $e->getCode() . ' Message: ' . $e->getMessage());
        }        
        
        return $res;        
    }
    
    /**
     * Verifica si el usuario ya tiene una cita para una fecha especifica
     * 
     * @param string $appkey
     * @param string $domain
     * @param string $applier_id
     * @param date $start_time
     * @return boolean
     */
    public function isOverlappingAppointmentByUser($appkey, $domain, $calendar_id, $applier_id, $start_time)
    {        
        $resp = true;
        try {            
            $ttl = (int)config('calendar.cache_ttl');
            $cache_id = sha1('cacheisOverlappingAppointmentByUser_'.$appkey.'_'.$domain.'_'.$applier_id.'_'.$start_time);
            $tag = sha1($appkey.'_'.$domain);
            $resp = Cache::tags($tag)->get($cache_id);
            
            if ($resp === null) {
                if (!empty($appkey) && !empty($domain) && !empty($applier_id) && !empty($start_time)) {
                    $start_time = new \DateTime($start_time);
                    $start_time = $start_time->format('Y-m-d H:i:s');
                    
                    $calendar = Calendar::where('id', $calendar_id)->get();
                    $appointments = Appointment::join('calendars', 'appointments.calendar_id', '=', 'calendars.id')
                                ->select('appointments.id')
                                ->where('calendars.appkey', $appkey)
                                ->where('calendars.domain', $domain)
                                ->where('appointments.applier_id', $applier_id)
                                ->where('appointments.is_canceled', '<>', 1)
                                ->where('appointments.appointment_start_time', $start_time)->get();

                    if ($appointments->count() < $calendar[0]->concurrency) {
                        $resp = false;
                    } else {
                        $resp = true;
                    }
                    Cache::tags([$tag])->put($cache_id, $resp, $ttl);                    
                }
            }
        } catch (QueryException $qe) {
            Log::error('code: ' .  $qe->getCode() . ' Message: ' . $qe->getMessage());
        } catch (Exception $e) {
            Log::error('code: ' .  $qe->getCode() . ' Message: ' . $e->getMessage());
        }        
        
        return $resp;
    }
    
    /**
     * Retorna el indice del elemento enviado buscando en el array_search
     * 
     * @param array $array_search
     * @param date $element_ini
     * @param date $element_end
     * @return int
     */
    private function getIndex($array_search, $element_ini, $element_end, $table, $num_appointment)
    {
        $index = -1;
        for ($i=0; $i < $num_appointment; $i++) {
            if (isset($array_search[$i])) {
                if ($table == 'appointment') {
                    $date_ini_db = new \DateTime($array_search[$i]['appointment_start_time']);
                    $date_end_db = new \DateTime($array_search[$i]['appointment_end_time']);
                } else {
                    $date_ini_db = new \DateTime($array_search[$i]['start_date']);
                    $date_end_db = new \DateTime($array_search[$i]['end_date']);
                }
                
                $date1 = new \DateTime($element_ini);
                $date2 = new \DateTime($element_end);
                
                if ($date_ini_db->format('Y-m-d H:i:s') < $date2->format('Y-m-d H:i:s') && 
                    $date_end_db->format('Y-m-d H:i:s') > $date1->format('Y-m-d H:i:s')) {
                    $index = $i;
                    break;
                }
            }
        }

        return $index;
    }
}
