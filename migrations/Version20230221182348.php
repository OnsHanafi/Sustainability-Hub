<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230221182348 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE reponse ADD description VARCHAR(255) NOT NULL, ADD mail VARCHAR(255) NOT NULL, DROP contenu_reponse, DROP titre, DROP email_reponse, CHANGE reclamation_id reclamation_id INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE reponse ADD contenu_reponse VARCHAR(255) NOT NULL, ADD titre VARCHAR(255) NOT NULL, ADD email_reponse VARCHAR(255) NOT NULL, DROP description, DROP mail, CHANGE reclamation_id reclamation_id INT NOT NULL');
    }
}
