import { describe, it, expect, vi, beforeEach } from 'vitest';
import { render, screen, fireEvent, waitFor } from '@testing-library/react';
import { Provider } from 'react-redux';
import { configureStore } from '@reduxjs/toolkit';
import authReducer from '../../features/auth/authSlice';
import RegisterForm from './RegisterForm';

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

describe('RegisterForm', () => {
  beforeEach(() => {
    // Clear localStorage before each test
    localStorage.clear();
  });

  it('renders registration form with all required fields', () => {
    const store = createMockStore();
    
    render(
      <Provider store={store}>
        <RegisterForm />
      </Provider>
    );
    
    expect(screen.getByLabelText(/nombre de usuario/i)).toBeInTheDocument();
    expect(screen.getByLabelText(/^correo electrónico/i)).toBeInTheDocument();
    expect(screen.getByLabelText(/^contraseña$/i)).toBeInTheDocument();
    expect(screen.getByLabelText(/confirmar contraseña/i)).toBeInTheDocument();
    expect(screen.getByRole('button', { name: /registrarse/i })).toBeInTheDocument();
  });

  it('validates username format', async () => {
    const store = createMockStore();
    
    render(
      <Provider store={store}>
        <RegisterForm />
      </Provider>
    );
    
    const usernameInput = screen.getByLabelText(/nombre de usuario/i);
    const submitButton = screen.getByRole('button', { name: /registrarse/i });
    
    // Enter invalid username (with special characters)
    fireEvent.change(usernameInput, { target: { value: 'user@name!' } });
    fireEvent.click(submitButton);
    
    await waitFor(() => {
      expect(screen.getByText(/solo puede contener letras, números, guiones/i)).toBeInTheDocument();
    });
  });

  it('validates username length', async () => {
    const store = createMockStore();
    
    render(
      <Provider store={store}>
        <RegisterForm />
      </Provider>
    );
    
    const usernameInput = screen.getByLabelText(/nombre de usuario/i);
    const submitButton = screen.getByRole('button', { name: /registrarse/i });
    
    // Enter too short username
    fireEvent.change(usernameInput, { target: { value: 'ab' } });
    fireEvent.click(submitButton);
    
    await waitFor(() => {
      expect(screen.getByText(/debe tener al menos 3 caracteres/i)).toBeInTheDocument();
    });
  });

  it('validates email format', async () => {
    const store = createMockStore();
    
    render(
      <Provider store={store}>
        <RegisterForm />
      </Provider>
    );
    
    const emailInput = screen.getByLabelText(/^correo electrónico/i);
    const submitButton = screen.getByRole('button', { name: /registrarse/i });
    
    // Enter invalid email
    fireEvent.change(emailInput, { target: { value: 'invalid-email' } });
    fireEvent.click(submitButton);
    
    await waitFor(() => {
      expect(screen.getByText(/debe proporcionar un correo electrónico válido/i)).toBeInTheDocument();
    });
  });

  it('validates password strength', async () => {
    const store = createMockStore();
    
    render(
      <Provider store={store}>
        <RegisterForm />
      </Provider>
    );
    
    const passwordInput = screen.getByLabelText(/^contraseña$/i);
    const submitButton = screen.getByRole('button', { name: /registrarse/i });
    
    // Enter weak password
    fireEvent.change(passwordInput, { target: { value: 'weak' } });
    fireEvent.click(submitButton);
    
    await waitFor(() => {
      expect(screen.getByText(/debe tener al menos 8 caracteres/i)).toBeInTheDocument();
    });
  });

  it('validates password confirmation match', async () => {
    const store = createMockStore();
    
    render(
      <Provider store={store}>
        <RegisterForm />
      </Provider>
    );
    
    const passwordInput = screen.getByLabelText(/^contraseña$/i);
    const confirmInput = screen.getByLabelText(/confirmar contraseña/i);
    const submitButton = screen.getByRole('button', { name: /registrarse/i });
    
    // Enter mismatched passwords
    fireEvent.change(passwordInput, { target: { value: 'ValidPass123!' } });
    fireEvent.change(confirmInput, { target: { value: 'DifferentPass123!' } });
    fireEvent.click(submitButton);
    
    await waitFor(() => {
      expect(screen.getByText(/las contraseñas no coinciden/i)).toBeInTheDocument();
    });
  });

  it('validates all required fields', async () => {
    const store = createMockStore();
    
    render(
      <Provider store={store}>
        <RegisterForm />
      </Provider>
    );
    
    const submitButton = screen.getByRole('button', { name: /registrarse/i });
    
    // Submit without filling fields
    fireEvent.click(submitButton);
    
    await waitFor(() => {
      expect(screen.getByText(/el nombre de usuario es obligatorio/i)).toBeInTheDocument();
      expect(screen.getByText(/el correo electrónico es obligatorio/i)).toBeInTheDocument();
      expect(screen.getByText(/la contraseña es obligatoria/i)).toBeInTheDocument();
    });
  });

  it('clears validation errors when user types', async () => {
    const store = createMockStore();
    
    render(
      <Provider store={store}>
        <RegisterForm />
      </Provider>
    );
    
    const usernameInput = screen.getByLabelText(/nombre de usuario/i);
    const submitButton = screen.getByRole('button', { name: /registrarse/i });
    
    // Trigger validation error
    fireEvent.click(submitButton);
    
    await waitFor(() => {
      expect(screen.getByText(/el nombre de usuario es obligatorio/i)).toBeInTheDocument();
    });
    
    // Type in username field
    fireEvent.change(usernameInput, { target: { value: 'testuser' } });
    
    await waitFor(() => {
      expect(screen.queryByText(/el nombre de usuario es obligatorio/i)).not.toBeInTheDocument();
    });
  });

  it('displays server error message', () => {
    const store = createMockStore({
      error: 'Este correo electrónico ya está registrado',
    });
    
    render(
      <Provider store={store}>
        <RegisterForm />
      </Provider>
    );
    
    expect(screen.getByText(/este correo electrónico ya está registrado/i)).toBeInTheDocument();
  });

  it('disables form during loading', () => {
    const store = createMockStore({
      loading: true,
    });
    
    render(
      <Provider store={store}>
        <RegisterForm />
      </Provider>
    );
    
    const usernameInput = screen.getByLabelText(/nombre de usuario/i);
    const emailInput = screen.getByLabelText(/^correo electrónico/i);
    const passwordInput = screen.getByLabelText(/^contraseña$/i);
    const confirmInput = screen.getByLabelText(/confirmar contraseña/i);
    const submitButton = screen.getByRole('button', { name: /registrando/i });
    
    expect(usernameInput).toBeDisabled();
    expect(emailInput).toBeDisabled();
    expect(passwordInput).toBeDisabled();
    expect(confirmInput).toBeDisabled();
    expect(submitButton).toBeDisabled();
  });

  it('submits form with valid data', async () => {
    const store = createMockStore();
    
    render(
      <Provider store={store}>
        <RegisterForm />
      </Provider>
    );
    
    const usernameInput = screen.getByLabelText(/nombre de usuario/i);
    const emailInput = screen.getByLabelText(/^correo electrónico/i);
    const passwordInput = screen.getByLabelText(/^contraseña$/i);
    const confirmInput = screen.getByLabelText(/confirmar contraseña/i);
    const submitButton = screen.getByRole('button', { name: /registrarse/i });
    
    // Fill form with valid data
    fireEvent.change(usernameInput, { target: { value: 'testuser' } });
    fireEvent.change(emailInput, { target: { value: 'test@example.com' } });
    fireEvent.change(passwordInput, { target: { value: 'ValidPass123!' } });
    fireEvent.change(confirmInput, { target: { value: 'ValidPass123!' } });
    
    // Submit form
    fireEvent.click(submitButton);
    
    // Form should not show validation errors
    await waitFor(() => {
      expect(screen.queryByText(/es obligatorio/i)).not.toBeInTheDocument();
      expect(screen.queryByText(/no coinciden/i)).not.toBeInTheDocument();
    });
  });
});
