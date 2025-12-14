<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserProfile extends Model
{
    use HasFactory;

    protected $fillable = ['id', 'currency', 'monthly_budget_limit'];

    public $incrementing = false;
    protected $primaryKey = 'id';

    public function user()
    {
        return $this->belongsTo(User::class, 'id');
    }
}