<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240211200854 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE championship (id INT AUTO_INCREMENT NOT NULL, admin_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, date_start DATE NOT NULL, date_end DATE NOT NULL, INDEX IDX_EBADDE6A642B8210 (admin_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE player (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, identifier VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_98197A65772E836A (identifier), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tournament (id INT AUTO_INCREMENT NOT NULL, championship_id INT DEFAULT NULL, admin_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, challonge_id VARCHAR(255) DEFAULT NULL, date DATE NOT NULL, paid TINYINT(1) NOT NULL, public TINYINT(1) NOT NULL, extra_data JSON DEFAULT NULL COMMENT \'(DC2Type:json)\', INDEX IDX_BD5FB8D994DDBCE9 (championship_id), INDEX IDX_BD5FB8D9642B8210 (admin_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tournament_results (tournament_id INT NOT NULL, player_id INT NOT NULL, points INT NOT NULL, rank INT NOT NULL, INDEX IDX_F4AC1F1E33D1A3E7 (tournament_id), INDEX IDX_F4AC1F1E99E6F5DF (player_id), PRIMARY KEY(tournament_id, player_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(180) NOT NULL, roles JSON NOT NULL COMMENT \'(DC2Type:json)\', password VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_8D93D649F85E0677 (username), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE championship ADD CONSTRAINT FK_EBADDE6A642B8210 FOREIGN KEY (admin_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE tournament ADD CONSTRAINT FK_BD5FB8D994DDBCE9 FOREIGN KEY (championship_id) REFERENCES championship (id)');
        $this->addSql('ALTER TABLE tournament ADD CONSTRAINT FK_BD5FB8D9642B8210 FOREIGN KEY (admin_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE tournament_results ADD CONSTRAINT FK_F4AC1F1E33D1A3E7 FOREIGN KEY (tournament_id) REFERENCES tournament (id)');
        $this->addSql('ALTER TABLE tournament_results ADD CONSTRAINT FK_F4AC1F1E99E6F5DF FOREIGN KEY (player_id) REFERENCES player (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE championship DROP FOREIGN KEY FK_EBADDE6A642B8210');
        $this->addSql('ALTER TABLE tournament DROP FOREIGN KEY FK_BD5FB8D994DDBCE9');
        $this->addSql('ALTER TABLE tournament DROP FOREIGN KEY FK_BD5FB8D9642B8210');
        $this->addSql('ALTER TABLE tournament_results DROP FOREIGN KEY FK_F4AC1F1E33D1A3E7');
        $this->addSql('ALTER TABLE tournament_results DROP FOREIGN KEY FK_F4AC1F1E99E6F5DF');
        $this->addSql('DROP TABLE championship');
        $this->addSql('DROP TABLE player');
        $this->addSql('DROP TABLE tournament');
        $this->addSql('DROP TABLE tournament_results');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
