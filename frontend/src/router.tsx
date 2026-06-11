import { createBrowserRouter, Navigate } from 'react-router-dom'
import { ProtectedRoute } from '@/components/auth/ProtectedRoute'
import { AuthLayout } from '@/components/layout/AuthLayout'
import { MainLayout } from '@/components/layout/MainLayout'
import { DashboardPage } from '@/pages/DashboardPage'
import { ForgotPasswordPage } from '@/pages/ForgotPasswordPage'
import { LoginPage } from '@/pages/LoginPage'
import { PermissionsPage } from '@/pages/PermissionsPage'
import { ProfilePage } from '@/pages/ProfilePage'
import { ResetPasswordPage } from '@/pages/ResetPasswordPage'
import { RolesPage } from '@/pages/RolesPage'
import { SecuritySettingsPage } from '@/pages/SecuritySettingsPage'
import { SettingsPage } from '@/pages/SettingsPage'
import { UnauthorizedPage } from '@/pages/UnauthorizedPage'
import { UsersPage } from '@/pages/UsersPage'

export const router = createBrowserRouter([
  {
    element: <AuthLayout />,
    children: [
      { path: '/login', element: <LoginPage /> },
      { path: '/forgot-password', element: <ForgotPasswordPage /> },
      { path: '/reset-password', element: <ResetPasswordPage /> },
    ],
  },
  { path: '/unauthorized', element: <UnauthorizedPage /> },
  {
    path: '/',
    element: (
      <ProtectedRoute>
        <MainLayout />
      </ProtectedRoute>
    ),
    children: [
      { index: true, element: <DashboardPage /> },
      { path: 'settings', element: <SettingsPage /> },
      { path: 'profile', element: <ProfilePage /> },
      { path: 'admin/security', element: <SecuritySettingsPage /> },
      {
        path: 'admin/users',
        element: (
          <ProtectedRoute permission="users.view">
            <UsersPage />
          </ProtectedRoute>
        ),
      },
      {
        path: 'admin/roles',
        element: (
          <ProtectedRoute permission="roles.view">
            <RolesPage />
          </ProtectedRoute>
        ),
      },
      {
        path: 'admin/permissions',
        element: (
          <ProtectedRoute permission="permissions.view">
            <PermissionsPage />
          </ProtectedRoute>
        ),
      },
    ],
  },
  { path: '*', element: <Navigate to="/" replace /> },
])
