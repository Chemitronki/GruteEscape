import { useRef, useCallback } from 'react';

/**
 * Custom hook for playing sound effects using Web Audio API
 */
const useSoundEffects = () => {
  const audioContextRef = useRef(null);

  const getAudioContext = useCallback(() => {
    if (!audioContextRef.current) {
      audioContextRef.current = new (window.AudioContext || window.webkitAudioContext)();
    }
    return audioContextRef.current;
  }, []);

  const playTone = useCallback((frequency, duration, volume = 0.3) => {
    try {
      const ctx = getAudioContext();
      const oscillator = ctx.createOscillator();
      const gainNode = ctx.createGain();

      oscillator.connect(gainNode);
      gainNode.connect(ctx.destination);

      oscillator.frequency.value = frequency;
      oscillator.type = 'sine';
      
      gainNode.gain.setValueAtTime(volume, ctx.currentTime);
      gainNode.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + duration);

      oscillator.start(ctx.currentTime);
      oscillator.stop(ctx.currentTime + duration);
    } catch (error) {
      console.error('Error playing sound:', error);
    }
  }, [getAudioContext]);

  const playPuzzleCorrect = useCallback(() => {
    playTone(523.25, 0.2, 0.3);
    setTimeout(() => playTone(659.25, 0.2, 0.3), 100);
    setTimeout(() => playTone(783.99, 0.3, 0.3), 200);
  }, [playTone]);

  const playPuzzleIncorrect = useCallback(() => {
    playTone(220, 0.15, 0.2);
    setTimeout(() => playTone(196, 0.3, 0.2), 150);
  }, [playTone]);

  const playHintAvailable = useCallback(() => {
    playTone(440, 0.1, 0.2);
    setTimeout(() => playTone(554.37, 0.1, 0.2), 100);
    setTimeout(() => playTone(659.25, 0.2, 0.2), 200);
  }, [playTone]);

  const playTimerWarning = useCallback(() => {
    playTone(293.66, 0.15, 0.25);
    setTimeout(() => playTone(293.66, 0.15, 0.25), 200);
  }, [playTone]);

  const playVictory = useCallback(() => {
    const notes = [523.25, 587.33, 659.25, 783.99, 880];
    notes.forEach((freq, i) => {
      setTimeout(() => playTone(freq, 0.3, 0.3), i * 150);
    });
  }, [playTone]);

  const playGameOver = useCallback(() => {
    playTone(392, 0.2, 0.3);
    setTimeout(() => playTone(349.23, 0.2, 0.3), 200);
    setTimeout(() => playTone(293.66, 0.2, 0.3), 400);
    setTimeout(() => playTone(261.63, 0.5, 0.3), 600);
  }, [playTone]);

  const playClick = useCallback(() => {
    playTone(880, 0.05, 0.1);
  }, [playTone]);

  return {
    playPuzzleCorrect,
    playPuzzleIncorrect,
    playHintAvailable,
    playTimerWarning,
    playVictory,
    playGameOver,
    playClick,
  };
};

export default useSoundEffects;
