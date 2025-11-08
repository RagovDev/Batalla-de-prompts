<?php

namespace App\Models;
use App\Models\User;
use App\Models\Vote;

use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    /**
     * Los atributos que se pueden asignar masivamente.
     *
     */
    protected $fillable = [
        'user_id',
        'image_url',
        'ronda',
        'votes',
    ];

    /**
     * Define la relación inversa: una imagen pertenece a un usuario.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Define la relación: una imagen puede tener muchos votos.
     */
    public function votes()
    {
        return $this->hasMany(Vote::class);
    }
}
