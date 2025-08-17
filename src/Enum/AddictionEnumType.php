<?php

namespace App\Enum;

enum AddictionEnumType: string
{
    case SUGAR = 'Sugar';
    case CAFFEINE = 'Caffeine';
    case GAMBLING = 'Gambling';
    case VIDEO_GAMES = 'Video games';
    case FASTFOOD = 'Fastfood';
    case CIGARETTES = 'Cigarettes';
    case ALCOHOL = 'Alcohol';
    case CANNABIS = 'Cannabis';
    case CLOTHES = 'Clothes';
    case HARD_DRUGS = 'Hard drugs';
}
