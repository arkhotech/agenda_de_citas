<?php

/**
 * Repository DayOff
 * 
 * @author Geovanni Escalante <gescalante@arkho.tech>
 */

namespace App\Repositories;

use App\DayOff;
use App\Repositories\CalendarRepository;
use App\App;
use DB;
use Log;
use Illuminate\Support\Facades\Cache;
use \Illuminate\Database\QueryException;

class DayOffRepository
{
    /**
     * Obtiene todos los dias no laborales por una appkey y domain del ano actual
     * 
     * @param string $appkey
     * @param string $domain
     * @return Collection
     */
    public function listDayOff($appkey, $domain)
    {
        $res = array();
        $ano = date('Y');
        
        try {            
            $ttl = (int)config('calendar.cache_ttl');
            $cache_id = sha1('cacheDayOffList_'.$appkey.'_'.$domain.'_'.$ano);            
            $tag = sha1($appkey.'_'.$domain);
            $res = Cache::tags($tag)->get($cache_id);
                    
            if ($res === null) {                
                $columns = array(
                    'id',
                    'name',
                    'date_dayoff',
                );
                $daysoff = DayOff::select($columns)
                        ->where('appkey', $appkey)
                        ->where('domain', $domain)
                        ->where(DB::raw('YEAR(date_dayoff)'), $ano)->get();
                
                $daysoff_array = array();
                $days_array = array();
                foreach ($daysoff as $day) {
                    $date = new \DateTime($day->date_dayoff);
                    $dayoff_date = $date->format('Y-m-d\TH:i:sO');
                    $days_array['id'] = $day->id;
                    $days_array['name'] = $day->name;
                    $days_array['date_dayoff'] = $dayoff_date;

                    array_push($daysoff_array, $days_array);
                }
                
                $res['data'] = $daysoff_array;
                $res['count'] = $daysoff->count();                
                $res['error'] = null;                
                
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
     * Retorna true si la fecha enviada se encuentra registrada en DB
     * 
     * @param string $appkey
     * @param string $domain
     * @param date $start_date
     * @param date $end_date
     * @return boolean
     */
    public function isDayOff($appkey, $domain, $start_date, $end_date)
    {
        $res = true;
        
        try {
            $start_date = new \DateTime($start_date);
            $start_date = $start_date->format('Y-m-d');
            $end_date = new \DateTime($end_date);
            $end_date = $end_date->format('Y-m-d');
            
            if ($appkey && $domain && $start_date && $end_date) {
                $ttl = (int)config('calendar.cache_ttl');
                $cache_id = sha1('cacheIsDayOff_'.$appkey.'_'.$domain.'_'.$start_date.'_'.$end_date);                
                $tag = sha1($appkey.'_'.$domain);
                $res = Cache::tags($tag)->get($cache_id);
                
                if ($res === null) {
                    $daysoff = DayOff::where('appkey', $appkey)
                            ->where('domain', $domain)
                            ->where('date_dayoff', '>=', $start_date)
                            ->where('date_dayoff', '<=', $end_date)->get();
                    
                    $res = $daysoff->count() ? true : false;                   
                    
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
     * Crea un nuevo registro de tipo dayOff
     * 
     * @param string $appkey
     * @param string $domain
     * @param array $data     
     * @return Collection
     */
    public function createDayOff($appkey, $domain, $data)
    {
        $res = array();
        
        try {            
            $apps = App::where('appkey', $data['appkey'])
                            ->where('domain', $data['domain'])
                            ->where('status', 1)->value('appkey');
            
            if ($apps) {
                $cal = new CalendarRepository();
                
                if ($data['date_dayoff'] >= date('Y-m-d')) {
                    //Verifico que no hayan citas programadas para ese dia
                    if (!$cal->hasAvailableAppointmentByDate($appkey, $domain, $data['date_dayoff'])) {
                        $dayoff = DayOff::create($data);
                        $res['error'] = null;

                        $tag = sha1($appkey.'_'.$domain);
                        Cache::tags($tag)->flush();
                    } else {
                        $res['error'] = new \Exception('', 1080);
                    }
                } else {
                    $res['error'] = new \Exception('', 1090);
                }
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
     * Elimina un registro de tipo dayOff
     *      
     * @param string $appkey
     * @param string $domain
     * @param int $id     
     * @return Collection
     */
    public function destroyDayOff($appkey, $domain, $id)
    {
        $res = array();
        
        try {
            $dayoff = DayOff::destroy($id);
            $res['error'] = $dayoff === false ? new \Exception('', 500) : null;
            
            $tag = sha1($appkey.'_'.$domain);
            Cache::tags($tag)->flush();
        } catch (QueryException $qe) {            
                $res['error'] = $qe;
        } catch (Exception $e) {
            $res['error'] = $e;
        }
        
        return $res;
    }
}
