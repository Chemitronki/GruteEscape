import { useEffect } from 'react';
import { useDispatch, useSelector } from 'react-redux';
import { decrementTimer, syncTimer } from '../../features/game/gameSlice';
import useSoundEffects from '../../hooks/useSoundEffects';
import '../../styles/animations.css';

const Timer = () => {
  const dispatch = useDispatch();
  const { timeRemaining, isActive } = useSelector((state) => state.game);
  const { playTimerWarning } = useSoundEffects();

  // Client-side timer with setInterval
  useEffect(() => {
    if (!isActive) return;

    const interval = setInterval(() => {
      dispatch(decrementTimer());
    }, 1000);

    return () => clearInterval(interval);
  }, [isActive, dispatch]);

  // Sync with backend every 30 seconds
  useEffect(() => {
    if (!isActive) return;

    const syncInterval = setInterval(() => {
      dispatch(syncTimer(timeRemaining));
    }, 30000);

    return () => clearInterval(syncInterval);
  }, [isActive, timeRemaining, dispatch]);

  // Play warning sound at 5 minutes
  useEffect(() => {
    if (timeRemaining === 300) {
      playTimerWarning();
    }
  }, [timeRemaining, playTimerWarning]);

  // Format time as MM:SS
  const formatTime = (seconds) => {
    const minutes = Math.floor(seconds / 60);
    const secs = seconds % 60;
    return `${minutes.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
  };

  // Determine timer class based on time remaining
  const getTimerClass = () => {
    if (timeRemaining <= 60) return 'timer-critical';
    if (timeRemaining <= 300) return 'timer-warning';
    return '';
  };

  // Determine color based on time remaining
  const getTimerColor = () => {
    if (timeRemaining > 900) return 'text-green-400';
    if (timeRemaining > 300) return 'text-yellow-400';
    return 'text-red-500';
  };

  return (
    <div className="timer-container bg-gray-900 border-2 border-gray-700 rounded-lg p-4 shadow-lg">
      <div className="text-center">
        <p className="text-gray-400 text-sm uppercase tracking-wider mb-2">
          Tiempo Restante
        </p>
        <div className={`text-5xl font-bold font-mono ${getTimerColor()} ${getTimerClass()} transition-colors duration-300`}>
          {formatTime(timeRemaining)}
        </div>
        {timeRemaining <= 60 && timeRemaining > 0 && (
          <p className="text-red-400 text-xs mt-2 animate-pulse">
            ¡Apresúrate!
          </p>
        )}
        {timeRemaining === 0 && (
          <p className="text-red-500 text-sm mt-2 font-bold game-over-shake">
            ¡Tiempo agotado!
          </p>
        )}
      </div>
    </div>
  );
};

export default Timer;
