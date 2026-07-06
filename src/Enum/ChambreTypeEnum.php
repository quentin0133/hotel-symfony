<?php
namespace App\Enum;

/**
 * Room types
 */
enum ChambreTypeEnum: string
{
    case SIMPLE = 'Simple';
    case DOUBLE = 'Double Standard';
    case TWIN = 'Twin';
    case TRIPLE = 'Triple';
    case JUNIOR_SUITE = 'Suite Junior';
    case FAMILY_SUITE = 'Suite Familiale';
    case PRESIDENTIAL_SUITE = "Suite Présidentielle";
    case DORMITORY = "Dortoir";
    case STUDIO = "Studio";
}
