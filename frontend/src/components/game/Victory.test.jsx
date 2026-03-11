import { describe, it, expect, vi } from 'vitest';
import { render, screen, fireEvent } from '@testing-library/react';
import { Provider } from 'react-redux';
import { BrowserRouter } from 'react-router-dom';
import { configureStore } from '@reduxjs/toolkit';
import Victory from './Victory';
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

describe('Victory Component', () => {
  const createMockStore = () => {
    return configureStore({
      reducer: {
        game: gameReducer,
      },
      preloadedState: {
        game: {
          session: { status: 'completed', completion_time: 600 },
          timeRemaining: 900,
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

  it('renders victory title', () => {
    renderWithProviders(<Victory completionTime={600} />);

    expect(screen.getByText('¡VICTORIA!')).toBeInTheDocument();
  });

  it('displays trophy emoji', () => {
    renderWithProviders(<Victory completionTime={600} />);

    expect(screen.getByText('🏆')).toBeInTheDocument();
  });

  it('shows victory message', () => {
    renderWithProviders(<Victory completionTime={600} />);

    expect(screen.getByText('Has escapado de la gruta oscura')).toBeInTheDocument();
  });

  it('displays completion time correctly formatted', () => {
    renderWithProviders(<Victory completionTime={665} />);

    expect(screen.getByText('11:05')).toBeInTheDocument();
  });

  it('formats completion time with zero padding', () => {
    renderWithProviders(<Victory completionTime={305} />);

    expect(screen.getByText('05:05')).toBeInTheDocument();
  });

  it('shows fast completion message for time < 10 minutes', () => {
    renderWithProviders(<Victory completionTime={500} />);

    expect(screen.getByText('¡Increíblemente rápido!')).toBeInTheDocument();
  });

  it('shows excellent time message for time between 10-20 minutes', () => {
    renderWithProviders(<Victory completionTime={800} />);

    expect(screen.getByText('¡Excelente tiempo!')).toBeInTheDocument();
  });

  it('shows completion message for time >= 20 minutes', () => {
    renderWithProviders(<Victory completionTime={1300} />);

    expect(screen.getByText('¡Lo lograste!')).toBeInTheDocument();
  });

  it('shows flavor text', () => {
    renderWithProviders(<Victory completionTime={600} />);

    expect(
      screen.getByText(/Has desafiado a los antiguos/i)
    ).toBeInTheDocument();
  });

  it('renders view ranking button', () => {
    renderWithProviders(<Victory completionTime={600} />);

    expect(screen.getByText('Ver Ranking')).toBeInTheDocument();
  });

  it('renders play again button', () => {
    renderWithProviders(<Victory completionTime={600} />);

    expect(screen.getByText('Jugar de Nuevo')).toBeInTheDocument();
  });

  it('renders go home button', () => {
    renderWithProviders(<Victory completionTime={600} />);

    expect(screen.getByText('Volver al Inicio')).toBeInTheDocument();
  });

  it('navigates to ranking when view ranking is clicked', () => {
    renderWithProviders(<Victory completionTime={600} />);

    const viewRankingButton = screen.getByText('Ver Ranking');
    fireEvent.click(viewRankingButton);

    expect(mockNavigate).toHaveBeenCalledWith('/ranking');
  });

  it('navigates to game page when play again is clicked', () => {
    renderWithProviders(<Victory completionTime={600} />);

    const playAgainButton = screen.getByText('Jugar de Nuevo');
    fireEvent.click(playAgainButton);

    expect(mockNavigate).toHaveBeenCalledWith('/game');
  });

  it('navigates to home when go home is clicked', () => {
    renderWithProviders(<Victory completionTime={600} />);

    const goHomeButton = screen.getByText('Volver al Inicio');
    fireEvent.click(goHomeButton);

    expect(mockNavigate).toHaveBeenCalledWith('/');
  });

  it('handles zero completion time', () => {
    renderWithProviders(<Victory completionTime={0} />);

    expect(screen.getByText('00:00')).toBeInTheDocument();
  });

  it('handles maximum completion time (25 minutes)', () => {
    renderWithProviders(<Victory completionTime={1500} />);

    expect(screen.getByText('25:00')).toBeInTheDocument();
  });
});
