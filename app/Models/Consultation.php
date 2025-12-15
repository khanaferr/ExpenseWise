<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Consultation extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'advisor_id', 'scheduled_at', 'status', 'notes'];

    public function client()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function advisor()
    {
        return $this->belongsTo(FinancialAdvisor::class, 'advisor_id');
    }
}