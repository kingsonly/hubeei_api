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

    public function liked()
    {
        return $this->HasMany(UserLikedContent::class, "content_id", "id");
    }

    public function views()
    {
        return $this->HasMany(ContentViews::class, "content_id", "id");
    }
}
