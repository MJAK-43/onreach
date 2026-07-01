<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260621110000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Parcours — horodatage double validation et réparation des validations antérieures';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE pathway_settings ADD double_validation_enabled_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');

        $this->addSql(
            'UPDATE pathway_settings SET double_validation_enabled_at = NOW() WHERE double_validation_enabled = true',
        );

        $this->addSql(
            'UPDATE candidate_pathway_sub_steps ss
             SET grandfathered_validation = true
             FROM candidate_pathway_stages s
             INNER JOIN candidate_pathways cp ON cp.id = s.candidate_pathway_id
             INNER JOIN pathway_templates pt ON pt.id = cp.pathway_template_id
             INNER JOIN pathway_settings ps ON ps.pathway_code = pt.code
             WHERE ss.candidate_stage_id = s.id
               AND ps.double_validation_enabled = true
               AND ss.counselor_validated_at IS NOT NULL
               AND ss.admin_validated_at IS NULL
               AND ss.grandfathered_validation = false
               AND ss.counselor_validated_at <= ps.double_validation_enabled_at',
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE pathway_settings DROP double_validation_enabled_at');
    }
}
