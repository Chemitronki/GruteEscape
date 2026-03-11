import './RankingEntry.css';

const RankingEntry = ({ ranking }) => {
  const getMedalClass = (rank) => {
    if (rank === 1) return 'gold';
    if (rank === 2) return 'silver';
    if (rank === 3) return 'bronze';
    return '';
  };

  const medalClass = getMedalClass(ranking.rank);

  return (
    <div className={`ranking-entry ${medalClass}`}>
      <div className="ranking-position">
        {ranking.rank <= 3 ? (
          <span className="ranking-medal">
            {ranking.rank === 1 && '🥇'}
            {ranking.rank === 2 && '🥈'}
            {ranking.rank === 3 && '🥉'}
          </span>
        ) : (
          <span className="ranking-number">#{ranking.rank}</span>
        )}
      </div>
      
      <div className="ranking-username">{ranking.username}</div>
      
      <div className="ranking-time">{ranking.formatted_time}</div>
    </div>
  );
};

export default RankingEntry;
