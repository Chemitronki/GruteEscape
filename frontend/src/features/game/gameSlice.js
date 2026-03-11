import { createSlice, createAsyncThunk } from '@reduxjs/toolkit';
import axios from 'axios';

const API_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000/api';

// Get session from localStorage
const getStoredSession = () => {
  const session = localStorage.getItem('game_session');
  return session ? JSON.parse(session) : null;
};

// Initial state
const initialState = {
  session: getStoredSession(),
  timeRemaining: getStoredSession()?.time_remaining || 1500,
  isActive: false,
  loading: false,
  error: null,
  syncInterval: null,
  currentPuzzle: null,
  puzzleLoading: false,
  puzzleError: null,
  completedPuzzles: 0,
  totalPuzzles: 10,
};

// Async thunks
export const startGame = createAsyncThunk(
  'game/start',
  async (_, { getState, rejectWithValue }) => {
    try {
      const { token } = getState().auth;
      
      const response = await axios.post(
        `${API_URL}/game/start`,
        {},
        {
          headers: {
            Authorization: `Bearer ${token}`,
          },
        }
      );
      
      if (response.data.success) {
        const session = response.data.data;
        localStorage.setItem('game_session', JSON.stringify(session));
        return session;
      } else {
        return rejectWithValue(response.data.message || 'Error al iniciar el juego');
      }
    } catch (error) {
      return rejectWithValue(
        error.response?.data?.message || 'Error de conexión al servidor'
      );
    }
  }
);

export const getSession = createAsyncThunk(
  'game/getSession',
  async (_, { getState, rejectWithValue }) => {
    try {
      const { token } = getState().auth;
      
      const response = await axios.get(`${API_URL}/game/session`, {
        headers: {
          Authorization: `Bearer ${token}`,
        },
      });
      
      if (response.data.success) {
        const session = response.data.data;
        localStorage.setItem('game_session', JSON.stringify(session));
        return session;
      } else {
        return rejectWithValue(response.data.message || 'No hay sesión activa');
      }
    } catch (error) {
      if (error.response?.status === 404) {
        localStorage.removeItem('game_session');
      }
      return rejectWithValue(
        error.response?.data?.message || 'Error al obtener la sesión'
      );
    }
  }
);

export const syncTimer = createAsyncThunk(
  'game/sync',
  async (timeRemaining, { getState, rejectWithValue }) => {
    try {
      const { token } = getState().auth;
      
      const response = await axios.post(
        `${API_URL}/game/sync`,
        { time_remaining: timeRemaining },
        {
          headers: {
            Authorization: `Bearer ${token}`,
          },
        }
      );
      
      if (response.data.success) {
        const session = response.data.data;
        localStorage.setItem('game_session', JSON.stringify(session));
        return session;
      } else {
        return rejectWithValue(response.data.message || 'Error al sincronizar');
      }
    } catch (error) {
      return rejectWithValue(
        error.response?.data?.message || 'Error de conexión al servidor'
      );
    }
  }
);

export const completeGame = createAsyncThunk(
  'game/complete',
  async (timeRemaining, { getState, rejectWithValue }) => {
    try {
      const { token } = getState().auth;
      
      const response = await axios.post(
        `${API_URL}/game/complete`,
        { time_remaining: timeRemaining },
        {
          headers: {
            Authorization: `Bearer ${token}`,
          },
        }
      );
      
      if (response.data.success) {
        const session = response.data.data;
        localStorage.setItem('game_session', JSON.stringify(session));
        return session;
      } else {
        return rejectWithValue(response.data.message || 'Error al completar el juego');
      }
    } catch (error) {
      return rejectWithValue(
        error.response?.data?.message || 'Error de conexión al servidor'
      );
    }
  }
);

export const abandonGame = createAsyncThunk(
  'game/abandon',
  async (_, { getState, rejectWithValue }) => {
    try {
      const { token } = getState().auth;
      
      const response = await axios.post(
        `${API_URL}/game/abandon`,
        {},
        {
          headers: {
            Authorization: `Bearer ${token}`,
          },
        }
      );
      
      if (response.data.success) {
        localStorage.removeItem('game_session');
        return response.data.data;
      } else {
        return rejectWithValue(response.data.message || 'Error al abandonar el juego');
      }
    } catch (error) {
      return rejectWithValue(
        error.response?.data?.message || 'Error de conexión al servidor'
      );
    }
  }
);

