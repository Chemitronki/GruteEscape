import { BrowserRouter as Router } from 'react-router-dom';
import { Provider } from 'react-redux';
import { store } from './store/store';
import './App.css';

function App() {
  return (
    <Provider store={store}>
      <Router>
        <div className="min-h-screen bg-abyss-900 text-eldritch-50">
          <div className="container mx-auto px-4 py-8">
            <h1 className="text-4xl font-lovecraft text-center mb-8 text-cosmic-400">
              Lovecraftian Escape Room
            </h1>
            <p className="text-center text-eldritch-300 font-body">
              La aplicación está configurada y lista para desarrollo.
            </p>
            <p className="text-center text-eldritch-400 font-body mt-4">
              Próximo paso: Implementar autenticación y componentes del juego.
            </p>
          </div>
        </div>
      </Router>
    </Provider>
  );
}

export default App;
