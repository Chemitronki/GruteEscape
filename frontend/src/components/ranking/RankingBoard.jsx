import { useEffect, useState } from 'react';
import { useSelector } from 'react-redux';
import { useNavigate } from 'react-router-dom';
import { rankingService } from '../../lib/supabaseRanking';
import './RankingBoard.css';

const RankingBoard = () => {
  const navigate = useNavigate();
  const { isAuthenticated, user } = useSelector((state) => state.auth);
  const [rankings, setRankings] = useState([]);
  const [userRank, setUserRank] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    if (!isAuthenticated) { navigate('/login'); return; }
    fetchRankings();
  }, [isAuthenticated, navigate]);

  const fetchRankings = async () => {
    try {
      setLoading(true);
      const top = await rankingService.getTop(100);
      setRankings(top);
      if (user?.id) {
        const rank = await rankingService.getUserRank(user.id);
        setUserRank(rank);
      }
      setError(null);
    } catch (err) {
      console.error('Error fetching rankings:', err);
      setError('Error al cargar el ranking');
    } finally {
      setLoading(false);
    }
  };

  if (!isAuthenticated) return null;

  const medal = (rank) => {
    if (rank === 1) return '🥇';
    if (rank === 2) return '🥈';
    if (rank === 3) return '🥉';
    return rank;
  };

  return (
    <div className="ranking-board">
      <div className="ranking-container">

        <div className="ranking-header">
          <h1 className="ranking-title">🏆 Ranking Global</h1>
          <p className="ranking-subtitle">Los mejores tiempos de escape</p>
        </div>

        {error && <div className="ranking-error">⚠️ {error}</div>}

        {userRank && (
          <div className="user-rank-card">
            <span className="user-rank-label">Tu posición</span>
            <span className="user-rank-position">#{userRank.rank}</span>
            <span className="user-rank-time">{userRank.formatted_time}</span>
          </div>
        )}

        {loading ? (
          <div className="ranking-loading">
            <div className="loading-spinner"></div>
            <p>Cargando ranking...</p>
          </div>
        ) : (
          <div className="rankings-table">
            <table>
              <thead>
                <tr>
                  <th className="th-pos">Pos.</th>
                  <th className="th-player">Jugador</th>
                  <th className="th-time">Tiempo</th>
                  <th className="th-date">Fecha</th>
                </tr>
              </thead>
              <tbody>
                {rankings.length > 0 ? rankings.map((r, i) => (
                  <tr
                    key={i}
                    className={`
                      ${r.rank <= 3 ? `top-${r.rank}` : ''}
                      ${userRank?.rank === r.rank ? 'current-user' : ''}
                    `}
                  >
                    <td className="td-pos">
                      <span className={`rank-badge rank-${r.rank}`}>{medal(r.rank)}</span>
                    </td>
                    <td className="td-player">
                      {r.username}
                      {userRank?.rank === r.rank && <span className="you-badge">Tú</span>}
                    </td>
                    <td className="td-time">{r.formatted_time}</td>
                    <td className="td-date">{new Date(r.completed_at).toLocaleDateString('es-ES')}</td>
                  </tr>
                )) : (
                  <tr>
                    <td colSpan="4" className="no-rankings">
                      Aún no hay registros. ¡Sé el primero en escapar!
                    </td>
                  </tr>
                )}
              </tbody>
            </table>
          </div>
        )}

        <div className="ranking-actions">
          <button onClick={() => navigate('/')} className="btn-back-home">
            ← Volver al Inicio
          </button>
        </div>

      </div>
    </div>
  );
};

export default RankingBoard;
