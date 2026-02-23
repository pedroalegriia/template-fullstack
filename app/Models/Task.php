<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $fillable = ['title', 'description', 'status'];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public const STATUS_PENDIENTE = 'pendiente';
    public const STATUS_EN_PROGRESO = 'en_progreso';
    public const STATUS_COMPLETADA = 'completada';

    public static function statuses(): array
    {
        return [
            self::STATUS_PENDIENTE,
            self::STATUS_EN_PROGRESO,
            self::STATUS_COMPLETADA,
        ];
    }
}
