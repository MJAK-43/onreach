export const GUIDE_CARDS = [
  {
    title: 'Calendrier Campus France',
    subtitle: 'Dates clés 2024',
    color: 'from-blue-600 to-blue-700',
    link: 'Voir le calendrier',
    to: '/procedure',
  },
  {
    title: 'Frais à prévoir',
    subtitle: 'Budget études France',
    color: 'from-emerald-600 to-teal-700',
    link: 'Estimer mes frais',
    to: '/payments',
  },
  {
    title: 'Pièces pour le visa',
    subtitle: 'Liste complète',
    color: 'from-violet-600 to-purple-700',
    link: 'Voir la liste',
    to: '/guides',
  },
  {
    title: 'Étapes d\'accompagnement',
    subtitle: 'Votre parcours',
    color: 'from-orange-500 to-amber-600',
    link: 'Voir les étapes',
    to: '/procedure',
  },
  {
    title: 'Parcoursup / Paris Saclay',
    subtitle: 'Procédures spéciales',
    color: 'from-pink-500 to-rose-600',
    link: 'En savoir plus',
    to: '/parcoursup',
  },
  {
    title: 'Liens utiles',
    subtitle: 'Ressources officielles',
    color: 'from-cyan-600 to-sky-700',
    link: 'Accéder',
    to: '/links',
  },
] as const

export const PAYMENT_TRANCHES = [
  { label: 'Tranche 1 — Inscription', amount: '250 €', status: 'paid' as const, date: '15/02/2024' },
  { label: 'Tranche 2 — Accompagnement', amount: '250 €', status: 'paid' as const, date: '10/03/2024' },
  { label: 'Tranche 3 — Visa', amount: '250 €', status: 'pending' as const },
]

export const USEFUL_LINKS = [
  { label: 'Campus France', url: 'https://www.campusfrance.org/', category: 'Admission' },
  { label: 'France Visas', url: 'https://france-visas.gouv.fr/', category: 'Visa' },
  { label: 'Parcoursup', url: 'https://www.parcoursup.fr/', category: 'Parcoursup' },
  { label: 'Université Paris-Saclay', url: 'https://www.universite-paris-saclay.fr/', category: 'Paris-Saclay' },
  { label: 'Étudier en France', url: 'https://www.campusfrance.org/fr/etudier-en-france', category: 'Ressources' },
  { label: 'CAF — Aide au logement', url: 'https://www.caf.fr/', category: 'Logement' },
]

export const HOUSING_LISTINGS = [
  {
    title: 'Résidence étudiante CROUS',
    city: 'Massy',
    price: '420 €/mois',
    type: 'Studio',
    status: 'Disponible',
  },
  {
    title: 'Colocation 3 chambres',
    city: 'Orsay',
    price: '550 €/mois',
    type: 'Chambre',
    status: 'Visite proposée',
  },
  {
    title: 'Studio meublé',
    city: 'Palaiseau',
    price: '680 €/mois',
    type: 'Studio',
    status: 'En attente',
  },
]

export const GUIDE_ARTICLES = [
  {
    title: 'Préparer son entretien Campus France',
    duration: '8 min',
    category: 'Campus France',
  },
  {
    title: 'Documents obligatoires pour le visa long séjour',
    duration: '12 min',
    category: 'Visa',
  },
  {
    title: 'Comprendre les frais de scolarité et de vie',
    duration: '6 min',
    category: 'Budget',
  },
  {
    title: 'S\'installer en France : premières démarches',
    duration: '10 min',
    category: 'Installation',
  },
]

export const PARCOURSUP_WISHES = [
  { rank: 1, program: 'Licence Informatique — Université Paris-Saclay', status: 'Admis' },
  { rank: 2, program: 'Licence MIASHS — Université Paris-Saclay', status: 'Liste d\'attente' },
  { rank: 3, program: 'Licence Mathématiques — Université d\'Évry', status: 'En attente' },
]

export const PARIS_SACLAY_STEPS = [
  { step: 'Candidature en ligne', status: 'done', date: '15/01/2024' },
  { step: 'Dossier académique transmis', status: 'done', date: '28/01/2024' },
  { step: 'Examen du dossier', status: 'done', date: '20/03/2024' },
  { step: 'Admission confirmée', status: 'current', date: '10/05/2024' },
  { step: 'Inscription administrative', status: 'upcoming' },
]
