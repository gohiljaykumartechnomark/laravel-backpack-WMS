<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;
use App\Jobs\SendWhatsAppMessageJob;
use Illuminate\Support\Str;

class Message extends Model
{
    use CrudTrait;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    public static function boot()
    {
        parent::boot();
        static::created(function($message) {
            dispatch(new SendWhatsAppMessageJob($message));
            // SendWhatsAppMessageJob::dispatch($message);
        });
    }

    protected $table = 'messages';
    // protected $primaryKey = 'id';
    // public $timestamps = false;
    protected $guarded = ['id'];
    // protected $fillable = [];
    // protected $hidden = [];
    // protected $dates = [];

    public function broadcast()
    {
        return $this->belongsTo(Broadcast::class,'broadcast_id');
    }
    
    public function setPathAttribute($value)
    {

        $attribute_name = "path";
        $disk = "root";
        $destination_path = "public/uploads/message/files";

        // if the File was erased
        if ($value==null) {
            // delete the File from disk
            \Storage::disk($disk)->delete($this->{$attribute_name});

            // set null in the database column
            $this->attributes[$attribute_name] = null;
        }

        if ($value) {
            $ext = '.' . $value->getClientOriginalExtension();
            $filename = "file_" .md5($value.time()) . $ext;

            \Storage::disk($disk)->put($destination_path.'/'.$filename, file_get_contents($value->getRealPath()));
            $public_destination_path = Str::replaceFirst('public/', '', $destination_path);
            $this->attributes[$attribute_name] = $public_destination_path.'/'.$filename;
        }
    }
}
