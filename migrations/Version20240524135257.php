<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240524135257 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE expense_type ADD parent_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE expense_type ADD CONSTRAINT FK_3879194B727ACA70 FOREIGN KEY (parent_id) REFERENCES expense_type (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_3879194B727ACA70 ON expense_type (parent_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE expense_type DROP FOREIGN KEY FK_3879194B727ACA70');
        $this->addSql('DROP INDEX IDX_3879194B727ACA70 ON expense_type');
        $this->addSql('ALTER TABLE expense_type DROP parent_id');
    }
}
