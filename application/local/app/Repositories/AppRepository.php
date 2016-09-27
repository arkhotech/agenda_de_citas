<?php

/**
 * Repository App
 * 
 * @author Manuel Vargas <mvargas@arkho.tech>
 */

namespace App\Repositories;

use App\App;
use DB;
use Illuminate\Support\Facades\Cache;
use \Illuminate\Database\QueryException;

class AppRepository
{
    /**
     * Obtiene la aplicacion correspondiente a una appkey y domain
     * 
     * @param string $appkey
     * @param string $domain
     * @return Collection
     */
    public function listApp($appkey, $domain)
    {
        $res = array();

        try {

            $ttl = (int)config('calendar.cache_ttl');
            $cache_id = 'cacheAppList';
            $res = Cache::get($cache_id);
                    
            if ($res === null) {
                
                if (!empty($appkey) && !empty($domain)) {
                    $app = App::where('appkey', $appkey)
                        ->where('domain', $domain)->get();
                } else {
                    $app = App::all();
                }
                
                $res['data'] = $app;
                $res['count'] = $app->count();                
                $res['error'] = null;

                Cache::put($cache_id, $res, $ttl);
            }
        } catch (QueryException $qe) {
            $res['error'] = $qe;
        } catch (Exception $e) {
            $res['error'] = $e;
        }        
        
        return $res;
    }

    /**
     * Crea un nuevo registro de tipo App
     * 
     * @param array $data
     * @return Collection
     */
    public function createApp($data)
    {
        $res = array();
        
        try {
            $data['appkey'] = uniqid(App::count());
            $data['status'] = 1;

            $app = App::create($data);
            $res['data'] = $app;
            $res['error'] = null;

            Cache::forget('cacheAppList');
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
     * Actualiza un nuevo registro de tipo App
     * 
     * @param array $data
     * @return Collection
     */
    public function updateApp($data, $appkey, $domain)
    {
        $res = array();
        
        try {

            $app = App::where('appkey', $appkey)
                        ->where('domain', $domain)
                        ->update($data);

            if ($app === false) {
                $res['error'] = new \Exception('', 500);
            } elseif ($app == 0) {
                $res['error'] = new \Exception('', 4020);
            } else {
                $res['error'] = null;
            }
            
            Cache::forget('cacheAppList');
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
     * Actualiza un nuevo registro de tipo App
     * 
     * @param array $data
     * @return Collection
     */
    public function changeStatusApp($data, $appkey, $domain)
    {
        $res = array();
        
        try {

            $app = App::where('appkey', $appkey)
                        ->where('domain', $domain)
                        ->update($data);

            if ($app === false) {
                $res['error'] = new \Exception('', 500);
            } elseif ($app == 0) {
                $res['error'] = new \Exception('', 4030);
            } else {
                $res['error'] = null;
            }
            
            Cache::forget('cacheAppList');
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
}
