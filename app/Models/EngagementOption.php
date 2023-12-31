<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EngagementOption extends Model
{
    use HasFactory;

    public function answers()
    {
        return $this->HasMany(Engagementanswers::class, "option_id", "id");
    }

}
