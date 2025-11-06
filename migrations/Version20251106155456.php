<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251106155456 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE carousel_image (id INT AUTO_INCREMENT NOT NULL, filename VARCHAR(255) NOT NULL, ordre INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE legal_page CHANGE content content LONGTEXT NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_39715897989D9B62 ON legal_page (slug)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE carousel_image');
        $this->addSql('DROP INDEX UNIQ_39715897989D9B62 ON legal_page');
        $this->addSql('ALTER TABLE legal_page CHANGE content content LONGTEXT DEFAULT NULL');
    }
}
