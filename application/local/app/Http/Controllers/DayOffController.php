<?php

/**
 * Controller DayOff
 * 
 * @author Geovanni Escalante <gescalante@arkho.tech>
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\DayOffRepository;
use App\Response as Resp;
use Validator;

class DayOffController extends Controller
{
    /**
     * Instancia de CalendarRepository
     *
     * @var CalendarRepository
     */
    protected $daysoff;

    /**
     * Crea una nueva instancia Controller
     *
     * @param DayOffRepository  $daysoff
     * @return void
     */
    public function __construct(DayOffRepository $daysoff)
    {
        $this->daysoff = $daysoff;
    }
    
    /**
     * Controller que despliega listado de dias no laborales
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {  
        $appkey = $request->header('appkey');
        $resp = array();
        
        if (!empty($appkey)) {
            $daysoff = $this->daysoff->listDayOff($appkey);
            
            if (isset($daysoff['error']) && is_a($daysoff['error'], 'Exception')) {
                $resp = Resp::error(500, $daysoff['error']->getCode(), '', $daysoff['error']);
            } else {
                if (count($daysoff['data']) > 0) {
                    $dayoff['daysoff'] = $daysoff['data'];
                    $dayoff['count'] = $daysoff['count'];
                    $resp = Resp::make(200, $dayoff);
                } else {
                    $resp = Resp::error(404, 1070);
                }
            }
        } else {
            $resp = Resp::error(400, 1000);
        }
        
        return $resp;
    }
    
    /**
     * Crea un nuevo registro de tipo dayoff
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $resp = array();
        $data = $request->json()->all();
        $appkey = $request->header('appkey');
        $data['appkey'] = $appkey;
        
        if (!empty($appkey)) {
            $validator = Validator::make($data, [
                'name' => 'bail|required|max:70',
                'date_dayoff' => 'bail|required|isodate',
                'appkey' => 'required|max:15'
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
                if (!$this->daysoff->dayOffExists($appkey, $data['date_dayoff'])) {
                    $dayoffs = $this->daysoff->createDayOff($appkey, $data);
                    
                    if (isset($dayoffs['error']) && is_a($dayoffs['error'], 'Exception')) {                
                        $resp = Resp::error(500, $dayoffs['error']->getCode(), '', $dayoffs['error']);
                    } else {                
                        $resp = Resp::make(201);
                    }
                } else {
                    $resp = Resp::error(406, 2001);
                }
            }
        } else {
            return Resp::error(400, 5000); 
        }
        
        return $resp;
    }

    /**
     * Crea un nuevo registro de tipo dayoff basado en un array
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function bulkLoad(Request $request)
    {
        $resp = array();
        $data = $request->json()->all();
        $appkey = $request->header('appkey');
        $domain = $request->header('domain');
        $data['appkey'] = $appkey;

        if (!empty($appkey) && !empty($domain)) {
            $validator = Validator::make($data, [
                'daysOffBulk' => 'required',
                'appkey' => 'required|max:15'
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
                $dayoffs = $this->daysoff->createDayOffBulkLoad($appkey, $domain, $data);

                if (isset($dayoffs['error']) && is_a($dayoffs['error'], 'Exception')) {                
                    $resp = Resp::error(500, $dayoffs['error']->getCode(), '', $dayoffs['error']);
                } else {                
                    $resp = Resp::make(201);
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
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        $appkey = $request->header('appkey');
        $resp = array();
        
        if (!empty($appkey)) {
            if ((int)$id <= 0) {
                $resp = Resp::error(400, 1020);            
            } else {
                $daysoff = $this->daysoff->destroyDayOff($appkey, $id);

                if (isset($daysoff['error']) && is_a($daysoff['error'], 'Exception')) {                
                    $resp = Resp::error(500, $daysoff['error']->getCode(), '', $daysoff['error']);
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
