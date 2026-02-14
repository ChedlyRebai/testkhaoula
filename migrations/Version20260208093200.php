<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260208093200 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create reaction table for forum and comment reactions';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE reaction (id INT AUTO_INCREMENT NOT NULL, forum_id INT DEFAULT NULL, comment_id INT DEFAULT NULL, type VARCHAR(50) NOT NULL, author VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_927EF47F29FF2BA (forum_id), INDEX IDX_927EF47F8C7B3F1 (comment_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE reaction ADD CONSTRAINT FK_927EF47F29FF2BA FOREIGN KEY (forum_id) REFERENCES forum (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reaction ADD CONSTRAINT FK_927EF47F8C7B3F1 FOREIGN KEY (comment_id) REFERENCES forum_comment (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE reaction DROP FOREIGN KEY FK_927EF47F8C7B3F1');
        $this->addSql('ALTER TABLE reaction DROP FOREIGN KEY FK_927EF47F29FF2BA');
        $this->addSql('DROP TABLE reaction');
    }
}
