<?php

namespace App\Enum\Trigger;

enum TriggerType: string
{
    case SADLY = 'sadly';
    case ANGRY = 'angry';
    case ANXIETY = 'anxiety';
    case BOREDOM = 'boredom'; // Ennui
    case LONELINESS = 'loneliness'; // Solitude
    case WORK = 'work';
    case FAMILY = 'family';
    case FRIENDS = 'friends';
    case PARTNER = 'partner';
    case PARTY = 'party';
    case CELEBRATIONS = 'celebrations';
    case HOLIDAYS = 'holidays';
}
