<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250911073751 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE contact ADD CONSTRAINT FK_4C62E638F624B39D FOREIGN KEY (sender_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE contact ADD CONSTRAINT FK_4C62E638E92F8F78 FOREIGN KEY (recipient_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_4C62E638F624B39D ON contact (sender_id)');
        $this->addSql('CREATE INDEX IDX_4C62E638E92F8F78 ON contact (recipient_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE contact DROP FOREIGN KEY FK_4C62E638F624B39D');
        $this->addSql('ALTER TABLE contact DROP FOREIGN KEY FK_4C62E638E92F8F78');
        $this->addSql('DROP INDEX IDX_4C62E638F624B39D ON contact');
        $this->addSql('DROP INDEX IDX_4C62E638E92F8F78 ON contact');
    }
}
