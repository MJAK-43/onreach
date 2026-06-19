<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260620100000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Phases 2-4 — relances échéances, messagerie candidat/conseiller';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE candidate_pathway_sub_steps ADD due_reminder_sent_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');

        $this->addSql('CREATE TABLE conversations (id UUID NOT NULL, candidate_id UUID NOT NULL, counselor_id UUID NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX uniq_conversation_pair ON conversations (candidate_id, counselor_id)');
        $this->addSql('CREATE INDEX idx_conversation_counselor ON conversations (counselor_id)');
        $this->addSql('ALTER TABLE conversations ADD CONSTRAINT FK_8A8E26E991BD8781 FOREIGN KEY (candidate_id) REFERENCES candidates (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE conversations ADD CONSTRAINT FK_8A8E26E9DABAE2E8 FOREIGN KEY (counselor_id) REFERENCES users (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->addSql('CREATE TABLE conversation_messages (id UUID NOT NULL, conversation_id UUID NOT NULL, author_id UUID NOT NULL, body TEXT NOT NULL, read_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_message_conversation ON conversation_messages (conversation_id)');
        $this->addSql('ALTER TABLE conversation_messages ADD CONSTRAINT FK_3B4CAF099AC0396 FOREIGN KEY (conversation_id) REFERENCES conversations (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE conversation_messages ADD CONSTRAINT FK_3B4CAF09F675F31B FOREIGN KEY (author_id) REFERENCES users (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE conversation_messages DROP CONSTRAINT FK_3B4CAF09F675F31B');
        $this->addSql('ALTER TABLE conversation_messages DROP CONSTRAINT FK_3B4CAF099AC0396');
        $this->addSql('DROP TABLE conversation_messages');
        $this->addSql('ALTER TABLE conversations DROP CONSTRAINT FK_8A8E26E9DABAE2E8');
        $this->addSql('ALTER TABLE conversations DROP CONSTRAINT FK_8A8E26E991BD8781');
        $this->addSql('DROP TABLE conversations');
        $this->addSql('ALTER TABLE candidate_pathway_sub_steps DROP due_reminder_sent_at');
    }
}
