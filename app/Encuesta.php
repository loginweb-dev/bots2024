<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class Encuesta extends Model
{
    protected $fillable = ['interractedAtTs', 'selectedOptions', 'isSentCagPollCreation', 'messageSecret', 'pollName', 'allowMultipleAnswers', 'voter', 'pollOptions', 'pollInvalidated'];
}
