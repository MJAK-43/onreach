import { useState } from 'react'
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import {
  ApiError,
  changePassword,
  mfaDisable,
  mfaEnable,
  mfaSetup,
} from '@/lib/api'
import { useCurrentUser } from '@/hooks/useCurrentUser'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from '@/components/ui/card'

export function SecuritySettingsPage() {
  const queryClient = useQueryClient()
  const { data: user } = useCurrentUser()

  const [currentPassword, setCurrentPassword] = useState('')
  const [newPassword, setNewPassword] = useState('')
  const [confirmPassword, setConfirmPassword] = useState('')
  const [passwordError, setPasswordError] = useState<string | null>(null)
  const [passwordSuccess, setPasswordSuccess] = useState<string | null>(null)

  const [mfaCode, setMfaCode] = useState('')
  const [disablePassword, setDisablePassword] = useState('')
  const [disableCode, setDisableCode] = useState('')
  const [mfaError, setMfaError] = useState<string | null>(null)
  const [mfaSuccess, setMfaSuccess] = useState<string | null>(null)

  const mfaSetupQuery = useQuery({
    queryKey: ['mfaSetup'],
    queryFn: mfaSetup,
    enabled: false,
  })

  const changePasswordMutation = useMutation({
    mutationFn: () => changePassword(currentPassword, newPassword),
    onSuccess: (data) => {
      setPasswordSuccess(data.message)
      setPasswordError(null)
      setCurrentPassword('')
      setNewPassword('')
      setConfirmPassword('')
    },
    onError: (err: Error) => {
      setPasswordSuccess(null)
      setPasswordError(err instanceof ApiError ? err.message : 'Erreur lors du changement.')
    },
  })

  const mfaEnableMutation = useMutation({
    mutationFn: () => mfaEnable(mfaCode),
    onSuccess: (data) => {
      setMfaSuccess(data.message)
      setMfaError(null)
      setMfaCode('')
      queryClient.invalidateQueries({ queryKey: ['currentUser'] })
      queryClient.removeQueries({ queryKey: ['mfaSetup'] })
    },
    onError: (err: Error) => {
      setMfaSuccess(null)
      setMfaError(err instanceof ApiError ? err.message : 'Erreur lors de l\'activation MFA.')
    },
  })

  const mfaDisableMutation = useMutation({
    mutationFn: () => mfaDisable(disablePassword, disableCode),
    onSuccess: (data) => {
      setMfaSuccess(data.message)
      setMfaError(null)
      setDisablePassword('')
      setDisableCode('')
      queryClient.invalidateQueries({ queryKey: ['currentUser'] })
    },
    onError: (err: Error) => {
      setMfaSuccess(null)
      setMfaError(err instanceof ApiError ? err.message : 'Erreur lors de la désactivation MFA.')
    },
  })

  function handlePasswordSubmit(e: React.FormEvent) {
    e.preventDefault()
    setPasswordError(null)
    setPasswordSuccess(null)

    if (newPassword !== confirmPassword) {
      setPasswordError('Les mots de passe ne correspondent pas.')
      return
    }

    changePasswordMutation.mutate()
  }

  function handleMfaEnable(e: React.FormEvent) {
    e.preventDefault()
    setMfaError(null)
    setMfaSuccess(null)
    mfaEnableMutation.mutate()
  }

  function handleMfaDisable(e: React.FormEvent) {
    e.preventDefault()
    setMfaError(null)
    setMfaSuccess(null)
    mfaDisableMutation.mutate()
  }

  return (
    <div className="space-y-6">
      <div>
        <h2 className="text-xl font-semibold">Paramètres de sécurité</h2>
        <p className="mt-1 text-sm text-muted-foreground">
          Gérez votre mot de passe et l&apos;authentification à deux facteurs
        </p>
      </div>

      <Card>
        <CardHeader>
          <CardTitle>Changer le mot de passe</CardTitle>
          <CardDescription>
            Utilisez un mot de passe fort d&apos;au moins 12 caractères
          </CardDescription>
        </CardHeader>
        <CardContent>
          <form onSubmit={handlePasswordSubmit} className="max-w-md space-y-4">
            {passwordError && (
              <p className="rounded-md bg-red-50 px-3 py-2 text-sm text-red-700" role="alert">
                {passwordError}
              </p>
            )}
            {passwordSuccess && (
              <p className="rounded-md bg-green-50 px-3 py-2 text-sm text-green-700">
                {passwordSuccess}
              </p>
            )}

            <div className="space-y-2">
              <Label htmlFor="currentPassword">Mot de passe actuel</Label>
              <Input
                id="currentPassword"
                type="password"
                autoComplete="current-password"
                required
                value={currentPassword}
                onChange={(e) => setCurrentPassword(e.target.value)}
              />
            </div>

            <div className="space-y-2">
              <Label htmlFor="newPassword">Nouveau mot de passe</Label>
              <Input
                id="newPassword"
                type="password"
                autoComplete="new-password"
                required
                minLength={12}
                value={newPassword}
                onChange={(e) => setNewPassword(e.target.value)}
              />
            </div>

            <div className="space-y-2">
              <Label htmlFor="confirmPassword">Confirmer le mot de passe</Label>
              <Input
                id="confirmPassword"
                type="password"
                autoComplete="new-password"
                required
                value={confirmPassword}
                onChange={(e) => setConfirmPassword(e.target.value)}
              />
            </div>

            <Button type="submit" disabled={changePasswordMutation.isPending}>
              {changePasswordMutation.isPending ? 'Enregistrement...' : 'Mettre à jour'}
            </Button>
          </form>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>Authentification à deux facteurs (MFA)</CardTitle>
          <CardDescription>
            {user?.mfaEnabled
              ? 'La MFA est actuellement activée sur votre compte.'
              : 'Renforcez la sécurité de votre compte avec la MFA.'}
          </CardDescription>
        </CardHeader>
        <CardContent className="space-y-6">
          {mfaError && (
            <p className="rounded-md bg-red-50 px-3 py-2 text-sm text-red-700" role="alert">
              {mfaError}
            </p>
          )}
          {mfaSuccess && (
            <p className="rounded-md bg-green-50 px-3 py-2 text-sm text-green-700">
              {mfaSuccess}
            </p>
          )}

          {!user?.mfaEnabled && (
            <div className="space-y-4">
              {!mfaSetupQuery.data && (
                <Button
                  variant="outline"
                  onClick={() => mfaSetupQuery.refetch()}
                  disabled={mfaSetupQuery.isFetching}
                >
                  {mfaSetupQuery.isFetching ? 'Génération...' : 'Configurer la MFA'}
                </Button>
              )}

              {mfaSetupQuery.data && (
                <div className="space-y-4">
                  <div className="flex flex-col items-start gap-4 sm:flex-row sm:items-center">
                    <img
                      src={mfaSetupQuery.data.qrCode}
                      alt="QR Code MFA"
                      className="h-40 w-40 rounded-lg border border-border"
                    />
                    <div>
                      <p className="text-sm font-medium">Clé secrète</p>
                      <p className="mt-1 font-mono text-xs text-muted-foreground">
                        {mfaSetupQuery.data.secret}
                      </p>
                    </div>
                  </div>

                  <form onSubmit={handleMfaEnable} className="max-w-md space-y-4">
                    <div className="space-y-2">
                      <Label htmlFor="mfaCode">Code de vérification</Label>
                      <Input
                        id="mfaCode"
                        type="text"
                        inputMode="numeric"
                        required
                        placeholder="000000"
                        value={mfaCode}
                        onChange={(e) => setMfaCode(e.target.value)}
                      />
                    </div>
                    <Button type="submit" disabled={mfaEnableMutation.isPending}>
                      {mfaEnableMutation.isPending ? 'Activation...' : 'Activer la MFA'}
                    </Button>
                  </form>
                </div>
              )}
            </div>
          )}

          {user?.mfaEnabled && (
            <form onSubmit={handleMfaDisable} className="max-w-md space-y-4">
              <div className="space-y-2">
                <Label htmlFor="disablePassword">Mot de passe</Label>
                <Input
                  id="disablePassword"
                  type="password"
                  autoComplete="current-password"
                  required
                  value={disablePassword}
                  onChange={(e) => setDisablePassword(e.target.value)}
                />
              </div>
              <div className="space-y-2">
                <Label htmlFor="disableCode">Code MFA ou code de récupération</Label>
                <Input
                  id="disableCode"
                  type="text"
                  required
                  value={disableCode}
                  onChange={(e) => setDisableCode(e.target.value)}
                />
              </div>
              <Button
                type="submit"
                variant="outline"
                disabled={mfaDisableMutation.isPending}
              >
                {mfaDisableMutation.isPending ? 'Désactivation...' : 'Désactiver la MFA'}
              </Button>
            </form>
          )}
        </CardContent>
      </Card>
    </div>
  )
}
