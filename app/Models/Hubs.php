<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hubs extends Model
{

    use HasFactory;

    protected $table = 'hubs';

    public function categories()
    {
        return $this->hasMany(HubCategory::class, "hub_id", "id");
    }
    public function settings()
    {
        return $this->hasMany(HubSettings::class, "hub_id", "id");
    }
    public function createHubRegistrationSettings()
    {
        return $this->hasOne(CreateHubRegistrationSettings::class, "hub_id", "id");
    }
}
