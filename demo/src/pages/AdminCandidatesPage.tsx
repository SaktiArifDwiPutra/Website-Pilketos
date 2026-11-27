import React, { useState } from 'react';
import { Link } from 'react-router-dom';
import { Pencil, Save, Trash2, UploadCloud, UserPlus, Users } from 'lucide-react';
import { useDemo } from '../context/DemoContext';
import { DEFAULT_AVATAR } from '../data/seedData';

export const AdminCandidatesPage: React.FC = () => {
  const { state, addCandidate, updateCandidate, deleteCandidate } = useDemo();
  const [editingId, setEditingId] = useState<number | null>(null);
  const [nama, setNama] = useState('');
  const [kelas, setKelas] = useState('');
  const [visi, setVisi] = useState('');
  const [misi, setMisi] = useState('');
  const [program, setProgram] = useState('');
  const [foto, setFoto] = useState('');
  const [msg, setMsg] = useState<{ type: 'success' | 'danger'; text: string } | null>(null);

  const resetForm = () => {
    setEditingId(null);
    setNama('');
    setKelas('');
    setVisi('');
    setMisi('');
    setProgram('');
    setFoto('');
  };

  const handleFileChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (!file) return;
    if (file.size > 2 * 1024 * 1024) {
      setMsg({ type: 'danger', text: 'Ukuran file maksimal 2MB.' });
      return;
    }
    const reader = new FileReader();
    reader.onload = () => {
      if (typeof reader.result === 'string') setFoto(reader.result);
    };
    reader.readAsDataURL(file);
  };

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (editingId !== null) {
      const res = updateCandidate(editingId, {
        nama,
        kelas,
        visi,
        misi,
        Program: program,
        foto: foto || DEFAULT_AVATAR,
      });
      if (res.ok) {
        setMsg({ type: 'success', text: 'Data calon simulasi berhasil diperbarui!' });
        resetForm();
      } else {
        setMsg({ type: 'danger', text: res.error ?? 'Gagal memperbarui.' });
      }
      return;
    }

    const res = addCandidate({
      nama,
      kelas,
      visi,
      misi,
      Program: program,
      foto: foto || DEFAULT_AVATAR,
    });
    if (res.ok) {
      setMsg({ type: 'success', text: 'Calon simulasi berhasil ditambahkan!' });
      resetForm();
    } else {
      setMsg({ type: 'danger', text: res.error ?? 'Gagal menambahkan calon.' });
    }
  };

  const startEdit = (id: number) => {
    const cand = state.candidates.find((c) => c.id === id);
    if (!cand) return;
    setEditingId(id);
    setNama(cand.nama);
    setKelas(cand.kelas);
    setVisi(cand.visi);
    setMisi(cand.misi);
    setProgram(cand.Program);
    setFoto(cand.foto);
    setMsg(null);
    window.scrollTo({ top: 0, behavior: 'smooth' });
  };

  const handleDelete = (id: number) => {
    if (!window.confirm('Hapus kandidat simulasi ini?')) return;
    const res = deleteCandidate(id);
    if (res.ok) {
      setMsg({ type: 'success', text: 'Kandidat berhasil dihapus!' });
      if (editingId === id) resetForm();
    } else {
      setMsg({ type: 'danger', text: res.error ?? 'Gagal menghapus.' });
    }
  };

  return (
    <div className="container" style={{ maxWidth: 940 }}>
      <div style={{ display: 'flex', alignItems: 'center', gap: 12, marginBottom: 24 }}>
        <div style={{ background: '#e0e7ff', color: '#4f46e5', padding: 10, borderRadius: 10, display: 'inline-flex' }}>
          <UserPlus size={22} />
        </div>
        <h1 style={{ fontSize: '1.5rem', fontWeight: 700 }}>
          {editingId !== null ? 'Edit Calon Ketua OSIS' : 'Tambah Calon Ketua OSIS'}
        </h1>
      </div>

      {msg && <div className={`alert alert-${msg.type}`}>{msg.text}</div>}

      <div className="card" style={{ marginBottom: 28 }}>
        <form onSubmit={handleSubmit}>
          <div className="form-group">
            <label className="form-label">Foto Calon (Opsional di Demo, Maks. 2MB)</label>
            <label
              style={{
                display: 'block',
                border: '2px dashed #d1d5db',
                borderRadius: 12,
                padding: 20,
                textAlign: 'center',
                cursor: 'pointer',
                background: '#f9fafb',
              }}
            >
              <input type="file" accept="image/*" style={{ display: 'none' }} onChange={handleFileChange} />
              {foto ? (
                <img src={foto} alt="Preview" style={{ maxHeight: 180, borderRadius: 8 }} />
              ) : (
                <div style={{ color: 'var(--text-muted)' }}>
                  <UploadCloud size={28} style={{ marginBottom: 6 }} />
                  <p style={{ fontSize: '0.9rem' }}>Klik untuk memilih gambar (atau biarkan kosong untuk avatar default)</p>
                </div>
              )}
            </label>
          </div>

          <div className="grid-2" style={{ gap: 16 }}>
            <div className="form-group">
              <label className="form-label" htmlFor="nama">Nama Lengkap</label>
              <input id="nama" type="text" className="form-control" value={nama} onChange={(e) => setNama(e.target.value)} required />
            </div>
            <div className="form-group">
              <label className="form-label" htmlFor="kelas">Kelas</label>
              <input id="kelas" type="text" className="form-control" value={kelas} onChange={(e) => setKelas(e.target.value)} required />
            </div>
          </div>

          <div className="form-group">
            <label className="form-label" htmlFor="visi">Visi</label>
            <textarea id="visi" className="form-control" rows={3} value={visi} onChange={(e) => setVisi(e.target.value)} required />
          </div>

          <div className="grid-2" style={{ gap: 16 }}>
            <div className="form-group">
              <label className="form-label" htmlFor="misi">Misi</label>
              <textarea id="misi" className="form-control" rows={4} value={misi} onChange={(e) => setMisi(e.target.value)} required />
            </div>
            <div className="form-group">
              <label className="form-label" htmlFor="program">Program Unggulan</label>
              <textarea id="program" className="form-control" rows={4} value={program} onChange={(e) => setProgram(e.target.value)} required />
            </div>
          </div>

          <div style={{ display: 'flex', gap: 12, marginTop: 12, flexWrap: 'wrap' }}>
            <button type="submit" className="btn btn-primary">
              <Save size={16} /> {editingId !== null ? 'Simpan Perubahan' : 'Simpan Data Calon'}
            </button>
            {editingId !== null ? (
              <button type="button" className="btn btn-secondary" onClick={resetForm}>
                Batal Edit
              </button>
            ) : (
              <Link to="/admin/dashboard" className="btn btn-secondary">
                Kembali
              </Link>
            )}
          </div>
        </form>
      </div>

      <div className="card">
        <h3 style={{ fontWeight: 700, display: 'flex', alignItems: 'center', gap: 8, marginBottom: 16 }}>
          <Users size={20} color="#4f46e5" /> Daftar Kandidat Terdaftar ({state.candidates.length})
        </h3>
        <div className="table-wrap">
          <table className="table-mobile-cards">
            <thead>
              <tr>
                <th>Foto</th>
                <th>Nama</th>
                <th>Kelas</th>
                <th>Suara</th>
                <th style={{ textAlign: 'right' }}>Aksi</th>
              </tr>
            </thead>
            <tbody>
              {state.candidates.map((c) => {
                const voteCount = state.votes.filter((v) => v.candidate_id === c.id).length;
                return (
                  <tr key={c.id}>
                    <td data-label="Foto">
                      <img src={c.foto || DEFAULT_AVATAR} alt={c.nama} style={{ width: 52, height: 52, objectFit: 'cover', borderRadius: 8 }} />
                    </td>
                    <td data-label="Nama" style={{ fontWeight: 600 }}>{c.nama}</td>
                    <td data-label="Kelas">{c.kelas}</td>
                    <td data-label="Suara">
                      <span className="badge badge-muted">{voteCount}</span>
                    </td>
                    <td data-label="Aksi" className="admin-actions-cell" style={{ textAlign: 'right', whiteSpace: 'nowrap' }}>
                      <div className="admin-actions-row" style={{ justifyContent: 'flex-end' }}>
                        <button type="button" className="btn btn-secondary btn-sm" onClick={() => startEdit(c.id)}>
                          <Pencil size={14} /> Edit
                        </button>
                        <button
                          type="button"
                          className="btn btn-danger btn-sm"
                          disabled={voteCount > 0}
                          title={voteCount > 0 ? 'Sudah memiliki suara' : 'Hapus kandidat'}
                          onClick={() => handleDelete(c.id)}
                        >
                          <Trash2 size={14} /> Hapus
                        </button>
                      </div>
                    </td>
                  </tr>
                );
              })}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  );
};

