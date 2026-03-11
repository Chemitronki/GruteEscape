import { useRef, useCallback } from 'react';

const SoundEffects = () => {
  const audioContextRef = useRef(null);

  // Initialize audio context
  const getAudioContext = useCallback(() => {
    if (!audioContextRef.current) {
      audioContextRef.current = new (window.AudioContext || window.webkitAudioContext)();
    }
    return audioContextRef.current;
  }, []);

  // Generate tone using Web Audio API
  const playTone = useCallback((frequency, duration, volume = 0.3) => {
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
  }, [getAudioContext]);

  // Sound effect functions
  const playPuzzleCorrect = useCallback(() => {
    playTone(523.25, 0.2, 0.3); // C5
    setTimeout(() => playTone(659.25, 0.2, 0.3), 100); // E5
    setTimeout(() => playTone(783.99, 0.3, 0.3), 200); // G5
  }, [playTone]);

  const playPuzzleIncorrect = useCallback(() => {
    playTone(220, 0.15, 0.2); // A3
    setTimeout(() => playTone(196, 0.3, 0.2), 150); // G3
  }, [playTone]);

  const playHintAvailable = useCallback(() => {
    playTone(440, 0.1, 0.2); // A4
    setTimeout(() => playTone(554.37, 0.1, 0.2), 100); // C#5
    setTimeout(() => playTone(659.25, 0.2, 0.2), 200); // E5
  }, [playTone]);

  const playTimerWarning = useCallback(() => {
    playTone(293.66, 0.15, 0.25); // D4
    setTimeout(() => playTone(293.66, 0.15, 0.25), 200);
  }, [playTone]);

  const playVictory = useCallback(() => {
    const notes = [523.25, 587.33, 659.25, 783.99, 880]; // C5, D5, E5, G5, A5
    notes.forEach((freq, i) => {
      setTimeout(() => playTone(freq, 0.3, 0.3), i * 150);
    });
  }, [playTone]);

  const playGameOver = useCallback(() => {
    playTone(392, 0.2, 0.3); // G4
    setTimeout(() => playTone(349.23, 0.2, 0.3), 200); // F4
    setTimeout(() => playTone(293.66, 0.2, 0.3), 400); // D4
    setTimeout(() => playTone(261.63, 0.5, 0.3), 600); // C4
  }, [playTone]);

  const playClick = useCallback(() => {
    playTone(880, 0.05, 0.1); // A5
  }, [playTone]);

  // Expose sound effects through ref
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

export default SoundEffects;
