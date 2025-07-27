<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes; 

class Task extends Model
{
    use HasFactory, SoftDeletes; 

    protected $fillable = [
        'title',
        'description',
        'status',
        'priority',
        'order',
        'user_id',
    ];

    protected $casts = [
        'order' => 'integer',
        'deleted_at' => 'datetime',
    ];

    protected $dates = [
        'deleted_at',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeByStatus(Builder $query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByPriority(Builder $query, string $priority)
    {
        return $query->where('priority', $priority);
    }

    public function scopeByUser(Builder $query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeOrdered(Builder $query)
    {
        return $query->orderBy('order');
    }

    public function scopeSearch(Builder $query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('title', 'LIKE', "%{$search}%")
              ->orWhere('description', 'LIKE', "%{$search}%");
        });
    }
}