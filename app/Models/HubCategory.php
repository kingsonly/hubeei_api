<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HubCategory extends Model
{
    use HasFactory;

    public function hub()
    {
        return $this->HasOne(Hubs::class, "id", "hub_id");
    }

    public function content()
    {
        return $this->HasMany(HubCategoryContent::class, "hub_category_id", "id")->orderBy('position', 'asc');
    }
}
