<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260208093100 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add likes and comments to forum';
    }

    public function up(Schema $schema): void
    {
        // Ajouter les likes à la table forum
        $this->addSql('ALTER TABLE forum ADD likes INT NOT NULL DEFAULT 0');
        
        // Créer la table forum_comment
        $this->addSql('CREATE TABLE forum_comment (id INT AUTO_INCREMENT NOT NULL, forum_id INT NOT NULL, content LONGTEXT NOT NULL, author VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', likes INT NOT NULL DEFAULT 0, INDEX IDX_F2DFC81529CCBAD0 (forum_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        
        // Ajouter la contrainte de clé étrangère
        $this->addSql('ALTER TABLE forum_comment ADD CONSTRAINT FK_F2DFC81529CCBAD0 FOREIGN KEY (forum_id) REFERENCES forum (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE forum_comment DROP FOREIGN KEY FK_F2DFC81529CCBAD0');
        $this->addSql('DROP TABLE forum_comment');
        $this->addSql('ALTER TABLE forum DROP likes');
    }
}
