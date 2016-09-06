<?php

/**
 * Repository BlockScheduleRepository
 * 
 * @author Geovanni Escalante <gescalante@arkho.tech>
 */

namespace App\Repositories;

use Log;
use App\BlockSchedule;
use App\Calendar;
use Illuminate\Support\Facades\Cache;
use \Illuminate\Database\QueryException;

class BlockScheduleRepository
{
    /**
     * Lista todos los blockschedules
     * 
     * @param string $appkey
     * @param string $domain
     * @return Collection
     */
    public function listBlockScheduleByCalendarId($appkey, $domain, $id)
    {
      $res = array();

        try {

            $ttl = (int)config('calendar.cache_ttl');
            $cache_id = sha1('cacheBlockScheduleList_'.$appkey.'_'.$domain.'_'.$id);
            $tag = sha1($appkey.'_'.$domain);
            $res = Cache::tags($tag)->get($cache_id);
                    
            if ($res === null) {
                
                if (!empty($appkey) && !empty($domain)) {
                    $blockSchedules = BlockSchedule::where('calendar_id', $id)->get()   ;
                } else {
                    $blockSchedules = BlockSchedule::all();
                }
                
                $i = 0;
                $blockSchedule_array = array();
                foreach ($blockSchedules as $blockSchedule) {
                    $date_ini = new \DateTime($blockSchedule->start_date);
                    $date_end = new \DateTime($blockSchedule->end_date);
                    $blockSchedule_array[$i]['id'] = $blockSchedule->id;
                    $blockSchedule_array[$i]['calendar_id'] = $blockSchedule->calendar_id;
                    $blockSchedule_array[$i]['user_id_block'] = $blockSchedule->user_id_block;
                    $blockSchedule_array[$i]['user_name_block'] = $blockSchedule->user_name_block;
                    $blockSchedule_array[$i]['start_date'] = $date_ini->format('Y-m-d\TH:i:sO');
                    $blockSchedule_array[$i]['end_date'] = $date_end->format('Y-m-d\TH:i:sO');
                    $blockSchedule_array[$i]['cause'] = $blockSchedule->cause;
                    $blockSchedule_array[$i]['created_date'] = $blockSchedule->created_date;
                    
                    $i++;
                }
                
                $res['data'] = $blockSchedule_array;
                $res['count'] = $blockSchedules->count();                
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
     * Crea un nuevo registro de tipo block schedule
     * 
     * @param string $appkey
     * @param string $domain
     * @param array $data     
     * @return Collection
     */
    public function createBlockSchedule($appkey, $domain, $data)
    {
        $res = array();
        
        try {            
            $calendar = Calendar::where('id', $data['calendar_id'])->get();
            
            if ($calendar->count() > 0) {
                $start_date = new \DateTime($data['start_date']);
                $start_date = $start_date->format('Y-m-d H:i:s');
                $end_date = new \DateTime($data['end_date']);
                $end_date = $end_date->format('Y-m-d H:i:s');
                
                if ($start_date >= date('Y-m-d H:i:s')) {
                    if ($end_date > $start_date) {
                        $data['created_date'] = date('Y-m-d H:i:s');
                        BlockSchedule::create($data);
                        $res['error'] = null;

                        $tag = sha1($appkey.'_'.$domain);
                        Cache::tags($tag)->flush();
                    } else {
                        $res['error'] = new \Exception('', 2080);
                    }
                } else {
                    $res['error'] = new \Exception('', 2090);
                }
            } else {
                $res['error'] = new \Exception('', 1010);
            }
        } catch (QueryException $qe) {
            $res['error'] = $qe;
        } catch (Exception $e) {
            $res['error'] = $e;
        }
        
        return $res;        
    }
    
    /**
     * Elimina un registro de tipo BlockSchedule
     * 
     * @param string $appkey
     * @param string $domain
     * @param int $calendar_id
     * @param date $start_date
     * @param date $end_date     
     * @return bool
     */
    public function destroyBlockSchedule($appkey, $domain, $id)
    {
        $res = array();
        
        try {
            $blockSchedule = BlockSchedule::destroy($id);
            $res['error'] = $blockSchedule === false ? new \Exception('', 500) : null;
            
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
     * Valida si hay bloqueo de horario en una fecha especifica
     * 
     * @param string $appkey
     * @param string $domain
     * @param int $calendar_id
     * @param date $start_date
     * @param date $end_date     
     * @return bool
     */
    public function validateBlock($appkey, $domain, $calendar_id, $start_date, $end_date)
    {
        $res = true;        
        
        try {
            if ((int)$calendar_id > 0 && $start_date && $end_date) {
                $start_date = new \DateTime($start_date);
                $start_date = $start_date->format('Y-m-d H:i:s');
                $end_date = new \DateTime($end_date);
                $end_date = $end_date->format('Y-m-d H:i:s');
            
                $ttl = (int)config('calendar.cache_ttl');
                $cache_id = sha1('cacheIsScheduleBlock_'.$calendar_id.'_'.$start_date.'_'.$end_date);
                $tag = sha1($appkey.'_'.$domain);
                $res = Cache::tags($tag)->get($cache_id);
                
                if ($res === null) {
                    $blocks = BlockSchedule::where('end_date', '>=', date('Y-m-d H:i:s'))
                          ->where('start_date', '<', $end_date)
                          ->Where('end_date', '>', $start_date)                          
                          ->where('calendar_id', $calendar_id)->get();
                    
                    $res = $blocks->count() ? true : false;                    
                    
                    Cache::tags([$tag])->put($cache_id, $res, $ttl);
                }
            }
        } catch (QueryException $qe) {
            Log::error('code: ' .  $qe->getCode() . ' Message: ' . $qe->getMessage());
        } catch (Exception $e) {
            Log::error('code: ' .  $qe->getCode() . ' Message: ' . $qe->getMessage());
        }        
        
        return $res;
    }
}
