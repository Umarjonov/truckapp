<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;
    protected $guarded = [];
    public function companyAdmins()
    {
        return $this->hasMany(CompanyAdmin::class);
    }
    public function track()
    {
        return $this->hasMany(Track::class);
    }
}
