import React, { useState } from 'react';
import { Link } from 'react-router-dom';
import { Check, CheckCircle2, Lock } from 'lucide-react';
import { useDemo } from '../context/DemoContext';
import { DEFAULT_AVATAR } from '../data/seedData';

export const VotePage: React.FC<{ mode: 'siswa' | 'guru' }> = ({ mode }) => {
  const { currentUser, state, electionStatus, castVote } = useDemo();
  const [justVoted, setJustVoted] = useState(false);
  const [error, setError] = useState('');

  if (!currentUser) return null;

  const alreadyVoted =
    currentUser.sudah_vote || state.votes.some((v) => v.user_id === currentUser.id);

  const handleVote = (candidateId: number, name: string) => {
    if (!window.confirm(`Apakah Anda yakin ingin memilih ${name}? Pilihan simulasi Anda tidak bisa diubah setelah ini.`)) {
      return;
    }
    const res = castVote(candidateId);
    if (!res.ok) {
      setError(res.error ?? 'Gagal menyimpan suara.');
      return;
    }
    setJustVoted(true);
  };

  if (justVoted) {
    return (
      <div className="container" style={{ maxWidth: 680 }}>
        <div className="alert alert-success" style={{ flexDirection: 'column', alignItems: 'center', textAlign: 'center', padding: 32 }}>
          <CheckCircle2 size={42} />
          <h1 style={{ fontSize: '1.6rem', fontWeight: 700 }}>Voting Simulasi Berhasil!</h1>
          <p>Terima kasih telah mencoba proses pemungutan suara pada mode demo ini.</p>
          <Link to="/user/dashboard" className="btn btn-success" style={{ marginTop: 10 }}>
            Kembali ke Dashboard
          </Link>
        </div>
      </div>
    );
  }

  if (alreadyVoted) {
    return (
      <div className="container" style={{ maxWidth: 680 }}>
        <div className="alert alert-info" style={{ flexDirection: 'column', alignItems: 'center', textAlign: 'center', padding: 32 }}>
          <h1 style={{ fontSize: '1.5rem', fontWeight: 700 }}>Anda Sudah Memberikan Suara</h1>
          <p>Setiap akun demo hanya dapat memberikan satu suara. Gunakan tombol Reset di banner atas untuk mencoba lagi.</p>
          <Link to="/user/dashboard" className="btn btn-primary" style={{ marginTop: 10 }}>
            Kembali ke Dashboard
          </Link>
        </div>
      </div>
    );
  }

  if (!electionStatus.is_open) {
    return (
      <div className="container" style={{ maxWidth: 680 }}>
        <div className="alert alert-warning" style={{ flexDirection: 'column', alignItems: 'center', textAlign: 'center', padding: 32 }}>
          <Lock size={36} />
          <h1 style={{ fontSize: '1.5rem', fontWeight: 700 }}>Pemungutan Suara Ditutup</h1>
          <p>{electionStatus.banner_text}</p>
          <Link to="/user/dashboard" className="btn btn-primary" style={{ marginTop: 10 }}>
            Kembali ke Dashboard
          </Link>
        </div>
      </div>
    );
  }

  return (
    <div className="container">
      <div style={{ textAlign: 'center', marginBottom: 30 }}>
        <h1 style={{ fontSize: '1.8rem', fontWeight: 800, marginBottom: 6 }}>
          Pemilihan Ketua OSIS {mode === 'guru' ? '— Guru' : ''}
        </h1>
        <p style={{ color: 'var(--text-muted)' }}>
          {mode === 'guru'
            ? 'Sebagai Guru, Anda berhak memberikan suara untuk memilih ketua OSIS (Simulasi).'
            : 'Silakan pilih salah satu calon ketua OSIS berikut (Simulasi):'}
        </p>
      </div>

      {error && <div className="alert alert-danger">{error}</div>}

      {state.candidates.length === 0 ? (
        <div className="alert alert-info">Belum ada kandidat terdaftar pada simulasi ini.</div>
      ) : (
      <div className="grid-3">
        {state.candidates.map((c) => (
          <div key={c.id} className="card" style={{ padding: 0, overflow: 'hidden', display: 'flex', flexDirection: 'column' }}>
            <div style={{ height: 220, overflow: 'hidden', background: '#e5e7eb' }}>
              <img
                src={c.foto || DEFAULT_AVATAR}
                alt={c.nama}
                style={{ width: '100%', height: '100%', objectFit: 'cover' }}
                onError={(e) => {
                  e.currentTarget.src = DEFAULT_AVATAR;
                }}
              />
            </div>
            <div style={{ padding: 20, display: 'flex', flexDirection: 'column', flex: 1 }}>
              <h3 style={{ fontSize: '1.15rem', fontWeight: 700, marginBottom: 4 }}>{c.nama}</h3>
              <p style={{ color: 'var(--text-muted)', fontSize: '0.88rem', marginBottom: 14 }}>Kelas: {c.kelas}</p>

              <div style={{ fontSize: '0.9rem', marginBottom: 18, flex: 1 }}>
                <p style={{ fontWeight: 700, color: 'var(--primary)', marginBottom: 2 }}>Visi:</p>
                <p style={{ marginBottom: 10, whiteSpace: 'pre-line' }}>{c.visi}</p>
                <p style={{ fontWeight: 700, color: 'var(--primary)', marginBottom: 2 }}>Misi:</p>
                <p style={{ marginBottom: 10, whiteSpace: 'pre-line' }}>{c.misi}</p>
                <p style={{ fontWeight: 700, color: 'var(--primary)', marginBottom: 2 }}>Program Unggulan:</p>
                <p style={{ whiteSpace: 'pre-line' }}>{c.Program}</p>
              </div>

              <button
                type="button"
                className="btn btn-success"
                style={{ width: '100%' }}
                onClick={() => handleVote(c.id, c.nama)}
              >
                <Check size={17} /> Pilih Saya
              </button>
            </div>
          </div>
        ))}
      </div>
      )}
    </div>
  );
};

