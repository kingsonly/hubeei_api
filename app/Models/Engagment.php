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
        return $this->HasMany(Engagementanswers::class, "engagment_id", "id");
    }

    public function optionAnswerCounts()
    {
        return $this->options->mapWithKeys(function ($option) {
            return [
                $option->id => $this->answers->where('option_id', $option->id)->count(),
            ];
        });
    }

    public function userAnswer()
    {
        return $this->hasMany(Engagementanswers::class, 'engagment_id');
    }

}
