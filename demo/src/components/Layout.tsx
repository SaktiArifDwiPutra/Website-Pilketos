import React, { useState } from 'react';
import { Link, NavLink, useNavigate } from 'react-router-dom';
import {
  Archive,
  BarChart3,
  CheckSquare,
  FlaskConical,
  Gauge,
  Home,
  LogOut,
  MapPin,
  Menu,
  RotateCcw,
  UserPlus,
  Users,
  X,
} from 'lucide-react';
import { useDemo } from '../context/DemoContext';
import { Role } from '../types';

export const Layout: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const { currentUser, logout, quickLogin, resetDemo, state } = useDemo();
  const [menuOpen, setMenuOpen] = useState(false);
  const navigate = useNavigate();

  const handleLogout = () => {
    if (window.confirm('Apakah Anda yakin ingin keluar dari mode demo?')) {
      logout();
      setMenuOpen(false);
      navigate('/masuk');
    }
  };

  const handleSwitchRole = (role: Role) => {
    const r = quickLogin(role);
    setMenuOpen(false);
    navigate(r === 'admin' ? '/admin/dashboard' : '/user/dashboard');
  };

  const handleReset = () => {
    if (window.confirm('Reset semua data simulasi ke kondisi awal?')) {
      resetDemo();
      navigate('/masuk');
    }
  };

  return (
    <div className="app-shell">
      <div className="demo-banner no-print">
        <div className="demo-banner-inner">
          <div style={{ display: 'flex', alignItems: 'center', gap: 10, flexWrap: 'wrap' }}>
            <span className="demo-banner-badge">
              <FlaskConical size={14} /> MODE DEMO INTERAKTIF
            </span>
            <span>
              Data fiktif tersimpan lokal di browser Anda — bukan hasil pemilihan nyata.
            </span>
          </div>
          <div className="demo-banner-actions">
            <span style={{ opacity: 0.85, fontSize: '0.78rem' }}>Ganti Peran:</span>
            {(['siswa', 'guru', 'admin'] as Role[]).map((r) => (
              <button
                key={r}
                type="button"
                className="btn-ghost-light"
                onClick={() => handleSwitchRole(r)}
                style={{
                  background: currentUser?.role === r ? 'rgba(16, 185, 129, 0.35)' : undefined,
                  borderColor: currentUser?.role === r ? 'rgba(16, 185, 129, 0.7)' : undefined,
                }}
              >
                {r.toUpperCase()}
              </button>
            ))}
            <button type="button" className="btn-ghost-light" onClick={handleReset} title="Reset data simulasi">
              <RotateCcw size={13} /> Reset
            </button>
          </div>
        </div>
      </div>

      <header>
        <nav className="navbar-custom">
          <div className="navbar-inner">
            <Link className="navbar-brand" to={currentUser ? (currentUser.role === 'admin' ? '/admin/dashboard' : '/user/dashboard') : '/masuk'}>
              <Archive size={22} />
              <span>{state.settings.election_title}</span>
            </Link>

            <button
              type="button"
              className="nav-toggler"
              aria-label="Toggle navigation"
              onClick={() => setMenuOpen((o) => !o)}
            >
              {menuOpen ? <X size={24} /> : <Menu size={24} />}
            </button>

            <div className={`nav-links ${menuOpen ? 'open' : ''}`}>
              {currentUser ? (
                <>
                  {currentUser.role === 'admin' ? (
                    <>
                      <NavLink className="nav-link" to="/admin/dashboard" onClick={() => setMenuOpen(false)}>
                        <Gauge size={16} /> Dashboard
                      </NavLink>
                      <NavLink className="nav-link" to="/admin/calon" onClick={() => setMenuOpen(false)}>
                        <UserPlus size={16} /> Calon
                      </NavLink>
                      <NavLink className="nav-link" to="/admin/akun" onClick={() => setMenuOpen(false)}>
                        <Users size={16} /> Akun
                      </NavLink>
                      <NavLink className="nav-link" to="/admin/hasil" onClick={() => setMenuOpen(false)}>
                        <BarChart3 size={16} /> Hasil
                      </NavLink>
                    </>
                  ) : (
                    <>
                      <NavLink className="nav-link" to="/user/dashboard" onClick={() => setMenuOpen(false)}>
                        <Home size={16} /> Beranda
                      </NavLink>
                      <NavLink
                        className="nav-link"
                        to={currentUser.role === 'guru' ? '/user/vote-guru' : '/user/vote'}
                        onClick={() => setMenuOpen(false)}
                      >
                        <CheckSquare size={16} /> Vote
                      </NavLink>
                    </>
                  )}
                  <button type="button" className="btn btn-danger btn-sm" onClick={handleLogout} style={{ marginLeft: 6 }}>
                    <LogOut size={15} /> Keluar
                  </button>
                </>
              ) : (
                <NavLink className="nav-link" to="/masuk" onClick={() => setMenuOpen(false)}>
                  Masuk
                </NavLink>
              )}
            </div>
          </div>
        </nav>
      </header>

      <main className="main-content">{children}</main>

      <footer className="elegant-footer">
        <div className="footer-content">
          <div className="container footer-grid">
            <div>
              <h3 style={{ color: '#818cf8', fontSize: '1.35rem', marginBottom: 6 }}>MPK SMKN 4 Padalarang</h3>
              <p style={{ color: '#94a3b8', fontSize: '0.9rem', marginBottom: 12 }}>
                Menjaga Kualitas Demokrasi Sekolah (Versi Demo Interaktif)
              </p>
              <p style={{ color: '#cbd5e1', fontSize: '0.9rem' }}>
                Simulasi frontend dari Pilketos Web App. Seluruh kandidat, akun, dan perolehan suara pada situs ini bersifat fiktif.
              </p>
            </div>
            <div>
              <h4 style={{ color: '#e0e7ff', marginBottom: 14 }}>Kontak</h4>
              <p style={{ color: '#cbd5e1', display: 'flex', alignItems: 'center', gap: 8, fontSize: '0.9rem' }}>
                <MapPin size={16} color="#818cf8" /> Jl. U.suryadi No. 451, Padalarang
              </p>
            </div>
          </div>
        </div>
        <div className="footer-bottom">
          &copy; {new Date().getFullYear()} Pilketos Web App Demo — Majelis Perwakilan Kelas SMKN 4 Padalarang.
        </div>
      </footer>
    </div>
  );
};

