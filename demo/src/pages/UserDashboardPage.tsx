import React from 'react';
import { Link } from 'react-router-dom';
import { CalendarDays, CheckCircle2, CheckSquare, Gauge, LogOut, Megaphone, TrendingUp, Users } from 'lucide-react';
import { useDemo } from '../context/DemoContext';

export const UserDashboardPage: React.FC = () => {
  const { currentUser, state, electionStatus, logout } = useDemo();
  if (!currentUser) return null;

  const voters = state.users.filter((u) => u.role === 'siswa' || u.role === 'guru');
  const totalUser = voters.length;
  const totalVote = state.votes.length;
  const totalCandidate = state.candidates.length;
  const progress = totalUser > 0 ? (totalVote / totalUser) * 100 : 0;

  const sudahVote =
    currentUser.sudah_vote || state.votes.some((v) => v.user_id === currentUser.id);
  const bisaVote =
    (currentUser.role === 'siswa' || currentUser.role === 'guru') &&
    !sudahVote &&
    electionStatus.is_open;

  const greetingName =
    currentUser.role === 'guru' ? 'Bapak/Ibu' : currentUser.role === 'siswa' ? 'Akang/Teteh' : 'Admin';

  return (
    <div className="container" style={{ maxWidth: 960 }}>
      <div className="card" style={{ marginBottom: 20 }}>
        <h1 style={{ fontSize: '1.4rem', fontWeight: 700, display: 'flex', alignItems: 'center', gap: 10, flexWrap: 'wrap', marginBottom: 8 }}>
          Selamat datang, {greetingName}
          <span className={`badge badge-${currentUser.role}`}>{currentUser.role.toUpperCase()}</span>
        </h1>
        <p style={{ color: 'var(--text-muted)' }}>
          {currentUser.role === 'admin'
            ? 'Anda masuk sebagai administrator simulasi. Kelola jalannya pemilihan melalui panel admin.'
            : currentUser.role === 'guru'
            ? 'Anda masuk sebagai guru yang berhak memberikan suara dalam simulasi pemilihan ketua OSIS.'
            : 'Anda masuk sebagai siswa yang berhak memilih ketua OSIS. Gunakan hak suara simulasi Anda!'}
        </p>
      </div>

      <div className="alert alert-info">
        <Megaphone size={20} style={{ flexShrink: 0, marginTop: 2 }} />
        <div>
          <strong>Info Penting:</strong> {electionStatus.banner_text}
        </div>
      </div>

      {sudahVote && (
        <div className="alert alert-success">
          <CheckCircle2 size={22} style={{ flexShrink: 0, marginTop: 2 }} />
          <div>
            <strong>Terima Kasih!</strong> Anda sudah memberikan suara simulasi. Lihat hasilnya di menu Admin → Hasil Live.
          </div>
        </div>
      )}

      <div className="grid-3" style={{ marginBottom: 20 }}>
        <div className="card">
          <div style={{ display: 'flex', alignItems: 'center', gap: 8, color: 'var(--text-muted)', fontSize: '0.92rem', marginBottom: 6 }}>
            <Users size={18} color="#4f46e5" /> Total Kandidat
          </div>
          <p style={{ fontSize: '1.5rem', fontWeight: 700 }}>
            {totalCandidate} <span style={{ fontSize: '0.9rem', fontWeight: 400, color: 'var(--text-muted)' }}>paslon</span>
          </p>
        </div>

        <div className="card">
          <div style={{ display: 'flex', alignItems: 'center', gap: 8, color: 'var(--text-muted)', fontSize: '0.92rem', marginBottom: 6 }}>
            <CheckSquare size={18} color="#4f46e5" /> Sudah Memilih
          </div>
          <p style={{ fontSize: '1.5rem', fontWeight: 700 }}>
            {totalVote} <span style={{ fontSize: '0.9rem', fontWeight: 400, color: 'var(--text-muted)' }}>dari {totalUser}</span>
          </p>
        </div>

        <div className="card">
          <div style={{ display: 'flex', alignItems: 'center', gap: 8, color: 'var(--text-muted)', fontSize: '0.92rem', marginBottom: 6 }}>
            <CalendarDays size={18} color="#4f46e5" /> Jadwal
          </div>
          <p style={{ fontSize: '1.1rem', fontWeight: 700 }}>{electionStatus.schedule_text}</p>
        </div>
      </div>

      <div className="card" style={{ marginBottom: 24 }}>
        <p style={{ fontWeight: 600, marginBottom: 10, display: 'flex', alignItems: 'center', gap: 8 }}>
          <TrendingUp size={18} color="#4f46e5" /> Partisipasi Pemilih: {progress.toFixed(1)}%
        </p>
        <div style={{ width: '100%', height: 12, background: '#e5e7eb', borderRadius: 999, overflow: 'hidden' }}>
          <div
            style={{
              width: `${progress}%`,
              height: '100%',
              background: 'linear-gradient(90deg, #4f46e5, #ec4899)',
              borderRadius: 999,
              transition: 'width 0.6s ease',
            }}
          />
        </div>
      </div>

      <div style={{ display: 'flex', gap: 12, flexWrap: 'wrap' }}>
        {bisaVote && (
          <Link
            to={currentUser.role === 'guru' ? '/user/vote-guru' : '/user/vote'}
            className="btn btn-primary"
          >
            <CheckSquare size={18} />
            {currentUser.role === 'guru' ? 'Berikan Suara Guru' : 'Pilih Calon Ketua OSIS'}
          </Link>
        )}

        {currentUser.role === 'admin' && (
          <Link to="/admin/dashboard" className="btn btn-primary">
            <Gauge size={18} /> Ke Admin Dashboard
          </Link>
        )}

        <button type="button" className="btn btn-secondary" onClick={logout}>
          <LogOut size={18} /> Keluar
        </button>
      </div>
    </div>
  );
};

