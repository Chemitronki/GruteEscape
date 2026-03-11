<?php

namespace App\Services;

class PuzzleValidatorService
{
    /**
     * Validate a puzzle solution based on puzzle type
     *
     * @param string $puzzleType
     * @param mixed $userSolution
     * @param array $solutionData
     * @return bool
     */
    public function validate(string $puzzleType, $userSolution, array $solutionData): bool
    {
        return match ($puzzleType) {
            'symbol_cipher' => $this->validateSymbolCipher($userSolution, $solutionData),
            'ritual_pattern' => $this->validateRitualPattern($userSolution, $solutionData),
            'ancient_lock' => $this->validateAncientLock($userSolution, $solutionData),
            'memory_fragments' => $this->validateMemoryFragments($userSolution, $solutionData),
            'cosmic_alignment' => $this->validateCosmicAlignment($userSolution, $solutionData),
            'tentacle_maze' => $this->validateTentacleMaze($userSolution, $solutionData),
            'forbidden_tome' => $this->validateForbiddenTome($userSolution, $solutionData),
            'shadow_reflection' => $this->validateShadowReflection($userSolution, $solutionData),
            'cultist_code' => $this->validateCultistCode($userSolution, $solutionData),
            'elder_sign' => $this->validateElderSign($userSolution, $solutionData),
            default => false,
        };
    }

    /**
     * Validate Symbol Cipher puzzle
     * User must decode lovecraftian symbols to reveal a word
     */
    private function validateSymbolCipher($userSolution, array $solutionData): bool
    {
        if (!isset($solutionData['solution'])) {
            return false;
        }

        $correctSolution = strtoupper(trim($solutionData['solution']));
        $userAnswer = strtoupper(trim($userSolution));

        return $userAnswer === $correctSolution;
    }

    /**
     * Validate Ritual Pattern puzzle
     * User must arrange ritual items in correct sequence
     */
    private function validateRitualPattern($userSolution, array $solutionData): bool
    {
        if (!isset($solutionData['solution']) || !is_array($userSolution)) {
            return false;
        }

        $correctSequence = $solutionData['solution'];
        
        // Check if arrays have same length
        if (count($userSolution) !== count($correctSequence)) {
            return false;
        }

        // Check if sequence matches exactly
        for ($i = 0; $i < count($correctSequence); $i++) {
            if ($userSolution[$i] !== $correctSequence[$i]) {
                return false;
            }
        }

        return true;
    }

    /**
     * Validate Ancient Lock puzzle
     * User must solve a combination based on clues
     */
    private function validateAncientLock($userSolution, array $solutionData): bool
    {
        if (!isset($solutionData['solution'])) {
            return false;
        }

        $correctCombination = (string) $solutionData['solution'];
        $userCombination = (string) $userSolution;

        return $userCombination === $correctCombination;
    }

    /**
     * Validate Memory Fragments puzzle
     * User must match pairs of eldritch imagery within time limit
     */
    private function validateMemoryFragments($userSolution, array $solutionData): bool
    {
        if (!isset($solutionData['pairs']) || !is_array($userSolution)) {
            return false;
        }

        $requiredPairs = $solutionData['pairs'];
        
        // Check if user completed all pairs
        if (!isset($userSolution['completed_pairs'])) {
            return false;
        }

        return $userSolution['completed_pairs'] === $requiredPairs;
    }

    /**
     * Validate Cosmic Alignment puzzle
     * User must align celestial bodies to match a star chart
     */
    private function validateCosmicAlignment($userSolution, array $solutionData): bool
    {
        if (!isset($solutionData['solution'], $solutionData['positions']) || !is_array($userSolution)) {
            return false;
        }

        $correctStars = $solutionData['solution'];
        $correctPositions = $solutionData['positions'];
        $tolerance = 30; // pixels tolerance for position matching

        // Check if all stars are placed
        if (count($userSolution) !== count($correctStars)) {
            return false;
        }

        // Check each star position
        foreach ($correctStars as $index => $starName) {
            if (!isset($userSolution[$index])) {
                return false;
            }

            $userStar = $userSolution[$index];
            $correctPos = $correctPositions[$index];

            // Check star name matches
            if ($userStar['name'] !== $starName) {
                return false;
            }

            // Check position is within tolerance
            $xDiff = abs($userStar['x'] - $correctPos['x']);
            $yDiff = abs($userStar['y'] - $correctPos['y']);

            if ($xDiff > $tolerance || $yDiff > $tolerance) {
                return false;
            }
        }

        return true;
    }

