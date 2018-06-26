<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VoxQuestion extends Model {
    
    use \Dimsav\Translatable\Translatable;
    
    public $translatedAttributes = [
        'question',
        'answers',
    ];

    protected $fillable = [
        'vox_id',
        'type',
        'question_trigger',
        'question',
        'answers',
        'vox_scale_id',
        'is_control',
        'order',
    ];

    public $timestamps = false;
    public function vox() {
        return $this->hasOne('App\Models\Vox', 'id', 'vox_id');
    }

}

class VoxQuestionTranslation extends Model {

    public $timestamps = false;
    protected $fillable = [
        'question',
        'answers',
    ];

}



?>