<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\RecordsActivity;

class PreviousSchoolRecordPage extends Model
{
    use RecordsActivity;

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
