<?php

namespace App\Enum\Subscription;

enum PlanType: string
{
    case MONTHLY = 'monthly';
    case ANNUAL = 'annual';
}
