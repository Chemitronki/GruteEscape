import { describe, it, expect } from 'vitest';
import { render, screen } from '@testing-library/react';
import PuzzleContainer from './PuzzleContainer';

describe('PuzzleContainer', () => {
  it('renders title and description', () => {
    render(
      <PuzzleContainer
        title="Test Puzzle"
        description="This is a test puzzle"
      >
        <div>Puzzle content</div>
      </PuzzleContainer>
    );

    expect(screen.getByText('Test Puzzle')).toBeInTheDocument();
    expect(screen.getByText('This is a test puzzle')).toBeInTheDocument();
    expect(screen.getByText('Puzzle content')).toBeInTheDocument();
  });

  it('shows loading state when isLoading is true', () => {
    render(
      <PuzzleContainer
        title="Test Puzzle"
        description="Description"
        isLoading={true}
      >
        <div>Puzzle content</div>
      </PuzzleContainer>
    );

    expect(screen.getByText('Cargando puzzle...')).toBeInTheDocument();
    expect(screen.queryByText('Puzzle content')).not.toBeInTheDocument();
  });

  it('shows overlay when disabled', () => {
    render(
      <PuzzleContainer
        title="Test Puzzle"
        description="Description"
        disabled={true}
      >
        <div>Puzzle content</div>
      </PuzzleContainer>
    );

    expect(screen.getByText('El juego ha terminado')).toBeInTheDocument();
    
    const container = screen.getByText('Test Puzzle').closest('.puzzle-container');
    expect(container).toHaveClass('puzzle-disabled');
  });

  it('renders children when not loading', () => {
    render(
      <PuzzleContainer
        title="Test Puzzle"
        description="Description"
        isLoading={false}
      >
        <div data-testid="puzzle-child">Puzzle content</div>
      </PuzzleContainer>
    );

    expect(screen.getByTestId('puzzle-child')).toBeInTheDocument();
    expect(screen.queryByText('Cargando puzzle...')).not.toBeInTheDocument();
  });

  it('applies correct CSS classes', () => {
    const { container } = render(
      <PuzzleContainer
        title="Test Puzzle"
        description="Description"
      >
        <div>Content</div>
      </PuzzleContainer>
    );

    const puzzleContainer = container.querySelector('.puzzle-container');
    expect(puzzleContainer).toBeInTheDocument();
    expect(puzzleContainer).not.toHaveClass('puzzle-disabled');
  });
});
