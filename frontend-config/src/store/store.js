import { configureStore } from '@reduxjs/toolkit';

// Import slices (to be created in Task 5)
// import authReducer from '../features/auth/authSlice';
// import gameReducer from '../features/game/gameSlice';
// import puzzleReducer from '../features/puzzle/puzzleSlice';
// import rankingReducer from '../features/ranking/rankingSlice';

export const store = configureStore({
  reducer: {
    // auth: authReducer,
    // game: gameReducer,
    // puzzle: puzzleReducer,
    // ranking: rankingReducer,
  },
  middleware: (getDefaultMiddleware) =>
    getDefaultMiddleware({
      serializableCheck: {
        // Ignore these action types
        ignoredActions: ['game/setTimer'],
        // Ignore these field paths in all actions
        ignoredActionPaths: ['meta.arg', 'payload.timestamp'],
        // Ignore these paths in the state
        ignoredPaths: ['game.timer'],
      },
    }),
});

export default store;