    /**
     * Validate Tentacle Maze puzzle
     * User must navigate through maze avoiding tentacles
     */
    private function validateTentacleMaze($userSolution, array $solutionData): bool
    {
        if (!isset($solutionData['exit']) || !is_array($userSolution)) {
            return false;
        }

        $exitPosition = $solutionData['exit'];

        // Check if user reached the exit
        if (!isset($userSolution['final_position'])) {
            return false;
        }

        $finalPos = $userSolution['final_position'];

        return $finalPos['x'] === $exitPosition['x'] && 
               $finalPos['y'] === $exitPosition['y'];
    }

    /**
     * Validate Forbidden Tome puzzle
     * User must reconstruct torn pages in correct order
     */
    private function validateForbiddenTome($userSolution, array $solutionData): bool
    {
        if (!isset($solutionData['solution']) || !is_array($userSolution)) {
            return false;
        }

        $correctOrder = $solutionData['solution'];

        // Check if arrays have same length
        if (count($userSolution) !== count($correctOrder)) {
            return false;
        }

        // Check if order matches exactly
        for ($i = 0; $i < count($correctOrder); $i++) {
            if ($userSolution[$i] !== $correctOrder[$i]) {
                return false;
            }
        }

        return true;
    }

    /**
     * Validate Shadow Reflection puzzle
     * User must mirror movements to match shadow patterns
     */
    private function validateShadowReflection($userSolution, array $solutionData): bool
    {
        if (!isset($solutionData['solution']) || !is_array($userSolution)) {
            return false;
        }

        $correctPattern = $solutionData['solution'];

        // Check if arrays have same length
        if (count($userSolution) !== count($correctPattern)) {
            return false;
        }

        // Check if pattern matches exactly
        for ($i = 0; $i < count($correctPattern); $i++) {
            if ($userSolution[$i] !== $correctPattern[$i]) {
                return false;
            }
        }

        return true;
    }

    /**
     * Validate Cultist Code puzzle
     * User must decode messages using frequency analysis
     */
    private function validateCultistCode($userSolution, array $solutionData): bool
    {
        if (!isset($solutionData['solution'])) {
            return false;
        }

        $correctSolution = strtoupper(trim($solutionData['solution']));
        $userAnswer = strtoupper(trim($userSolution));

        return $userAnswer === $correctSolution;
    }

    /**
     * Validate Elder Sign Drawing puzzle
     * User must trace complex geometric patterns without lifting cursor
     */
    private function validateElderSign($userSolution, array $solutionData): bool
    {
        if (!isset($solutionData['points']) || !is_array($userSolution)) {
            return false;
        }

        $correctPoints = $solutionData['points'];
        $tolerance = $solutionData['tolerance'] ?? 20;

        // Check if user traced all points
        if (!isset($userSolution['traced_points']) || !is_array($userSolution['traced_points'])) {
            return false;
        }

        $tracedPoints = $userSolution['traced_points'];

        // Must have traced at least as many points as required
        if (count($tracedPoints) < count($correctPoints)) {
            return false;
        }

        // Check if traced points match the pattern within tolerance
        foreach ($correctPoints as $index => $correctPoint) {
            if (!isset($tracedPoints[$index])) {
                return false;
            }

            $tracedPoint = $tracedPoints[$index];
            $xDiff = abs($tracedPoint['x'] - $correctPoint['x']);
            $yDiff = abs($tracedPoint['y'] - $correctPoint['y']);

            if ($xDiff > $tolerance || $yDiff > $tolerance) {
                return false;
            }
        }

        // Check if cursor was not lifted (continuous path)
        if (isset($userSolution['lifted']) && $userSolution['lifted'] === true) {
            return false;
        }

        return true;
    }
}
