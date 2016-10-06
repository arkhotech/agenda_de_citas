<?php

/**
 * Model Calendar
 * 
 * @author Geovanni Escalante <gescalante@arkho.tech>
 */

namespace App;

use Illuminate\Database\Eloquent\Model;

class BlockSchedule extends Model
{    
	protected $table = 'block_schedules';
    
    public $timestamps = false;
    
	protected $fillable = array(
        'calendar_id',
        'user_id_block',
        'user_name_block',
        'start_date',
        'end_date',
        'cause',
        'created_date'
    );
    
    protected $hidden = array(
        'created_date'
    );
}
