<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260208100000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Rename forum tables to post/commentaire, add reactions JSON columns, drop legacy reaction table';
    }

    public function up(Schema $schema): void
    {
        // Rename legacy tables (may fail if names already changed)
        $this->addSql('RENAME TABLE forum TO post');
        $this->addSql('RENAME TABLE forum_comment TO commentaire');

        // Add reactions JSON column to post and commentaire
        $this->addSql('ALTER TABLE post ADD reactions JSON DEFAULT NULL');
        $this->addSql('ALTER TABLE commentaire ADD reactions JSON DEFAULT NULL');

        // Drop legacy reaction table if exists
        $this->addSql('DROP TABLE IF EXISTS reaction');
    }

    public function down(Schema $schema): void
    {
        // Attempt to recreate previous schema state (best-effort)
        // Remove reactions columns
        $this->addSql('ALTER TABLE post DROP COLUMN reactions');
        $this->addSql('ALTER TABLE commentaire DROP COLUMN reactions');

        // Rename tables back
        $this->addSql('RENAME TABLE post TO forum');
        $this->addSql('RENAME TABLE commentaire TO forum_comment');

        // Recreate a minimal reaction table (structure may differ from original)
        $this->addSql('CREATE TABLE IF NOT EXISTS reaction (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(50) NOT NULL, author VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, forum_id INT DEFAULT NULL, comment_id INT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
    }
}
