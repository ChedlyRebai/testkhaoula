<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Drop old Post and Commentaire tables
 */
final class Version20260208232037 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Drop old Post and Commentaire tables from database';
    }

    public function up(Schema $schema): void
    {
        // Drop tables if they exist
        $this->addSql('SET FOREIGN_KEY_CHECKS=0');
        $this->addSql('DROP TABLE IF EXISTS post');
        $this->addSql('DROP TABLE IF EXISTS commentaire');
        $this->addSql('SET FOREIGN_KEY_CHECKS=1');
    }

    public function down(Schema $schema): void
    {
        // No down migration
    }
}
