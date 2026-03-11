import { useSelector } from 'react-redux';

const ProgressIndicator = ({ puzzlesCompleted = 0, totalPuzzles = 10 }) => {
  const percentage = (puzzlesCompleted / totalPuzzles) * 100;

  return (
    <div className="progress-indicator bg-gray-900 border-2 border-gray-700 rounded-lg p-4 shadow-lg">
      <div className="mb-3">
        <div className="flex justify-between items-center mb-2">
          <span className="text-gray-400 text-sm uppercase tracking-wider">
            Progreso
          </span>
          <span className="text-green-400 font-bold">
            {puzzlesCompleted} / {totalPuzzles}
          </span>
        </div>
        
        {/* Progress bar */}
        <div className="w-full bg-gray-800 rounded-full h-3 overflow-hidden">
          <div
            className="bg-gradient-to-r from-green-500 to-emerald-400 h-full transition-all duration-500 ease-out"
            style={{ width: `${percentage}%` }}
          />
        </div>
      </div>

      {/* Puzzle indicators */}
      <div className="flex gap-1 justify-center">
        {Array.from({ length: totalPuzzles }).map((_, index) => (
          <div
            key={index}
            className={`w-6 h-6 rounded-full border-2 flex items-center justify-center text-xs font-bold transition-all duration-300 ${
              index < puzzlesCompleted
                ? 'bg-green-500 border-green-400 text-white'
                : 'bg-gray-800 border-gray-600 text-gray-500'
            }`}
          >
            {index < puzzlesCompleted ? '✓' : index + 1}
          </div>
        ))}
      </div>

      {puzzlesCompleted === totalPuzzles && (
        <p className="text-center text-green-400 text-sm mt-3 font-bold animate-pulse">
          ¡Todos los puzzles completados!
        </p>
      )}
    </div>
  );
};

export default ProgressIndicator;
