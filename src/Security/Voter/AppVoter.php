<?php

namespace App\Security\Voter;

use App\Entity\Tournament\TournamentMatch;
use App\Model\User\Type\UserType;
use App\Repository\Team\TeamRepository;
use App\Repository\User\UserRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Bundle\SecurityBundle\Security;

class AppVoter extends Voter
{
    public const DELETE_TEAM = 'delete_team';
    public const UPDATE_TEAM = 'update_team';
    public const INVITE_TEAM = 'invite_team';
    public const JOIN_TEAM = 'join_team';
    public const LEAVE_TEAM = 'leave_team';
    public const DECLINE_TEAM = 'decline_team';
    public const JOIN_TOURNAMENT = 'join_tournament';
    public const LEAVE_TOURNAMENT = 'leave_tournament';
    public const UPDATE_PERSONAL_INFO = 'update_personal_info';
    public const DELETE_SELF_USER = 'delete_self_user';
    public const REPORT_MATCH = 'report_match';

    public TeamRepository $teamRepository;
    public UserRepository $userRepository;
    public Security $security;

    public function __construct(TeamRepository $teamRepository, UserRepository $userRepository, Security $security)
    {
        $this->teamRepository = $teamRepository;
        $this->userRepository = $userRepository;
        $this->security = $security;
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        if (!in_array(
            $attribute,
            [
                self::DELETE_TEAM,
                self::UPDATE_TEAM,
                self::INVITE_TEAM,
                self::JOIN_TEAM,
                self::LEAVE_TEAM,
                self::DECLINE_TEAM,
                self::JOIN_TOURNAMENT,
                self::LEAVE_TOURNAMENT,
                self::UPDATE_PERSONAL_INFO,
                self::DELETE_SELF_USER,
                self::REPORT_MATCH
            ]
        )) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $userConnected = $token->getUser();

        if (!$userConnected) {
            return false;
        }

        switch ($attribute) {
            // Connected user is the one who is joining / leaving / decline Team
            // Connected user is the one who updating or deleting himself
            // subject is PlayerUser
            case self::DELETE_SELF_USER:
            case self::UPDATE_PERSONAL_INFO:
            case self::DECLINE_TEAM:
            case self::LEAVE_TEAM:
            case self::JOIN_TEAM:
                $userGiven = $this->userRepository->findOneBy(['id' => $subject]);
                if (!$userGiven || $userGiven->getId() !== $userConnected->getId()) {
                    return false;
                }
                return true;

            // Connected user must be Team Captain who is deleting / updating / inviting to Team
            // Connected user must be Team Captain who is joining / leaving a Tournament
            // subject is Team
            case self::DELETE_TEAM:
            case self::UPDATE_TEAM:
            case self::INVITE_TEAM:
            case self::JOIN_TOURNAMENT:
            case self::LEAVE_TOURNAMENT:
                $team = $this->teamRepository->findOneBy(['id' => $subject]);
                if (!$team) {
                    return false;
                }
                if ($userConnected->getUserIdentifier() !== $team->getCaptain()->getUserIdentifier()) {
                    return false;
                }

                if (!$this->security->isGranted('ROLE_CAPTAIN')) {
                    return false;
                }
                return true;

            // Connected user reporting must be one of Match Team's Captain
            // subject is Match
            case self::REPORT_MATCH:
                /** @var  $subject TournamentMatch */
                if ($userConnected->getUserIdentifier() !== $subject->getTeamOne()->getCaptain()->getUserIdentifier() &&
                    $userConnected->getUserIdentifier() !== $subject->getTeamTwo()->getCaptain()->getUserIdentifier()) {
                    return false;
                }
                return true;
        }

        return false;
    }
}
