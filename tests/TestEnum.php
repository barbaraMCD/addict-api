<?php

namespace App\Tests;

enum TestEnum: string
{
    case ENDPOINT_USERS = '/users';
    case ENDPOINT_ADDICTIONS = '/addictions';
    case ENDPOINT_TRIGGERS = '/triggers';

}
