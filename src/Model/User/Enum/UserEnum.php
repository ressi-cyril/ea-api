<?php

namespace App\Model\User\Enum;
enum UserEnum: string
{
    case STAFF = 'staff';
    case PLAYER = 'player';
    case ROLE_STAFF = 'ROLE_STAFF';
    case ROLE_PLAYER = 'ROLE_PLAYER';
    case ROLE_CAPTAIN = 'ROLE_CAPTAIN';

}