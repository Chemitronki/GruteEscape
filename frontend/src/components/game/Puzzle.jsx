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
const Puzzle = () => {
  const dispatch = useDispatch();
  const { session, currentPuzzle, puzzleLoading, isActive } = useSelector((state) => state.game);
  const { submitSolution, isSubmitting, feedback, clearFeedback } = usePuzzleSubmit(
    currentPuzzle?.id,
    session?.id
  );
  
  const [localSolution, setLocalSolution] = useState('');

  useEffect(() => {
    // Load current puzzle when component mounts or session changes
    if (session?.id && isActive) {
      dispatch(getCurrentPuzzle(session.id));
    }
  }, [dispatch, session?.id, isActive]);

  useEffect(() => {
    // Reload puzzle when one is completed
    if (feedback?.puzzleCompleted && !feedback?.allCompleted) {
      const timer = setTimeout(() => {
        dispatch(getCurrentPuzzle(session.id));
        clearFeedback();
      }, 2000);
      
      return () => clearTimeout(timer);
    }
  }, [feedback, dispatch, session?.id, clearFeedback]);

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

    const PuzzleComponent = puzzleComponents[currentPuzzle.type];
    
    if (PuzzleComponent) {
      return (
        <PuzzleComponent
          puzzleData={currentPuzzle}
          onSubmit={handleSubmit}
          disabled={isDisabled}
        />
      );
    }

    // Fallback for unknown puzzle types
    return (
      <div className="puzzle-type-placeholder">
        <p className="puzzle-type-label">Tipo: {currentPuzzle.type}</p>
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

  if (!currentPuzzle) {
    return (
      <div className="no-puzzle">
        <p>No hay puzzle disponible</p>
      </div>
    );
  }

  const isDisabled = !isActive || isSubmitting;

  const handleHintUsed = (hint) => {
    console.log('Hint used:', hint);
    // You can add additional logic here, like tracking hints in analytics
  };

  return (
    <PuzzleContainer
      title={currentPuzzle.title}
      description={currentPuzzle.description}
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
      {isActive && currentPuzzle && (
        <HintPanel
          puzzleId={currentPuzzle.id}
          timeSpent={currentPuzzle.time_spent || 0}
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
