<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260621100000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Parcours — validation antérieure conservée si double validation activée après coup';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE candidate_pathway_sub_steps ADD grandfathered_validation BOOLEAN DEFAULT false NOT NULL');
        $this->addSql('ALTER TABLE candidate_pathway_sub_steps ALTER grandfathered_validation DROP DEFAULT');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE candidate_pathway_sub_steps DROP grandfathered_validation');
    }
}
