import { describe, it, expect, vi, beforeEach } from 'vitest';
import { render, screen, fireEvent, waitFor } from '@testing-library/react';
import { Provider } from 'react-redux';
import { configureStore } from '@reduxjs/toolkit';
import authReducer from '../../features/auth/authSlice';
import LoginForm from './LoginForm';

// Mock store factory
const createMockStore = (initialState = {}) => {
  return configureStore({
    reducer: {
      auth: authReducer,
    },
    preloadedState: {
      auth: {
        user: null,
        token: null,
        isAuthenticated: false,
        loading: false,
        error: null,
        ...initialState,
      },
    },
  });
};

describe('LoginForm', () => {
  beforeEach(() => {
    // Clear localStorage before each test
    localStorage.clear();
  });

  it('renders login form with email and password fields', () => {
    const store = createMockStore();
    
    render(
      <Provider store={store}>
        <LoginForm />
      </Provider>
    );
    
    expect(screen.getByLabelText(/correo electrónico/i)).toBeInTheDocument();
    expect(screen.getByLabelText(/contraseña/i)).toBeInTheDocument();
    expect(screen.getByRole('button', { name: /iniciar sesión/i })).toBeInTheDocument();
  });

  it('validates email format', async () => {
    const store = createMockStore();
    
    render(
      <Provider store={store}>
        <LoginForm />
      </Provider>
    );
    
    const emailInput = screen.getByLabelText(/correo electrónico/i);
    const submitButton = screen.getByRole('button', { name: /iniciar sesión/i });
    
    // Enter invalid email
    fireEvent.change(emailInput, { target: { value: 'invalid-email' } });
    fireEvent.click(submitButton);
    
    await waitFor(() => {
      expect(screen.getByText(/debe proporcionar un correo electrónico válido/i)).toBeInTheDocument();
    });
  });

  it('validates required fields', async () => {
    const store = createMockStore();
    
    render(
      <Provider store={store}>
        <LoginForm />
      </Provider>
    );
    
    const submitButton = screen.getByRole('button', { name: /iniciar sesión/i });
    
    // Submit without filling fields
    fireEvent.click(submitButton);
    
    await waitFor(() => {
      expect(screen.getByText(/el correo electrónico es obligatorio/i)).toBeInTheDocument();
      expect(screen.getByText(/la contraseña es obligatoria/i)).toBeInTheDocument();
    });
  });

  it('clears validation errors when user types', async () => {
    const store = createMockStore();
    
    render(
      <Provider store={store}>
        <LoginForm />
      </Provider>
    );
    
    const emailInput = screen.getByLabelText(/correo electrónico/i);
    const submitButton = screen.getByRole('button', { name: /iniciar sesión/i });
    
    // Trigger validation error
    fireEvent.click(submitButton);
    
    await waitFor(() => {
      expect(screen.getByText(/el correo electrónico es obligatorio/i)).toBeInTheDocument();
    });
    
    // Type in email field
    fireEvent.change(emailInput, { target: { value: 'test@example.com' } });
    
    await waitFor(() => {
      expect(screen.queryByText(/el correo electrónico es obligatorio/i)).not.toBeInTheDocument();
    });
  });

  it('displays server error message', () => {
    const store = createMockStore({
      error: 'Credenciales inválidas',
    });
    
    render(
      <Provider store={store}>
        <LoginForm />
      </Provider>
    );
    
    expect(screen.getByText(/credenciales inválidas/i)).toBeInTheDocument();
  });

  it('disables form during loading', () => {
    const store = createMockStore({
      loading: true,
    });
    
    render(
      <Provider store={store}>
        <LoginForm />
      </Provider>
    );
    
    const emailInput = screen.getByLabelText(/correo electrónico/i);
    const passwordInput = screen.getByLabelText(/contraseña/i);
    const submitButton = screen.getByRole('button', { name: /iniciando sesión/i });
    
    expect(emailInput).toBeDisabled();
    expect(passwordInput).toBeDisabled();
    expect(submitButton).toBeDisabled();
  });

  it('submits form with valid data', async () => {
    const store = createMockStore();
    
    render(
      <Provider store={store}>
        <LoginForm />
      </Provider>
    );
    
    const emailInput = screen.getByLabelText(/correo electrónico/i);
    const passwordInput = screen.getByLabelText(/contraseña/i);
    const submitButton = screen.getByRole('button', { name: /iniciar sesión/i });
    
    // Fill form with valid data
    fireEvent.change(emailInput, { target: { value: 'test@example.com' } });
    fireEvent.change(passwordInput, { target: { value: 'password123' } });
    
    // Submit form
    fireEvent.click(submitButton);
    
    // Form should not show validation errors
    await waitFor(() => {
      expect(screen.queryByText(/el correo electrónico es obligatorio/i)).not.toBeInTheDocument();
      expect(screen.queryByText(/la contraseña es obligatoria/i)).not.toBeInTheDocument();
    });
  });
});
