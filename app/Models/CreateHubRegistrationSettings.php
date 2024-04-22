<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CreateHubRegistrationSettings extends Model
{
    use HasFactory;
    protected $fillable = [
        'hub_id',
        "hub_id",
        "with_payment",
        "tenure",
        "primary_amount",
    ];

    public function hubRegistrationSettingFields()
    {
        return $this->hasMany(HubSubsribtionRequiredFields::class, "hub_registration_settings_id", "id");
    }
}
