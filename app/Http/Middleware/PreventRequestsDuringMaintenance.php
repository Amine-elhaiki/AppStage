<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance as Middleware;

class PreventRequestsDuringMaintenance extends Middleware
{
    /**
     * The URIs that should be reachable while maintenance mode is enabled.
     *
     * @var array<int, string>
     */
    protected $except = [
        // Ajoutez ici les URLs qui doivent rester accessibles en mode maintenance
        // Par exemple pour PlanifTech :
        // '/admin/maintenance',
        // '/api/health-check',
    ];
}
