<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubsribersHub extends Model
{
    use HasFactory;
    protected $fillable = [
        'subscribers_id',
        'hub_id',
    ];

    public function subscriber()
    {
        return $this->hasOne(HubSubscription::class, "id", "subscribers_id");
    }
    public function subscriberData()
    {
        return $this->hasMany(HubSubsriberData::class, "subsribers_hubs_id", "id");
    }
}
