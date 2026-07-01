import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { useState } from 'react'
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import {
  createCampaign,
  fetchCampaigns,
  fetchPathwayTemplate,
  fetchPathwayTemplates,
  importPathwayCalendar,
  updateCampaign,
  updatePathwaySubStepTemplate,
  updatePathwayTemplate,
} from '@/lib/pathway-admin-api'
import { fetchPathwaySettings, updatePathwaySetting } from '@/lib/notifications-api'

function formatDueDate(campaignStartDate: string, offsetDays: number | null): string | null {
  if (offsetDays === null) {
    return null
  }
  const start = new Date(`${campaignStartDate}T00:00:00`)
  start.setDate(start.getDate() + offsetDays)
  return start.toLocaleDateString('fr-FR')
}

export function PathwaySettingsPage() {
  const queryClient = useQueryClient()
  const [selectedTemplateId, setSelectedTemplateId] = useState<string | null>(null)
  const [calendarText, setCalendarText] = useState('')
  const [newCampaignYear, setNewCampaignYear] = useState('2027')
  const [syncMessage, setSyncMessage] = useState<string | null>(null)

  const settingsQuery = useQuery({
    queryKey: ['pathway-settings'],
    queryFn: fetchPathwaySettings,
  })

  const campaignsQuery = useQuery({
    queryKey: ['admin-campaigns'],
    queryFn: fetchCampaigns,
  })

  const templatesQuery = useQuery({
    queryKey: ['admin-pathway-templates'],
    queryFn: () => fetchPathwayTemplates(),
  })

  const templateDetailQuery = useQuery({
    queryKey: ['admin-pathway-template', selectedTemplateId],
    queryFn: () => fetchPathwayTemplate(selectedTemplateId!),
    enabled: Boolean(selectedTemplateId),
  })

  const settingsMutation = useMutation({
    mutationFn: ({ code, enabled }: { code: string; enabled: boolean }) =>
      updatePathwaySetting(code, enabled),
    onSuccess: () => {
      void queryClient.invalidateQueries({ queryKey: ['pathway-settings'] })
    },
  })

  const createCampaignMutation = useMutation({
    mutationFn: () =>
      createCampaign({
        name: `Campagne ${newCampaignYear}`,
        year: Number(newCampaignYear),
      }),
    onSuccess: () => {
      void queryClient.invalidateQueries({ queryKey: ['admin-campaigns'] })
      void queryClient.invalidateQueries({ queryKey: ['admin-pathway-templates'] })
    },
  })

  const activateCampaignMutation = useMutation({
    mutationFn: (id: string) => updateCampaign(id, { active: true }),
    onSuccess: () => {
      void queryClient.invalidateQueries({ queryKey: ['admin-campaigns'] })
    },
  })

  const importMutation = useMutation({
    mutationFn: (apply: boolean) =>
      importPathwayCalendar({
        templateId: selectedTemplateId!,
        calendarText,
        apply,
      }),
  })

  return (
    <div className="space-y-6">
      <div>
        <h2 className="text-xl font-semibold">Administration des parcours</h2>
        <p className="mt-1 text-sm text-muted-foreground">
          Campagnes, templates, double validation et import calendrier.
        </p>
      </div>

      <Card>
        <CardHeader>
          <CardTitle>Campagnes</CardTitle>
          <CardDescription>Gérez les campagnes d&apos;admission (2026, 2027…).</CardDescription>
        </CardHeader>
        <CardContent className="space-y-4">
          {campaignsQuery.data?.items.map((campaign) => (
            <div
              key={campaign.id}
              className="flex flex-wrap items-center justify-between gap-3 rounded-lg border border-border p-4"
            >
              <div>
                <p className="font-medium">
                  {campaign.name} ({campaign.year})
                </p>
                <p className="text-xs text-muted-foreground">
                  {campaign.startDate} → {campaign.endDate} — {campaign.templateCount} parcours
                </p>
              </div>
              <div className="flex items-center gap-2">
                <Badge variant={campaign.active ? 'success' : 'default'}>
                  {campaign.active ? 'Active' : 'Inactive'}
                </Badge>
                {!campaign.active && (
                  <Button
                    size="sm"
                    variant="outline"
                    disabled={activateCampaignMutation.isPending}
                    onClick={() => activateCampaignMutation.mutate(campaign.id)}
                  >
                    Activer
                  </Button>
                )}
              </div>
            </div>
          ))}
          <div className="flex flex-wrap items-end gap-2">
            <Input
              value={newCampaignYear}
              onChange={(event) => setNewCampaignYear(event.target.value)}
              className="max-w-[120px]"
              placeholder="Année"
            />
            <Button
              disabled={createCampaignMutation.isPending}
              onClick={() => createCampaignMutation.mutate()}
            >
              Créer campagne
            </Button>
          </div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>Templates de parcours</CardTitle>
          <CardDescription>Consultez et ajustez les libellés et échéances par défaut.</CardDescription>
        </CardHeader>
        <CardContent className="space-y-4">
          <div className="flex flex-wrap gap-2">
            {templatesQuery.data?.items.map((template) => (
              <Button
                key={template.id}
                size="sm"
                variant={selectedTemplateId === template.id ? 'default' : 'outline'}
                onClick={() => setSelectedTemplateId(template.id)}
              >
                {template.name}
              </Button>
            ))}
          </div>

          {templateDetailQuery.data && (
            <div className="space-y-3">
              {syncMessage && (
                <p className="text-sm text-emerald-700" role="status">
                  {syncMessage}
                </p>
              )}
              {templateDetailQuery.data.stages.map((stage) => (
                <div key={stage.id} className="rounded-lg border border-border p-3">
                  <p className="font-medium">{stage.title}</p>
                  <ul className="mt-2 space-y-2">
                    {stage.subSteps.map((subStep) => (
                      <li key={subStep.id} className="flex flex-wrap items-center gap-2 text-sm">
                        <span className="min-w-0 flex-1">{subStep.title}</span>
                        <Input
                          type="number"
                          defaultValue={subStep.defaultDueOffsetDays ?? ''}
                          className="w-24"
                          placeholder="J+"
                          onBlur={(event) => {
                            const value = event.target.value
                            void updatePathwaySubStepTemplate(subStep.id, {
                              defaultDueOffsetDays: value === '' ? null : Number(value),
                            }).then((result) => {
                              const synced = (result as { syncedCount?: number }).syncedCount
                              if (typeof synced === 'number') {
                                setSyncMessage(`${synced} échéance(s) propagée(s) aux candidats.`)
                              }
                              void queryClient.invalidateQueries({
                                queryKey: ['admin-pathway-template', selectedTemplateId],
                              })
                            })
                          }}
                        />
                        <span className="text-xs text-muted-foreground">
                          {formatDueDate(
                            templateDetailQuery.data.campaignStartDate,
                            subStep.defaultDueOffsetDays,
                          ) ?? '—'}
                        </span>
                      </li>
                    ))}
                  </ul>
                </div>
              ))}
              <Button
                variant="outline"
                size="sm"
                onClick={() => {
                  const name = window.prompt('Nouveau nom du parcours', templateDetailQuery.data.name)
                  if (!name) return
                  void updatePathwayTemplate(templateDetailQuery.data.id, { name }).then(() => {
                    void queryClient.invalidateQueries({ queryKey: ['admin-pathway-template', selectedTemplateId] })
                    void queryClient.invalidateQueries({ queryKey: ['admin-pathway-templates'] })
                  })
                }}
              >
                Renommer le parcours
              </Button>
            </div>
          )}
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>Import calendrier</CardTitle>
          <CardDescription>
            Collez un calendrier texte (date — libellé) pour suggérer des échéances.
          </CardDescription>
        </CardHeader>
        <CardContent className="space-y-3">
          <textarea
            className="min-h-32 w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
            value={calendarText}
            onChange={(event) => setCalendarText(event.target.value)}
            placeholder={'15/09 - Ouverture Parcoursup\n20/01 - Clôture vœux'}
            disabled={!selectedTemplateId}
          />
          <div className="flex flex-wrap gap-2">
            <Button
              variant="outline"
              disabled={!selectedTemplateId || importMutation.isPending}
              onClick={() => importMutation.mutate(false)}
            >
              Analyser
            </Button>
            <Button
              disabled={!selectedTemplateId || importMutation.isPending}
              onClick={() => importMutation.mutate(true)}
            >
              Appliquer
            </Button>
          </div>
          {importMutation.data && (
            <p className="text-sm text-muted-foreground">
              {importMutation.data.applied > 0
                ? `${importMutation.data.applied} échéance(s) appliquée(s).`
                : `${importMutation.data.suggestions.length} suggestion(s) trouvée(s).`}
            </p>
          )}
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>Double validation</CardTitle>
          <CardDescription>
            Lorsqu&apos;elle est activée, une étape n&apos;est complète qu&apos;après validation conseiller et admin.
          </CardDescription>
        </CardHeader>
        <CardContent className="space-y-4">
          {settingsQuery.isLoading && <p className="text-sm">Chargement...</p>}
          {settingsQuery.data?.items.map((setting) => (
            <div
              key={setting.code}
              className="flex flex-wrap items-center justify-between gap-3 rounded-lg border border-border p-4"
            >
              <div>
                <p className="font-medium">{setting.label}</p>
                <p className="text-xs text-muted-foreground">{setting.code}</p>
              </div>
              <div className="flex items-center gap-3">
                <Badge variant={setting.doubleValidationEnabled ? 'warning' : 'default'}>
                  {setting.doubleValidationEnabled ? 'Double validation' : 'Validation simple'}
                </Badge>
                <button
                  type="button"
                  role="switch"
                  aria-checked={setting.doubleValidationEnabled}
                  disabled={settingsMutation.isPending}
                  onClick={() =>
                    settingsMutation.mutate({
                      code: setting.code,
                      enabled: !setting.doubleValidationEnabled,
                    })
                  }
                  className={`relative h-6 w-11 rounded-full transition-colors ${
                    setting.doubleValidationEnabled ? 'bg-primary' : 'bg-muted'
                  }`}
                >
                  <span
                    className={`absolute top-0.5 h-5 w-5 rounded-full bg-white shadow transition-transform ${
                      setting.doubleValidationEnabled ? 'left-5' : 'left-0.5'
                    }`}
                  />
                </button>
              </div>
            </div>
          ))}
        </CardContent>
      </Card>
    </div>
  )
}
