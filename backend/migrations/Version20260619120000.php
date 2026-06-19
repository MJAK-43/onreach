<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260619120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Phase 3 — notifications in-app';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE in_app_notifications (id UUID NOT NULL, recipient_id UUID NOT NULL, type VARCHAR(80) NOT NULL, title VARCHAR(180) NOT NULL, message TEXT NOT NULL, link_url VARCHAR(255) DEFAULT NULL, metadata JSON DEFAULT NULL, read_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_in_app_notifications_recipient_read ON in_app_notifications (recipient_id, read_at)');
        $this->addSql('ALTER TABLE in_app_notifications ADD CONSTRAINT FK_in_app_notifications_recipient FOREIGN KEY (recipient_id) REFERENCES users (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE in_app_notifications DROP CONSTRAINT FK_in_app_notifications_recipient');
        $this->addSql('DROP TABLE in_app_notifications');
    }
}
