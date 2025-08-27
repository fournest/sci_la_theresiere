<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250827115531 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE reservation_option (reservation_id INT NOT NULL, option_id INT NOT NULL, INDEX IDX_1277492BB83297E7 (reservation_id), INDEX IDX_1277492BA7C41D6F (option_id), PRIMARY KEY(reservation_id, option_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE reservation_option ADD CONSTRAINT FK_1277492BB83297E7 FOREIGN KEY (reservation_id) REFERENCES reservation (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reservation_option ADD CONSTRAINT FK_1277492BA7C41D6F FOREIGN KEY (option_id) REFERENCES `option` (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE reservation_option DROP FOREIGN KEY FK_1277492BB83297E7');
        $this->addSql('ALTER TABLE reservation_option DROP FOREIGN KEY FK_1277492BA7C41D6F');
        $this->addSql('DROP TABLE reservation_option');
    }
}
