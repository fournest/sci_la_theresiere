<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260509142348 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE tarif ADD categorie_id INT DEFAULT NULL, ADD option_id INT DEFAULT NULL, ADD montant NUMERIC(10, 2) NOT NULL, ADD libelle VARCHAR(255) DEFAULT NULL, DROP prix_reservation, DROP prix_option');
        $this->addSql('ALTER TABLE tarif ADD CONSTRAINT FK_E7189C9BCF5E72D FOREIGN KEY (categorie_id) REFERENCES categorie (id)');
        $this->addSql('ALTER TABLE tarif ADD CONSTRAINT FK_E7189C9A7C41D6F FOREIGN KEY (option_id) REFERENCES `option` (id)');
        $this->addSql('CREATE INDEX IDX_E7189C9BCF5E72D ON tarif (categorie_id)');
        $this->addSql('CREATE INDEX IDX_E7189C9A7C41D6F ON tarif (option_id)');
        $this->addSql('DROP INDEX IDX_75EA56E016BA31DB ON messenger_messages');
        $this->addSql('DROP INDEX IDX_75EA56E0E3BD61CE ON messenger_messages');
        $this->addSql('DROP INDEX IDX_75EA56E0FB7336F0 ON messenger_messages');
        $this->addSql('CREATE INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 ON messenger_messages (queue_name, available_at, delivered_at, id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE tarif DROP FOREIGN KEY FK_E7189C9BCF5E72D');
        $this->addSql('ALTER TABLE tarif DROP FOREIGN KEY FK_E7189C9A7C41D6F');
        $this->addSql('DROP INDEX IDX_E7189C9BCF5E72D ON tarif');
        $this->addSql('DROP INDEX IDX_E7189C9A7C41D6F ON tarif');
        $this->addSql('ALTER TABLE tarif ADD prix_option NUMERIC(10, 2) NOT NULL, DROP categorie_id, DROP option_id, DROP libelle, CHANGE montant prix_reservation NUMERIC(10, 2) NOT NULL');
        $this->addSql('DROP INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 ON messenger_messages');
        $this->addSql('CREATE INDEX IDX_75EA56E016BA31DB ON messenger_messages (delivered_at)');
        $this->addSql('CREATE INDEX IDX_75EA56E0E3BD61CE ON messenger_messages (available_at)');
        $this->addSql('CREATE INDEX IDX_75EA56E0FB7336F0 ON messenger_messages (queue_name)');
    }
}
