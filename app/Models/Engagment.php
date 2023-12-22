<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Engagment extends Model
{
    use HasFactory;

    public function options()
    {
        return $this->HasMany(EngagementOption::class, "engagment_id", "id");
    }

    public function answers()
    {
        return $this->HasMany(Engagementanswers::class, "engagment_option_id", "id");
    }

}
