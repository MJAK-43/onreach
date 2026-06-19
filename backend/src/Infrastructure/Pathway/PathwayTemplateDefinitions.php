<?php

declare(strict_types=1);

namespace App\Infrastructure\Pathway;

use App\Domain\Pathway\Enum\PathwayCode;
use App\Domain\Pathway\Enum\StudyApplicationType;

/**
 * Définitions des parcours préchargés (campagne 2026).
 *
 * @phpstan-type PathwayStageDefinition array{
 *     title: string,
 *     description: string|null,
 *     subSteps: list<array{title: string, description: string|null, required: bool, dueOffsetDays: int|null}>
 * }
 * @phpstan-type PathwayDefinition array{
 *     code: PathwayCode,
 *     name: string,
 *     eligibleStudyTypes: list<StudyApplicationType>,
 *     stages: list<PathwayStageDefinition>
 * }
 */
final class PathwayTemplateDefinitions
{
    /**
     * @return list<PathwayDefinition>
     */
    public static function all(): array
    {
        return [
            self::campusFrance(),
            self::parcoursup(),
            self::parisSaclay(),
        ];
    }

    /**
     * @return list<PathwayCode>
     */
    public static function codesForStudyType(StudyApplicationType $type): array
    {
        return match ($type) {
            StudyApplicationType::FIRST_YEAR => [PathwayCode::PARCOURSUP, PathwayCode::CAMPUS_FRANCE],
            StudyApplicationType::CONTINUING => [PathwayCode::CAMPUS_FRANCE, PathwayCode::PARIS_SACLAY],
        };
    }

    /**
     * @return PathwayDefinition
     */
    private static function campusFrance(): array
    {
        return [
            'code' => PathwayCode::CAMPUS_FRANCE,
            'name' => 'Campus France',
            'eligibleStudyTypes' => [StudyApplicationType::FIRST_YEAR, StudyApplicationType::CONTINUING],
            'stages' => [
                self::stage('Dossier créé', 'Création et saisie du dossier Campus France', [
                    ['Création du compte Campus France', null, true, 30],
                    ['Compléter les informations personnelles', null, true, 45],
                    ['Compléter le parcours académique', null, true, 45],
                    ['Compléter les expériences professionnelles', null, true, 60],
                    ['Enregistrer le dossier', null, true, 60],
                ]),
                self::stage('Dossier complet', 'Pièces et vérification de complétude', [
                    ['Passeport renseigné', null, true, 75],
                    ['Diplômes ajoutés', null, true, 75],
                    ['Relevés de notes ajoutés', null, true, 75],
                    ['Attestations de réussite ajoutées', null, true, 75],
                    ['CV ajouté', null, true, 75],
                    ['Lettre de motivation ajoutée', null, true, 75],
                    ['Vérification de la complétude du dossier', null, true, 90],
                ]),
                self::stage('Dépôt Campus France', 'Choix des formations et dépôt officiel', [
                    ['Choix des formations', null, true, 120],
                    ['Choix des établissements', null, true, 120],
                    ['Paiement Campus France', null, true, 130],
                    ['Dépôt du dossier', null, true, 140],
                    ['Confirmation du dépôt', null, true, 140],
                ]),
                self::stage('Entretien CF', 'Phase entretien avec Campus France', [
                    ['Convocation reçue', null, true, 160],
                    ['Rendez-vous programmé', null, true, 170],
                    ['Entretien réalisé', null, true, 180],
                    ['Compte rendu reçu', null, true, 190],
                ]),
                self::stage('Admission obtenue', 'Réponses établissements et acceptation', [
                    ['Réponse établissement reçue', null, true, 210],
                    ['Choix de l\'établissement', null, true, 220],
                    ['Admission acceptée', null, true, 230],
                    ['Attestation d\'admission téléchargée', null, true, 240],
                ]),
                self::stage('Demande de visa', 'Préparation et dépôt visa', [
                    ['Dossier visa préparé', null, true, 260],
                    ['Justificatif financier fourni', null, true, 270],
                    ['Justificatif logement fourni', null, true, 270],
                    ['Rendez-vous visa obtenu', null, true, 280],
                    ['Dépôt de la demande de visa', null, true, 290],
                ]),
                self::stage('Visa obtenu', 'Finalisation avant départ', [
                    ['Visa accordé', null, true, 320],
                    ['Passeport récupéré', null, true, 330],
                    ['Billet réservé', null, true, 340],
                    ['Départ préparé', null, true, 365],
                ]),
            ],
        ];
    }

