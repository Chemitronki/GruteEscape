<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Puzzle extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'sequence_order',
        'title',
        'description',
        'solution_data',
    ];

    protected $casts = [
        'solution_data' => 'json',
    ];

    public function hints()
    {
        return $this->hasMany(Hint::class);
    }

    public function puzzleProgress()
    {
        return $this->hasMany(PuzzleProgress::class);
    }
}
