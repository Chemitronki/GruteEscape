<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GameSession extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'started_at',
        'completed_at',
        'time_remaining',
        'status',
        'completion_time',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'time_remaining' => 'integer',
        'completion_time' => 'integer',
    ];

    /**
     * Get the user that owns the game session.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the puzzle progress for this game session.
     */
    public function puzzleProgress()
    {
        return $this->hasMany(PuzzleProgress::class);
    }

    /**
     * Scope a query to only include active sessions.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Check if the session is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if the session has timed out.
     */
    public function hasTimedOut(): bool
    {
        return $this->time_remaining <= 0;
    }

    /**
     * Mark the session as completed.
     */
    public function markAsCompleted(): void
    {
        $this->status = 'completed';
        $this->completed_at = now();
        $this->completion_time = 1500 - $this->time_remaining;
        $this->save();
    }

    /**
     * Mark the session as abandoned.
     */
    public function markAsAbandoned(): void
    {
        $this->status = 'abandoned';
        $this->save();
    }

    /**
     * Mark the session as timed out.
     */
    public function markAsTimeout(): void
    {
        $this->status = 'timeout';
        $this->time_remaining = 0;
        $this->save();
    }

    /**
     * Update the remaining time.
     */
    public function updateTimeRemaining(int $timeRemaining): void
    {
        $this->time_remaining = max(0, $timeRemaining);
        
        if ($this->time_remaining <= 0) {
            $this->markAsTimeout();
        } else {
            $this->save();
        }
    }
}
