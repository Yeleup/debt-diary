<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240211121554 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE expense ADD control_user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE expense ADD CONSTRAINT FK_2D3A8DA67715039 FOREIGN KEY (control_user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_2D3A8DA67715039 ON expense (control_user_id)');
        $this->addSql('ALTER TABLE expense_type CHANGE is_add_expense is_add_expense TINYINT(1) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE expense DROP FOREIGN KEY FK_2D3A8DA67715039');
        $this->addSql('DROP INDEX IDX_2D3A8DA67715039 ON expense');
        $this->addSql('ALTER TABLE expense DROP control_user_id');
        $this->addSql('ALTER TABLE expense_type CHANGE is_add_expense is_add_expense TINYINT(1) DEFAULT 1 NOT NULL');
    }
}
