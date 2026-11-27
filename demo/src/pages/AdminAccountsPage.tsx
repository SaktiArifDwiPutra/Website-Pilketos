import React, { useState } from 'react';
import { Link } from 'react-router-dom';
import { KeyRound, Plus, Trash2, UserCog, Users } from 'lucide-react';
import { useDemo } from '../context/DemoContext';

export const AdminAccountsPage: React.FC = () => {
  const { state, addVoterAccount, resetVoterPassword, deleteVoterAccount } = useDemo();
  const [username, setUsername] = useState('');
  const [password, setPassword] = useState('');
  const [role, setRole] = useState<'siswa' | 'guru'>('siswa');
  const [resetInputs, setResetInputs] = useState<Record<number, string>>({});
  const [msg, setMsg] = useState<{ type: 'success' | 'danger'; text: string } | null>(null);

  const voterAccounts = state.users.filter((u) => u.role === 'siswa' || u.role === 'guru');

  const handleCreate = (e: React.FormEvent) => {
    e.preventDefault();
    const res = addVoterAccount(username, password, role);
    if (res.ok) {
      setMsg({ type: 'success', text: `Akun ${username} berhasil ditambahkan!` });
      setUsername('');
      setPassword('');
    } else {
      setMsg({ type: 'danger', text: res.error ?? 'Gagal menambahkan akun.' });
    }
  };

  const handleReset = (userId: number) => {
    const newPass = resetInputs[userId] ?? '';
    const res = resetVoterPassword(userId, newPass);
    if (res.ok) {
      setMsg({ type: 'success', text: 'Password akun pemilih berhasil diperbarui!' });
      setResetInputs((prev) => ({ ...prev, [userId]: '' }));
    } else {
      setMsg({ type: 'danger', text: res.error ?? 'Gagal mereset password.' });
    }
  };

  const handleDelete = (userId: number) => {
    if (!window.confirm('Hapus akun pemilih simulasi ini?')) return;
    const res = deleteVoterAccount(userId);
    if (res.ok) {
      setMsg({ type: 'success', text: 'Akun pemilih berhasil dihapus!' });
    } else {
      setMsg({ type: 'danger', text: res.error ?? 'Gagal menghapus akun.' });
    }
  };

  return (
    <div className="container" style={{ maxWidth: 840 }}>
      <div style={{ textAlign: 'center', marginBottom: 24 }}>
        <div
          style={{
            width: 58,
            height: 58,
            borderRadius: 16,
            background: 'linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%)',
            color: '#fff',
            display: 'inline-flex',
            alignItems: 'center',
            justifyContent: 'center',
            marginBottom: 12,
          }}
        >
          <UserCog size={26} />
        </div>
        <h1 style={{ fontSize: '1.5rem', fontWeight: 700 }}>Kelola Akun Pengguna</h1>
        <p style={{ color: 'var(--text-muted)' }}>Buat dan kelola akun simulasi untuk Siswa atau Guru</p>
      </div>

      {msg && <div className={`alert alert-${msg.type}`}>{msg.text}</div>}

      <div className="card" style={{ marginBottom: 28 }}>
        <form onSubmit={handleCreate}>
          <div className="form-group">
            <label className="form-label" htmlFor="acc_user">Username</label>
            <input id="acc_user" type="text" className="form-control" value={username} onChange={(e) => setUsername(e.target.value)} required />
          </div>
          <div className="form-group">
            <label className="form-label" htmlFor="acc_pass">Password</label>
            <input id="acc_pass" type="password" className="form-control" value={password} onChange={(e) => setPassword(e.target.value)} required />
          </div>
          <div className="form-group">
            <label className="form-label" htmlFor="acc_role">Hak Akses (Role)</label>
            <select id="acc_role" className="form-control" value={role} onChange={(e) => setRole(e.target.value as 'siswa' | 'guru')}>
              <option value="siswa">Siswa</option>
              <option value="guru">Guru</option>
            </select>
          </div>
          <div style={{ display: 'flex', gap: 12, flexWrap: 'wrap' }}>
            <button type="submit" className="btn btn-primary">
              <Plus size={16} /> Tambah Akun
            </button>
            <Link to="/admin/dashboard" className="btn btn-secondary">
              Batal
            </Link>
          </div>
        </form>
      </div>

      <div className="card">
        <h3 style={{ fontWeight: 700, display: 'flex', alignItems: 'center', gap: 8, marginBottom: 16 }}>
          <Users size={20} color="#4f46e5" /> Daftar Akun Pemilih ({voterAccounts.length})
        </h3>
        <div className="table-wrap">
          <table className="table-mobile-cards">
            <thead>
              <tr>
                <th>Username</th>
                <th>Role</th>
                <th>Status Voting</th>
                <th style={{ textAlign: 'right' }}>Aksi</th>
              </tr>
            </thead>
            <tbody>
              {voterAccounts.map((acc) => (
                <tr key={acc.id}>
                  <td data-label="Username" style={{ fontWeight: 600 }}>{acc.username}</td>
                  <td data-label="Role">
                    <span className={`badge badge-${acc.role}`}>{acc.role.toUpperCase()}</span>
                  </td>
                  <td data-label="Status">
                    <span className="badge badge-muted">{acc.sudah_vote ? 'Sudah Vote' : 'Belum Vote'}</span>
                  </td>
                  <td data-label="Aksi" className="admin-actions-cell" style={{ textAlign: 'right', whiteSpace: 'nowrap' }}>
                    <div className="admin-actions-row" style={{ justifyContent: 'flex-end', alignItems: 'center' }}>
                      <input
                        type="password"
                        className="form-control"
                        style={{ width: 130, padding: '5px 10px', fontSize: '0.82rem' }}
                        placeholder="Pass baru (6+)"
                        value={resetInputs[acc.id] ?? ''}
                        onChange={(e) => setResetInputs((prev) => ({ ...prev, [acc.id]: e.target.value }))}
                      />
                      <button type="button" className="btn btn-secondary btn-sm" onClick={() => handleReset(acc.id)} title="Reset Password">
                        <KeyRound size={14} /> Reset
                      </button>
                      <button
                        type="button"
                        className="btn btn-danger btn-sm"
                        disabled={acc.sudah_vote}
                        title={acc.sudah_vote ? 'Sudah memberikan suara' : 'Hapus akun'}
                        onClick={() => handleDelete(acc.id)}
                      >
                        <Trash2 size={14} /> Hapus
                      </button>
                    </div>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  );
};

