import { Info } from 'lucide-react'

export function CounselorPendingBanner() {
  return (
    <div
      className="flex gap-3 rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-900"
      role="status"
    >
      <Info className="mt-0.5 h-5 w-5 shrink-0 text-blue-600" aria-hidden />
      <div>
        <p className="font-medium">Conseiller en cours d&apos;attribution</p>
        <p className="mt-1 leading-relaxed text-blue-800/90">
          Votre dossier a bien été créé. Un administrateur vous attribuera un conseiller sous peu.
          En attendant, vous pouvez compléter votre dossier et suivre vos démarches.
        </p>
      </div>
    </div>
  )
}
