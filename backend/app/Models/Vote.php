<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Vote extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'image_id', 'ronda'];

    /**
     * Define la relación inversa: un voto pertenece a un usuario.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Define la relación inversa: un voto pertenece a una imagen.
     */
    public function image()
    {
        return $this->belongsTo(Image::class);
    }
}
