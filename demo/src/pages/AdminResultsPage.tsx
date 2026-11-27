import React from 'react';
import { Bar } from 'react-chartjs-2';
import {
  BarElement,
  CategoryScale,
  Chart as ChartJS,
  Legend,
  LinearScale,
  Tooltip,
} from 'chart.js';
import { BarChart3, CheckSquare, Printer, RefreshCw, Table } from 'lucide-react';
import { useDemo } from '../context/DemoContext';

ChartJS.register(CategoryScale, LinearScale, BarElement, Tooltip, Legend);

const BAR_COLORS = [
  'rgba(30, 58, 138, 0.85)',
  'rgba(16, 185, 129, 0.85)',
  'rgba(79, 70, 229, 0.85)',
  'rgba(14, 165, 233, 0.85)',
  'rgba(245, 158, 11, 0.85)',
];

export const AdminResultsPage: React.FC = () => {
  const { state } = useDemo();
  const totalVotes = state.votes.length;

  const rows = state.candidates.map((c) => {
    const count = state.votes.filter((v) => v.candidate_id === c.id).length;
    const pct = totalVotes > 0 ? Math.round((count / totalVotes) * 1000) / 10 : 0;
    return { ...c, count, pct };
  });

  const chartData = {
    labels: rows.map((r) => r.nama),
    datasets: [
      {
        label: 'Perolehan Suara',
        data: rows.map((r) => r.count),
        backgroundColor: rows.map((_, idx) => BAR_COLORS[idx % BAR_COLORS.length]),
        borderRadius: 8,
        barPercentage: 0.6,
      },
    ],
  };

  return (
    <div>
      <div
        style={{
          background: 'linear-gradient(135deg, #1e3a8a 0%, #4f46e5 100%)',
          color: '#fff',
          padding: '36px 20px 68px',
          borderRadius: '0 0 28px 28px',
          marginTop: -28,
          marginBottom: -44,
          textAlign: 'center',
        }}
      >
        <div className="container">
          <h1 style={{ fontSize: '1.8rem', fontWeight: 800 }}>Live Count Pemilihan (Simulasi)</h1>
          <p style={{ opacity: 0.9, marginTop: 4 }}>
            Pantau hasil perolehan suara simulasi secara real-time di browser Anda.
          </p>
        </div>
      </div>

      <div className="container" style={{ position: 'relative', zIndex: 10 }}>
        <div
          className="card"
          style={{
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'space-between',
            flexWrap: 'wrap',
            gap: 16,
            borderLeft: '5px solid #10b981',
            marginBottom: 24,
          }}
        >
          <div>
            <p style={{ fontSize: '0.85rem', fontWeight: 600, color: 'var(--text-muted)', textTransform: 'uppercase' }}>
              Total Suara Masuk (Simulasi)
            </p>
            <p style={{ fontSize: '2.3rem', fontWeight: 800, lineHeight: 1.1 }}>{totalVotes}</p>
          </div>
          <div className="no-print" style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
            <button type="button" className="btn btn-secondary btn-sm" onClick={() => window.location.reload()}>
              <RefreshCw size={15} /> Refresh
            </button>
            <button type="button" className="btn btn-secondary btn-sm" onClick={() => window.print()}>
              <Printer size={15} /> Cetak
            </button>
            <div style={{ width: 52, height: 52, borderRadius: '50%', background: 'rgba(16,185,129,0.12)', color: '#10b981', display: 'inline-flex', alignItems: 'center', justifyContent: 'center' }}>
              <CheckSquare size={24} />
            </div>
          </div>
        </div>

        <div className="card" style={{ marginBottom: 24 }}>
          <h3 style={{ fontWeight: 700, display: 'flex', alignItems: 'center', gap: 8, marginBottom: 18 }}>
            <BarChart3 size={20} color="#4f46e5" /> Statistik Perolehan Suara
          </h3>
          <div style={{ height: 320 }}>
            <Bar
              data={chartData}
              options={{
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                  y: { beginAtZero: true, ticks: { stepSize: 1 } },
                  x: { grid: { display: false } },
                },
              }}
            />
          </div>
        </div>

        <div className="card">
          <h3 style={{ fontWeight: 700, display: 'flex', alignItems: 'center', gap: 8, marginBottom: 16 }}>
            <Table size={20} color="#4f46e5" /> Rincian Detail Kandidat
          </h3>
          <div className="table-wrap">
            <table>
              <thead>
                <tr>
                  <th>No</th>
                  <th>Nama Kandidat</th>
                  <th>Kelas</th>
                  <th>Jumlah Suara</th>
                  <th>Persentase</th>
                </tr>
              </thead>
              <tbody>
                {rows.map((r, idx) => (
                  <tr key={r.id}>
                    <td style={{ fontWeight: 700, color: 'var(--text-muted)' }}>#{idx + 1}</td>
                    <td style={{ fontWeight: 700 }}>{r.nama}</td>
                    <td>
                      <span className="badge badge-muted">{r.kelas}</span>
                    </td>
                    <td style={{ fontWeight: 700, fontSize: '1.05rem' }}>{r.count}</td>
                    <td>
                      <span
                        style={{
                          background: 'rgba(79,70,229,0.1)',
                          color: '#4f46e5',
                          padding: '4px 10px',
                          borderRadius: 999,
                          fontWeight: 700,
                          fontSize: '0.85rem',
                        }}
                      >
                        {r.pct}%
                      </span>
                      <div style={{ width: 100, height: 6, background: '#e2e8f0', borderRadius: 4, marginTop: 6, overflow: 'hidden' }}>
                        <div style={{ width: `${r.pct}%`, height: '100%', background: '#4f46e5' }} />
                      </div>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  );
};

