<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Make title and content optional in forum table
 */
final class Version20260208232033 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Make title and content nullable in forum table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE forum MODIFY title VARCHAR(255) DEFAULT NULL, MODIFY content LONGTEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE forum MODIFY title VARCHAR(255) NOT NULL, MODIFY content LONGTEXT NOT NULL');
    }
}
