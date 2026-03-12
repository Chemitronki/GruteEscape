import React, { useState } from 'react';
import PropTypes from 'prop-types';
import './AncientLock.css';

/**
 * AncientLock - Puzzle where user solves a combination based on clues
 * Requirements: 3.1, 3.7
 */
const AncientLock = ({ puzzleData, onSubmit, disabled }) => {
  const solutionData = typeof puzzleData?.solution_data === 'string' 
    ? JSON.parse(puzzleData.solution_data) 
    : puzzleData?.solution_data || {};
  
  const clues = solutionData?.clues || [];
  const combinationLength = solutionData?.solution?.length || 4;
  
  const [combination, setCombination] = useState(Array(combinationLength).fill(''));

  const handleDigitChange = (index, value) => {
    if (disabled) return;
    
    // Only allow numbers
    if (value && !/^\d$/.test(value)) return;
    
    const newCombination = [...combination];
    newCombination[index] = value;
    setCombination(newCombination);

    // Auto-focus next input
    if (value && index < combinationLength - 1) {
      const nextInput = document.getElementById(`lock-digit-${index + 1}`);
      if (nextInput) nextInput.focus();
    }
  };

  const handleKeyDown = (index, e) => {
    if (e.key === 'Backspace' && !combination[index] && index > 0) {
      const prevInput = document.getElementById(`lock-digit-${index - 1}`);
      if (prevInput) prevInput.focus();
    }
  };

  const handleSubmit = () => {
    const fullCombination = combination.join('');
    if (fullCombination.length === combinationLength && !disabled) {
      onSubmit(fullCombination);
    }
  };

  const handleReset = () => {
    setCombination(Array(combinationLength).fill(''));
    const firstInput = document.getElementById('lock-digit-0');
    if (firstInput) firstInput.focus();
  };

  const isComplete = combination.every(digit => digit !== '');

  return (
    <div className="ancient-lock">
      <div className="lock-visual">
        <div className="lock-body">
          <div className="lock-shackle"></div>
          <div className="lock-face">
            <div className="lock-keyhole"></div>
          </div>
        </div>
      </div>

      <div className="clues-section">
        <h3 className="clues-title">Pistas del Entorno:</h3>
        <div className="clues-list">
          {clues.map((clue, index) => (
            <div key={index} className="clue-item">
              <span className="clue-icon">🔮</span>
              <span className="clue-text">{clue}</span>
            </div>
          ))}
        </div>
      </div>

      <div className="combination-input">
        <div className="combination-display">
          {combination.map((digit, index) => (
            <input
              key={index}
              id={`lock-digit-${index}`}
              type="text"
              maxLength="1"
              value={digit}
              onChange={(e) => handleDigitChange(index, e.target.value)}
              onKeyDown={(e) => handleKeyDown(index, e)}
              disabled={disabled}
              className="digit-input"
            />
          ))}
        </div>
      </div>

      <div className="lock-controls">
        <button
          onClick={handleReset}
          disabled={disabled || !combination.some(d => d)}
          className="lock-reset"
        >
          Reiniciar
        </button>
        <button
          onClick={handleSubmit}
          disabled={disabled || !isComplete}
          className="lock-submit"
        >
          Abrir Cerradura
        </button>
      </div>
    </div>
  );
};

AncientLock.propTypes = {
  puzzleData: PropTypes.shape({
    data: PropTypes.shape({
      clues: PropTypes.arrayOf(PropTypes.string),
      length: PropTypes.number,
    }),
  }).isRequired,
  onSubmit: PropTypes.func.isRequired,
  disabled: PropTypes.bool,
};

export default AncientLock;
