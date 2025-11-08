<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ThemeVote extends Model
{
    use HasFactory;

    /**
     * Los atributos que se pueden asignar masivamente.
     */
    protected $fillable = [
        'user_id',
        'theme_id',
        'ronda',
    ];

    /**
     * Define la relación: un voto de tema pertenece a un usuario.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Define la relación: un voto de tema pertenece a un tema.
     */
    public function theme()
    {
        return $this->belongsTo(Theme::class);
    }
}
