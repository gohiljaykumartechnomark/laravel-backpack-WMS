<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;
use App\Models\BroadcastHasContact;
use App\Models\Message;

class Broadcast extends Model
{
    use CrudTrait;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $table = 'broadcasts';
    // protected $primaryKey = 'id';
    // public $timestamps = false;
    protected $guarded = ['id'];
    // protected $fillable = [];
    // protected $hidden = [];
    // protected $dates = [];

    public static function boot()
    {
        parent::boot();
        static::deleting(function($broadcast) {
            BroadcastHasContact::where('broadcast_id',$broadcast->id)->delete();
            Message::where('broadcast_id',$broadcast->id)->delete();
        });
    }

    public function contacts()
    {
        return $this->hasMany(Contact::class,'id');
    }

    public function broadcastHasContacts()
    {
        return $this->belongsToMany(Broadcast::class,'broadcast_has_contacts', 'broadcast_id', 'contact_id');
    }

}
