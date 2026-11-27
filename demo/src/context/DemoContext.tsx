import React, { createContext, useContext, useEffect, useMemo, useState } from 'react';
import { INITIAL_DEMO_STATE } from '../data/seedData';
import { DemoCandidate, DemoElectionSettings, DemoState, DemoUser, Role } from '../types';

const STORAGE_KEY = 'pilketos_demo_state_v1';

interface ElectionComputedStatus {
  is_open: boolean;
  schedule_text: string;
  banner_text: string;
}

interface DemoContextValue {
  state: DemoState;
  currentUser: DemoUser | null;
  electionStatus: ElectionComputedStatus;
  login: (username: string, password: string) => { ok: boolean; error?: string; role?: Role };
  quickLogin: (role: Role) => Role;
  logout: () => void;
  resetDemo: () => void;
  castVote: (candidateId: number) => { ok: boolean; error?: string };
  addCandidate: (data: Omit<DemoCandidate, 'id'>) => { ok: boolean; error?: string };
  updateCandidate: (id: number, data: Partial<Omit<DemoCandidate, 'id'>>) => { ok: boolean; error?: string };
  deleteCandidate: (id: number) => { ok: boolean; error?: string };
  addVoterAccount: (username: string, password: string, role: 'siswa' | 'guru') => { ok: boolean; error?: string };
  resetVoterPassword: (userId: number, newPassword: string) => { ok: boolean; error?: string };
  deleteVoterAccount: (userId: number) => { ok: boolean; error?: string };
  updateAdminPassword: (currentPassword: string, newPassword: string, confirmPassword: string) => { ok: boolean; error?: string };
  updateSettings: (settings: DemoElectionSettings) => { ok: boolean; error?: string };
}

const DemoContext = createContext<DemoContextValue | null>(null);

function loadInitialState(): DemoState {
  try {
    const raw = localStorage.getItem(STORAGE_KEY);
    if (!raw) return INITIAL_DEMO_STATE;
    const parsed = JSON.parse(raw) as DemoState;
    if (
      !parsed ||
      !Array.isArray(parsed.users) ||
      !Array.isArray(parsed.candidates) ||
      !Array.isArray(parsed.votes) ||
      !parsed.settings ||
      typeof parsed.settings !== 'object' ||
      typeof parsed.settings.election_title !== 'string' ||
      typeof parsed.settings.is_voting_open !== 'boolean'
    ) {
      return INITIAL_DEMO_STATE;
    }
    return parsed;
  } catch {
    return INITIAL_DEMO_STATE;
  }
}

