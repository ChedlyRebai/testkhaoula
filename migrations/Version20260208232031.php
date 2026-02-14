<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260208232031 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Fix schema for Reaction entity implementation';
    }

    public function up(Schema $schema): void
    {
        // Update timestamp columns to include proper DC2Type comment if needed
        $this->addSql('ALTER TABLE forum_comment MODIFY COLUMN created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE reaction MODIFY COLUMN created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        // Downgrade - no action needed
    }
}
