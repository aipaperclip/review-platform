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
        'trigger_type',
        'question',
        'answers',
        'vox_scale_id',
        'is_control',
        'order',
        'used_for_stats',
        'stats_title',
        'stats_relation_id',
        'stats_answer_id',
        'stats_featured',
        'stats_fields',
    ];

    public $timestamps = false;
    
    public function vox() {
        return $this->hasOne('App\Models\Vox', 'id', 'vox_id');
    }

    public function related() {
        return $this->hasOne('App\Models\VoxQuestion', 'id', 'stats_relation_id');
    }

    public function respondents() {
        return $this->hasMany('App\Models\VoxAnswer', 'question_id', 'id')->where('is_completed', 1)->has('user');
    }
    public function respondent_count() {
        return $this->hasMany('App\Models\VoxAnswer', 'question_id', 'id')->where('is_completed', 1)->has('user')->count();
    }



    public function getStatsFieldsAttribute($value)
    {
        return $value ? explode(',', $value) : [];
    }

    public function setStatsFieldsAttribute($value)
    {
        $this->attributes['stats_fields'] = implode(',', $value);
    }


}

class VoxQuestionTranslation extends Model {

    public $timestamps = false;
    protected $fillable = [
        'question',
        'answers',
        'stats_title',
    ];

}



?>