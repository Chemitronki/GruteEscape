import { useDispatch } from 'react-redux';
import { useNavigate } from 'react-router-dom';
import { clearSession } from '../../features/game/gameSlice';
import { useEffect, useState } from 'react';
import useSoundEffects from '../../hooks/useSoundEffects';
import '../../styles/animations.css';

const GameOver = ({ puzzlesCompleted = 0, totalPuzzles = 10 }) => {
  const dispatch = useDispatch();
  const navigate = useNavigate();
  const { playGameOver } = useSoundEffects();
  const [bloodDrips, setBloodDrips] = useState([]);

  useEffect(() => {
    // Play game over sound
    playGameOver();

    // Generate blood drip effects
    const drips = [];
    for (let i = 0; i < 10; i++) {
      drips.push({
        id: i,
        left: Math.random() * 100,
        delay: Math.random() * 1,
      });
    }
    setBloodDrips(drips);
  }, [playGameOver]);

  const handleTryAgain = () => {
    dispatch(clearSession());
    navigate('/game');
  };

  const handleGoHome = () => {
    dispatch(clearSession());
    navigate('/');
  };

  return (
    <div className="fixed inset-0 bg-black bg-opacity-90 flex items-center justify-center z-50 game-over-overlay">
      {/* Blood drip effects */}
      {bloodDrips.map((drip) => (
        <div
          key={drip.id}
          className="blood-drip"
          style={{
            left: `${drip.left}%`,
            animationDelay: `${drip.delay}s`,
          }}
        />
      ))}

      <div className="game-over-container bg-gray-900 border-4 border-red-900 rounded-lg p-8 max-w-md w-full mx-4 shadow-2xl game-over-fade-in">
        {/* Skull icon or game over image */}
        <div className="text-center mb-6">
          <div className="text-8xl mb-4 flicker-animation">💀</div>
          <h1 className="text-5xl font-bold text-red-500 mb-2 font-serif game-over-shake">
            GAME OVER
          </h1>
          <p className="text-gray-400 text-lg italic">
            El tiempo se ha agotado...
          </p>
        </div>

        {/* Stats */}
        <div className="bg-gray-800 rounded-lg p-4 mb-6 border border-gray-700">
          <div className="flex justify-between items-center mb-2">
            <span className="text-gray-400">Puzzles completados:</span>
            <span className="text-white font-bold text-xl">
              {puzzlesCompleted} / {totalPuzzles}
            </span>
          </div>
          <div className="w-full bg-gray-700 rounded-full h-2">
            <div
              className="bg-red-500 h-full rounded-full transition-all duration-500"
              style={{ width: `${(puzzlesCompleted / totalPuzzles) * 100}%` }}
            />
          </div>
        </div>

        {/* Flavor text */}
        <div className="bg-gray-800 border-l-4 border-red-500 p-4 mb-6">
          <p className="text-gray-300 text-sm italic">
            "La oscuridad te ha consumido. Los antiguos susurran tu nombre en las sombras, 
            pero aún no todo está perdido. ¿Te atreves a intentarlo de nuevo?"
          </p>
        </div>

        {/* Action buttons */}
        <div className="space-y-3">
          <button
            onClick={handleTryAgain}
            className="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-6 rounded-lg transition-colors duration-200 transform hover:scale-105"
          >
            Intentar de Nuevo
          </button>
          <button
            onClick={handleGoHome}
            className="w-full bg-gray-700 hover:bg-gray-600 text-white font-bold py-3 px-6 rounded-lg transition-colors duration-200"
          >
            Volver al Inicio
          </button>
        </div>
      </div>
    </div>
  );
};

export default GameOver;
