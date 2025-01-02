<?php

declare(strict_types=1);

namespace Database\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241223184437 extends AbstractMigration {
    public function getDescription(): string {
        return 'Add constraints for nip_id and email columns as UNIQUE';
    }

    public function up(Schema $schema): void {
        $sql = <<<SQL
            ALTER TABLE users ADD CONSTRAINT unique_nip_id UNIQUE(nip_id);
            ALTER TABLE users ADD CONSTRAINT unique_email UNIQUE(email);
        SQL;

        $this->addSql($sql);

    }

    public function down(Schema $schema): void {
        $sql = <<<SQL
            ALTER TABLE users DROP CONSTRAINT unique_nip_id;
            ALTER TABLE users DROP CONSTRAINT unique_email;
        SQL;

        $this->addSql($sql);

    }
}
