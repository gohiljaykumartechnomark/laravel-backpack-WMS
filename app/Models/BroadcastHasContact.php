<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;

class BroadcastHasContact extends Model
{
    use CrudTrait;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $table = 'broadcast_has_contacts';
    // protected $primaryKey = 'id';
    // public $timestamps = false;
    // protected $guarded = ['id'];
    protected $fillable = ['broadcast_id','contact_id'];
    // protected $hidden = [];
    // protected $dates = [];
    protected $casts = [
        'contact_id' => 'array',
    ];

    public function broadcast()
    {
        return $this->belongsTo(Broadcast::class,'broadcast_id');
    }

    public function contact()
    {
        return $this->belongsTo(Contact::class,'contact_id');
    }
}