    /**
     * @return PathwayDefinition
     */
    private static function parcoursup(): array
    {
        return [
            'code' => PathwayCode::PARCOURSUP,
            'name' => 'Parcoursup',
            'eligibleStudyTypes' => [StudyApplicationType::FIRST_YEAR],
            'stages' => [
                self::stage('Création du dossier', 'Ouverture du compte Parcoursup', [
                    ['Compte Parcoursup créé', null, true, 30],
                    ['Informations personnelles complétées', null, true, 45],
                    ['Informations scolaires vérifiées', null, true, 60],
                ]),
                self::stage('Formulation des vœux', 'Recherche et ajout des formations', [
                    ['Recherche des formations', null, true, 90],
                    ['Ajout des vœux', null, true, 100],
                    ['Vérification des vœux', null, true, 110],
                ]),
                self::stage('Finalisation du dossier', 'Pièces et confirmation des vœux', [
                    ['Projet de formation motivé rédigé', null, true, 120],
                    ['Pièces complémentaires ajoutées', null, true, 130],
                    ['Vœux confirmés', null, true, 140],
                ]),
                self::stage('Phase d\'admission', 'Réponses et acceptation', [
                    ['Réponses consultées', null, true, 180],
                    ['Proposition reçue', null, true, 200],
                    ['Proposition acceptée', null, true, 210],
                ]),
                self::stage('Inscription administrative', 'Finalisation inscription', [
                    ['Attestation d\'admission obtenue', null, true, 240],
                    ['Inscription réalisée', null, true, 270],
                    ['Certificat de scolarité obtenu', null, true, 300],
                ]),
            ],
        ];
    }

    /**
     * @return PathwayDefinition
     */
    private static function parisSaclay(): array
    {
        return [
            'code' => PathwayCode::PARIS_SACLAY,
            'name' => 'Paris-Saclay',
            'eligibleStudyTypes' => [StudyApplicationType::CONTINUING],
            'stages' => [
                self::stage('Préparation de la candidature', 'Sélection formation et préparation', [
                    ['Formation sélectionnée', null, true, 30],
                    ['Conditions d\'admission vérifiées', null, true, 45],
                    ['Dossier préparé', null, true, 60],
                ]),
                self::stage('Dépôt de candidature', 'Soumission officielle', [
                    ['Compte candidat créé', null, true, 75],
                    ['Documents déposés', null, true, 90],
                    ['Candidature soumise', null, true, 100],
                ]),
                self::stage('Étude du dossier', 'Instruction par l\'établissement', [
                    ['Dossier réceptionné', null, true, 120],
                    ['Dossier en cours d\'étude', null, true, 150],
                    ['Entretien demandé', null, false, 170],
                    ['Entretien réalisé', null, false, 180],
                ]),
                self::stage('Admission', 'Décision et acceptation', [
                    ['Résultat reçu', null, true, 210],
                    ['Admission acceptée', null, true, 220],
                    ['Attestation d\'admission téléchargée', null, true, 230],
                ]),
                self::stage('Préparation du visa', 'Dossier visa', [
                    ['Documents visa préparés', null, true, 260],
                    ['Rendez-vous visa obtenu', null, true, 280],
                    ['Demande déposée', null, true, 290],
                ]),
                self::stage('Visa obtenu', 'Finalisation', [
                    ['Visa accordé', null, true, 320],
                    ['Passeport récupéré', null, true, 330],
                    ['Voyage préparé', null, true, 365],
                ]),
            ],
        ];
    }

    /**
     * @param list<array{0: string, 1: string|null, 2: bool, 3: int|null}> $subSteps
     *
     * @return array{title: string, description: string|null, subSteps: list<array{title: string, description: string|null, required: bool, dueOffsetDays: int|null}>}
     */
    private static function stage(string $title, ?string $description, array $subSteps): array
    {
        return [
            'title' => $title,
            'description' => $description,
            'subSteps' => array_map(
                static fn (array $row): array => [
                    'title' => $row[0],
                    'description' => $row[1],
                    'required' => $row[2],
                    'dueOffsetDays' => $row[3],
                ],
                $subSteps,
            ),
        ];
    }
}
