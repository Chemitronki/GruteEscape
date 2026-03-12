import React, { useEffect, useState } from 'react';
import { useDispatch, useSelector } from 'react-redux';
import PropTypes from 'prop-types';
import PuzzleContainer from './PuzzleContainer';
import PuzzleFeedback from './PuzzleFeedback';
import PuzzleLoading from './PuzzleLoading';
import HintPanel from './HintPanel';
import usePuzzleSubmit from '../../hooks/usePuzzleSubmit';
import { getCurrentPuzzle } from '../../features/game/gameSlice';
import {
  SymbolCipher,
  RitualPattern,
  AncientLock,
  MemoryFragments,
  CosmicAlignment,
  TentacleMaze,
  ForbiddenTome,
  ShadowReflection,
  CultistCode,
  ElderSignDrawing,
} from './puzzles';
import './Puzzle.css';

/**
 * Puzzle - Main puzzle component that renders different puzzle types
 * Handles puzzle loading, submission, and feedback
 */
const Puzzle = ({ puzzle, onComplete, disabled }) => {
  const dispatch = useDispatch();
  const { session, currentPuzzle, puzzleLoading, isActive } = useSelector((state) => state.game);
  const puzzleToUse = puzzle || currentPuzzle;
  const { submitSolution, isSubmitting, feedback, clearFeedback } = usePuzzleSubmit(
    puzzleToUse?.id,
    session?.id
  );
  
  const [localSolution, setLocalSolution] = useState('');

  useEffect(() => {
    // Load current puzzle when component mounts or session changes
    if (session?.id && isActive && !puzzle) {
      dispatch(getCurrentPuzzle(session.id));
    }
  }, [dispatch, session?.id, isActive, puzzle]);

  useEffect(() => {
    // Reload puzzle when one is completed
    if (feedback?.puzzleCompleted && !feedback?.allCompleted) {
      const timer = setTimeout(() => {
        if (onComplete) {
          onComplete();
        } else {
          dispatch(getCurrentPuzzle(session.id));
        }
        clearFeedback();
      }, 2000);
      
      return () => clearTimeout(timer);
    }
  }, [feedback, dispatch, session?.id, clearFeedback, onComplete]);

  const handleSubmit = async (solution) => {
    const result = await submitSolution(solution || localSolution);
    
    if (result.success && result.correct) {
      setLocalSolution('');
    }
  };

  // Map puzzle types to their components
  const getPuzzleComponent = () => {
    const puzzleComponents = {
      'symbol_cipher': SymbolCipher,
      'ritual_pattern': RitualPattern,
      'ancient_lock': AncientLock,
      'memory_fragments': MemoryFragments,
      'cosmic_alignment': CosmicAlignment,
      'tentacle_maze': TentacleMaze,
      'forbidden_tome': ForbiddenTome,
      'shadow_reflection': ShadowReflection,
      'cultist_code': CultistCode,
      'elder_sign_drawing': ElderSignDrawing,
    };

    const PuzzleComponent = puzzleComponents[puzzleToUse.type];
    
    if (PuzzleComponent) {
      return (
        <PuzzleComponent
          puzzleData={puzzleToUse}
          onSubmit={handleSubmit}
          disabled={isDisabled}
        />
      );
    }

    // Fallback for unknown puzzle types
    return (
      <div className="puzzle-type-placeholder">
        <p className="puzzle-type-label">Tipo: {puzzleToUse.type}</p>
        <p className="puzzle-implementation-note">
          Tipo de puzzle no reconocido
        </p>
        
        {/* Generic input for testing */}
        <div className="generic-puzzle-input">
          <input
            type="text"
            value={localSolution}
            onChange={(e) => setLocalSolution(e.target.value)}
            placeholder="Ingresa tu solución..."
            disabled={isDisabled}
            className="puzzle-input"
          />
          <button
            onClick={() => handleSubmit()}
            disabled={isDisabled || !localSolution}
            className="puzzle-submit-btn"
          >
            {isSubmitting ? 'Enviando...' : 'Enviar Solución'}
          </button>
        </div>
      </div>
    );
  };

  if (puzzleLoading) {
    return <PuzzleLoading />;
  }

  if (!puzzleToUse) {
    return (
      <div className="no-puzzle">
        <p>No hay puzzle disponible</p>
      </div>
    );
  }

  const isDisabled = disabled || !isActive || isSubmitting;

  const handleHintUsed = (hint) => {
    console.log('Hint used:', hint);
    // You can add additional logic here, like tracking hints in analytics
  };

  return (
    <PuzzleContainer
      title={puzzleToUse.title}
      description={puzzleToUse.description}
      disabled={!isActive}
    >
      {feedback && (
        <PuzzleFeedback
          isCorrect={feedback.isCorrect}
          message={feedback.message}
          onDismiss={clearFeedback}
        />
      )}
      
      {/* Hint Panel */}
      {isActive && puzzleToUse && (
        <HintPanel
          puzzleId={puzzleToUse.id}
          timeSpent={puzzleToUse.time_spent || 0}
          onHintUsed={handleHintUsed}
        />
      )}
      
      <div className="puzzle-interface">
        {getPuzzleComponent()}
      </div>
    </PuzzleContainer>
  );
};

Puzzle.propTypes = {};

export default Puzzle;
