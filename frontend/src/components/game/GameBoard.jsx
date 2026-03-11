import { useEffect, useState } from 'react';
import { useDispatch, useSelector } from 'react-redux';
import { useNavigate } from 'react-router-dom';
import { startGame, getSession, recoverSession, abandonGame } from '../../features/game/gameSlice';
import Timer from './Timer';
import ProgressIndicator from './ProgressIndicator';
import GameOver from './GameOver';
import Victory from './Victory';

const GameBoard = () => {
  const dispatch = useDispatch();
  const navigate = useNavigate();
  const { session, timeRemaining, isActive, loading, error } = useSelector((state) => state.game);
  const { isAuthenticated } = useSelector((state) => state.auth);
  
  const [puzzlesCompleted, setPuzzlesCompleted] = useState(0);
  const totalPuzzles = 10;

  // Check authentication
  useEffect(() => {
    if (!isAuthenticated) {
      navigate('/login');
    }
  }, [isAuthenticated, navigate]);

  // Try to recover session from localStorage on mount
  useEffect(() => {
    dispatch(recoverSession());
    
    // If no session in localStorage, try to fetch from server
    const storedSession = localStorage.getItem('game_session');
    if (!storedSession) {
      dispatch(getSession());
    }
  }, [dispatch]);

  // Handle game start
  const handleStartGame = async () => {
    try {
      await dispatch(startGame()).unwrap();
    } catch (err) {
      console.error('Error starting game:', err);
    }
  };

  // Handle abandon game
  const handleAbandonGame = async () => {
    if (window.confirm('¿Estás seguro de que quieres abandonar el juego?')) {
      try {
        await dispatch(abandonGame()).unwrap();
        navigate('/');
      } catch (err) {
        console.error('Error abandoning game:', err);
      }
    }
  };

  // Show game over screen
  if (session?.status === 'timeout' || (timeRemaining === 0 && isActive)) {
    return <GameOver puzzlesCompleted={puzzlesCompleted} totalPuzzles={totalPuzzles} />;
  }

  // Show victory screen
  if (session?.status === 'completed') {
    return <Victory completionTime={session.completion_time} />;
  }

  // Show start screen if no active session
  if (!session || !isActive) {
    return (
      <div className="min-h-screen bg-gradient-to-b from-gray-900 via-gray-800 to-black flex items-center justify-center p-4">
        <div className="max-w-2xl w-full bg-gray-900 border-2 border-gray-700 rounded-lg p-8 shadow-2xl">
          <div className="text-center mb-8">
            <h1 className="text-5xl font-bold text-green-400 mb-4 font-serif">
              Escape Room Lovecraftiano
            </h1>
            <p className="text-gray-400 text-lg mb-6">
              Adéntrate en la gruta oscura y resuelve los misterios antes de que el tiempo se agote
            </p>
          </div>

          <div className="bg-gray-800 border-l-4 border-green-500 p-6 mb-8">
            <h2 className="text-xl font-bold text-white mb-3">Instrucciones:</h2>
            <ul className="text-gray-300 space-y-2 list-disc list-inside">
              <li>Tienes 25 minutos para completar todos los puzzles</li>
              <li>Resuelve 10 puzzles únicos en secuencia</li>
              <li>Las pistas estarán disponibles después de 2 minutos en cada puzzle</li>
              <li>Tu tiempo de completado se registrará en el ranking global</li>
            </ul>
          </div>

          {error && (
            <div className="bg-red-900 border border-red-700 text-red-200 p-4 rounded-lg mb-6">
              {error}
            </div>
          )}

          <button
            onClick={handleStartGame}
            disabled={loading}
            className="w-full bg-green-600 hover:bg-green-700 disabled:bg-gray-600 text-white font-bold py-4 px-6 rounded-lg transition-colors duration-200 transform hover:scale-105 disabled:transform-none text-xl"
          >
            {loading ? 'Iniciando...' : 'Comenzar Juego'}
          </button>
        </div>
      </div>
    );
  }

  // Main game board
  return (
    <div className="min-h-screen bg-gradient-to-b from-gray-900 via-gray-800 to-black p-4">
      <div className="max-w-6xl mx-auto">
        {/* Header with timer and progress */}
        <div className="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
          <Timer />
          <ProgressIndicator 
            puzzlesCompleted={puzzlesCompleted} 
            totalPuzzles={totalPuzzles} 
          />
        </div>

        {/* Main game area */}
        <div className="bg-gray-900 border-2 border-gray-700 rounded-lg p-8 shadow-2xl min-h-[500px]">
          <div className="text-center">
            <h2 className="text-3xl font-bold text-white mb-4">
              Área de Juego
            </h2>
            <p className="text-gray-400 mb-8">
              Los puzzles se cargarán aquí. Por ahora, este es un placeholder.
            </p>
            
            {/* Placeholder for puzzle components */}
            <div className="bg-gray-800 border-2 border-dashed border-gray-600 rounded-lg p-12 mb-6">
              <p className="text-gray-500 text-lg">
                Puzzle {puzzlesCompleted + 1} de {totalPuzzles}
              </p>
              <p className="text-gray-600 text-sm mt-2">
                Los componentes de puzzle se implementarán en las siguientes tareas
              </p>
            </div>

            {/* Test button to simulate puzzle completion */}
            <button
              onClick={() => {
                if (puzzlesCompleted < totalPuzzles) {
                  setPuzzlesCompleted(puzzlesCompleted + 1);
                }
              }}
              className="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-lg transition-colors duration-200 mr-4"
            >
              Simular Completar Puzzle (Test)
            </button>

            <button
              onClick={handleAbandonGame}
              className="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-6 rounded-lg transition-colors duration-200"
            >
              Abandonar Juego
            </button>
          </div>
        </div>

        {/* Footer info */}
        <div className="mt-6 text-center text-gray-500 text-sm">
          <p>Sesión ID: {session?.id}</p>
        </div>
      </div>
    </div>
  );
};

export default GameBoard;
