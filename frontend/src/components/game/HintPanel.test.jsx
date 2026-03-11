import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest';
import { render, screen, fireEvent, waitFor } from '@testing-library/react';
import axios from 'axios';
import HintPanel from './HintPanel';

// Feature: lovecraftian-escape-room
// **Validates: Requirements 4.2, 4.3, 4.4**

vi.mock('axios');

describe('HintPanel Component', () => {
  const mockPuzzleId = 1;
  const mockOnHintUsed = vi.fn();

  beforeEach(() => {
    vi.clearAllMocks();
    localStorage.setItem('token', 'mock-token');
  });

  afterEach(() => {
    vi.restoreAllMocks();
  });

  it('displays hint button when available', async () => {
    axios.get.mockResolvedValueOnce({
      data: {
        success: true,
        data: {
          available: true,
          time_spent: 150,
          hints_used: 0,
          max_hints: 3,
          next_hint_level: 1,
        },
      },
    });

    render(<HintPanel puzzleId={mockPuzzleId} timeSpent={150} onHintUsed={mockOnHintUsed} />);

    await waitFor(() => {
      expect(screen.getByText('Obtener Pista')).toBeInTheDocument();
    });

    const hintButton = screen.getByRole('button', { name: /Obtener Pista/i });
    expect(hintButton).not.toBeDisabled();
  });

  it('displays unavailable state when time < 120 seconds', async () => {
    axios.get.mockResolvedValueOnce({
      data: {
        success: true,
        data: {
          available: false,
          time_spent: 60,
          hints_used: 0,
          max_hints: 3,
          next_hint_level: 1,
        },
      },
    });

    render(<HintPanel puzzleId={mockPuzzleId} timeSpent={60} onHintUsed={mockOnHintUsed} />);

    await waitFor(() => {
      expect(screen.getByText('Sin Pistas')).toBeInTheDocument();
    });

    const hintButton = screen.getByRole('button');
    expect(hintButton).toBeDisabled();
  });

  it('displays hints used counter', async () => {
    axios.get.mockResolvedValueOnce({
      data: {
        success: true,
        data: {
          available: true,
          time_spent: 150,
          hints_used: 2,
          max_hints: 3,
          next_hint_level: 3,
        },
      },
    });

    render(<HintPanel puzzleId={mockPuzzleId} timeSpent={150} onHintUsed={mockOnHintUsed} />);

    await waitFor(() => {
      expect(screen.getByText('Pistas usadas:')).toBeInTheDocument();
      expect(screen.getByText('2 / 3')).toBeInTheDocument();
    });
  });

  it('shows notification when hints become available', async () => {
    axios.get.mockResolvedValueOnce({
      data: {
        success: true,
        data: {
          available: true,
          time_spent: 125,
          hints_used: 0,
          max_hints: 3,
          next_hint_level: 1,
        },
      },
    });

    render(<HintPanel puzzleId={mockPuzzleId} timeSpent={125} onHintUsed={mockOnHintUsed} />);

    await waitFor(() => {
      expect(
        screen.getByText(/¡Pista disponible! Has pasado más de 2 minutos en este puzzle./i)
      ).toBeInTheDocument();
    });
  });

  it('requests and displays hint when button is clicked', async () => {
    const mockHint = {
      level: 1,
      content: 'Esta es una pista útil',
      hints_used: 1,
    };

    axios.get
      .mockResolvedValueOnce({
        data: {
          success: true,
          data: {
            available: true,
            time_spent: 150,
            hints_used: 0,
            max_hints: 3,
            next_hint_level: 1,
          },
        },
      })
      .mockResolvedValueOnce({
        data: {
          success: true,
          data: mockHint,
        },
      });

    render(<HintPanel puzzleId={mockPuzzleId} timeSpent={150} onHintUsed={mockOnHintUsed} />);

    await waitFor(() => {
      expect(screen.getByText('Obtener Pista')).toBeInTheDocument();
    });

    const hintButton = screen.getByRole('button', { name: /Obtener Pista/i });
    fireEvent.click(hintButton);

    await waitFor(() => {
      expect(screen.getByText('Pista 1')).toBeInTheDocument();
      expect(screen.getByText('Esta es una pista útil')).toBeInTheDocument();
    });

    expect(mockOnHintUsed).toHaveBeenCalledWith(mockHint);
  });

  it('closes hint modal when close button is clicked', async () => {
    const mockHint = {
      level: 1,
      content: 'Esta es una pista útil',
      hints_used: 1,
    };

    axios.get
      .mockResolvedValueOnce({
        data: {
          success: true,
          data: {
            available: true,
            time_spent: 150,
            hints_used: 0,
            max_hints: 3,
            next_hint_level: 1,
          },
        },
      })
      .mockResolvedValueOnce({
        data: {
          success: true,
          data: mockHint,
        },
      });

    render(<HintPanel puzzleId={mockPuzzleId} timeSpent={150} onHintUsed={mockOnHintUsed} />);

    await waitFor(() => {
      expect(screen.getByText('Obtener Pista')).toBeInTheDocument();
    });

    const hintButton = screen.getByRole('button', { name: /Obtener Pista/i });
    fireEvent.click(hintButton);

    await waitFor(() => {
      expect(screen.getByText('Pista 1')).toBeInTheDocument();
    });

    const closeButton = screen.getByText('✕');
    fireEvent.click(closeButton);

    await waitFor(() => {
      expect(screen.queryByText('Pista 1')).not.toBeInTheDocument();
    });
  });

  it('closes hint modal when "Entendido" button is clicked', async () => {
    const mockHint = {
      level: 1,
      content: 'Esta es una pista útil',
      hints_used: 1,
    };

    axios.get
      .mockResolvedValueOnce({
        data: {
          success: true,
          data: {
            available: true,
            time_spent: 150,
            hints_used: 0,
            max_hints: 3,
            next_hint_level: 1,
          },
        },
      })
      .mockResolvedValueOnce({
        data: {
          success: true,
          data: mockHint,
        },
      });

    render(<HintPanel puzzleId={mockPuzzleId} timeSpent={150} onHintUsed={mockOnHintUsed} />);

    await waitFor(() => {
      expect(screen.getByText('Obtener Pista')).toBeInTheDocument();
    });

    const hintButton = screen.getByRole('button', { name: /Obtener Pista/i });
    fireEvent.click(hintButton);

    await waitFor(() => {
      expect(screen.getByText('Entendido')).toBeInTheDocument();
    });

    const understoodButton = screen.getByText('Entendido');
    fireEvent.click(understoodButton);

    await waitFor(() => {
      expect(screen.queryByText('Pista 1')).not.toBeInTheDocument();
    });
  });

  it('displays error message when hint request fails', async () => {
    axios.get
      .mockResolvedValueOnce({
        data: {
          success: true,
          data: {
            available: true,
            time_spent: 150,
            hints_used: 0,
            max_hints: 3,
            next_hint_level: 1,
          },
        },
      })
      .mockRejectedValueOnce({
        response: {
          data: {
            message: 'Error al obtener la pista',
          },
        },
      });

    render(<HintPanel puzzleId={mockPuzzleId} timeSpent={150} onHintUsed={mockOnHintUsed} />);

    await waitFor(() => {
      expect(screen.getByText('Obtener Pista')).toBeInTheDocument();
    });

    const hintButton = screen.getByRole('button', { name: /Obtener Pista/i });
    fireEvent.click(hintButton);

    await waitFor(() => {
      expect(screen.getByText('Error al obtener la pista')).toBeInTheDocument();
    });
  });

  it('disables hint button when all hints are used', async () => {
    axios.get.mockResolvedValueOnce({
      data: {
        success: true,
        data: {
          available: false,
          time_spent: 200,
          hints_used: 3,
          max_hints: 3,
          next_hint_level: 4,
        },
      },
    });

    render(<HintPanel puzzleId={mockPuzzleId} timeSpent={200} onHintUsed={mockOnHintUsed} />);

    await waitFor(() => {
      expect(screen.getByText('Sin Pistas')).toBeInTheDocument();
    });

    const hintButton = screen.getByRole('button');
    expect(hintButton).toBeDisabled();
  });

  it('updates hints used counter after requesting a hint', async () => {
    const mockHint = {
      level: 1,
      content: 'Esta es una pista útil',
      hints_used: 1,
    };

    axios.get
      .mockResolvedValueOnce({
        data: {
          success: true,
          data: {
            available: true,
            time_spent: 150,
            hints_used: 0,
            max_hints: 3,
            next_hint_level: 1,
          },
        },
      })
      .mockResolvedValueOnce({
        data: {
          success: true,
          data: mockHint,
        },
      });

    render(<HintPanel puzzleId={mockPuzzleId} timeSpent={150} onHintUsed={mockOnHintUsed} />);

    await waitFor(() => {
      expect(screen.getByText('0 / 3')).toBeInTheDocument();
    });

    const hintButton = screen.getByRole('button', { name: /Obtener Pista/i });
    fireEvent.click(hintButton);

    await waitFor(() => {
      expect(screen.getByText('1 / 3')).toBeInTheDocument();
    });
  });
});
