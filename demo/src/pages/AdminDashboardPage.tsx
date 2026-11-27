import React, { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import { CalendarCheck, CheckSquare, IdCard, Lock, PieChart, Shield, UserCheck } from 'lucide-react';
import { useDemo } from '../context/DemoContext';

export const AdminDashboardPage: React.FC = () => {
  const { state, updateSettings, updateAdminPassword } = useDemo();

  const [title, setTitle] = useState(state.settings.election_title);
  const [start, setStart] = useState(state.settings.voting_start);
  const [end, setEnd] = useState(state.settings.voting_end);
  const [isOpen, setIsOpen] = useState(state.settings.is_voting_open ? '1' : '0');

  useEffect(() => {
    setTitle(state.settings.election_title);
    setStart(state.settings.voting_start);
    setEnd(state.settings.voting_end);
    setIsOpen(state.settings.is_voting_open ? '1' : '0');
  }, [state.settings]);
  const [settingsMsg, setSettingsMsg] = useState<{ type: 'success' | 'danger'; text: string } | null>(null);

  const [currPass, setCurrPass] = useState('');
  const [newPass, setNewPass] = useState('');
  const [confPass, setConfPass] = useState('');
  const [passMsg, setPassMsg] = useState<{ type: 'success' | 'danger'; text: string } | null>(null);

  const totalKandidat = state.candidates.length;
  const totalPemilih = state.users.filter((u) => u.role === 'siswa' || u.role === 'guru').length;
  const suaraMasuk = state.votes.length;

  const handleSettingsSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    const res = updateSettings({
      election_title: title,
      voting_start: start,
      voting_end: end,
      is_voting_open: isOpen === '1',
    });
    if (!res.ok) {
      setSettingsMsg({ type: 'danger', text: res.error ?? 'Gagal menyimpan pengaturan.' });
    } else {
      setSettingsMsg({ type: 'success', text: 'Pengaturan pemilihan simulasi berhasil disimpan!' });
    }
  };

  const handlePasswordSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    const res = updateAdminPassword(currPass, newPass, confPass);
    if (!res.ok) {
      setPassMsg({ type: 'danger', text: res.error ?? 'Gagal memperbarui password.' });
    } else {
      setPassMsg({ type: 'success', text: 'Password admin demo berhasil diperbarui!' });
      setCurrPass('');
      setNewPass('');
      setConfPass('');
    }
  };

  return (
    <div>
      <div
        className="admin-banner"
        style={{
          background: 'linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%)',
          color: '#fff',
          padding: '36px 20px 64px',
          borderRadius: '0 0 28px 28px',
          marginTop: -28,
          marginBottom: -40,
          textAlign: 'center',
        }}
      >
        <div className="container">
          <h1 style={{ fontSize: '1.8rem', fontWeight: 800, display: 'inline-flex', alignItems: 'center', gap: 10 }}>
            <Shield size={26} /> Panel Administrator
          </h1>
          <p style={{ opacity: 0.9, marginTop: 4 }}>
            Kelola sistem pemilihan, kandidat, dan pantau perolehan suara simulasi.
          </p>
        </div>
      </div>

      <div className="container" style={{ position: 'relative', zIndex: 10 }}>
        <div className="grid-3" style={{ marginBottom: 28 }}>
          <Link to="/admin/calon" className="card" style={{ textAlign: 'center', textDecoration: 'none', color: 'inherit' }}>
            <div style={{ width: 60, height: 60, borderRadius: 14, background: '#e0e7ff', color: '#4f46e5', display: 'inline-flex', alignItems: 'center', justifyContent: 'center', marginBottom: 12 }}>
              <UserCheck size={26} />
            </div>
            <h3 style={{ fontSize: '1.1rem', fontWeight: 700, marginBottom: 4 }}>Kandidat OSIS</h3>
            <p style={{ color: 'var(--text-muted)', fontSize: '0.88rem', marginBottom: 10 }}>
              Kelola data calon ketua dan wakil ketua OSIS.
            </p>
            <span className="badge badge-muted">{totalKandidat} Terdaftar</span>
          </Link>

          <Link to="/admin/akun" className="card" style={{ textAlign: 'center', textDecoration: 'none', color: 'inherit' }}>
            <div style={{ width: 60, height: 60, borderRadius: 14, background: '#dcfce7', color: '#16a34a', display: 'inline-flex', alignItems: 'center', justifyContent: 'center', marginBottom: 12 }}>
              <IdCard size={26} />
            </div>
            <h3 style={{ fontSize: '1.1rem', fontWeight: 700, marginBottom: 4 }}>Akun Pemilih</h3>
            <p style={{ color: 'var(--text-muted)', fontSize: '0.88rem', marginBottom: 10 }}>
              Buat dan kelola akun akses untuk siswa/guru.
            </p>
            <span className="badge badge-muted">{totalPemilih} Akun</span>
          </Link>

          <Link to="/admin/hasil" className="card" style={{ textAlign: 'center', textDecoration: 'none', color: 'inherit' }}>
            <div style={{ width: 60, height: 60, borderRadius: 14, background: '#fee2e2', color: '#dc2626', display: 'inline-flex', alignItems: 'center', justifyContent: 'center', marginBottom: 12 }}>
              <PieChart size={26} />
            </div>
            <h3 style={{ fontSize: '1.1rem', fontWeight: 700, marginBottom: 4 }}>Hasil Live</h3>
            <p style={{ color: 'var(--text-muted)', fontSize: '0.88rem', marginBottom: 10 }}>
              Pantau perolehan suara simulasi secara real-time.
            </p>
            <span className="badge badge-muted">
              <CheckSquare size={13} style={{ marginRight: 4 }} /> {suaraMasuk} Suara Masuk
            </span>
          </Link>
        </div>

        <div className="grid-2">
          <div className="card">
            <h3 style={{ fontWeight: 700, display: 'flex', alignItems: 'center', gap: 8, marginBottom: 4 }}>
              <CalendarCheck size={20} color="#6b7280" /> Pengaturan Pemilihan
            </h3>
            <p style={{ color: 'var(--text-muted)', fontSize: '0.88rem', marginBottom: 18 }}>
              Atur judul dan status buka/tutup pemungutan suara simulasi.
            </p>

            {settingsMsg && (
              <div className={`alert alert-${settingsMsg.type}`}>{settingsMsg.text}</div>
            )}

            <form onSubmit={handleSettingsSubmit}>
              <div className="form-group">
                <label className="form-label" htmlFor="election_title">Judul Pemilihan</label>
                <input
                  id="election_title"
                  type="text"
                  className="form-control"
                  value={title}
                  onChange={(e) => setTitle(e.target.value)}
                  required
                />
              </div>
              <div className="grid-2" style={{ gap: 12 }}>
                <div className="form-group">
                  <label className="form-label" htmlFor="voting_start">Waktu Mulai (Opsional)</label>
                  <input
                    id="voting_start"
                    type="datetime-local"
                    className="form-control"
                    value={start}
                    onChange={(e) => setStart(e.target.value)}
                  />
                </div>
                <div className="form-group">
                  <label className="form-label" htmlFor="voting_end">Waktu Selesai (Opsional)</label>
                  <input
                    id="voting_end"
                    type="datetime-local"
                    className="form-control"
                    value={end}
                    onChange={(e) => setEnd(e.target.value)}
                  />
                </div>
              </div>
              <div className="form-group">
                <label className="form-label" htmlFor="is_voting_open">Status Voting</label>
                <select
                  id="is_voting_open"
                  className="form-control"
                  value={isOpen}
                  onChange={(e) => setIsOpen(e.target.value)}
                >
                  <option value="1">Dibuka (Aktif)</option>
                  <option value="0">Ditutup</option>
                </select>
              </div>
              <button type="submit" className="btn btn-primary" style={{ width: '100%' }}>
                Simpan Pengaturan Pemilihan
              </button>
            </form>
          </div>

          <div className="card">
            <h3 style={{ fontWeight: 700, display: 'flex', alignItems: 'center', gap: 8, marginBottom: 4 }}>
              <Lock size={20} color="#6b7280" /> Keamanan Akun
            </h3>
            <p style={{ color: 'var(--text-muted)', fontSize: '0.88rem', marginBottom: 18 }}>
              Simulasi pembaruan password administrator di browser.
            </p>

            {passMsg && (
              <div className={`alert alert-${passMsg.type}`}>{passMsg.text}</div>
            )}

            <form onSubmit={handlePasswordSubmit}>
              <div className="form-group">
                <label className="form-label">Password Saat Ini</label>
                <input
                  type="password"
                  className="form-control"
                  placeholder="Masukkan password lama (default: demo123)"
                  value={currPass}
                  onChange={(e) => setCurrPass(e.target.value)}
                  required
                />
              </div>
              <div className="form-group">
                <label className="form-label">Password Baru</label>
                <input
                  type="password"
                  className="form-control"
                  placeholder="Minimal 6 karakter"
                  value={newPass}
                  onChange={(e) => setNewPass(e.target.value)}
                  required
                />
              </div>
              <div className="form-group">
                <label className="form-label">Konfirmasi Password Baru</label>
                <input
                  type="password"
                  className="form-control"
                  placeholder="Ketik ulang password baru"
                  value={confPass}
                  onChange={(e) => setConfPass(e.target.value)}
                  required
                />
              </div>
              <button type="submit" className="btn btn-primary" style={{ width: '100%' }}>
                Simpan Perubahan Password
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>
  );
};

