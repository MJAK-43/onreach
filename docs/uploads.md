# Uploads — Mon dossier

## Contraintes

- Taille max : **10 Mo**
- MIME autorisés : PDF, JPEG, PNG, WEBP, DOCX
- Validation : `DocumentUploadValidator` (backend)

## Stockage

Actuellement : système de fichiers local via `DocumentStorageService`.  
Prévu : migration MinIO/S3 (même interface de service).

## Frontend

- Upload XHR avec progression (`uploadMyDocument`)
- Drag & drop sur la page Documents
- Aperçu authentifié via `openDocumentPreview` (Bearer JWT)

## Sécurité

- Contrôle type et taille côté serveur
- RBAC : `documents.upload`, `documents.view`
- Scan antivirus : hook préparé pour sprint futur
