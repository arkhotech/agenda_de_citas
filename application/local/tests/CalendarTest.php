<?php

/**
 * Unit Test Calendar
 * 
 * @author Geovanni Escalante <gescalante@arkho.tech>
 */

use Illuminate\Foundation\Testing\WithoutMiddleware;

class CalendarTest extends TestCase
{
    use WithoutMiddleware;
    
    /**
     * Test Get Request calendars
     *
     * @return void
     */
    public function testGetCalendars()
    {
        $this->refreshApplication();
        
        $headers = array(
            'HTTP_APPKEY' => 'mMRUI7s7Nn0yGq0', 
            'HTTP_DOMAIN' => 'Santiago'
        );
        
        $response = $this->get('calendars', $headers);
        $response->seeStatusCode(200);
    }
    
    /**
     * Test Get Request calendars
     *
     * @return void
     */
    public function testPostCalendars()
    {
        $this->refreshApplication();
        
        $data = array(
            'name' => 'Agenda Pedro Fernandez',
            'owner_id' => 1,
            'owner_name' => 'Pedro Fernandez',
            'is_group' => 0,
            'schedule' => 'Lunes, Martes, Miercoles, Jueves, Viernes',
            'time_attention' => 30,
            'concurrency' => 1,
            'ignore_non_working_days' => 0,
            'time_cancel_appointment' => 4,
            'appkey' => 'mMRUI7s7Nn0yGq0',
            'domain' => 'Santiago'
        );
        
        $response = $this->post('calendars', $data);
        $response->seeStatusCode(201);
    }
}
