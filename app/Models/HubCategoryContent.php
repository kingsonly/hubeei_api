<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HubCategoryContent extends Model
{
    use HasFactory;
    public function category()
    {
        return $this->HasOne(HubCategory::class, "id", "hub_category_id");
    }
}
