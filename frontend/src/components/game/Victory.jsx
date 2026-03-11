import { useDispatch } from 'react-redux';
import { useNavigate } from 'react-router-dom';
import { clearSession } from '../../features/game/gameSlice';
import { useEffect, useState } from 'react';
import useSoundEffects from '../../hooks/useSoundEffects';
import '../../styles/animations.css';

const Victory = ({ completionTime = 0 }) => {
  const dispatch = useDispatch();
  const navigate = useNavigate();
  const { playVictory } = useSoundEffects();
  const [confetti, setConfetti] = useState([]);

  useEffect(() => {
    // Play victory sound
    playVictory();

    // Generate confetti particles
    const particles = [];
    for (let i = 0; i < 30; i++) {
      particles.push({
        id: i,
        left: Math.random() * 100,
        delay: Math.random() * 2,
        color: ['#8b5cf6', '#10b981', '#fbbf24'][Math.floor(Math.random() * 3)],
      });
    }
    setConfetti(particles);
  }, [playVictory]);

  // Format completion time as MM:SS
  const formatTime = (seconds) => {
    const minutes = Math.floor(seconds / 60);
    const secs = seconds % 60;
    return `${minutes.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
  };

  const handlePlayAgain = () => {
    dispatch(clearSession());
    navigate('/game');
  };

  const handleViewRanking = () => {
    dispatch(clearSession());
    navigate('/ranking');
  };

  const handleGoHome = () => {
    dispatch(clearSession());
    navigate('/');
  };

  return (
    <div className="fixed inset-0 bg-black bg-opacity-90 flex items-center justify-center z-50 animate-fade-in">
      {/* Confetti particles */}
      {confetti.map((particle) => (
        <div
          key={particle.id}
          className="victory-confetti"
          style={{
            left: `${particle.left}%`,
            animationDelay: `${particle.delay}s`,
            background: particle.color,
          }}
        />
      ))}

      <div className="victory-container bg-gray-900 border-4 border-green-600 rounded-lg p-8 max-w-md w-full mx-4 shadow-2xl victory-glow-effect">
        {/* Trophy icon */}
        <div className="text-center mb-6">
          <div className="text-8xl mb-4 victory-burst">🏆</div>
          <h1 className="text-5xl font-bold text-green-400 mb-2 font-serif">
            ¡VICTORIA!
          </h1>
          <p className="text-gray-400 text-lg italic">
            Has escapado de la gruta oscura
          </p>
        </div>

        {/* Completion time */}
        <div className="bg-gray-800 rounded-lg p-6 mb-6 border-2 border-green-600">
          <div className="text-center">
            <p className="text-gray-400 text-sm uppercase tracking-wider mb-2">
              Tiempo de Completado
            </p>
            <div className="text-6xl font-bold text-green-400 font-mono">
              {formatTime(completionTime)}
            </div>
            <p className="text-gray-500 text-xs mt-2">
              {completionTime < 600 && '¡Increíblemente rápido!'}
              {completionTime >= 600 && completionTime < 1200 && '¡Excelente tiempo!'}
              {completionTime >= 1200 && '¡Lo lograste!'}
            </p>
          </div>
        </div>

        {/* Flavor text */}
        <div className="bg-gray-800 border-l-4 border-green-500 p-4 mb-6">
          <p className="text-gray-300 text-sm italic">
            "Has desafiado a los antiguos y emergido victorioso. Tu nombre será recordado 
            en las leyendas de aquellos que escaparon de las profundidades. 
            Los susurros de la oscuridad se desvanecen... por ahora."
          </p>
        </div>

        {/* Action buttons */}
        <div className="space-y-3">
          <button
            onClick={handleViewRanking}
            className="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-6 rounded-lg transition-colors duration-200 transform hover:scale-105"
          >
            Ver Ranking
          </button>
          <button
            onClick={handlePlayAgain}
            className="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg transition-colors duration-200"
          >
            Jugar de Nuevo
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

export default Victory;
