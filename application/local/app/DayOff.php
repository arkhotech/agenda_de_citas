<?php

/**
 * Model DayOff
 * 
 * @author Geovanni Escalante <gescalante@arkho.tech>
 */

namespace App;

use Illuminate\Database\Eloquent\Model;

class DayOff extends Model
{    
	protected $table = 'non_working_days';
    
    public $timestamps = false;
    
	protected $fillable = array(
        'name',
        'date_dayoff',
        'appkey',
        'domain'
    );
    
    protected $hidden = array(
        'appkey',
        'domain'
    );
}
