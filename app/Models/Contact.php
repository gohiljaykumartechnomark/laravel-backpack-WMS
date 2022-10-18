<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Intervention\Image\ImageManagerStatic as Image;
use App\Models\BroadcastHasContact;
use File;

class Contact extends Model
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

        static::deleting(function($contact) {
            BroadcastHasContact::where('contact_id',$contact->id)->delete();
        });
        static::deleted(function($contact) {
            if (File::exists(public_path($contact->path))) {
                File::delete(public_path($contact->path));
            }
        });
    }
    
    protected $table = 'contacts';
    // protected $primaryKey = 'id';
    // public $timestamps = false;
    protected $guarded = ['id'];
    // protected $fillable = [];
    // protected $hidden = [];
    // protected $dates = [];

    public function setPathAttribute($value)
    {
        $attribute_name = "path";
        // or use your own disk, defined in config/filesystems.php
        $disk = 'root';
        // destination path relative to the disk above
        $destination_path = "public/uploads/images/contact";

        // if the image was erased
        if ($value==null) {
            // delete the image from disk
            \Storage::disk($disk)->delete($this->{$attribute_name});

            // set null in the database column
            $this->attributes[$attribute_name] = null;
        }

        // if a base64 was sent, store it in the db
        if (Str::startsWith($value, 'data:image'))
        {
            // 0. Make the image
            $image = Image::make($value)->encode('jpg', 90);

            // 1. Generate a filename.
            $filename = "contact_" .md5($value.time()).'.jpg';

            // 2. Store the image on disk.
            \Storage::disk($disk)->put($destination_path.'/'.$filename, $image->stream());

            // 3. Delete the previous image, if there was one.
            \Storage::disk($disk)->delete($this->{$attribute_name});

            // 4. Save the public path to the database
            // but first, remove "public/" from the path, since we're pointing to it
            // from the root folder; that way, what gets saved in the db
            // is the public URL (everything that comes after the domain name)
            $public_destination_path = Str::replaceFirst('public/', '', $destination_path);
            $this->attributes[$attribute_name] = $public_destination_path.'/'.$filename;
        }
    }

    /*public function sampleFile($crud = false)
    {
        return '<a href="'.asset('file/Sample Contact.xlsx').'" class="btn btn-primary" data-style="zoom-in" download><span class="ladda-label"><i class="la la-cloud-upload"></i> Import Contact</span></a>';
        // return '<a href="'.asset('file/Sample Contact.xlsx').'" class="btn btn-primary" data-style="zoom-in" download><span class="ladda-label"><i class="la la-cloud-download"></i> Sample File</span></a>';
    }*/

}
