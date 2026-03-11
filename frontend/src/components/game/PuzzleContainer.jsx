import React from 'react';
import PropTypes from 'prop-types';

/**
 * PuzzleContainer - Wrapper component for all puzzle types
 * Provides consistent layout and styling for puzzle components
 */
const PuzzleContainer = ({ 
  children, 
  title, 
  description, 
  isLoading = false,
  disabled = false 
}) => {
  return (
    <div className={`puzzle-container ${disabled ? 'puzzle-disabled' : ''}`}>
      <div className="puzzle-header">
        <h2 className="puzzle-title">{title}</h2>
        <p className="puzzle-description">{description}</p>
      </div>
      
      <div className="puzzle-content">
        {isLoading ? (
          <div className="puzzle-loading">
            <div className="loading-spinner"></div>
            <p>Cargando puzzle...</p>
          </div>
        ) : (
          children
        )}
      </div>
      
      {disabled && (
        <div className="puzzle-overlay">
          <p>El juego ha terminado</p>
        </div>
      )}
    </div>
  );
};

PuzzleContainer.propTypes = {
  children: PropTypes.node.isRequired,
  title: PropTypes.string.isRequired,
  description: PropTypes.string.isRequired,
  isLoading: PropTypes.bool,
  disabled: PropTypes.bool,
};

export default PuzzleContainer;
