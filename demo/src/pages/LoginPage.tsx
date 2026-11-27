import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { AlertCircle, Fingerprint, Lock, ShieldCheck, User, UserCheck, Users } from 'lucide-react';
import { useDemo } from '../context/DemoContext';
import { Role } from '../types';

export const LoginPage: React.FC = () => {
  const { login, quickLogin } = useDemo();
  const navigate = useNavigate();
  const [username, setUsername] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState('');

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    const res = login(username, password);
    if (!res.ok) {
      setError(res.error ?? 'Gagal masuk');
      return;
    }
    navigate(res.role === 'admin' ? '/admin/dashboard' : '/user/dashboard');
  };

  const handleQuick = (role: Role) => {
    const r = quickLogin(role);
    navigate(r === 'admin' ? '/admin/dashboard' : '/user/dashboard');
  };

  return (
    <div className="container" style={{ maxWidth: 440, paddingTop: 16 }}>
      <div className="card" style={{ padding: '36px 28px' }}>
        <div style={{ textAlign: 'center', marginBottom: 24 }}>
          <div
            style={{
              width: 60,
              height: 60,
              background: 'linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%)',
              color: '#fff',
              borderRadius: 16,
              display: 'inline-flex',
              alignItems: 'center',
              justifyContent: 'center',
              marginBottom: 14,
              boxShadow: '0 8px 15px rgba(79, 70, 229, 0.3)',
            }}
          >
            <Fingerprint size={28} />
          </div>
          <h1 style={{ fontSize: '1.5rem', fontWeight: 700, marginBottom: 6 }}>Selamat Datang</h1>
          <p style={{ color: 'var(--text-muted)', fontSize: '0.94rem' }}>
            Silakan masuk untuk mencoba simulasi pemilihan
          </p>
        </div>

        {error && (
          <div className="alert alert-danger" style={{ alignItems: 'center' }}>
            <AlertCircle size={18} />
            <span>{error}</span>
          </div>
        )}

        <form onSubmit={handleSubmit}>
          <div className="form-group">
            <label className="form-label" htmlFor="username">Username</label>
            <div style={{ position: 'relative' }}>
              <User size={16} style={{ position: 'absolute', left: 13, top: '50%', transform: 'translateY(-50%)', color: 'var(--text-muted)' }} />
              <input
                id="username"
                type="text"
                className="form-control"
                style={{ paddingLeft: 38 }}
                placeholder="Masukkan username demo"
                value={username}
                onChange={(e) => setUsername(e.target.value)}
                required
              />
            </div>
          </div>

          <div className="form-group">
            <label className="form-label" htmlFor="password">Password</label>
            <div style={{ position: 'relative' }}>
              <Lock size={16} style={{ position: 'absolute', left: 13, top: '50%', transform: 'translateY(-50%)', color: 'var(--text-muted)' }} />
              <input
                id="password"
                type="password"
                className="form-control"
                style={{ paddingLeft: 38 }}
                placeholder="Masukkan password demo"
                value={password}
                onChange={(e) => setPassword(e.target.value)}
                required
              />
            </div>
          </div>

          <button type="submit" className="btn btn-primary" style={{ width: '100%', padding: 13 }}>
            Masuk ke Sistem
          </button>
        </form>

        <div style={{ marginTop: 24, paddingTop: 20, borderTop: '1px solid var(--border)' }}>
          <p style={{ fontSize: '0.84rem', fontWeight: 600, color: 'var(--text-muted)', marginBottom: 10, textAlign: 'center' }}>
            Akses Cepat Demo (1-Klik)
          </p>
          <div style={{ display: 'grid', gridTemplateColumns: 'repeat(3, 1fr)', gap: 8 }}>
            <button type="button" className="btn btn-secondary btn-sm" onClick={() => handleQuick('siswa')}>
              <Users size={14} /> Siswa
            </button>
            <button type="button" className="btn btn-secondary btn-sm" onClick={() => handleQuick('guru')}>
              <UserCheck size={14} /> Guru
            </button>
            <button type="button" className="btn btn-secondary btn-sm" onClick={() => handleQuick('admin')}>
              <ShieldCheck size={14} /> Admin
            </button>
          </div>
          <p style={{ fontSize: '0.78rem', color: 'var(--text-muted)', marginTop: 10, textAlign: 'center' }}>
            Akun fiktif: <code>siswa_demo</code> / <code>guru_demo</code> / <code>admin_demo</code> (pass: <code>demo123</code>)
          </p>
        </div>
      </div>
    </div>
  );
};

