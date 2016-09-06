<?php

/**
 * Model Calendar
 * 
 * @author Geovanni Escalante <gescalante@arkho.tech>
 */

namespace App;

use Illuminate\Database\Eloquent\Model;

class Calendar extends Model
{    
	protected $table = 'calendars';
    
    public $timestamps = false;
    
	protected $fillable = array(
        'name',
        'owner_id',
        'owner_name',
        'owner_email',
        'is_group',
        'schedule',
        'time_attention',
        'concurrency',
        'ignore_non_working_days',
        'time_cancel_appointment',
        'appkey',
        'domain',
        'status'
    );
    
    protected $hidden = array(
        'status'
    );
    
    /**
     * Obtiene todas las citas que pertenecen a un calendario
     */
    public function appointments()
    {
        return $this->hasMany('App\Appointment');
    }
}
