<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230918085548 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE article (id UUID NOT NULL, title VARCHAR(255) NOT NULL, content TEXT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN article.id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE player_user (id UUID NOT NULL, gamer_tag VARCHAR(20) NOT NULL, points INT NOT NULL, is_captain BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_2F89867A9CA629E0 ON player_user (gamer_tag)');
        $this->addSql('COMMENT ON COLUMN player_user.id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE staff_user (id UUID NOT NULL, name VARCHAR(20) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN staff_user.id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE team (id UUID NOT NULL, captain_id UUID DEFAULT NULL, name VARCHAR(25) NOT NULL, points INT NOT NULL, type VARCHAR(25) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, discriminator VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_C4E0A61F3346729B ON team (captain_id)');
        $this->addSql('COMMENT ON COLUMN team.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN team.captain_id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE team_player_user (team_id UUID NOT NULL, player_user_id UUID NOT NULL, PRIMARY KEY(team_id, player_user_id))');
        $this->addSql('CREATE INDEX IDX_C4B96937296CD8AE ON team_player_user (team_id)');
        $this->addSql('CREATE INDEX IDX_C4B96937C69A2CD0 ON team_player_user (player_user_id)');
        $this->addSql('COMMENT ON COLUMN team_player_user.team_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN team_player_user.player_user_id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE team_fake (id UUID NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN team_fake.id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE team_invite (id UUID NOT NULL, team_id UUID NOT NULL, player_id UUID NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_B1F9570E296CD8AE ON team_invite (team_id)');
        $this->addSql('CREATE INDEX IDX_B1F9570E99E6F5DF ON team_invite (player_id)');
        $this->addSql('COMMENT ON COLUMN team_invite.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN team_invite.team_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN team_invite.player_id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE tournament (id UUID NOT NULL, name VARCHAR(25) NOT NULL, points INT NOT NULL, max_teams INT NOT NULL, has_loser_bracket BOOLEAN NOT NULL, type VARCHAR(25) NOT NULL, cash_price NUMERIC(7, 2) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, start_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, is_started BOOLEAN NOT NULL, is_finished BOOLEAN NOT NULL, state VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN tournament.id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE tournament_team (tournament_id UUID NOT NULL, team_id UUID NOT NULL, PRIMARY KEY(tournament_id, team_id))');
        $this->addSql('CREATE INDEX IDX_F36D142133D1A3E7 ON tournament_team (tournament_id)');
        $this->addSql('CREATE INDEX IDX_F36D1421296CD8AE ON tournament_team (team_id)');
        $this->addSql('COMMENT ON COLUMN tournament_team.tournament_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN tournament_team.team_id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE tournament_bracket (id UUID NOT NULL, tournament_id UUID NOT NULL, name VARCHAR(25) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_2A01DD6433D1A3E7 ON tournament_bracket (tournament_id)');
        $this->addSql('COMMENT ON COLUMN tournament_bracket.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN tournament_bracket.tournament_id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE tournament_match (id UUID NOT NULL, team_one_id UUID DEFAULT NULL, team_two_id UUID DEFAULT NULL, round_id UUID NOT NULL, name VARCHAR(25) NOT NULL, result VARCHAR(10) DEFAULT NULL, is_finish BOOLEAN NOT NULL, is_waiting_for_admin BOOLEAN NOT NULL, discriminator VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_BB0D551C8D8189CA ON tournament_match (team_one_id)');
        $this->addSql('CREATE INDEX IDX_BB0D551CE6DD6E05 ON tournament_match (team_two_id)');
        $this->addSql('CREATE INDEX IDX_BB0D551CA6005CA0 ON tournament_match (round_id)');
        $this->addSql('COMMENT ON COLUMN tournament_match.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN tournament_match.team_one_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN tournament_match.team_two_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN tournament_match.round_id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE tournament_match_final (id UUID NOT NULL, requires_replay BOOLEAN NOT NULL, is_grand_final BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN tournament_match_final.id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE tournament_ranking (id UUID NOT NULL, tournament_id UUID NOT NULL, points_by_tier JSON NOT NULL, result JSON DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_EBB7C2DA33D1A3E7 ON tournament_ranking (tournament_id)');
        $this->addSql('COMMENT ON COLUMN tournament_ranking.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN tournament_ranking.tournament_id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE tournament_round (id UUID NOT NULL, bracket_id UUID NOT NULL, name VARCHAR(25) NOT NULL, best_of INT NOT NULL, infos JSON DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, is_finish BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_4B87A2D6E8D78 ON tournament_round (bracket_id)');
        $this->addSql('COMMENT ON COLUMN tournament_round.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN tournament_round.bracket_id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE "user" (id UUID NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, is_enabled BOOLEAN NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, last_login TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, discr VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649E7927C74 ON "user" (email)');
        $this->addSql('COMMENT ON COLUMN "user".id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE player_user ADD CONSTRAINT FK_2F89867ABF396750 FOREIGN KEY (id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE staff_user ADD CONSTRAINT FK_6FA5A0C6BF396750 FOREIGN KEY (id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE team ADD CONSTRAINT FK_C4E0A61F3346729B FOREIGN KEY (captain_id) REFERENCES player_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE team_player_user ADD CONSTRAINT FK_C4B96937296CD8AE FOREIGN KEY (team_id) REFERENCES team (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE team_player_user ADD CONSTRAINT FK_C4B96937C69A2CD0 FOREIGN KEY (player_user_id) REFERENCES player_user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE team_fake ADD CONSTRAINT FK_1C2C0514BF396750 FOREIGN KEY (id) REFERENCES team (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE team_invite ADD CONSTRAINT FK_B1F9570E296CD8AE FOREIGN KEY (team_id) REFERENCES team (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE team_invite ADD CONSTRAINT FK_B1F9570E99E6F5DF FOREIGN KEY (player_id) REFERENCES player_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE tournament_team ADD CONSTRAINT FK_F36D142133D1A3E7 FOREIGN KEY (tournament_id) REFERENCES tournament (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE tournament_team ADD CONSTRAINT FK_F36D1421296CD8AE FOREIGN KEY (team_id) REFERENCES team (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE tournament_bracket ADD CONSTRAINT FK_2A01DD6433D1A3E7 FOREIGN KEY (tournament_id) REFERENCES tournament (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE tournament_match ADD CONSTRAINT FK_BB0D551C8D8189CA FOREIGN KEY (team_one_id) REFERENCES team (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE tournament_match ADD CONSTRAINT FK_BB0D551CE6DD6E05 FOREIGN KEY (team_two_id) REFERENCES team (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE tournament_match ADD CONSTRAINT FK_BB0D551CA6005CA0 FOREIGN KEY (round_id) REFERENCES tournament_round (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE tournament_match_final ADD CONSTRAINT FK_D96BFB2DBF396750 FOREIGN KEY (id) REFERENCES tournament_match (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE tournament_ranking ADD CONSTRAINT FK_EBB7C2DA33D1A3E7 FOREIGN KEY (tournament_id) REFERENCES tournament (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE tournament_round ADD CONSTRAINT FK_4B87A2D6E8D78 FOREIGN KEY (bracket_id) REFERENCES tournament_bracket (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE player_user DROP CONSTRAINT FK_2F89867ABF396750');
        $this->addSql('ALTER TABLE staff_user DROP CONSTRAINT FK_6FA5A0C6BF396750');
        $this->addSql('ALTER TABLE team DROP CONSTRAINT FK_C4E0A61F3346729B');
        $this->addSql('ALTER TABLE team_player_user DROP CONSTRAINT FK_C4B96937296CD8AE');
        $this->addSql('ALTER TABLE team_player_user DROP CONSTRAINT FK_C4B96937C69A2CD0');
        $this->addSql('ALTER TABLE team_fake DROP CONSTRAINT FK_1C2C0514BF396750');
        $this->addSql('ALTER TABLE team_invite DROP CONSTRAINT FK_B1F9570E296CD8AE');
        $this->addSql('ALTER TABLE team_invite DROP CONSTRAINT FK_B1F9570E99E6F5DF');
        $this->addSql('ALTER TABLE tournament_team DROP CONSTRAINT FK_F36D142133D1A3E7');
        $this->addSql('ALTER TABLE tournament_team DROP CONSTRAINT FK_F36D1421296CD8AE');
        $this->addSql('ALTER TABLE tournament_bracket DROP CONSTRAINT FK_2A01DD6433D1A3E7');
        $this->addSql('ALTER TABLE tournament_match DROP CONSTRAINT FK_BB0D551C8D8189CA');
        $this->addSql('ALTER TABLE tournament_match DROP CONSTRAINT FK_BB0D551CE6DD6E05');
        $this->addSql('ALTER TABLE tournament_match DROP CONSTRAINT FK_BB0D551CA6005CA0');
        $this->addSql('ALTER TABLE tournament_match_final DROP CONSTRAINT FK_D96BFB2DBF396750');
        $this->addSql('ALTER TABLE tournament_ranking DROP CONSTRAINT FK_EBB7C2DA33D1A3E7');
        $this->addSql('ALTER TABLE tournament_round DROP CONSTRAINT FK_4B87A2D6E8D78');
        $this->addSql('DROP TABLE article');
        $this->addSql('DROP TABLE player_user');
        $this->addSql('DROP TABLE staff_user');
        $this->addSql('DROP TABLE team');
        $this->addSql('DROP TABLE team_player_user');
        $this->addSql('DROP TABLE team_fake');
        $this->addSql('DROP TABLE team_invite');
        $this->addSql('DROP TABLE tournament');
        $this->addSql('DROP TABLE tournament_team');
        $this->addSql('DROP TABLE tournament_bracket');
        $this->addSql('DROP TABLE tournament_match');
        $this->addSql('DROP TABLE tournament_match_final');
        $this->addSql('DROP TABLE tournament_ranking');
        $this->addSql('DROP TABLE tournament_round');
        $this->addSql('DROP TABLE "user"');
    }
}
