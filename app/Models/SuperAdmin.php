<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Superadmin extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'user_id',
        'is_active',
        'permissions',
    ];
    
    protected $casts = [
        'is_active' => 'boolean',
        'permissions' => 'array',
    ];
    
    /**
     * Get the user that is a superadmin
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
