<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PreviousSchoolRecordPage extends Model
{
    protected $fillable = [
        'previous_school_record_id',
        'path',
        'width',
        'height',
        'sort_order',
    ];

    protected $casts = [
        'width'      => 'integer',
        'height'     => 'integer',
        'sort_order' => 'integer',
    ];

    public function record()
    {
        return $this->belongsTo(PreviousSchoolRecord::class, 'previous_school_record_id');
    }
}
