<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231017042348 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE customer DROP FOREIGN KEY FK_81398E09622F3F37');
        $this->addSql('DROP INDEX idx_81398e09622f3f37 ON customer');
        $this->addSql('CREATE INDEX customer__market_id__ind ON customer (market_id)');
        $this->addSql('ALTER TABLE customer ADD CONSTRAINT FK_81398E09622F3F37 FOREIGN KEY (market_id) REFERENCES market (id)');
        $this->addSql('ALTER TABLE transaction DROP FOREIGN KEY FK_723705D19395C3F3');
        $this->addSql('ALTER TABLE transaction DROP FOREIGN KEY FK_723705D1A76ED395');
        $this->addSql('ALTER TABLE transaction DROP FOREIGN KEY FK_723705D14C3A3BB');
        $this->addSql('ALTER TABLE transaction DROP FOREIGN KEY FK_723705D1C54C8C93');
        $this->addSql('DROP INDEX idx_723705d1c54c8c93 ON transaction');
        $this->addSql('CREATE INDEX transaction__type_id__ind ON transaction (type_id)');
        $this->addSql('DROP INDEX idx_723705d14c3a3bb ON transaction');
        $this->addSql('CREATE INDEX transaction__payment_id__ind ON transaction (payment_id)');
        $this->addSql('DROP INDEX idx_723705d19395c3f3 ON transaction');
        $this->addSql('CREATE INDEX transaction__customer_id__ind ON transaction (customer_id)');
        $this->addSql('DROP INDEX idx_723705d1a76ed395 ON transaction');
        $this->addSql('CREATE INDEX transaction__user_id__ind ON transaction (user_id)');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_723705D19395C3F3 FOREIGN KEY (customer_id) REFERENCES customer (id)');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_723705D1A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_723705D14C3A3BB FOREIGN KEY (payment_id) REFERENCES payment (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_723705D1C54C8C93 FOREIGN KEY (type_id) REFERENCES type (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE customer DROP FOREIGN KEY FK_81398E09622F3F37');
        $this->addSql('DROP INDEX customer__market_id__ind ON customer');
        $this->addSql('CREATE INDEX IDX_81398E09622F3F37 ON customer (market_id)');
        $this->addSql('ALTER TABLE customer ADD CONSTRAINT FK_81398E09622F3F37 FOREIGN KEY (market_id) REFERENCES market (id)');
        $this->addSql('ALTER TABLE transaction DROP FOREIGN KEY FK_723705D1C54C8C93');
        $this->addSql('ALTER TABLE transaction DROP FOREIGN KEY FK_723705D14C3A3BB');
        $this->addSql('ALTER TABLE transaction DROP FOREIGN KEY FK_723705D19395C3F3');
        $this->addSql('ALTER TABLE transaction DROP FOREIGN KEY FK_723705D1A76ED395');
        $this->addSql('DROP INDEX transaction__payment_id__ind ON transaction');
        $this->addSql('CREATE INDEX IDX_723705D14C3A3BB ON transaction (payment_id)');
        $this->addSql('DROP INDEX transaction__customer_id__ind ON transaction');
        $this->addSql('CREATE INDEX IDX_723705D19395C3F3 ON transaction (customer_id)');
        $this->addSql('DROP INDEX transaction__user_id__ind ON transaction');
        $this->addSql('CREATE INDEX IDX_723705D1A76ED395 ON transaction (user_id)');
        $this->addSql('DROP INDEX transaction__type_id__ind ON transaction');
        $this->addSql('CREATE INDEX IDX_723705D1C54C8C93 ON transaction (type_id)');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_723705D1C54C8C93 FOREIGN KEY (type_id) REFERENCES type (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_723705D14C3A3BB FOREIGN KEY (payment_id) REFERENCES payment (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_723705D19395C3F3 FOREIGN KEY (customer_id) REFERENCES customer (id)');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_723705D1A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }
}
