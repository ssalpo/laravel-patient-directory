<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class SyncPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permission:sync-all';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync all permissions';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $permissions = [
            ['name' => 'payments_manage', 'readable_name' => 'Управлять выплатами'],
            ['name' => 'add_comment', 'readable_name' => 'Добавлять комментарий'],
            ['name' => 'read_medical_clinics', 'readable_name' => 'Просматривать список учреждений'],
            ['name' => 'edit_medical_clinics', 'readable_name' => 'Редактирование учреждения'],
            ['name' => 'create_medical_clinics', 'readable_name' => 'Добавлять учреждения'],
            ['name' => 'delete_medical_clinics', 'readable_name' => 'Удалять учреждения'],
            ['name' => 'manage_locations', 'readable_name' => 'Управлять локациями'],
            ['name' => 'share_patients', 'readable_name' => 'Делиться пациентами'],
            ['name' => 'read_shared_patients', 'readable_name' => 'Просматривать поделенных пациентов'],
        ];

        foreach ($permissions as $permission) {
            if (! Permission::whereName($permission['name'])->exists()) {
                Permission::create($permission);

                $this->info(sprintf('Created %s permission', $permission['name']));
            }
        }

        if ($role = Role::where('name', 'admin')->first()) {
            $role->syncPermissions(array_column($permissions, 'name'));
        }

        return Command::SUCCESS;
    }
}
