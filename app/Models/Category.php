<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Budget;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'name', 'type'];

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public function budget()
    {
        return $this->hasOne(Budget::class);
    }
}