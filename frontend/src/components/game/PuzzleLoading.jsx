import React from 'react';

/**
 * PuzzleLoading - Loading state component for puzzles
 * Displays a lovecraftian-themed loading animation
 */
const PuzzleLoading = () => {
  return (
    <div className="puzzle-loading-state">
      <div className="eldritch-spinner">
        <div className="tentacle tentacle-1"></div>
        <div className="tentacle tentacle-2"></div>
        <div className="tentacle tentacle-3"></div>
        <div className="tentacle tentacle-4"></div>
        <div className="eye-center"></div>
      </div>
      <p className="loading-text">Invocando el siguiente enigma...</p>
    </div>
  );
};

export default PuzzleLoading;
