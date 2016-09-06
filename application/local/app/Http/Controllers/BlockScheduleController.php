<?php

/**
 * Controller Calendar
 * 
 * @author Geovanni Escalante <gescalante@arkho.tech>
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\BlockScheduleRepository;
use App\Response as Resp;
use Validator;

class BlockScheduleController extends Controller
{
    /**
     * Instancia de BlockScheduleRepository
     *
     * @var BlockScheduleRepository
     */
    protected $blockSchedules;

    /**
     * Crea una nueva instancia Controller
     *
     * @param BlockScheduleRepository $blockSchedule
     * @return void
     */
    public function __construct(BlockScheduleRepository $blockSchedules)
    {
        $this->blockSchedules = $blockSchedules;
    }

    /**
     * Controller que despliega listado de blockschedules
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $id)
    {  
        $appkey = $request->header('appkey');
        $domain = $request->header('domain');
        $resp = array();
        
        if (!empty($appkey) && !empty($domain)) {            
            if ((int)$id <= 0) {
                $resp = Resp::error(400, 1020);            
            } else {
                $blockschedule = $this->blockSchedules->listBlockScheduleByCalendarId($appkey, $domain, $id);

                if (isset($blockschedule['error']) && is_a($blockschedule['error'], 'Exception')) {
                    $resp = Resp::error(500, $blockschedule['error']->getCode(), '', $blockschedule['error']);
                } else {
                    if (count($blockschedule['data']) > 0) {
                        $blockschedules['blockSchedules'] = $blockschedule['data'];
                        $blockschedules['count'] = $blockschedule['count'];
                        $resp = Resp::make(200, $blockschedules);
                    } else {
                        $resp = Resp::error(404, 1070);
                    }
                }
            }
        } else {
            $resp = Resp::error(400, 1000);
        }
        
        return $resp;
    }
    
    /**
     * Crea un nuevo registro de tipo blockschedule
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
                'calendar_id' => 'bail|required|integer',
                'user_id_block' => 'bail|required|max:20',
                'user_name_block' => 'bail|required|max:150',
                'start_date' => 'bail|required|isodate',
                'end_date' => 'bail|required|isodate',
                'cause' => 'required',
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
                $blockschedule = $this->blockSchedules->createBlockSchedule($appkey, $domain, $data);

                if (isset($blockschedule['error']) && is_a($blockschedule['error'], 'Exception')) {                
                    $resp = Resp::error(500, $blockschedule['error']->getCode(), '', $blockschedule['error']);
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
     * Elimina un registro de tipo blockschedule
     * 
     * @param  \Illuminate\Http\Request $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        $appkey = $request->header('appkey');
        $domain = $request->header('domain');
        $resp = array();
        
        if (!empty($appkey) && !empty($domain)) {
            if ((int)$id <= 0) {
                $resp = Resp::error(400, 1020);            
            } else {
                $blockschedule = $this->blockSchedules->destroyBlockSchedule($appkey, $domain, $id);

                if (isset($blockschedule['error']) && is_a($blockschedule['error'], 'Exception')) {                
                    $resp = Resp::error(500, $blockschedule['error']->getCode(), '', $blockschedule['error']);
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
