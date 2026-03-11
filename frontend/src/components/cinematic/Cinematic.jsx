import { useState, useEffect } from 'react';
import './Cinematic.css';

const Cinematic = ({ type = 'opening', onComplete }) => {
  const [canSkip, setCanSkip] = useState(false);

  useEffect(() => {
    // Allow skipping after 2 seconds
    const timer = setTimeout(() => setCanSkip(true), 2000);
    return () => clearTimeout(timer);
  }, []);

  const handleSkip = () => {
    if (canSkip && onComplete) {
      onComplete();
    }
  };

  const handleCinematicEnd = () => {
    if (onComplete) {
      onComplete();
    }
  };

  return (
    <div className="cinematic-overlay">
      <div className="cinematic-content">
        {type === 'opening' && <OpeningCinematic onEnd={handleCinematicEnd} />}
        {type === 'victory' && <VictoryCinematic onEnd={handleCinematicEnd} />}
      </div>

      {canSkip && (
        <button 
          className="cinematic-skip-btn"
          onClick={handleSkip}
          aria-label="Saltar cinemática"
        >
          Saltar →
        </button>
      )}
    </div>
  );
};

const OpeningCinematic = ({ onEnd }) => {
  useEffect(() => {
    const timer = setTimeout(onEnd, 8000); // 8 seconds
    return () => clearTimeout(timer);
  }, [onEnd]);

  return (
    <div className="opening-cinematic">
      <div className="cinematic-text fade-in-slow">
        <p className="cinematic-line delay-1">
          En las profundidades olvidadas...
        </p>
        <p className="cinematic-line delay-2">
          Donde la luz no alcanza...
        </p>
        <p className="cinematic-line delay-3">
          Yace una gruta ancestral...
        </p>
        <p className="cinematic-line delay-4">
          ¿Podrás escapar antes de que sea demasiado tarde?
        </p>
      </div>
      
      <div className="cinematic-vignette" />
    </div>
  );
};

const VictoryCinematic = ({ onEnd }) => {
  useEffect(() => {
    const timer = setTimeout(onEnd, 6000); // 6 seconds
    return () => clearTimeout(timer);
  }, [onEnd]);

  return (
    <div className="victory-cinematic">
      <div className="cinematic-text fade-in-slow">
        <p className="cinematic-title delay-1">
          ¡HAS ESCAPADO!
        </p>
        <p className="cinematic-line delay-2">
          La luz del mundo exterior te recibe...
        </p>
        <p className="cinematic-line delay-3">
          Pero las sombras nunca olvidan...
        </p>
      </div>
      
      <div className="victory-glow" />
    </div>
  );
};

export default Cinematic;
