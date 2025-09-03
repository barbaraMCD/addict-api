<?php

namespace App\Tests;

enum TestEnum: string
{
    case ENDPOINT_USERS = '/users';

    case ENDPOINT_REGISTER = '/api/register';
    case ENDPOINT_ADDICTIONS = '/addictions';

    case ENDPOINT_CONSUMPTIONS = '/consumptions';
    case ENDPOINT_TRIGGERS = '/triggers';

    case ENDPOINT_LOGIN = '/api/login_check';

}
