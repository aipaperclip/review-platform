<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use WebPConvert\WebPConvert;

use Image;

class DentistTestimonial extends Model {
    
    use SoftDeletes;

    protected $fillable = [
        'image',
        'description',
        'name',
        'job',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    public function getImageUrl() {
        return url('/storage/testimonials/'.($this->id%100).'/'.$this->id.'.png').'?rev='.$this->updated_at->timestamp;
    }
    public function getImagePath() {
        $folder = storage_path().'/app/public/testimonials/'.($this->id%100);
        if(!is_dir($folder)) {
            mkdir($folder);
        }
        return $folder.'/'.$this->id.'.png';
    }

    public function addImage($img) {

        $to = $this->getImagePath();
        $img->fit( 400, 400 );
        $img->save($to);
        $this->image = true;
        $this->save();

        $destination = self::getImagePath().'.webp';
        WebPConvert::convert(self::getImagePath(), $destination, []);
    }
    
}



?>