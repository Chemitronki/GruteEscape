<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PuzzleProgress extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'game_session_id',
        'puzzle_id',
        'started_at',
        'completed_at',
        'time_spent',
        'attempts',
        'hints_used',
        'is_completed',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'time_spent' => 'integer',
        'attempts' => 'integer',
        'hints_used' => 'integer',
        'is_completed' => 'boolean',
    ];

    /**
     * Get the game session that owns the puzzle progress.
     */
    public function gameSession()
    {
        return $this->belongsTo(GameSession::class);
    }

    /**
     * Get the puzzle that this progress tracks.
     */
    public function puzzle()
    {
        return $this->belongsTo(Puzzle::class);
    }
}
