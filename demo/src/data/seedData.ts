import { DemoState } from '../types';

function makeAvatarSvg(initials: string, bg1: string, bg2: string): string {
  const svg = `<svg xmlns="http://www.w3.org/2000/svg" width="400" height="280" viewBox="0 0 400 280">
    <defs>
      <linearGradient id="g" x1="0" y1="0" x2="1" y2="1">
        <stop offset="0%" stop-color="${bg1}"/>
        <stop offset="100%" stop-color="${bg2}"/>
      </linearGradient>
    </defs>
    <rect width="400" height="280" fill="url(#g)"/>
    <circle cx="200" cy="112" r="48" fill="rgba(255,255,255,0.22)"/>
    <text x="200" y="128" text-anchor="middle" fill="#ffffff" font-family="Inter, sans-serif" font-size="36" font-weight="700">${initials}</text>
    <rect x="115" y="180" width="170" height="44" rx="22" fill="rgba(255,255,255,0.16)"/>
    <text x="200" y="207" text-anchor="middle" fill="#ffffff" font-family="Inter, sans-serif" font-size="14" font-weight="600">Kandidat Simulasi</text>
  </svg>`;
  return `data:image/svg+xml;utf8,${encodeURIComponent(svg)}`;
}

export const DEFAULT_AVATAR = makeAvatarSvg('OSIS', '#64748b', '#475569');

export const INITIAL_DEMO_STATE: DemoState = {
  currentUserId: null,
  settings: {
    election_title: 'Pemilihan Wakil & Ketua OSIS (Simulasi)',
    voting_start: '',
    voting_end: '',
    is_voting_open: true,
  },
  candidates: [
    {
      id: 1,
      foto: makeAvatarSvg('01', '#1e3a8a', '#4f46e5'),
      nama: 'Raka Pratama & Sinta Dewi',
      kelas: 'XI RPL 1 / XI RPL 2',
      visi: 'Mewujudkan OSIS yang adaptif, kolaboratif, dan berprestasi di era digital.',
      misi: '1. Mengoptimalkan literasi digital siswa.\n2. Memperkuat kolaborasi antar ekstrakurikuler.\n3. Menjadikan aspirasi siswa sebagai prioritas program.',
      Program: 'Portal Aspirasi Siswa Digital & Festival Inovasi Tahunan.',
    },
    {
      id: 2,
      foto: makeAvatarSvg('02', '#065f46', '#10b981'),
      nama: 'Dimas Saputra & Nadia Putri',
      kelas: 'XI TKJ 2 / XI AKL 1',
      visi: 'Membangun lingkungan sekolah yang inklusif, kreatif, dan berkarakter.',
      misi: '1. Menghidupkan ruang kreasi seni dan olahraga.\n2. Meningkatkan kepedulian sosial dan lingkungan.\n3. Transparansi agenda OSIS setiap bulan.',
      Program: 'Green School Movement & Pekan Kreativitas Pelajar.',
    },
    {
      id: 3,
      foto: makeAvatarSvg('03', '#4f46e5', '#7c3aed'),
      nama: 'Fajar Nugroho & Alya Rahma',
      kelas: 'XI DKV 1 / XI MP 2',
      visi: 'Menjadikan OSIS sebagai motor penggerak prestasi akademik dan non-akademik.',
      misi: '1. Mengadakan bimbingan minat bakat rutin.\n2. Mempererat sinergi guru, siswa, dan alumni.\n3. Mendorong budaya disiplin dan tanggung jawab.',
      Program: 'Kelas Mentoring Prestasi & Bazar Kewirausahaan Siswa.',
    },
  ],
  users: [
    { id: 1, username: 'admin_demo', password: 'demo123', role: 'admin', sudah_vote: false },
    { id: 2, username: 'siswa_demo', password: 'demo123', role: 'siswa', sudah_vote: false },
    { id: 3, username: 'guru_demo', password: 'demo123', role: 'guru', sudah_vote: false },
    { id: 4, username: 'siswa_01', password: 'demo123', role: 'siswa', sudah_vote: true },
    { id: 5, username: 'siswa_02', password: 'demo123', role: 'siswa', sudah_vote: true },
    { id: 6, username: 'siswa_03', password: 'demo123', role: 'siswa', sudah_vote: true },
    { id: 7, username: 'guru_01', password: 'demo123', role: 'guru', sudah_vote: true },
    { id: 8, username: 'siswa_04', password: 'demo123', role: 'siswa', sudah_vote: true },
  ],
  votes: [
    { id: 1, user_id: 4, candidate_id: 1, guru_vote: false, timestamp: '2026-10-24T08:15:00Z' },
    { id: 2, user_id: 5, candidate_id: 2, guru_vote: false, timestamp: '2026-10-24T08:22:00Z' },
    { id: 3, user_id: 6, candidate_id: 1, guru_vote: false, timestamp: '2026-10-24T09:05:00Z' },
    { id: 4, user_id: 7, candidate_id: 3, guru_vote: true, timestamp: '2026-10-24T09:30:00Z' },
    { id: 5, user_id: 8, candidate_id: 2, guru_vote: false, timestamp: '2026-10-24T10:12:00Z' },
  ],
};

