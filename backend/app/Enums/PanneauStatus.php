<?php

declare(strict_types=1);

namespace App\Enums;

enum PanneauStatus: string
{
case ACTIF = 'actif';
case MAINTENANCE = 'maintenance';
case HORS_SERVICE = 'hors_service';
}
