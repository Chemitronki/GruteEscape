import { describe, it, expect, vi } from 'vitest';
import { render, screen, fireEvent } from '@testing-library/react';
import { Provider } from 'react-redux';
import { BrowserRouter } from 'react-router-dom';
import { configureStore } from '@reduxjs/toolkit';
import GameOver from './GameOver';
import gameReducer from '../../features/game/gameSlice';

// Feature: lovecraftian-escape-room
// **Validates: Requirements 2.4, 2.5**

const mockNavigate = vi.fn();

vi.mock('react-router-dom', async () => {
  const actual = await vi.importActual('react-router-dom');
  return {
    ...actual,
    useNavigate: () => mockNavigate,
  };
});

describe('GameOver Component', () => {
  const createMockStore = () => {
    return configureStore({
      reducer: {
        game: gameReducer,
      },
      preloadedState: {
        game: {
          session: null,
          timeRemaining: 0,
          isActive: false,
          loading: false,
          error: null,
        },
      },
    });
  };

  const renderWithProviders = (component) => {
    const store = createMockStore();
    return render(
      <Provider store={store}>
        <BrowserRouter>{component}</BrowserRouter>
      </Provider>
    );
  };

  it('renders game over title', () => {
    renderWithProviders(<GameOver puzzlesCompleted={3} totalPuzzles={10} />);

    expect(screen.getByText('GAME OVER')).toBeInTheDocument();
  });

  it('displays skull emoji', () => {
    renderWithProviders(<GameOver puzzlesCompleted={3} totalPuzzles={10} />);

    expect(screen.getByText('💀')).toBeInTheDocument();
  });

  it('shows timeout message', () => {
    renderWithProviders(<GameOver puzzlesCompleted={3} totalPuzzles={10} />);

    expect(screen.getByText('El tiempo se ha agotado...')).toBeInTheDocument();
  });

  it('displays puzzles completed count', () => {
    renderWithProviders(<GameOver puzzlesCompleted={5} totalPuzzles={10} />);

    expect(screen.getByText('5 / 10')).toBeInTheDocument();
  });

  it('displays correct progress percentage', () => {
    const { container } = renderWithProviders(
      <GameOver puzzlesCompleted={3} totalPuzzles={10} />
    );

    const progressBar = container.querySelector('.bg-red-500');
    expect(progressBar).toHaveStyle({ width: '30%' });
  });

  it('shows flavor text', () => {
    renderWithProviders(<GameOver puzzlesCompleted={3} totalPuzzles={10} />);

    expect(
      screen.getByText(/La oscuridad te ha consumido/i)
    ).toBeInTheDocument();
  });

  it('renders try again button', () => {
    renderWithProviders(<GameOver puzzlesCompleted={3} totalPuzzles={10} />);

    expect(screen.getByText('Intentar de Nuevo')).toBeInTheDocument();
  });

  it('renders go home button', () => {
    renderWithProviders(<GameOver puzzlesCompleted={3} totalPuzzles={10} />);

    expect(screen.getByText('Volver al Inicio')).toBeInTheDocument();
  });

  it('navigates to game page when try again is clicked', () => {
    renderWithProviders(<GameOver puzzlesCompleted={3} totalPuzzles={10} />);

    const tryAgainButton = screen.getByText('Intentar de Nuevo');
    fireEvent.click(tryAgainButton);

    expect(mockNavigate).toHaveBeenCalledWith('/game');
  });

  it('navigates to home when go home is clicked', () => {
    renderWithProviders(<GameOver puzzlesCompleted={3} totalPuzzles={10} />);

    const goHomeButton = screen.getByText('Volver al Inicio');
    fireEvent.click(goHomeButton);

    expect(mockNavigate).toHaveBeenCalledWith('/');
  });

  it('handles zero puzzles completed', () => {
    renderWithProviders(<GameOver puzzlesCompleted={0} totalPuzzles={10} />);

    expect(screen.getByText('0 / 10')).toBeInTheDocument();
  });

  it('handles all puzzles completed (timeout after completion)', () => {
    renderWithProviders(<GameOver puzzlesCompleted={10} totalPuzzles={10} />);

    expect(screen.getByText('10 / 10')).toBeInTheDocument();
  });
});
