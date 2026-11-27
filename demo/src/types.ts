export type Role = 'admin' | 'guru' | 'siswa';

export interface DemoUser {
  id: number;
  username: string;
  password: string;
  role: Role;
  sudah_vote: boolean;
}

export interface DemoCandidate {
  id: number;
  foto: string;
  nama: string;
  kelas: string;
  visi: string;
  misi: string;
  Program: string;
}

export interface DemoVote {
  id: number;
  user_id: number;
  candidate_id: number;
  guru_vote: boolean;
  timestamp: string;
}

export interface DemoElectionSettings {
  election_title: string;
  voting_start: string;
  voting_end: string;
  is_voting_open: boolean;
}

export interface DemoState {
  users: DemoUser[];
  candidates: DemoCandidate[];
  votes: DemoVote[];
  settings: DemoElectionSettings;
  currentUserId: number | null;
}

