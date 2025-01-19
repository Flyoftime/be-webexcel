<?php

namespace App\Enums;

enum StatusEnums: string
{
    case Pending = 'Pending';
    case Completed  = 'Completed';
    case Cancelled = 'Cancelled';

    
}
