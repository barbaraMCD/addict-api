<?php

namespace App\Enum;

enum TriggerEnumType: string
{
    case SADLY = 'Sadly';
    case ANGRY = 'Angry';
    case ANXIETY = 'Anxiety';
    case BOREDOM = 'Boredom'; // Ennui
    case LONELINESS = 'Loneliness'; // Solitude
    case WORK = 'Work';
    case FAMILY = 'Family';
    case FRIENDS = 'Friends';
    case PARTNER = 'Partner';
    case PARTY = 'Party';
    case CELEBRATIONS = 'Celebrations';
    case HOLIDAYS = 'Holidays';
}
