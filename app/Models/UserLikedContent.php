<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class UserLikedContent extends Model
{
    use HasFactory;
    public function content()
    {
        return $this->HasOne(HubCategoryContent::class, "id", "user_cookies_id");
    }
}
