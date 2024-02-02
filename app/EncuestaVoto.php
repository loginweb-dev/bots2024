<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class EncuestaVoto extends Model
{
    protected $fillable = ['selectedOptions', 'voter', 'encuesta_id'];
}
