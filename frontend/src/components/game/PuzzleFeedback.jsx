import React, { useEffect, useState } from 'react';
import PropTypes from 'prop-types';
import useSoundEffects from '../../hooks/useSoundEffects';
import '../../styles/animations.css';

/**
 * PuzzleFeedback - Displays feedback for puzzle submissions
 * Shows correct/incorrect messages with appropriate styling
 */
const PuzzleFeedback = ({ isCorrect, message, onDismiss }) => {
  const [isVisible, setIsVisible] = useState(true);
  const { playPuzzleCorrect, playPuzzleIncorrect } = useSoundEffects();

  useEffect(() => {
    // Play appropriate sound
    if (isCorrect) {
      playPuzzleCorrect();
    } else {
      playPuzzleIncorrect();
    }
  }, [isCorrect, playPuzzleCorrect, playPuzzleIncorrect]);

  useEffect(() => {
    if (isCorrect) {
      // Auto-dismiss success messages after 3 seconds
      const timer = setTimeout(() => {
        setIsVisible(false);
        if (onDismiss) onDismiss();
      }, 3000);
      
      return () => clearTimeout(timer);
    }
  }, [isCorrect, onDismiss]);

  if (!isVisible) return null;

  const feedbackClass = isCorrect ? 'feedback-correct success-flash' : 'feedback-incorrect error-shake';

  return (
    <div className={`puzzle-feedback ${feedbackClass}`}>
      <div className="feedback-icon">
        {isCorrect ? (
          <svg className="icon-success" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
          </svg>
        ) : (
          <svg className="icon-error" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
          </svg>
        )}
      </div>
      
      <div className="feedback-content">
        <h3 className="feedback-title">
          {isCorrect ? '¡Correcto!' : 'Incorrecto'}
        </h3>
        <p className="feedback-message">{message}</p>
      </div>
      
      {!isCorrect && onDismiss && (
        <button 
          className="feedback-dismiss"
          onClick={() => {
            setIsVisible(false);
            onDismiss();
          }}
          aria-label="Cerrar"
        >
          ×
        </button>
      )}
    </div>
  );
};

PuzzleFeedback.propTypes = {
  isCorrect: PropTypes.bool.isRequired,
  message: PropTypes.string.isRequired,
  onDismiss: PropTypes.func,
};

export default PuzzleFeedback;
