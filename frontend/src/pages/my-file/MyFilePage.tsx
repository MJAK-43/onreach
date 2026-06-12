import { useCallback, useMemo, useState } from 'react'
import { Link } from 'react-router-dom'
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import {
  Calendar,
  History,
  Mail,
  MessageCircle,
  Upload,
} from 'lucide-react'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Progress } from '@/components/ui/progress'
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs'
import { Badge } from '@/components/ui/badge'
import { useAutoSave } from '@/hooks/useAutoSave'
import {
  deleteMyDocument,
  DOCUMENT_TYPES,
  fetchMyHistory,
  fetchMyProfile,
  fetchMyProfileDocuments,
  openDocumentPreview,
  type CandidateProfile,
  type ProfileDocument,
  updateMyProfile,
  uploadMyDocument,
} from '@/lib/profile-api'

type EditableSection = 'personal' | 'contact' | 'studyProject' | 'careerProject'

const defaultAcademic = (base: CandidateProfile): NonNullable<CandidateProfile['academic']> => ({
  highestDiploma: null,
  institutionName: null,
  graduationYear: null,
  overallAverage: null,
  ranking: null,
  specialty: null,
  academicAchievements: null,
  records: [],
  ...base.academic,
})

const defaultLanguages = (base: CandidateProfile): NonNullable<CandidateProfile['languages']> => ({
  frenchLevel: null,
  englishLevel: null,
  otherLanguages: [],
  certificates: [],
  ...base.languages,
})

const defaultFinancing = (base: CandidateProfile): NonNullable<CandidateProfile['financing']> => ({
  type: 'personal',
  availableBudget: null,
  plannedAmount: null,
  description: null,
  guarantors: [],
  ...base.financing,
})

const EMPTY_SAVE_PAYLOAD = {
  personal: {} as CandidateProfile['personal'],
  contact: {} as CandidateProfile['contact'],
  studyProject: {} as CandidateProfile['studyProject'],
  careerProject: {} as CandidateProfile['careerProject'],
  academic: null as CandidateProfile['academic'],
  languages: null as CandidateProfile['languages'],
  financing: null as CandidateProfile['financing'],
  experiences: [] as CandidateProfile['experiences'],
}

const STATUS_LABELS: Record<string, string> = {
  missing: 'Manquant',
  uploaded: 'Téléversé',
  validated: 'Validé',
  rejected: 'Refusé',
}

function SaveIndicator({ status }: { status: string }) {
  const map = {
    idle: { text: '', className: '' },
    saving: { text: 'Enregistrement...', className: 'text-blue-600' },
    saved: { text: 'Sauvegardé', className: 'text-emerald-600' },
    error: { text: 'Erreur de sauvegarde', className: 'text-red-600' },
  } as const
  const item = map[status as keyof typeof map] ?? map.idle
  if (!item.text) return null
  return <span className={`text-xs font-medium ${item.className}`}>{item.text}</span>
}

function Field({
  label,
  value,
  onChange,
  type = 'text',
}: {
  label: string
  value: string
  onChange: (v: string) => void
  type?: string
}) {
  return (
    <div className="space-y-1.5">
      <Label>{label}</Label>
      <Input type={type} value={value} onChange={(e) => onChange(e.target.value)} />
    </div>
  )
}

function TextAreaField({
  label,
  value,
  onChange,
}: {
  label: string
  value: string
  onChange: (v: string) => void
}) {
  return (
    <div className="space-y-1.5">
      <Label>{label}</Label>
      <textarea
        className="flex min-h-[100px] w-full rounded-md border border-slate-200 bg-white px-3 py-2 text-sm"
        value={value}
        onChange={(e) => onChange(e.target.value)}
      />
    </div>
  )
}

