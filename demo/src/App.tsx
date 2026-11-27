import React from 'react';
import { BrowserRouter, Navigate, Route, Routes } from 'react-router-dom';
import { Layout } from './components/Layout';
import { DemoProvider, useDemo } from './context/DemoContext';
import { AdminAccountsPage } from './pages/AdminAccountsPage';
import { AdminCandidatesPage } from './pages/AdminCandidatesPage';
import { AdminDashboardPage } from './pages/AdminDashboardPage';
import { AdminResultsPage } from './pages/AdminResultsPage';
import { LoginPage } from './pages/LoginPage';
import { UserDashboardPage } from './pages/UserDashboardPage';
import { VotePage } from './pages/VotePage';

const RequireAuth: React.FC<{ allowRoles?: Array<'admin' | 'guru' | 'siswa'>; children: React.ReactNode }> = ({
  allowRoles,
  children,
}) => {
  const { currentUser } = useDemo();
  if (!currentUser) {
    return <Navigate to="/masuk" replace />;
  }
  if (allowRoles && !allowRoles.includes(currentUser.role)) {
    return <Navigate to={currentUser.role === 'admin' ? '/admin/dashboard' : '/user/dashboard'} replace />;
  }
  return <>{children}</>;
};

const HomeRedirect: React.FC = () => {
  const { currentUser } = useDemo();
  if (!currentUser) return <Navigate to="/masuk" replace />;
  return <Navigate to={currentUser.role === 'admin' ? '/admin/dashboard' : '/user/dashboard'} replace />;
};

export const App: React.FC = () => {
  return (
    <DemoProvider>
      <BrowserRouter>
        <Layout>
          <Routes>
            <Route path="/" element={<HomeRedirect />} />
            <Route path="/masuk" element={<LoginPage />} />

            <Route
              path="/user/dashboard"
              element={
                <RequireAuth>
                  <UserDashboardPage />
                </RequireAuth>
              }
            />
            <Route
              path="/user/vote"
              element={
                <RequireAuth allowRoles={['siswa']}>
                  <VotePage mode="siswa" />
                </RequireAuth>
              }
            />
            <Route
              path="/user/vote-guru"
              element={
                <RequireAuth allowRoles={['guru']}>
                  <VotePage mode="guru" />
                </RequireAuth>
              }
            />

            <Route
              path="/admin/dashboard"
              element={
                <RequireAuth allowRoles={['admin']}>
                  <AdminDashboardPage />
                </RequireAuth>
              }
            />
            <Route
              path="/admin/calon"
              element={
                <RequireAuth allowRoles={['admin']}>
                  <AdminCandidatesPage />
                </RequireAuth>
              }
            />
            <Route
              path="/admin/akun"
              element={
                <RequireAuth allowRoles={['admin']}>
                  <AdminAccountsPage />
                </RequireAuth>
              }
            />
            <Route
              path="/admin/hasil"
              element={
                <RequireAuth allowRoles={['admin']}>
                  <AdminResultsPage />
                </RequireAuth>
              }
            />

            <Route path="*" element={<HomeRedirect />} />
          </Routes>
        </Layout>
      </BrowserRouter>
    </DemoProvider>
  );
};

export default App;

