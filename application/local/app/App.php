<?php

/**
 * Model App
 * 
 * @author Geovanni Escalante <gescalante@arkho.tech>
 */

namespace App;

use Illuminate\Database\Eloquent\Model;

class App extends Model
{    
	protected $table = 'apps';
    
    protected $primaryKey = 'appkey';
    
    public $incrementing = false;
    
    public $timestamps = false;
    
	protected $fillable = array(
        'appkey',
        'domain',
        'name',
        'contact_email',
        'from_email',
        'from_name',
        'html_confirmation_email',
        'html_modify_email',
        'html_cancel_email',
        'status'
    );
}
