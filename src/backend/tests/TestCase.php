<?php

namespace Tests;

use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Run seeder after database refresh.
     */
    protected bool $seed = true;
    protected string $seeder = RolesAndPermissionsSeeder::class;
}
