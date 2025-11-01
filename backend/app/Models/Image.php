<?php

namespace App\Models;
use App\Models\User;

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

    public function user()
    {
        // Define que este modelo (Image) pertenece a un modelo User.
        // Laravel asumirá que la clave foránea es 'user_id' por convención.
        return $this->belongsTo(User::class);
    }
}
