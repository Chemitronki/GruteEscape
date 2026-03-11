import React, { useState } from 'react';
import PropTypes from 'prop-types';
import './ForbiddenTome.css';

/**
 * ForbiddenTome - Puzzle where user reconstructs torn pages in correct order
 * Requirements: 3.1, 3.7
 */
const ForbiddenTome = ({ puzzleData, onSubmit, disabled }) => {
  const pages = puzzleData?.data?.pages || [];
  const [orderedPages, setOrderedPages] = useState([...pages]);
  const [draggedPage, setDraggedPage] = useState(null);

  const handleDragStart = (e, index) => {
    if (disabled) return;
    setDraggedPage(index);
    e.dataTransfer.effectAllowed = 'move';
  };

  const handleDragOver = (e) => {
    e.preventDefault();
    e.dataTransfer.dropEffect = 'move';
  };

  const handleDrop = (e, dropIndex) => {
    e.preventDefault();
    if (draggedPage === null || disabled) return;

    const newPages = [...orderedPages];
    const draggedItem = newPages[draggedPage];
    
    // Remove from old position
    newPages.splice(draggedPage, 1);
    // Insert at new position
    newPages.splice(dropIndex, 0, draggedItem);
    
    setOrderedPages(newPages);
    setDraggedPage(null);
  };

  const handleDragEnd = () => {
    setDraggedPage(null);
  };

  const handleSubmit = () => {
    if (!disabled) {
      const pageIds = orderedPages.map(page => page.id);
      onSubmit(pageIds);
    }
  };

  const handleReset = () => {
    setOrderedPages([...pages]);
  };

  return (
    <div className="forbidden-tome">
      <div className="tome-instructions">
        Arrastra las páginas desgarradas para reconstruir el tomo en el orden correcto
      </div>

      <div className="pages-container">
        {orderedPages.map((page, index) => (
          <div
            key={page.id}
            className={`tome-page ${draggedPage === index ? 'dragging' : ''}`}
            draggable={!disabled}
            onDragStart={(e) => handleDragStart(e, index)}
            onDragOver={handleDragOver}
            onDrop={(e) => handleDrop(e, index)}
            onDragEnd={handleDragEnd}
          >
            <div className="page-number">{index + 1}</div>
            <div className="page-content">
              <div className="page-text">{page.content}</div>
              {page.image && (
                <div className="page-image">{page.image}</div>
              )}
            </div>
            <div className="page-tear"></div>
          </div>
        ))}
      </div>

      <div className="tome-controls">
        <button
          onClick={handleReset}
          disabled={disabled}
          className="tome-reset"
        >
          Reiniciar
        </button>
        <button
          onClick={handleSubmit}
          disabled={disabled}
          className="tome-submit"
        >
          Reconstruir Tomo
        </button>
      </div>
    </div>
  );
};

ForbiddenTome.propTypes = {
  puzzleData: PropTypes.shape({
    data: PropTypes.shape({
      pages: PropTypes.arrayOf(
        PropTypes.shape({
          id: PropTypes.oneOfType([PropTypes.string, PropTypes.number]).isRequired,
          content: PropTypes.string.isRequired,
          image: PropTypes.string,
        })
      ),
    }),
  }).isRequired,
  onSubmit: PropTypes.func.isRequired,
  disabled: PropTypes.bool,
};

export default ForbiddenTome;
