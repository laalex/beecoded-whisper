<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            // Lead permissions
            'leads.view', 'leads.view_all', 'leads.create', 'leads.update', 'leads.delete',
            'leads.assign', 'leads.export', 'leads.import',
            // Interaction permissions
            'interactions.view', 'interactions.create', 'interactions.update', 'interactions.delete',
            // Sequence permissions
            'sequences.view', 'sequences.create', 'sequences.update', 'sequences.delete', 'sequences.execute',
            // Offer permissions
            'offers.view', 'offers.create', 'offers.update', 'offers.delete', 'offers.send',
            // Integration permissions
            'integrations.view', 'integrations.manage', 'integrations.sync',
            // Transcription permissions
            'transcriptions.view', 'transcriptions.create', 'transcriptions.delete',
            // Reminder permissions
            'reminders.view', 'reminders.create', 'reminders.update', 'reminders.delete',
            // Analytics permissions
            'analytics.view', 'analytics.export',
            // Settings permissions
            'settings.view', 'settings.update',
            // User management
            'users.view', 'users.create', 'users.update', 'users.delete', 'users.manage_roles',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Admin - full access
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin->syncPermissions(Permission::all());

        // Manager - can manage team and view all leads
        $manager = Role::firstOrCreate(['name' => 'manager', 'guard_name' => 'web']);
        $manager->syncPermissions([
            'leads.view', 'leads.view_all', 'leads.create', 'leads.update', 'leads.assign', 'leads.export',
            'interactions.view', 'interactions.create', 'interactions.update',
            'sequences.view', 'sequences.create', 'sequences.update', 'sequences.execute',
            'offers.view', 'offers.create', 'offers.update', 'offers.send',
            'integrations.view', 'integrations.sync',
            'transcriptions.view', 'transcriptions.create',
            'reminders.view', 'reminders.create', 'reminders.update',
            'analytics.view', 'analytics.export',
            'users.view',
        ]);

        // Sales Rep - can manage own leads
        $salesRep = Role::firstOrCreate(['name' => 'sales_rep', 'guard_name' => 'web']);
        $salesRep->syncPermissions([
            'leads.view', 'leads.create', 'leads.update',
            'interactions.view', 'interactions.create', 'interactions.update',
            'sequences.view', 'sequences.execute',
            'offers.view', 'offers.create', 'offers.update', 'offers.send',
            'integrations.view',
            'transcriptions.view', 'transcriptions.create',
            'reminders.view', 'reminders.create', 'reminders.update',
            'analytics.view',
        ]);

        // Viewer - read-only access
        $viewer = Role::firstOrCreate(['name' => 'viewer', 'guard_name' => 'web']);
        $viewer->syncPermissions([
            'leads.view',
            'interactions.view',
            'sequences.view',
            'offers.view',
            'analytics.view',
        ]);
    }
}
