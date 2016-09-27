<?php

/**
 * Controller App
 * 
 * @author Manuel Vargas <mvargas@arkho.tech>
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\AppRepository;
use App\Response as Resp;
use Validator;

class AppController extends Controller
{
    /**
     * Instancia de AppRepository
     *
     * @var AppRepository
     */
    protected $app;

    /**
     * Crea una nueva instancia Controller
     *
     * @param AppRepository  $app
     * @return void
     */
    public function __construct(AppRepository $app)
    {
        $this->app = $app;
    }
    
    /**
     * Controller que despliega listado de App
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $appkey = $request->input('appkey', '');
        $domain = $request->input('domain', '');
        $resp = array();
        
        $app = $this->app->listApp($appkey, $domain);

        if (isset($app['error']) && is_a($app['error'], 'Exception')) {
            $resp = Resp::error(500, $app['error']->getCode(), '', $app['error']);
        } else {
            if (count($app['data']) > 0) {
                $apps['apps'] = $app['data'];
                $apps['count'] = $app['count'];
                $resp = Resp::make(200, $apps);
            } else {
                $resp = Resp::error(404, 4010);
            }
        }

        return $resp;
    }

    /**
     * Crea un nuevo registro de tipo App
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $resp = array();
        $data = $request->json()->all();

        $validator = Validator::make($data, [
            'domain' => 'required|max:150',
            'name' => 'required|max:70',
            'contact_email' => 'required|max:150',
            'from_email' => 'required|max:150',
            'from_name' => 'required|max:70'
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
            $apps = $this->app->createApp($data);
            
            if (isset($apps['error']) && is_a($apps['error'], 'Exception')) {                
                $resp = Resp::error(500, $apps['error']->getCode(), '', $apps['error']);
            } else {
                $app['app']['appkey'] = $apps['data']['appkey'];
                $app['app']['domain'] = $apps['data']['domain'];
                $resp = Resp::make(201, $app);
            }
        }
        
        return $resp;
    }

    /**
     * Actualiza un registro de tipo App
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {        
        $resp = array();
        $appkey = $request->header('appkey');
        $domain = $request->header('domain');
        $data = $request->json()->all();
        
        $validator = Validator::make($data, [
            'name' => 'required|max:70',
            'contact_email' => 'required|max:150',
            'from_email' => 'required|max:150',
            'from_name' => 'required|max:70'
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
            $apps = $this->app->updateApp($data, $appkey, $domain);
            
            if (isset($apps['error']) && is_a($apps['error'], 'Exception')) {                
                $resp = Resp::error(500, $apps['error']->getCode(), '', $apps['error']);
            } else {
                $resp = Resp::make(200);
            }
        }
        
        return $resp;
    }

    /**
     * Cambia el estado de una App
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function changeStatus(Request $request)
    {        
        $resp = array();
        $appkey = $request->header('appkey');
        $domain = $request->header('domain');
        $data = $request->json()->all();
        
        $validator = Validator::make($data, [
            'status' => 'bail|required|boolean'
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
            $apps = $this->app->changeStatusApp($data, $appkey, $domain);
            
            if (isset($apps['error']) && is_a($apps['error'], 'Exception')) {                
                $resp = Resp::error(500, $apps['error']->getCode(), '', $apps['error']);
            } else {
                $resp = Resp::make(200);
            }
        }
        
        return $resp;
    }
}
