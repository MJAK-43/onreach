import { AppSidebar } from '@/components/layout/AppSidebar'
import { candidateNavItems } from '@/config/navigation'
import { Button } from '@/components/ui/button'

export function CandidateSidebar() {
  return (
    <AppSidebar
      items={candidateNavItems}
      footer={
        <div className="m-3 rounded-xl bg-white/5 p-4">
          <p className="text-sm font-medium text-white">Besoin d&apos;aide ?</p>
          <p className="mt-1 text-xs text-slate-400">
            Votre conseillère est disponible pour vous accompagner.
          </p>
          <Button
            type="button"
            className="mt-3 w-full bg-blue-500 text-white hover:bg-blue-600"
            size="sm"
          >
            Contacter mon conseiller
          </Button>
        </div>
      }
    />
  )
}
