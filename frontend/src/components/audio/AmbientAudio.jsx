import { useEffect, useRef, useState } from 'react';
import './AmbientAudio.css';

const AmbientAudio = ({ enabled = true }) => {
  const audioRef = useRef(null);
  const [volume, setVolume] = useState(0.3);
  const [isMuted, setIsMuted] = useState(false);
  const [isPlaying, setIsPlaying] = useState(false);

  useEffect(() => {
    if (!audioRef.current) return;

    // Create audio context for ambient sounds
    // In production, replace with actual audio files
    const audioContext = new (window.AudioContext || window.webkitAudioContext)();
    
    if (enabled && !isMuted) {
      audioContext.resume();
      setIsPlaying(true);
    }

    return () => {
      if (audioContext.state !== 'closed') {
        audioContext.close();
      }
    };
  }, [enabled, isMuted]);

  useEffect(() => {
    if (audioRef.current) {
      audioRef.current.volume = isMuted ? 0 : volume;
    }
  }, [volume, isMuted]);

  const toggleMute = () => {
    setIsMuted(!isMuted);
  };

  const handleVolumeChange = (e) => {
    setVolume(parseFloat(e.target.value));
  };

  if (!enabled) return null;

  return (
    <div className="ambient-audio-controls">
      <button 
        className="audio-toggle-btn"
        onClick={toggleMute}
        aria-label={isMuted ? 'Activar sonido' : 'Silenciar sonido'}
      >
        {isMuted ? '🔇' : '🔊'}
      </button>
      
      <div className="volume-control">
        <input
          type="range"
          min="0"
          max="1"
          step="0.1"
          value={volume}
          onChange={handleVolumeChange}
          className="volume-slider"
          aria-label="Control de volumen"
        />
      </div>

      {/* Hidden audio element for future implementation */}
      <audio 
        ref={audioRef}
        loop
        preload="auto"
      >
        {/* Add audio sources here when available */}
        {/* <source src="/audio/ambient-cave.mp3" type="audio/mpeg" /> */}
      </audio>
    </div>
  );
};

export default AmbientAudio;
