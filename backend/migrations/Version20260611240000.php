<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260611240000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add appointment_calendar_enabled on users';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE users ADD appointment_calendar_enabled BOOLEAN DEFAULT true NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE users DROP appointment_calendar_enabled');
    }
}
