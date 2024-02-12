<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CreateHubRegistrationSettings extends Model
{
    use HasFactory;
    protected $fillable = [
        'hub_id',
        'structure',
    ];
}
