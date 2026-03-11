import React, { useState, useEffect } from 'react';
import PropTypes from 'prop-types';
import axios from 'axios';
import useSoundEffects from '../../hooks/useSoundEffects';
import './HintPanel.css';
import '../../styles/animations.css';

/**
 * HintPanel - Component for displaying and managing puzzle hints
 * Shows hint button when available, displays hint content, and tracks hints used
 */
const HintPanel = ({ puzzleId, timeSpent, onHintUsed }) => {
  const [hintAvailability, setHintAvailability] = useState({
    available: false,
    time_spent: 0,
    hints_used: 0,
    max_hints: 3,
    next_hint_level: 1,
  });
  const [currentHint, setCurrentHint] = useState(null);
  const [showHintModal, setShowHintModal] = useState(false);
  const [showNotification, setShowNotification] = useState(false);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);
  const { playHintAvailable } = useSoundEffects();

  // Check hint availability
  useEffect(() => {
    if (!puzzleId) return;

    const checkAvailability = async () => {
      try {
        const token = localStorage.getItem('token');
        const response = await axios.get(
          `${import.meta.env.VITE_API_URL}/api/puzzles/${puzzleId}/hints/available`,
          {
            headers: { Authorization: `Bearer ${token}` }
          }
        );

        if (response.data.success) {
          const availability = response.data.data;
          const wasUnavailable = !hintAvailability.available;
          
          setHintAvailability(availability);

          // Show notification when hints become available for the first time
          if (availability.available && wasUnavailable && availability.hints_used === 0) {
            setShowNotification(true);
            playHintAvailable();
            setTimeout(() => setShowNotification(false), 5000);
          }
        }
      } catch (err) {
        console.error('Error checking hint availability:', err);
      }
    };

    // Check availability every 10 seconds
    checkAvailability();
    const interval = setInterval(checkAvailability, 10000);

    return () => clearInterval(interval);
  }, [puzzleId, timeSpent, playHintAvailable]);

  // Request a hint
  const requestHint = async () => {
    if (!hintAvailability.available) return;

    setLoading(true);
    setError(null);

    try {
      const token = localStorage.getItem('token');
      const level = hintAvailability.next_hint_level;
      
      const response = await axios.get(
        `${import.meta.env.VITE_API_URL}/api/puzzles/${puzzleId}/hints/${level}`,
        {
          headers: { Authorization: `Bearer ${token}` }
        }
      );

      if (response.data.success) {
        const hint = response.data.data;
        setCurrentHint(hint);
        setShowHintModal(true);
        setHintAvailability(prev => ({
          ...prev,
          hints_used: hint.hints_used,
          available: hint.hints_used < 3,
          next_hint_level: hint.hints_used + 1,
        }));

        // Notify parent component
        if (onHintUsed) {
          onHintUsed(hint);
        }
      }
    } catch (err) {
      console.error('Error requesting hint:', err);
      setError(err.response?.data?.message || 'Error al obtener la pista');
    } finally {
      setLoading(false);
    }
  };

  const closeModal = () => {
    setShowHintModal(false);
  };

  return (
    <div className="hint-panel">
      {/* Hint notification */}
      {showNotification && (
        <div className="hint-notification hint-bounce">
          <span className="hint-notification-icon">💡</span>
          <span>¡Pista disponible! Has pasado más de 2 minutos en este puzzle.</span>
        </div>
      )}

      {/* Hint button */}
      <div className="hint-button-container">
        <button
          className={`hint-button ${hintAvailability.available ? 'available' : 'unavailable'}`}
          onClick={requestHint}
          disabled={!hintAvailability.available || loading}
          title={
            hintAvailability.available
              ? `Obtener pista ${hintAvailability.next_hint_level}`
              : hintAvailability.time_spent < 120
              ? `Pistas disponibles después de 2 minutos (${Math.floor((120 - hintAvailability.time_spent) / 60)}:${String((120 - hintAvailability.time_spent) % 60).padStart(2, '0')} restantes)`
              : 'No hay más pistas disponibles'
          }
        >
          {loading ? (
            <span className="hint-loading">⏳</span>
          ) : (
            <>
              <span className="hint-icon">💡</span>
              <span className="hint-text">
                {hintAvailability.available ? 'Obtener Pista' : 'Sin Pistas'}
              </span>
            </>
          )}
        </button>

        {/* Hints used counter */}
        <div className="hints-used-counter">
          <span className="hints-used-label">Pistas usadas:</span>
          <span className="hints-used-value">
            {hintAvailability.hints_used} / {hintAvailability.max_hints}
          </span>
        </div>
      </div>

      {/* Error message */}
      {error && (
        <div className="hint-error">
          <span className="error-icon">⚠️</span>
          <span>{error}</span>
        </div>
      )}

      {/* Hint modal */}
      {showHintModal && currentHint && (
        <div className="hint-modal-overlay" onClick={closeModal}>
          <div className="hint-modal" onClick={(e) => e.stopPropagation()}>
            <div className="hint-modal-header">
              <h3>Pista {currentHint.level}</h3>
              <button className="hint-modal-close" onClick={closeModal}>
                ✕
              </button>
            </div>
            <div className="hint-modal-content">
              <p>{currentHint.content}</p>
            </div>
            <div className="hint-modal-footer">
              <button className="hint-modal-button" onClick={closeModal}>
                Entendido
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
};

HintPanel.propTypes = {
  puzzleId: PropTypes.number.isRequired,
  timeSpent: PropTypes.number,
  onHintUsed: PropTypes.func,
};

export default HintPanel;
