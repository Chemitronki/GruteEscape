import React, { useState } from 'react';
import PropTypes from 'prop-types';
import './SymbolCipher.css';

/**
 * SymbolCipher - Puzzle where user decodes lovecraftian symbols to reveal a word
 * Requirements: 3.1, 3.7
 */
const SymbolCipher = ({ puzzleData, onSubmit, disabled }) => {
  const [decodedWord, setDecodedWord] = useState('');
  
  const symbols = puzzleData?.data?.symbols || [];
  const hint = puzzleData?.data?.hint || '';

  const handleSubmit = (e) => {
    e.preventDefault();
    if (decodedWord.trim() && !disabled) {
      onSubmit(decodedWord.trim());
    }
  };

  return (
    <div className="symbol-cipher">
      <div className="symbols-display">
        {symbols.map((symbol, index) => (
          <div key={index} className="symbol-card">
            <div className="symbol-icon">{symbol}</div>
          </div>
        ))}
      </div>

      {hint && (
        <div className="cipher-hint">
          <span className="hint-label">Pista:</span> {hint}
        </div>
      )}

      <form onSubmit={handleSubmit} className="cipher-form">
        <input
          type="text"
          value={decodedWord}
          onChange={(e) => setDecodedWord(e.target.value.toUpperCase())}
          placeholder="INGRESA LA PALABRA DECODIFICADA"
          disabled={disabled}
          className="cipher-input"
          maxLength={20}
        />
        <button
          type="submit"
          disabled={disabled || !decodedWord.trim()}
          className="cipher-submit"
        >
          Descifrar
        </button>
      </form>
    </div>
  );
};

SymbolCipher.propTypes = {
  puzzleData: PropTypes.shape({
    data: PropTypes.shape({
      symbols: PropTypes.arrayOf(PropTypes.string),
      hint: PropTypes.string,
    }),
  }).isRequired,
  onSubmit: PropTypes.func.isRequired,
  disabled: PropTypes.bool,
};

export default SymbolCipher;
