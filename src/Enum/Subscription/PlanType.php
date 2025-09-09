<?php

namespace App\Enum\Subscription;

enum PlanType: string
{
    case MONTHLY = 'premium_monthly';
    case ANNUAL = 'premium_annual';
}
