<?php

namespace App\Service\Team;

use App\Entity\Team\Team;
use App\Entity\Team\TeamInvite;
use App\Entity\User\PlayerUser;
use App\Model\Team\Dto\TeamDto;
use App\Model\Team\Enum\TeamEnum;
use App\Model\User\Enum\UserEnum;
use App\Repository\Team\TeamInviteRepository;
use App\Service\ValidationService;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class TeamService
{
    private ManagerRegistry $registry;
    private TeamInviteRepository $inviteRepository;
    private Security $security;
    private ValidationService $validationService;

    /***
     * @param ManagerRegistry $registry
     * @param TeamInviteRepository $inviteRepository
     * @param Security $security
     * @param ValidationService $validationService
     */
    public function __construct(
        ManagerRegistry $registry,
        TeamInviteRepository $inviteRepository,
        Security $security,
        ValidationService $validationService,
    ) {
        $this->registry = $registry;
        $this->inviteRepository = $inviteRepository;
        $this->security = $security;
        $this->validationService = $validationService;
    }

    /**
     * Create a new team based on provided teamDto.
     *
     * @param TeamDto $teamDto
     * @return Team
     */
    public function createTeam(TeamDto $teamDto): Team
    {
        $this->validationService->performEntityValidation($teamDto, ["teamCreate"]);

        $userPosting = $this->security->getUser();
        $captain = $this->verifyAndGetPlayer($teamDto->playerCaptain);

        if ($userPosting !== $captain) {
            throw new BadRequestHttpException('User posting must be given player_captain');
        }

        if (!in_array($teamDto->type, TeamEnum::getValidTypes())) {
            throw new BadRequestHttpException('Team type is invalid', null, 400);
        }

        $team = new Team();

        $team->setType($teamDto->type);

        if ($this->isTeamJoinableByPlayer($team, $captain)) {
            $team
                ->setName($teamDto->name)
                ->setPoints(0)
                ->setCreatedAt(new \DateTime('now'))
                ->addPlayer($captain);

            $this->changeCaptain($team, $captain);
        } else {
            throw new BadRequestHttpException("Player already part of a team with the same type", null, 400);
        }

        $this->validateAndSave($team, ["teamCreate"]);

        return $team;
    }

    /**
     * Update team based on provided TeamDto.
     * name, and captain can be updated
     *
     * @param Team $team
     * @param TeamDto $teamDto
     * @return Team
     */
    public function updateTeam(Team $team, TeamDto $teamDto): Team
    {
        $this->validationService->performEntityValidation($teamDto, ["teamUpdate"]);

        // Change value only if they are different, prevent duplicate key errors
        if ($teamDto->name !== $team->getName()) {
            $team->setName($teamDto->name);
        }

        // Change captain if given captain is different
        if ($teamDto->playerCaptain !== $team->getCaptain()->getId()) {
            $this->changeCaptain($team, $this->verifyAndGetPlayer($teamDto->playerCaptain));
        }

        $this->validateAndSave($team, ["teamUpdate"]);

        return $team;
    }

    /**
     * Delete Team, captain only.
     *
     * @param Team $team
     * @return void
     */
    public function deleteTeam(Team $team): void
    {
        // Retrieve formerCaptain to update status
        $formerCaptain = $team->getCaptain();

        $entityManager = $this->registry->getManager();
        $entityManager->remove($team);
        $entityManager->flush();

        // Update status
        $this->updateFormerCaptain($formerCaptain);
    }

    /**
     * Team invite Player to join.
     *
     * @param Team $teamInviting
     * @param PlayerUser $playerInvited
     * @return TeamInvite
     */
    public function invitePlayer(Team $teamInviting, PlayerUser $playerInvited): TeamInvite
    {
        // Throw exception if player is already in the team
        if ($teamInviting->isTeamPlayer($playerInvited)) {
            throw new BadRequestHttpException('Player is already in the team.');
        }

        // Throw exception if player is already invited to join the team
        $inviteFound = $this->inviteRepository->findOneBy(['team' => $teamInviting, 'player' => $playerInvited]);
        if ($inviteFound) {
            throw new BadRequestHttpException('Player is already invited to join the team.');
        }

        // Throw exception if the team is full and cannot invite another player
        if ($this->isTeamFull($teamInviting)) {
            throw new BadRequestHttpException('The team is full and cannot invite another player.');
        }

        // Create a new TeamInvite
        $invite = new TeamInvite();
        $invite
            ->setTeam($teamInviting)
            ->setPlayer($playerInvited)
            ->setCreatedAt(new \DateTime('now'));

        // Validate and save the TeamInvite
        $this->validateAndSave($invite, ['teamInvite']);

        return $invite;
    }

    /**
     * Player decline TeamInvite and delete TeamInvite
     *
     * @param Team $teamInviting
     * @param PlayerUser $playerInvited
     * @return void
     */
    public function playerDecline(Team $teamInviting, PlayerUser $playerInvited): void
    {
        $entityManager = $this->registry->getManager();

        $inviteFound = false;
        foreach ($playerInvited->getTeamInvites() as $invite) {
            if ($invite->getTeam() === $teamInviting) {
                $inviteFound = true;
                $entityManager->remove($invite);
                $entityManager->flush();
                break;
            }
        }

        if (!$inviteFound) {
            throw new BadRequestHttpException('player was not invited to join team');
        }
    }

    /**
     * Player joinTeam based on provided TeamJoinDto
     *
     * @param Team $team
     * @param PlayerUser $playerJoining
     * @return Team
     */
    public function playerJoinTeam(Team $team, PlayerUser $playerJoining): Team
    {
        $invite = $this->inviteRepository->findOneBy(['team' => $team, 'player' => $playerJoining]);

        if (!$invite) {
            throw new BadRequestHttpException('player is not invited to join team.');
        }

        if ($this->isTeamJoinableByPlayer($team, $playerJoining)) {
            $team->addPlayer($playerJoining);
            $this->registry->getManager()->remove($invite);
        }

        $this->validateAndSave($team, ["teamJoin"]);

        return $team;
    }

    /**
     * Player leaves Team
     *
     * @param Team $team
     * @param PlayerUser $playerLeaving
     * @return void
     */
    public function playerLeaveTeam(Team $team, PlayerUser $playerLeaving): void
    {
        $actualCaptain = $team->getCaptain();

        if ($playerLeaving === $actualCaptain) {
            $teamPlayers = $team->getPlayers();

            // Ensure there is at least one other player in the team
            if (count($teamPlayers) <= 1) {
                throw new BadRequestHttpException("Player cannot leave team because he is the only player inside");
            }

            // Get the first different player and set him as the new captain
            foreach ($teamPlayers as $player) {
                if ($player->getId() !== $actualCaptain->getId()) {
                    $this->changeCaptain($team, $player);
                    break;
                }
            }
        }

        $team->removePlayer($playerLeaving);

        $this->validateAndSave($team, ["leaveTeam"]);
    }

    /**
     * Set new Captain and update former captain status
     * @param Team $team
     * @param PlayerUser $newCaptain
     * @return void
     * @throws BadRequestHttpException
     */
    private function changeCaptain(Team $team, PlayerUser $newCaptain): void
    {
        // Retrieve formerCaptain when updating or leaving team
        $formerCaptain = $team->getCaptain();

        // If player is not in team, he can't be new captain
        if (!$team->isTeamPlayer($newCaptain)) {
            throw new BadRequestHttpException('new captain is not in team.');
        }

        // Set the new captain and update their role and status
        $team->setCaptain($newCaptain);
        $newCaptain->setIsCaptain(true);
        $newCaptain->setRoles([UserEnum::ROLE_CAPTAIN->value]);

        // Update the status of the former captain if exists
        if ($formerCaptain) {
            $this->updateFormerCaptain($formerCaptain);
        }
    }

    /**
     * Update former captain isCaptain and role
     * @param PlayerUser $formerCaptain
     * @return void
     */
    private function updateFormerCaptain(PlayerUser $formerCaptain): void
    {
        $formerCaptainIsCaptain = false;

        /** @var Team $team */
        foreach ($formerCaptain->getTeams() as $team) {
            if ($team->getCaptain() === $formerCaptain) {
                $formerCaptainIsCaptain = true;
                break;
            }
        }

        if (!$formerCaptainIsCaptain) {
            $formerCaptain->setIsCaptain(false);
            $formerCaptain->setRoles([UserEnum::ROLE_PLAYER->value]);
        }

        $this->registry->getManager()->persist($formerCaptain);
        $this->registry->getManager()->flush();
    }

    /**
     * Verify playerUser exists and get.
     *
     * @param string $playerId
     * @return PlayerUser
     */
    private function verifyAndGetPlayer(string $playerId): PlayerUser
    {
        $repository = $this->registry->getRepository(PlayerUser::class);

        return $repository->findOneByOrThrow(['id' => $playerId]);
    }

    /**
     * Check if Team is joinable by Player.
     *
     * @param Team $team
     * @param PlayerUser $playerUser
     * @return bool
     */
    private function isTeamJoinableByPlayer(Team $team, PlayerUser $playerUser): bool
    {
        // verify if player is already part of a team with the same type
        $actualTeams = $playerUser->getTeams();

        /** @var Team $actualTeam */
        foreach ($actualTeams as $actualTeam) {
            if ($actualTeam->getType() === $team->getType()) {
                throw new BadRequestHttpException("Player already part of a team with the same type", null, 400);
            }
        }

        if ($this->isTeamFull($team)) {
            throw new BadRequestHttpException("the maximum amount of team players is reached");
        }

        return true;
    }

    /**
     * @param Team $team
     * @return bool
     */
    private function isTeamFull(Team $team): bool
    {
        $maxPlayers = match ($team->getType()) {
            TeamEnum::ONE->value => 1,
            TeamEnum::TWO->value => 2,
            TeamEnum::FOUR->value => 4,
            TeamEnum::EIGHT->value => 8,
        };

        if (count($team->getPlayers()) >= $maxPlayers) {
            return true;
        }

        return false;
    }

    /**
     * @param object $object
     * @param array|null $groups
     * @return void
     */
    private function validateAndSave(object $object, ?array $groups = null): void
    {
        // Verify entity validation
        $this->validationService->performEntityValidation($object, $groups);

        // Save team to Database
        $entityManager = $this->registry->getManager();
        $entityManager->persist($object);
        $entityManager->flush();
    }

}