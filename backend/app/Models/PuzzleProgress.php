<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PuzzleProgress extends Model
{
    use HasFactory;

    protected $table = 'puzzle_progress';

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

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function gameSession()
    {
        return $this->belongsTo(GameSession::class);
    }

    public function puzzle()
    {
        return $this->belongsTo(Puzzle::class);
    }
}
