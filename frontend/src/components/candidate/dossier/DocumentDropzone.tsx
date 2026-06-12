import { useCallback, useState } from 'react'
import { Upload } from 'lucide-react'
import { Button } from '@/components/ui/button'
import { Label } from '@/components/ui/label'
import { Progress } from '@/components/ui/progress'
import { DOCUMENT_TYPES, uploadMyDocument, type MyDocument } from '@/lib/profile-api'
import { cn } from '@/lib/utils'

interface DocumentDropzoneProps {
  onUploaded: (doc: MyDocument) => void
  replaceDocumentId?: string
  defaultType?: string
}

export function DocumentDropzone({ onUploaded, replaceDocumentId, defaultType = 'other' }: DocumentDropzoneProps) {
  const [type, setType] = useState(defaultType)
  const [dragOver, setDragOver] = useState(false)
  const [progress, setProgress] = useState<number | null>(null)
  const [error, setError] = useState<string | null>(null)

  const handleFiles = useCallback(
    async (files: FileList | null) => {
      if (!files?.length) {
        return
      }
      setError(null)
      for (const file of Array.from(files)) {
        try {
          setProgress(0)
          const doc = await uploadMyDocument(file, type, setProgress, replaceDocumentId)
          onUploaded(doc)
        } catch (e) {
          setError(e instanceof Error ? e.message : 'Échec du téléversement')
        } finally {
          setProgress(null)
        }
      }
    },
    [type, onUploaded, replaceDocumentId],
  )

  return (
    <div className="space-y-4">
      <div className="space-y-2">
        <Label htmlFor="doc-type">Type de document</Label>
        <select
          id="doc-type"
          className="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
          value={type}
          onChange={(e) => setType(e.target.value)}
        >
          {DOCUMENT_TYPES.map((t) => (
            <option key={t.value} value={t.value}>
              {t.label}
            </option>
          ))}
        </select>
      </div>

      <div
        className={cn(
          'rounded-xl border-2 border-dashed p-8 text-center transition-colors',
          dragOver ? 'border-blue-500 bg-blue-50/50 dark:bg-blue-950/30' : 'border-slate-200 dark:border-slate-700',
        )}
        onDragOver={(e) => {
          e.preventDefault()
          setDragOver(true)
        }}
        onDragLeave={() => setDragOver(false)}
        onDrop={(e) => {
          e.preventDefault()
          setDragOver(false)
          void handleFiles(e.dataTransfer.files)
        }}
      >
        <Upload className="mx-auto h-10 w-10 text-slate-400" />
        <p className="mt-3 text-sm font-medium">Glisser-déposer vos documents</p>
        <p className="mt-1 text-xs text-muted-foreground">PDF, JPG, PNG, WEBP, DOCX — max 10 Mo</p>
        <label className="mt-4 inline-block">
          <Button type="button" variant="outline" size="sm" asChild>
            <span>Parcourir…</span>
          </Button>
          <input
            type="file"
            className="sr-only"
            multiple
            accept=".pdf,.jpg,.jpeg,.png,.webp,.docx,application/pdf,image/*"
            onChange={(e) => void handleFiles(e.target.files)}
          />
        </label>
      </div>

      {progress !== null ? (
        <div className="space-y-1">
          <p className="text-xs text-muted-foreground">Téléversement… {progress}%</p>
          <Progress value={progress} className="h-2" />
        </div>
      ) : null}

      {error ? <p className="text-sm text-red-600">{error}</p> : null}
    </div>
  )
}
