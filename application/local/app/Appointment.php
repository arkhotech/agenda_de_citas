<?php

/**
 * Model Calendar
 * 
 * @author Geovanni Escalante <gescalante@arkho.tech>
 */

namespace App;

use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{    
	protected $table = 'appointments';
    
    public $timestamps = false;
    
	protected $fillable = array(
        'applier_id',
        'applier_name',
        'applier_email',
        'calendar_id',
        'subject',
        'appointment_start_time',
        'appointment_end_time',
        'is_reserved',
        'reservation_date',
        'confirmation_date',
        'metadata',
        'user_id_cancel',
        'user_name_cancel',
        'is_canceled',
        'cancelation_date',
        'cancelation_cause',
        'applier_attended'
    );
    
    /**
     * Obtiene el calendario al cual pertenece
     */
    public function calendar()
    {
        return $this->belongsTo('App\Calendar');
    }
}
