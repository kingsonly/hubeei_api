<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class HubSubscription extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    public function subscribedHub()
    {
        return $this->hasMany(SubsribersHub::class, "subscribers_id", "id");
    }
}
