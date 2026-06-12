import { createBrowserRouter, Navigate } from 'react-router-dom'
import { AdminOnlyRoute } from '@/components/auth/AdminOnlyRoute'
import { CandidateOnlyRoute } from '@/components/auth/CandidateOnlyRoute'
import { ProtectedRoute } from '@/components/auth/ProtectedRoute'
import { StaffOnlyRoute } from '@/components/auth/StaffOnlyRoute'
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
import { CandidateCreatePage } from '@/pages/CandidateCreatePage'
import { CandidateDetailPage } from '@/pages/CandidateDetailPage'
import { CandidatesPage } from '@/pages/CandidatesPage'
import { DossierLayout } from '@/components/candidate/dossier/DossierLayout'
import { DossierAcademicPage } from '@/pages/candidate/dossier/DossierAcademicPage'
import { DossierCareerProjectPage } from '@/pages/candidate/dossier/DossierCareerProjectPage'
import { DossierContactPage } from '@/pages/candidate/dossier/DossierContactPage'
import { DossierCounselorPage } from '@/pages/candidate/dossier/DossierCounselorPage'
import { DossierDocumentsPage } from '@/pages/candidate/dossier/DossierDocumentsPage'
import { DossierExperiencesPage } from '@/pages/candidate/dossier/DossierExperiencesPage'
import { DossierFinancingPage } from '@/pages/candidate/dossier/DossierFinancingPage'
import { DossierGuarantorPage } from '@/pages/candidate/dossier/DossierGuarantorPage'
import { DossierHistoryPage } from '@/pages/candidate/dossier/DossierHistoryPage'
import { DossierLanguagesPage } from '@/pages/candidate/dossier/DossierLanguagesPage'
import { DossierOverviewPage } from '@/pages/candidate/dossier/DossierOverviewPage'
import { DossierPersonalPage } from '@/pages/candidate/dossier/DossierPersonalPage'
import { DossierStudyProjectPage } from '@/pages/candidate/dossier/DossierStudyProjectPage'
import { UsersPage } from '@/pages/UsersPage'
import { CandidateAppointmentsPage } from '@/pages/candidate/CandidateAppointmentsPage'
import { CandidateGuidesPage } from '@/pages/candidate/CandidateGuidesPage'
import { CandidateHousingPage } from '@/pages/candidate/CandidateHousingPage'
import { CandidateLinksPage } from '@/pages/candidate/CandidateLinksPage'
import { CandidatePaymentsPage } from '@/pages/candidate/CandidatePaymentsPage'
import { CandidateProcedurePage } from '@/pages/candidate/CandidateProcedurePage'
import { CounselorAvailabilityPage } from '@/pages/CounselorAvailabilityPage'
import { CounselorsPage } from '@/pages/admin/CounselorsPage'
import { MatchingPage } from '@/pages/admin/MatchingPage'
import { ReportsPage } from '@/pages/admin/ReportsPage'
import { DemarchesLayout } from '@/components/candidate/demarches/DemarchesLayout'
import { CampusFrancePage } from '@/pages/candidate/demarches/CampusFrancePage'
import { DemarchesHistoryPage } from '@/pages/candidate/demarches/DemarchesHistoryPage'
import { DemarchesOverviewPage } from '@/pages/candidate/demarches/DemarchesOverviewPage'
import { ParisSaclayPage } from '@/pages/candidate/demarches/ParisSaclayPage'
import { ParcoursupPage } from '@/pages/candidate/demarches/ParcoursupPage'

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
      { path: 'appointments', element: <CandidateAppointmentsPage /> },
      {
        path: 'guides',
        element: (
          <StaffOnlyRoute>
            <CandidateGuidesPage />
          </StaffOnlyRoute>
        ),
      },
      { path: 'payments', element: <CandidatePaymentsPage /> },
      { path: 'housing', element: <CandidateHousingPage /> },
      { path: 'procedure', element: <CandidateProcedurePage /> },
      { path: 'parcoursup', element: <Navigate to="/demarches/parcoursup" replace /> },
      {
        path: 'demarches',
        element: <DemarchesLayout />,
        children: [
          { index: true, element: <DemarchesOverviewPage /> },
          { path: 'campus-france', element: <CampusFrancePage /> },
          { path: 'parcoursup', element: <ParcoursupPage /> },
          { path: 'paris-saclay', element: <ParisSaclayPage /> },
          { path: 'historique', element: <DemarchesHistoryPage /> },
        ],
      },
      { path: 'links', element: <CandidateLinksPage /> },
      {
        path: 'my-file',
        element: (
          <CandidateOnlyRoute>
            <DossierLayout />
          </CandidateOnlyRoute>
        ),
        children: [
          { index: true, element: <DossierOverviewPage /> },
          { path: 'personal', element: <DossierPersonalPage /> },
          { path: 'contact', element: <DossierContactPage /> },
          { path: 'academic', element: <DossierAcademicPage /> },
          { path: 'languages', element: <DossierLanguagesPage /> },
          { path: 'study-project', element: <DossierStudyProjectPage /> },
          { path: 'career-project', element: <DossierCareerProjectPage /> },
          { path: 'financing', element: <DossierFinancingPage /> },
          { path: 'guarantor', element: <DossierGuarantorPage /> },
          { path: 'experiences', element: <DossierExperiencesPage /> },
          { path: 'documents', element: <DossierDocumentsPage /> },
          { path: 'counselor', element: <DossierCounselorPage /> },
          { path: 'history', element: <DossierHistoryPage /> },
        ],
      },
      {
        path: 'candidates',
        element: (
          <ProtectedRoute permission="candidates.view">
            <CandidatesPage />
          </ProtectedRoute>
        ),
      },
      {
        path: 'candidates/new',
        element: (
          <ProtectedRoute permission="candidates.create">
            <CandidateCreatePage />
          </ProtectedRoute>
        ),
      },
      {
        path: 'candidates/:id',
        element: (
          <ProtectedRoute permission="candidates.view">
            <CandidateDetailPage />
          </ProtectedRoute>
        ),
      },
      {
        path: 'counselor/availability',
        element: (
          <ProtectedRoute permission="appointments.manage">
            <CounselorAvailabilityPage />
          </ProtectedRoute>
        ),
      },
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
      {
        path: 'admin/reports',
        element: (
          <AdminOnlyRoute>
            <ReportsPage />
          </AdminOnlyRoute>
        ),
      },
      {
        path: 'admin/counselors',
        element: (
          <AdminOnlyRoute>
            <CounselorsPage />
          </AdminOnlyRoute>
        ),
      },
      {
        path: 'admin/matching',
        element: (
          <AdminOnlyRoute>
            <MatchingPage />
          </AdminOnlyRoute>
        ),
      },
    ],
  },
  { path: '*', element: <Navigate to="/" replace /> },
])
