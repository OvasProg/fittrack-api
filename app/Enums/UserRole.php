<?php

namespace App\Enums;

enum UserRole: string
{
    case FREE = 'free';
    case PRO = 'pro';
    case ADMIN = 'admin';
}
