<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentSetting extends Model
{
    protected $fillable = [
        'registrar_name',
        'registrar_credentials',
        'registrar_title',
    ];

    public static function current(): self
    {
        return static::first() ?? new static([
            'registrar_name' => '',
            'registrar_credentials' => '',
            'registrar_title' => '',
        ]);
    }
}