export function MyFilePage() {
  const queryClient = useQueryClient()
  const [tab, setTab] = useState('overview')
  const [draft, setDraft] = useState<CandidateProfile | null>(null)
  const [uploadProgress, setUploadProgress] = useState<Record<string, number>>({})

  const profileQuery = useQuery({
    queryKey: ['my-profile'],
    queryFn: fetchMyProfile,
  })

  const documentsQuery = useQuery({
    queryKey: ['my-profile-documents'],
    queryFn: fetchMyProfileDocuments,
  })

  const historyQuery = useQuery({
    queryKey: ['my-history'],
    queryFn: fetchMyHistory,
    enabled: tab === 'history',
  })

  const profile = draft ?? profileQuery.data

  const savePayload = useMemo(() => {
    if (!profile) return null
    return {
      personal: profile.personal,
      contact: profile.contact,
      studyProject: profile.studyProject,
      careerProject: profile.careerProject,
      academic: profile.academic,
      languages: profile.languages,
      financing: profile.financing,
      experiences: profile.experiences,
    }
  }, [profile])

  const saveMutation = useMutation({
    mutationFn: (payload: NonNullable<typeof savePayload>) => updateMyProfile(payload as Partial<CandidateProfile>),
    onSuccess: (data) => {
      setDraft(data)
      void queryClient.invalidateQueries({ queryKey: ['my-profile'] })
    },
  })

  const { status: saveStatus } = useAutoSave(
    savePayload ?? EMPTY_SAVE_PAYLOAD,
    useCallback(
      async (p) => {
        if (!savePayload) return
        await saveMutation.mutateAsync(p)
      },
      [saveMutation, savePayload],
    ),
    5000,
  )

  if (profileQuery.isLoading || !profile) {
    return (
      <div className="flex min-h-[40vh] items-center justify-center">
        <p className="text-sm text-slate-500">Chargement de votre dossier...</p>
      </div>
    )
  }

  const c = profile.completion
  const counselor = profile.counselor

  const patch = (section: EditableSection, key: string, value: unknown) => {
    setDraft((prev: CandidateProfile | null) => {
      const base = prev ?? profile
      const sectionData = { ...(base[section] as Record<string, unknown>), [key]: value }
      return { ...base, [section]: sectionData }
    })
  }

  const handleUpload = async (files: FileList | null, type: string) => {
    if (!files?.length) return
    for (const file of Array.from(files)) {
      setUploadProgress((p) => ({ ...p, [type]: 0 }))
      await uploadMyDocument(file, type, (pct) => setUploadProgress((p) => ({ ...p, [type]: pct })))
      setUploadProgress((p) => ({ ...p, [type]: 100 }))
    }
    void queryClient.invalidateQueries({ queryKey: ['my-profile-documents'] })
    void queryClient.invalidateQueries({ queryKey: ['my-profile'] })
  }

  const docs: ProfileDocument[] = documentsQuery.data ?? []

  return (
    <div className="mx-auto max-w-6xl space-y-6">
      <div className="flex flex-wrap items-start justify-between gap-4">
        <div>
          <h1 className="text-2xl font-bold text-slate-900">Mon dossier</h1>
          <p className="mt-1 text-sm text-slate-500">
            Référence {profile.referenceNumber} · Dernière mise à jour{' '}
            {new Date(profile.updatedAt).toLocaleDateString('fr-FR')}
          </p>
        </div>
        <SaveIndicator status={saveStatus} />
      </div>

      <Tabs value={tab} onValueChange={setTab}>
        <TabsList className="max-w-full overflow-x-auto">
          <TabsTrigger value="overview">Vue générale</TabsTrigger>
          <TabsTrigger value="personal">Informations</TabsTrigger>
          <TabsTrigger value="contact">Coordonnées</TabsTrigger>
          <TabsTrigger value="academic">Académique</TabsTrigger>
          <TabsTrigger value="languages">Langues</TabsTrigger>
          <TabsTrigger value="study">Projet d&apos;études</TabsTrigger>
          <TabsTrigger value="career">Projet pro</TabsTrigger>
          <TabsTrigger value="financing">Financement</TabsTrigger>
          <TabsTrigger value="documents">Documents</TabsTrigger>
          <TabsTrigger value="counselor">Conseiller</TabsTrigger>
          <TabsTrigger value="history">Historique</TabsTrigger>
        </TabsList>

        <TabsContent value="overview">
          <div className="grid gap-6 lg:grid-cols-[280px_1fr]">
            <Card>
              <CardContent className="flex flex-col items-center pt-6 text-center">
                <div className="flex h-24 w-24 items-center justify-center rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 text-2xl font-bold text-white">
                  {profile.personal.firstName?.[0]}{profile.personal.lastName?.[0]}
                </div>
                <h2 className="mt-4 text-lg font-semibold">
                  {profile.personal.firstName} {profile.personal.lastName}
                </h2>
                <p className="text-sm text-slate-500">{profile.personal.nationality}</p>
                {counselor && (
                  <p className="mt-2 text-xs text-slate-500">
                    Conseillère : {counselor.firstName} {counselor.lastName}
                  </p>
                )}
              </CardContent>
            </Card>
            <div className="space-y-4">
              <Card>
                <CardHeader>
                  <CardTitle>Complétude du dossier</CardTitle>
                  <CardDescription>Indicateurs calculés automatiquement</CardDescription>
                </CardHeader>
                <CardContent className="space-y-4">
                  {[
                    { label: 'Profil', value: c.profile },
                    { label: 'Documents', value: c.documents },
                    { label: 'Académique', value: c.academic },
                    { label: 'Financement', value: c.financing },
                  ].map((item) => (
                    <div key={item.label}>
                      <div className="mb-1 flex justify-between text-sm">
                        <span>{item.label}</span>
                        <span className="font-semibold">{item.value}%</span>
                      </div>
                      <Progress value={item.value} />
                    </div>
                  ))}
                  <div className="rounded-xl bg-blue-50 p-4">
                    <p className="text-sm text-blue-800">Complétude globale</p>
                    <p className="text-3xl font-bold text-blue-900">{c.global}%</p>
                  </div>
                </CardContent>
              </Card>
            </div>
          </div>
        </TabsContent>

        <TabsContent value="personal">
          <Card>
            <CardHeader><CardTitle>Informations personnelles</CardTitle></CardHeader>
            <CardContent className="grid gap-4 sm:grid-cols-2">
              <Field label="Nom" value={String(profile.personal.lastName ?? '')} onChange={(v) => patch('personal', 'lastName', v)} />
              <Field label="Prénom" value={String(profile.personal.firstName ?? '')} onChange={(v) => patch('personal', 'firstName', v)} />
              <Field label="Sexe" value={String(profile.personal.gender ?? '')} onChange={(v) => patch('personal', 'gender', v)} />
              <Field label="Date de naissance" type="date" value={String(profile.personal.dateOfBirth ?? '')} onChange={(v) => patch('personal', 'dateOfBirth', v)} />
              <Field label="Lieu de naissance" value={String(profile.personal.placeOfBirth ?? '')} onChange={(v) => patch('personal', 'placeOfBirth', v)} />
              <Field label="Nationalité" value={String(profile.personal.nationality ?? '')} onChange={(v) => patch('personal', 'nationality', v)} />
              <Field label="Situation matrimoniale" value={String(profile.personal.maritalStatus ?? '')} onChange={(v) => patch('personal', 'maritalStatus', v)} />
              <Field label="N° passeport" value={String(profile.personal.passportNumber ?? '')} onChange={(v) => patch('personal', 'passportNumber', v)} />
              <Field label="Délivrance passeport" type="date" value={String(profile.personal.passportIssuedAt ?? '')} onChange={(v) => patch('personal', 'passportIssuedAt', v)} />
              <Field label="Expiration passeport" type="date" value={String(profile.personal.passportExpiresAt ?? '')} onChange={(v) => patch('personal', 'passportExpiresAt', v)} />
              <Field label="Pays délivrance" value={String(profile.personal.passportCountry ?? '')} onChange={(v) => patch('personal', 'passportCountry', v)} />
              <Field label="N° CNI" value={String(profile.personal.identityCardNumber ?? '')} onChange={(v) => patch('personal', 'identityCardNumber', v)} />
            </CardContent>
          </Card>
        </TabsContent>

        <TabsContent value="contact">
          <Card>
            <CardHeader><CardTitle>Coordonnées</CardTitle></CardHeader>
            <CardContent className="grid gap-4 sm:grid-cols-2">
              <Field label="Adresse" value={String(profile.contact.address ?? '')} onChange={(v) => patch('contact', 'address', v)} />
              <Field label="Ville" value={String(profile.contact.city ?? '')} onChange={(v) => patch('contact', 'city', v)} />
              <Field label="Région" value={String(profile.contact.region ?? '')} onChange={(v) => patch('contact', 'region', v)} />
              <Field label="Code postal" value={String(profile.contact.postalCode ?? '')} onChange={(v) => patch('contact', 'postalCode', v)} />
              <Field label="Pays" value={String(profile.contact.country ?? '')} onChange={(v) => patch('contact', 'country', v)} />
              <Field label="Téléphone" value={String(profile.contact.phone ?? '')} onChange={(v) => patch('contact', 'phone', v)} />
              <Field label="WhatsApp" value={String(profile.contact.whatsapp ?? '')} onChange={(v) => patch('contact', 'whatsapp', v)} />
              <Field label="Email principal" value={String(profile.contact.email ?? '')} onChange={(v) => patch('contact', 'email', v)} />
              <Field label="Email secondaire" value={String(profile.contact.secondaryEmail ?? '')} onChange={(v) => patch('contact', 'secondaryEmail', v)} />
            </CardContent>
          </Card>
        </TabsContent>

        <TabsContent value="academic">
          <Card>
            <CardHeader><CardTitle>Parcours académique</CardTitle></CardHeader>
            <CardContent className="grid gap-4 sm:grid-cols-2">
              <Field label="Diplôme le plus élevé" value={String(profile.academic?.highestDiploma ?? '')} onChange={(v) => setDraft((p) => ({ ...(p ?? profile), academic: { ...defaultAcademic(p ?? profile), highestDiploma: v } }))} />
              <Field label="Établissement" value={String(profile.academic?.institutionName ?? '')} onChange={(v) => setDraft((p) => ({ ...(p ?? profile), academic: { ...defaultAcademic(p ?? profile), institutionName: v } }))} />
              <Field label="Année" value={String(profile.academic?.graduationYear ?? '')} onChange={(v) => setDraft((p) => ({ ...(p ?? profile), academic: { ...defaultAcademic(p ?? profile), graduationYear: Number(v) || null } }))} />
              <Field label="Moyenne" value={String(profile.academic?.overallAverage ?? '')} onChange={(v) => setDraft((p) => ({ ...(p ?? profile), academic: { ...defaultAcademic(p ?? profile), overallAverage: v } }))} />
            </CardContent>
          </Card>
        </TabsContent>

        <TabsContent value="languages">
          <Card>
            <CardHeader><CardTitle>Langues</CardTitle></CardHeader>
            <CardContent className="grid gap-4 sm:grid-cols-2">
              <Field label="Niveau français" value={String(profile.languages?.frenchLevel ?? '')} onChange={(v) => setDraft((p) => ({ ...(p ?? profile), languages: { ...defaultLanguages(p ?? profile), frenchLevel: v } }))} />
              <Field label="Niveau anglais" value={String(profile.languages?.englishLevel ?? '')} onChange={(v) => setDraft((p) => ({ ...(p ?? profile), languages: { ...defaultLanguages(p ?? profile), englishLevel: v } }))} />
            </CardContent>
          </Card>
        </TabsContent>

        <TabsContent value="study">
          <Card>
            <CardHeader><CardTitle>Projet d&apos;études</CardTitle></CardHeader>
            <CardContent className="grid gap-4 sm:grid-cols-2">
              <Field label="Domaine" value={String(profile.studyProject.domain ?? '')} onChange={(v) => patch('studyProject', 'domain', v)} />
              <Field label="Spécialité" value={String(profile.studyProject.specialty ?? '')} onChange={(v) => patch('studyProject', 'specialty', v)} />
              <Field label="Niveau" value={String(profile.studyProject.level ?? '')} onChange={(v) => patch('studyProject', 'level', v)} />
              <Field label="Pays visé" value={String(profile.studyProject.targetCountry ?? '')} onChange={(v) => patch('studyProject', 'targetCountry', v)} />
              <div className="sm:col-span-2">
                <TextAreaField label="Description" value={String(profile.studyProject.description ?? '')} onChange={(v) => patch('studyProject', 'description', v)} />
              </div>
            </CardContent>
          </Card>
        </TabsContent>

        <TabsContent value="career">
          <Card>
            <CardHeader><CardTitle>Projet professionnel</CardTitle></CardHeader>
            <CardContent className="grid gap-4 sm:grid-cols-2">
              <Field label="Métier visé" value={String(profile.careerProject.targetJob ?? '')} onChange={(v) => patch('careerProject', 'targetJob', v)} />
              <Field label="Secteur" value={String(profile.careerProject.sector ?? '')} onChange={(v) => patch('careerProject', 'sector', v)} />
              <div className="sm:col-span-2">
                <TextAreaField label="Objectifs" value={String(profile.careerProject.objectives ?? '')} onChange={(v) => patch('careerProject', 'objectives', v)} />
              </div>
              <div className="sm:col-span-2">
                <TextAreaField label="Description" value={String(profile.careerProject.description ?? '')} onChange={(v) => patch('careerProject', 'description', v)} />
              </div>
            </CardContent>
          </Card>
        </TabsContent>

        <TabsContent value="financing">
          <Card>
            <CardHeader><CardTitle>Financement & garant</CardTitle></CardHeader>
            <CardContent className="grid gap-4 sm:grid-cols-2">
              <Field label="Type" value={String(profile.financing?.type ?? '')} onChange={(v) => setDraft((p) => ({ ...(p ?? profile), financing: { ...defaultFinancing(p ?? profile), type: v } }))} />
              <Field label="Budget disponible" value={String(profile.financing?.availableBudget ?? '')} onChange={(v) => setDraft((p) => ({ ...(p ?? profile), financing: { ...defaultFinancing(p ?? profile), availableBudget: v } }))} />
              <Field label="Montant prévu" value={String(profile.financing?.plannedAmount ?? '')} onChange={(v) => setDraft((p) => ({ ...(p ?? profile), financing: { ...defaultFinancing(p ?? profile), plannedAmount: v } }))} />
              <div className="sm:col-span-2">
                <TextAreaField label="Description" value={String(profile.financing?.description ?? '')} onChange={(v) => setDraft((p) => ({ ...(p ?? profile), financing: { ...defaultFinancing(p ?? profile), description: v } }))} />
              </div>
            </CardContent>
          </Card>
        </TabsContent>

        <TabsContent value="documents">
          <Card>
            <CardHeader>
              <CardTitle>Documents associés</CardTitle>
              <CardDescription>Glissez-déposez vos fichiers (PDF, JPG, PNG, WEBP, DOCX — max 10 Mo)</CardDescription>
            </CardHeader>
            <CardContent className="space-y-4">
              <label className="flex cursor-pointer flex-col items-center justify-center rounded-2xl border-2 border-dashed border-slate-200 bg-slate-50 px-6 py-10 text-center hover:border-blue-300 hover:bg-blue-50/50">
                <Upload className="mb-2 h-8 w-8 text-slate-400" />
                <span className="text-sm font-medium text-slate-700">Glisser-déposer vos documents</span>
                <input type="file" multiple className="hidden" onChange={(e) => handleUpload(e.target.files, 'other')} />
              </label>
              <div className="grid gap-3">
                {DOCUMENT_TYPES.map((dt) => {
                  const doc = docs.find((d) => d.type === dt.value)
                  const progress = uploadProgress[dt.value]
                  return (
                    <div key={dt.value} className="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-slate-100 p-4">
                      <div>
                        <p className="font-medium text-slate-900">{dt.label}</p>
                        <p className="text-xs text-slate-500">{doc?.originalFilename ?? 'Aucun fichier'}</p>
                        {progress !== undefined && progress < 100 && (
                          <Progress value={progress} className="mt-2 h-1 w-32" />
                        )}
                      </div>
                      <div className="flex items-center gap-2">
                        <Badge variant={doc?.status === 'validated' ? 'success' : 'default'}>
                          {STATUS_LABELS[doc?.status ?? 'missing']}
                        </Badge>
                        <label>
                          <Button type="button" size="sm" variant="outline" asChild>
                            <span>Téléverser</span>
                          </Button>
                          <input type="file" className="hidden" onChange={(e) => { void handleUpload(e.target.files, dt.value) }} />
                        </label>
                        {doc?.id && doc.status !== 'validated' && (
                          <Button type="button" size="sm" variant="ghost" onClick={() => void deleteMyDocument(doc.id).then(() => documentsQuery.refetch())}>
                            Supprimer
                          </Button>
                        )}
                        {doc?.id && doc.originalFilename && (
                          <Button
                            type="button"
                            size="sm"
                            variant="ghost"
                            onClick={() => void openDocumentPreview(doc.id)}
                          >
                            Aperçu
                          </Button>
                        )}
                      </div>
                    </div>
                  )
                })}
              </div>
            </CardContent>
          </Card>
        </TabsContent>

        <TabsContent value="counselor">
          {counselor ? (
            <Card>
              <CardContent className="flex flex-col items-center gap-4 pt-8 sm:flex-row sm:items-start">
                <div className="flex h-16 w-16 items-center justify-center rounded-full bg-blue-600 text-lg font-bold text-white">
                  {counselor.firstName[0]}{counselor.lastName[0]}
                </div>
                <div className="flex-1 text-center sm:text-left">
                  <h3 className="text-lg font-semibold">{counselor.firstName} {counselor.lastName}</h3>
                  <p className="mt-1 flex items-center justify-center gap-1 text-sm text-slate-600 sm:justify-start">
                    <Mail className="h-4 w-4" /> {counselor.email}
                  </p>
                  <div className="mt-4 flex flex-wrap justify-center gap-2 sm:justify-start">
                    <Button variant="outline" size="sm" asChild>
                      <a href={`mailto:${counselor.email}`}><MessageCircle className="mr-1 h-4 w-4" /> Envoyer message</a>
                    </Button>
                    <Button size="sm" asChild>
                      <Link to="/appointments"><Calendar className="mr-1 h-4 w-4" /> Prendre rendez-vous</Link>
                    </Button>
                  </div>
                </div>
              </CardContent>
            </Card>
          ) : (
            <p className="text-sm text-slate-500">Aucune conseillère assignée pour le moment.</p>
          )}
        </TabsContent>

        <TabsContent value="history">
          <Card>
            <CardHeader><CardTitle className="flex items-center gap-2"><History className="h-5 w-5" /> Historique</CardTitle></CardHeader>
            <CardContent>
              {historyQuery.isLoading ? (
                <p className="text-sm text-slate-500">Chargement...</p>
              ) : (
                <ul className="space-y-3">
                  {(historyQuery.data ?? []).map((entry) => (
                    <li key={entry.id} className="rounded-lg border border-slate-100 px-4 py-3">
                      <p className="text-sm font-medium text-slate-900">{entry.description}</p>
                      <p className="text-xs text-slate-500">
                        {new Date(entry.occurredAt).toLocaleString('fr-FR')}
                        {entry.author ? ` · ${entry.author}` : ''}
                      </p>
                    </li>
                  ))}
                </ul>
              )}
            </CardContent>
          </Card>
        </TabsContent>
      </Tabs>
    </div>
  )
}
