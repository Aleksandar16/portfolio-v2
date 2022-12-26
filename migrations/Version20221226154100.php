<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221226154100 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add foreign key to tables';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE project_technology (project_id INT NOT NULL, technology_id INT NOT NULL, INDEX IDX_ECC5297F166D1F9C (project_id), INDEX IDX_ECC5297F4235D463 (technology_id), PRIMARY KEY(project_id, technology_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE project_technology ADD CONSTRAINT FK_ECC5297F166D1F9C FOREIGN KEY (project_id) REFERENCES project (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE project_technology ADD CONSTRAINT FK_ECC5297F4235D463 FOREIGN KEY (technology_id) REFERENCES technology (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE doc ADD project_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE doc ADD CONSTRAINT FK_8641FD64166D1F9C FOREIGN KEY (project_id) REFERENCES project (id)');
        $this->addSql('CREATE INDEX IDX_8641FD64166D1F9C ON doc (project_id)');
        $this->addSql('ALTER TABLE screen ADD project_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE screen ADD CONSTRAINT FK_DF4C6130166D1F9C FOREIGN KEY (project_id) REFERENCES project (id)');
        $this->addSql('CREATE INDEX IDX_DF4C6130166D1F9C ON screen (project_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE project_technology DROP FOREIGN KEY FK_ECC5297F166D1F9C');
        $this->addSql('ALTER TABLE project_technology DROP FOREIGN KEY FK_ECC5297F4235D463');
        $this->addSql('DROP TABLE project_technology');
        $this->addSql('ALTER TABLE doc DROP FOREIGN KEY FK_8641FD64166D1F9C');
        $this->addSql('DROP INDEX IDX_8641FD64166D1F9C ON doc');
        $this->addSql('ALTER TABLE doc DROP project_id');
        $this->addSql('ALTER TABLE screen DROP FOREIGN KEY FK_DF4C6130166D1F9C');
        $this->addSql('DROP INDEX IDX_DF4C6130166D1F9C ON screen');
        $this->addSql('ALTER TABLE screen DROP project_id');
    }
}
