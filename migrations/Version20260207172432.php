<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260207172432 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE cours (id_cours INT AUTO_INCREMENT NOT NULL, titre VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, date_publication DATETIME NOT NULL, date_creation DATETIME NOT NULL, visibilité TINYINT NOT NULL, contenu VARCHAR(255) NOT NULL, type_contenu VARCHAR(100) DEFAULT NULL, url_contenu VARCHAR(255) DEFAULT NULL, PRIMARY KEY (id_cours)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE quiz (id_quiz INT AUTO_INCREMENT NOT NULL, titre VARCHAR(255) NOT NULL, descrition LONGTEXT DEFAULT NULL, questions JSON NOT NULL, date_creation DATETIME NOT NULL, date_echeance DATETIME NOT NULL, duree INT DEFAULT NULL, score_max INT NOT NULL, tentatives_max INT NOT NULL, id_cours INT NOT NULL, INDEX IDX_A412FA92134FCDAC (id_cours), PRIMARY KEY (id_quiz)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE quiz ADD CONSTRAINT FK_A412FA92134FCDAC FOREIGN KEY (id_cours) REFERENCES cours (id_cours)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE quiz DROP FOREIGN KEY FK_A412FA92134FCDAC');
        $this->addSql('DROP TABLE cours');
        $this->addSql('DROP TABLE quiz');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
