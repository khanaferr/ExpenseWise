<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinancialAdvisor extends Model
{
    use HasFactory;

    protected $fillable = ['id', 'certification_id', 'hourly_rate'];
    
    public $incrementing = false;
    protected $primaryKey = 'id';

    public function user()
    {
        return $this->belongsTo(User::class, 'id');
    }

    public function consultations()
    {
        return $this->hasMany(Consultation::class, 'advisor_id');
    }

    public function clients()
    {
        return $this->belongsToMany(User::class, 'consultations', 'advisor_id', 'user_id')
                    ->withPivot('scheduled_at', 'status');
    }
}