export const getCurrentPuzzle = createAsyncThunk(
  'game/getCurrentPuzzle',
  async (sessionId, { getState, rejectWithValue }) => {
    try {
      const { token } = getState().auth;
      
      const response = await axios.get(
        `${API_URL}/puzzles/${sessionId}`,
        {
          headers: {
            Authorization: `Bearer ${token}`,
          },
        }
      );
      
      if (response.data.success) {
        return response.data.data;
      } else {
        return rejectWithValue(response.data.message || 'Error al obtener el puzzle');
      }
    } catch (error) {
      return rejectWithValue(
        error.response?.data?.message || 'Error de conexión al servidor'
      );
    }
  }
);

// Slice
const gameSlice = createSlice({
  name: 'game',
  initialState,
  reducers: {
    decrementTimer: (state) => {
      if (state.timeRemaining > 0 && state.isActive) {
        state.timeRemaining -= 1;
        
        // Update session in state
        if (state.session) {
          state.session.time_remaining = state.timeRemaining;
          localStorage.setItem('game_session', JSON.stringify(state.session));
        }
        
        // Check for timeout
        if (state.timeRemaining <= 0) {
          state.isActive = false;
          state.session.status = 'timeout';
        }
      }
    },
    clearError: (state) => {
      state.error = null;
    },
    clearSession: (state) => {
      state.session = null;
      state.timeRemaining = 1500;
      state.isActive = false;
      state.error = null;
      state.currentPuzzle = null;
      state.puzzleLoading = false;
      state.puzzleError = null;
      state.completedPuzzles = 0;
      localStorage.removeItem('game_session');
    },
    recoverSession: (state) => {
      const storedSession = getStoredSession();
      if (storedSession && storedSession.status === 'active') {
        state.session = storedSession;
        state.timeRemaining = storedSession.time_remaining;
        state.isActive = true;
      }
    },
  },
  extraReducers: (builder) => {
    // Start game
    builder
      .addCase(startGame.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(startGame.fulfilled, (state, action) => {
        state.loading = false;
        state.session = action.payload;
        state.timeRemaining = action.payload.time_remaining;
        state.isActive = action.payload.status === 'active';
        state.error = null;
      })
      .addCase(startGame.rejected, (state, action) => {
        state.loading = false;
        state.error = action.payload;
      });
    
    // Get session
    builder
      .addCase(getSession.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(getSession.fulfilled, (state, action) => {
        state.loading = false;
        state.session = action.payload;
        state.timeRemaining = action.payload.time_remaining;
        state.isActive = action.payload.status === 'active';
        state.error = null;
      })
      .addCase(getSession.rejected, (state, action) => {
        state.loading = false;
        state.error = action.payload;
        state.session = null;
        state.isActive = false;
      });
    
    // Sync timer
    builder
      .addCase(syncTimer.pending, (state) => {
        // Don't set loading for sync to avoid UI flicker
      })
      .addCase(syncTimer.fulfilled, (state, action) => {
        state.session = action.payload;
        state.timeRemaining = action.payload.time_remaining;
        state.isActive = action.payload.status === 'active';
      })
      .addCase(syncTimer.rejected, (state, action) => {
        state.error = action.payload;
      });
    
    // Complete game
    builder
      .addCase(completeGame.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(completeGame.fulfilled, (state, action) => {
        state.loading = false;
        state.session = action.payload;
        state.isActive = false;
        state.error = null;
      })
      .addCase(completeGame.rejected, (state, action) => {
        state.loading = false;
        state.error = action.payload;
      });
    
    // Abandon game
    builder
      .addCase(abandonGame.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(abandonGame.fulfilled, (state) => {
        state.loading = false;
        state.session = null;
        state.timeRemaining = 1500;
        state.isActive = false;
        state.error = null;
      })
      .addCase(abandonGame.rejected, (state, action) => {
        state.loading = false;
        state.error = action.payload;
      });
    
    // Get current puzzle
    builder
      .addCase(getCurrentPuzzle.pending, (state) => {
        state.puzzleLoading = true;
        state.puzzleError = null;
      })
      .addCase(getCurrentPuzzle.fulfilled, (state, action) => {
        state.puzzleLoading = false;
        
        if (action.payload.all_completed) {
          state.currentPuzzle = null;
          state.completedPuzzles = action.payload.total_puzzles;
        } else {
          state.currentPuzzle = action.payload.puzzle;
          state.completedPuzzles = action.payload.completed_puzzles || 0;
          state.totalPuzzles = action.payload.total_puzzles || 10;
        }
      })
      .addCase(getCurrentPuzzle.rejected, (state, action) => {
        state.puzzleLoading = false;
        state.puzzleError = action.payload;
      });
  },
});

export const { decrementTimer, clearError, clearSession, recoverSession } = gameSlice.actions;
export default gameSlice.reducer;
