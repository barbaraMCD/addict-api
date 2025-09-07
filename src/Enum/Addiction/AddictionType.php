<?php

namespace App\Enum\Addiction;

enum AddictionType: string
{
    case SUGAR = 'sugar';
    case CAFFEINE = 'caffeine';
    case GAMBLING = 'gambling';
    case VIDEO_GAMES = 'video games';
    case FASTFOOD = 'fastfood';
    case CIGARETTES = 'cigarettes';
    case ALCOHOL = 'alcohol';
    case CANNABIS = 'cannabis';
    case CLOTHES = 'clothes';
    case HARD_DRUGS = 'hard drugs';
}
