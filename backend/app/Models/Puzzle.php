<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Puzzle extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'type',
        'sequence_order',
        'title',
        'description',
        'solution_data',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'solution_data' => 'array',
        'sequence_order' => 'integer',
    ];

    /**
     * Get the puzzle progress records for this puzzle.
     */
    public function progress()
    {
        return $this->hasMany(PuzzleProgress::class);
    }

    /**
     * Get the hints for this puzzle.
     */
    public function hints()
    {
        return $this->hasMany(Hint::class);
    }

    /**
     * Scope a query to order puzzles by sequence.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sequence_order');
    }
}
