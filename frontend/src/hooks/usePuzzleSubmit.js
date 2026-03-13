import { useState } from 'react';
import axios from 'axios';

/**
 * Custom hook for handling puzzle solution submissions
 * Manages submission state, feedback, and API communication
 */
const usePuzzleSubmit = (puzzleId, sessionId) => {
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [feedback, setFeedback] = useState(null);
  const [error, setError] = useState(null);

  const submitSolution = async (solution) => {
    setIsSubmitting(true);
    setError(null);
    setFeedback(null);

    try {
      const token = localStorage.getItem('auth_token');
      if (!token) {
        throw new Error('No hay token de autenticación');
      }
      
      const response = await axios.post(
        `${import.meta.env.VITE_API_URL}/puzzles/${puzzleId}/submit`,
        {
          session_id: sessionId,
          solution: solution,
        },
        {
          headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json',
          },
          withCredentials: true,
        }
      );

      if (response.data.success) {
        const { correct, feedback: feedbackMessage, puzzle_completed, all_puzzles_completed } = response.data.data;
        
        setFeedback({
          isCorrect: correct,
          message: correct ? '¡Excelente! Has resuelto el puzzle.' : feedbackMessage,
          puzzleCompleted: puzzle_completed,
          allCompleted: all_puzzles_completed,
        });

        return {
          success: true,
          correct,
          puzzleCompleted: puzzle_completed,
          allCompleted: all_puzzles_completed,
        };
      } else {
        throw new Error(response.data.message || 'Error al enviar la solución');
      }
    } catch (err) {
      const errorMessage = err.response?.data?.message || err.message || 'Error al enviar la solución';
      setError(errorMessage);
      
      return {
        success: false,
        error: errorMessage,
      };
    } finally {
      setIsSubmitting(false);
    }
  };

  const clearFeedback = () => {
    setFeedback(null);
    setError(null);
  };

  return {
    submitSolution,
    isSubmitting,
    feedback,
    error,
    clearFeedback,
  };
};

export default usePuzzleSubmit;
