import { describe, it, expect, vi } from 'vitest';
import { render, screen, waitFor } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import PuzzleFeedback from './PuzzleFeedback';

describe('PuzzleFeedback', () => {
  it('renders correct feedback with success styling', () => {
    render(
      <PuzzleFeedback
        isCorrect={true}
        message="¡Excelente trabajo!"
      />
    );

    expect(screen.getByText('¡Correcto!')).toBeInTheDocument();
    expect(screen.getByText('¡Excelente trabajo!')).toBeInTheDocument();
    
    const feedback = screen.getByText('¡Correcto!').closest('.puzzle-feedback');
    expect(feedback).toHaveClass('feedback-correct');
  });

  it('renders incorrect feedback with error styling', () => {
    render(
      <PuzzleFeedback
        isCorrect={false}
        message="Intenta de nuevo"
      />
    );

    expect(screen.getByText('Incorrecto')).toBeInTheDocument();
    expect(screen.getByText('Intenta de nuevo')).toBeInTheDocument();
    
    const feedback = screen.getByText('Incorrecto').closest('.puzzle-feedback');
    expect(feedback).toHaveClass('feedback-incorrect');
  });

  it('auto-dismisses success messages after 3 seconds', async () => {
    const onDismiss = vi.fn();
    
    const { container } = render(
      <PuzzleFeedback
        isCorrect={true}
        message="Success!"
        onDismiss={onDismiss}
      />
    );

    expect(screen.getByText('¡Correcto!')).toBeInTheDocument();

    await waitFor(
      () => {
        expect(onDismiss).toHaveBeenCalled();
      },
      { timeout: 3500 }
    );
  });

  it('shows dismiss button for incorrect feedback', () => {
    const onDismiss = vi.fn();
    
    render(
      <PuzzleFeedback
        isCorrect={false}
        message="Try again"
        onDismiss={onDismiss}
      />
    );

    const dismissButton = screen.getByLabelText('Cerrar');
    expect(dismissButton).toBeInTheDocument();
  });

  it('calls onDismiss when dismiss button is clicked', async () => {
    const user = userEvent.setup();
    const onDismiss = vi.fn();
    
    render(
      <PuzzleFeedback
        isCorrect={false}
        message="Try again"
        onDismiss={onDismiss}
      />
    );

    const dismissButton = screen.getByLabelText('Cerrar');
    await user.click(dismissButton);

    expect(onDismiss).toHaveBeenCalledTimes(1);
  });

  it('does not show dismiss button for success without onDismiss', () => {
    render(
      <PuzzleFeedback
        isCorrect={true}
        message="Success!"
      />
    );

    expect(screen.queryByLabelText('Cerrar')).not.toBeInTheDocument();
  });
});
