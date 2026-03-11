import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest';
import { render, screen, waitFor } from '@testing-library/react';
import { Provider } from 'react-redux';
import { configureStore } from '@reduxjs/toolkit';
import Timer from './Timer';
import gameReducer from '../../features/game/gameSlice';

// Feature: lovecraftian-escape-room
// **Validates: Requirements 2.3, 2.4, 2.5**

describe('Timer Component', () => {
  let store;

  beforeEach(() => {
    vi.useFakeTimers();
  });

  afterEach(() => {
    vi.restoreAllMocks();
  });

  const createMockStore = (initialState = {}) => {
    return configureStore({
      reducer: {
        game: gameReducer,
      },
      preloadedState: {
        game: {
          session: null,
          timeRemaining: 1500,
          isActive: false,
          loading: false,
          error: null,
          ...initialState,
        },
      },
    });
  };

  const renderWithStore = (component, store) => {
    return render(<Provider store={store}>{component}</Provider>);
  };

  it('displays initial timer value correctly', () => {
    store = createMockStore({ timeRemaining: 1500 });
    renderWithStore(<Timer />, store);

    expect(screen.getByText('25:00')).toBeInTheDocument();
  });

  it('formats time correctly as MM:SS', () => {
    store = createMockStore({ timeRemaining: 665 });
    renderWithStore(<Timer />, store);

    expect(screen.getByText('11:05')).toBeInTheDocument();
  });

  it('displays time remaining label', () => {
    store = createMockStore();
    renderWithStore(<Timer />, store);

    expect(screen.getByText('Tiempo Restante')).toBeInTheDocument();
  });

  it('shows green color when time > 15 minutes', () => {
    store = createMockStore({ timeRemaining: 1000 });
    renderWithStore(<Timer />, store);

    const timerDisplay = screen.getByText('16:40');
    expect(timerDisplay).toHaveClass('text-green-400');
  });

  it('shows yellow color when time between 5-15 minutes', () => {
    store = createMockStore({ timeRemaining: 600 });
    renderWithStore(<Timer />, store);

    const timerDisplay = screen.getByText('10:00');
    expect(timerDisplay).toHaveClass('text-yellow-400');
  });

  it('shows red color when time < 5 minutes', () => {
    store = createMockStore({ timeRemaining: 200 });
    renderWithStore(<Timer />, store);

    const timerDisplay = screen.getByText('03:20');
    expect(timerDisplay).toHaveClass('text-red-500');
  });

  it('shows warning message when time <= 60 seconds', () => {
    store = createMockStore({ timeRemaining: 45, isActive: true });
    renderWithStore(<Timer />, store);

    expect(screen.getByText('¡Apresúrate!')).toBeInTheDocument();
  });

  it('shows timeout message when time reaches zero', () => {
    store = createMockStore({ timeRemaining: 0 });
    renderWithStore(<Timer />, store);

    expect(screen.getByText('¡Tiempo agotado!')).toBeInTheDocument();
  });

  it('decrements timer every second when active', async () => {
    store = createMockStore({ timeRemaining: 100, isActive: true });
    renderWithStore(<Timer />, store);

    expect(screen.getByText('01:40')).toBeInTheDocument();

    // Advance timer by 1 second
    vi.advanceTimersByTime(1000);

    await waitFor(() => {
      const state = store.getState();
      expect(state.game.timeRemaining).toBe(99);
    });
  });

  it('does not decrement timer when not active', async () => {
    store = createMockStore({ timeRemaining: 100, isActive: false });
    renderWithStore(<Timer />, store);

    const initialTime = store.getState().game.timeRemaining;

    // Advance timer by 5 seconds
    vi.advanceTimersByTime(5000);

    await waitFor(() => {
      const state = store.getState();
      expect(state.game.timeRemaining).toBe(initialTime);
    });
  });

  it('handles zero padding correctly for single digit seconds', () => {
    store = createMockStore({ timeRemaining: 305 });
    renderWithStore(<Timer />, store);

    expect(screen.getByText('05:05')).toBeInTheDocument();
  });

  it('handles zero padding correctly for single digit minutes', () => {
    store = createMockStore({ timeRemaining: 65 });
    renderWithStore(<Timer />, store);

    expect(screen.getByText('01:05')).toBeInTheDocument();
  });
});