export const DemoProvider: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const [state, setState] = useState<DemoState>(loadInitialState);

  useEffect(() => {
    try {
      localStorage.setItem(STORAGE_KEY, JSON.stringify(state));
    } catch {
      // Ignore storage quota errors in restricted browsers
    }
  }, [state]);

  const currentUser = useMemo(
    () => state.users.find((u) => u.id === state.currentUserId) ?? null,
    [state.users, state.currentUserId]
  );

  const electionStatus = useMemo<ElectionComputedStatus>(() => {
    const { is_voting_open, voting_start, voting_end } = state.settings;
    const now = Date.now();
    const startTs = voting_start ? new Date(voting_start).getTime() : null;
    const endTs = voting_end ? new Date(voting_end).getTime() : null;

    let isOpen = is_voting_open;
    let bannerText = 'Simulasi pemilihan sedang berlangsung. Silakan coba memberikan suara!';

    if (!is_voting_open) {
      isOpen = false;
      bannerText = 'Pemungutan suara saat ini sedang ditutup oleh administrator (Mode Demo).';
    } else if (startTs && now < startTs) {
      isOpen = false;
      bannerText = `Pemungutan suara akan dibuka pada ${new Date(startTs).toLocaleString('id-ID')}.`;
    } else if (endTs && now > endTs) {
      isOpen = false;
      bannerText = `Pemungutan suara telah ditutup pada ${new Date(endTs).toLocaleString('id-ID')}.`;
    } else if (endTs) {
      bannerText = `Pemilihan akan ditutup pada ${new Date(endTs).toLocaleString('id-ID')}.`;
    }

    let scheduleText = is_voting_open ? 'Simulasi Dibuka' : 'Ditutup';
    if (startTs && endTs) {
      scheduleText = `${new Date(startTs).toLocaleDateString('id-ID')} – ${new Date(endTs).toLocaleDateString('id-ID')}`;
    } else if (endTs) {
      scheduleText = `s/d ${new Date(endTs).toLocaleDateString('id-ID')}`;
    } else if (startTs) {
      scheduleText = `Mulai ${new Date(startTs).toLocaleDateString('id-ID')}`;
    }

    return { is_open: isOpen, schedule_text: scheduleText, banner_text: bannerText };
  }, [state.settings]);

  const login = (username: string, password: string) => {
    const user = state.users.find((u) => u.username === username.trim() && u.password === password);
    if (!user) {
      return { ok: false, error: 'Username atau password salah!' };
    }
    setState((prev) => ({ ...prev, currentUserId: user.id }));
    return { ok: true, role: user.role };
  };

  const quickLogin = (role: Role) => {
    const target = state.users.find((u) => u.role === role) ?? state.users[0];
    setState((prev) => ({ ...prev, currentUserId: target.id }));
    return target.role;
  };

  const logout = () => {
    setState((prev) => ({ ...prev, currentUserId: null }));
  };

  const resetDemo = () => {
    localStorage.removeItem(STORAGE_KEY);
    setState(INITIAL_DEMO_STATE);
  };

  const castVote = (candidateId: number) => {
    if (!currentUser || currentUser.role === 'admin') {
      return { ok: false, error: 'Akun ini tidak memiliki hak suara.' };
    }
    if (!electionStatus.is_open) {
      return { ok: false, error: electionStatus.banner_text };
    }
    if (currentUser.sudah_vote || state.votes.some((v) => v.user_id === currentUser.id)) {
      return { ok: false, error: 'Anda sudah memberikan suara sebelumnya.' };
    }
    const cand = state.candidates.find((c) => c.id === candidateId);
    if (!cand) {
      return { ok: false, error: 'Kandidat tidak ditemukan.' };
    }

    setState((prev) => ({
      ...prev,
      users: prev.users.map((u) => (u.id === currentUser.id ? { ...u, sudah_vote: true } : u)),
      votes: [
        ...prev.votes,
        {
          id: Math.max(0, ...prev.votes.map((v) => v.id)) + 1,
          user_id: currentUser.id,
          candidate_id: candidateId,
          guru_vote: currentUser.role === 'guru',
          timestamp: new Date().toISOString(),
        },
      ],
    }));
    return { ok: true };
  };

  const addCandidate = (data: Omit<DemoCandidate, 'id'>) => {
    if (!data.nama.trim() || !data.kelas.trim() || !data.visi.trim() || !data.misi.trim()) {
      return { ok: false, error: 'Semua field wajib diisi!' };
    }
    setState((prev) => ({
      ...prev,
      candidates: [
        ...prev.candidates,
        { ...data, id: Math.max(0, ...prev.candidates.map((c) => c.id)) + 1 },
      ],
    }));
    return { ok: true };
  };

  const updateCandidate = (id: number, data: Partial<Omit<DemoCandidate, 'id'>>) => {
    setState((prev) => ({
      ...prev,
      candidates: prev.candidates.map((c) => (c.id === id ? { ...c, ...data } : c)),
    }));
    return { ok: true };
  };

  const deleteCandidate = (id: number) => {
    if (state.votes.some((v) => v.candidate_id === id)) {
      return { ok: false, error: 'Kandidat tidak dapat dihapus karena sudah memiliki suara.' };
    }
    setState((prev) => ({
      ...prev,
      candidates: prev.candidates.filter((c) => c.id !== id),
    }));
    return { ok: true };
  };

  const addVoterAccount = (username: string, password: string, role: 'siswa' | 'guru') => {
    const clean = username.trim();
    if (!clean || !password) {
      return { ok: false, error: 'Semua field harus diisi!' };
    }
    if (state.users.some((u) => u.username === clean)) {
      return { ok: false, error: 'Username sudah digunakan! Silakan gunakan username lain.' };
    }
    setState((prev) => ({
      ...prev,
      users: [
        ...prev.users,
        {
          id: Math.max(0, ...prev.users.map((u) => u.id)) + 1,
          username: clean,
          password,
          role,
          sudah_vote: false,
        },
      ],
    }));
    return { ok: true };
  };

  const resetVoterPassword = (userId: number, newPassword: string) => {
    if (newPassword.length < 6) {
      return { ok: false, error: 'Password baru minimal 6 karakter.' };
    }
    setState((prev) => ({
      ...prev,
      users: prev.users.map((u) =>
        u.id === userId && u.role !== 'admin' ? { ...u, password: newPassword } : u
      ),
    }));
    return { ok: true };
  };

  const deleteVoterAccount = (userId: number) => {
    if (state.votes.some((v) => v.user_id === userId)) {
      return { ok: false, error: 'Akun tidak dapat dihapus karena sudah memberikan suara.' };
    }
    setState((prev) => ({
      ...prev,
      users: prev.users.filter((u) => !(u.id === userId && u.role !== 'admin')),
    }));
    return { ok: true };
  };

  const updateAdminPassword = (currentPassword: string, newPassword: string, confirmPassword: string) => {
    if (!currentUser) return { ok: false, error: 'Sesi tidak ditemukan.' };
    if (newPassword.length < 6) return { ok: false, error: 'Password baru minimal 6 karakter.' };
    if (currentUser.password !== currentPassword) return { ok: false, error: 'Password saat ini salah.' };
    if (newPassword !== confirmPassword) return { ok: false, error: 'Password baru tidak cocok.' };
    setState((prev) => ({
      ...prev,
      users: prev.users.map((u) => (u.id === currentUser.id ? { ...u, password: newPassword } : u)),
    }));
    return { ok: true };
  };

  const updateSettings = (settings: DemoElectionSettings) => {
    if (!settings.election_title.trim()) {
      return { ok: false, error: 'Judul pemilihan tidak boleh kosong.' };
    }
    if (settings.voting_start && settings.voting_end && new Date(settings.voting_end) < new Date(settings.voting_start)) {
      return { ok: false, error: 'Waktu selesai tidak boleh lebih awal dari waktu mulai.' };
    }
    setState((prev) => ({ ...prev, settings }));
    return { ok: true };
  };

  return (
    <DemoContext.Provider
      value={{
        state,
        currentUser,
        electionStatus,
        login,
        quickLogin,
        logout,
        resetDemo,
        castVote,
        addCandidate,
        updateCandidate,
        deleteCandidate,
        addVoterAccount,
        resetVoterPassword,
        deleteVoterAccount,
        updateAdminPassword,
        updateSettings,
      }}
    >
      {children}
    </DemoContext.Provider>
  );
};

export function useDemo() {
  const ctx = useContext(DemoContext);
  if (!ctx) throw new Error('useDemo must be used within DemoProvider');
  return ctx;
}
