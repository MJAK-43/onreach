import type { LucideIcon } from 'lucide-react'
import {
  BarChart3,
  Bot,
  Briefcase,
  Calendar,
  CalendarCheck,
  CalendarDays,
  CalendarRange,
  ClipboardList,
  CreditCard,
  Euro,
  FileText,
  FolderOpen,
  GraduationCap,
  Home,
  KeyRound,
  LayoutDashboard,
  Link2,
  Mail,
  Map,
  Shield,
  Shuffle,
  UserCog,
  Users,
} from 'lucide-react'

export type NavItemConfig = {
  to: string
  label: string
  icon: LucideIcon
  end?: boolean
  disabled?: boolean
  permission?: string
}

export type NavSectionConfig = {
  title: string
  items: NavItemConfig[]
}

export const candidateNavItems: NavItemConfig[] = [
  { to: '/', label: 'Tableau de bord', icon: LayoutDashboard, end: true },
  { to: '/my-file', label: 'Mon dossier', icon: FolderOpen },
  { to: '/demarches', label: 'Mes démarches', icon: GraduationCap },
  { to: '/payments', label: 'Paiements', icon: CreditCard },
  { to: '/appointments', label: 'Rendez-vous', icon: Calendar },
  { to: '/housing', label: 'Recherche de logement', icon: Home },
  { to: '/my-file/documents', label: 'Documents', icon: FileText },
]

export const staffDashboardItem: NavItemConfig = {
  to: '/',
  label: 'Tableau de bord',
  icon: LayoutDashboard,
  end: true,
}

export const counselorNavSections: NavSectionConfig[] = [
  {
    title: 'Gestion des dossiers',
    items: [
      { to: '/candidates', label: 'Mes candidats', icon: Users, permission: 'candidates.view' },
      {
        to: '/pathways/tracking',
        label: 'Suivi des candidatures',
        icon: CalendarCheck,
        permission: 'applications.view',
      },
      { to: '/housing', label: 'Recherche de logement', icon: Home },
      { to: '/payments', label: 'Paiements', icon: Briefcase },
    ],
  },
  {
    title: 'Agenda & rendez-vous',
    items: [
      {
        to: '/counselor/availability',
        label: 'Calendrier',
        icon: Calendar,
        permission: 'appointments.manage',
      },
      { to: '/appointments', label: 'Rendez-vous', icon: CalendarDays },
      { to: '#', label: 'Ateliers & événements', icon: ClipboardList, disabled: true },
    ],
  },
  {
    title: 'Ressources',
    items: [
      { to: '#', label: 'Calendrier Campus France', icon: CalendarRange, disabled: true },
      { to: '/guides', label: 'Guides & Procédures', icon: FileText },
      { to: '#', label: 'Pièces pour le visa', icon: FileText, disabled: true },
      { to: '#', label: 'Frais à prévoir', icon: Euro, disabled: true },
      { to: '/parcoursup', label: 'Parcoursup / Paris Saclay', icon: Map },
      { to: '/links', label: 'Liens utiles', icon: Link2 },
    ],
  },
  {
    title: 'Outils',
    items: [
      { to: '#', label: 'Documents types', icon: FileText, disabled: true },
      { to: '#', label: "Modèles d'emails", icon: Mail, disabled: true },
      { to: '#', label: 'Agent IA', icon: Bot, disabled: true },
    ],
  },
]

export const adminExtraNavItems: NavItemConfig[] = [
  { to: '/admin/reports', label: 'Rapports statistiques', icon: BarChart3 },
  { to: '/admin/counselors', label: 'Conseillers', icon: UserCog, permission: 'users.view' },
  { to: '/admin/matching', label: 'Matching', icon: Shuffle, permission: 'candidates.edit' },
]

export const systemAdminNavItems: NavItemConfig[] = [
  { to: '/admin/pathway-settings', label: 'Parcours', icon: ClipboardList },
  { to: '/admin/users', label: 'Utilisateurs', icon: Users, permission: 'users.view' },
  { to: '/admin/roles', label: 'Rôles', icon: UserCog, permission: 'roles.view' },
  { to: '/admin/permissions', label: 'Permissions', icon: KeyRound, permission: 'permissions.view' },
  { to: '/admin/security', label: 'Sécurité', icon: Shield },
]
