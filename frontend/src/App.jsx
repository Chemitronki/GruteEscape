import { BrowserRouter as Router, Routes, Route, Navigate } from 'react-router-dom';
import { Provider } from 'react-redux';
import store from './store/store';
import LoginPage from './pages/LoginPage';
import RegisterPage from './pages/RegisterPage';
import GamePage from './pages/GamePage';
import ParticleEffect from './components/atmosphere/ParticleEffect';
import './styles/theme.css';
import './styles/animations.css';
import './styles/responsive.css';
import './styles/touch.css';

function App() {
  return (
    <Provider store={store}>
      <ParticleEffect count={30} />
      <Router>
        <Routes>
          <Route path="/" element={<Navigate to="/login" replace />} />
          <Route path="/login" element={<LoginPage />} />
          <Route path="/register" element={<RegisterPage />} />
          <Route path="/game" element={<GamePage />} />
        </Routes>
      </Router>
    </Provider>
  );
}

export default App;
