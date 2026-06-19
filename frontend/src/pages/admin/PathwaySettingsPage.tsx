import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { Badge } from '@/components/ui/badge'
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from '@/components/ui/card'
import { fetchPathwaySettings, updatePathwaySetting } from '@/lib/notifications-api'

export function PathwaySettingsPage() {
  const queryClient = useQueryClient()
  const settingsQuery = useQuery({
    queryKey: ['pathway-settings'],
    queryFn: fetchPathwaySettings,
  })

  const mutation = useMutation({
    mutationFn: ({ code, enabled }: { code: string; enabled: boolean }) =>
      updatePathwaySetting(code, enabled),
    onSuccess: () => {
      void queryClient.invalidateQueries({ queryKey: ['pathway-settings'] })
    },
  })

  return (
    <div className="space-y-6">
      <div>
        <h2 className="text-xl font-semibold">Paramètres des parcours</h2>
        <p className="mt-1 text-sm text-muted-foreground">
          Activez la double validation (conseiller + administrateur) par parcours.
        </p>
      </div>

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
                  disabled={mutation.isPending}
                  onClick={() =>
                    mutation.mutate({
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
