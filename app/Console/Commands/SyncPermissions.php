<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

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
            ['name' => 'read_roles', 'readable_name' => 'Просматривать список ролей'],
            ['name' => 'edit_roles', 'readable_name' => 'Редактировать ролей'],
            ['name' => 'create_roles', 'readable_name' => 'Создавать ролей'],
            ['name' => 'delete_roles', 'readable_name' => 'Удалять ролей'],
            ['name' => 'read_users', 'readable_name' => 'Просматривать список пользователей'],
            ['name' => 'edit_users', 'readable_name' => 'Редактировать пользователей'],
            ['name' => 'create_users', 'readable_name' => 'Создавать пользователей'],
            ['name' => 'delete_users', 'readable_name' => 'Удалять пользователей'],
            ['name' => 'toggle_activity_users', 'readable_name' => 'Изменять активность пользователей'],
            ['name' => 'read_all_patients', 'readable_name' => 'Просматривать всех пациентов'],
            ['name' => 'select_doctor_patients', 'readable_name' => 'Выбрать врача при создании/редактировании'],
            ['name' => 'edit_patients', 'readable_name' => 'Редактировать пациентов'],
            ['name' => 'create_patients', 'readable_name' => 'Создавать пациентов'],
            ['name' => 'delete_patients', 'readable_name' => 'Удалять пациентов'],
            ['name' => 'read_doctors', 'readable_name' => 'Просматривать список врачей'],
            ['name' => 'edit_doctors', 'readable_name' => 'Редактировать врачей'],
            ['name' => 'create_doctors', 'readable_name' => 'Создавать врачей'],
            ['name' => 'delete_doctors', 'readable_name' => 'Удалять врачей'],
            ['name' => 'add_report', 'readable_name' => 'Добавлять диагноз'],
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

        DB::transaction(function () use ($permissions): void {
            foreach ($permissions as $permission) {
                $model = Permission::updateOrCreate(
                    ['name' => $permission['name']],
                    ['readable_name' => $permission['readable_name']]
                );

                if ($model->wasRecentlyCreated) {
                    $this->info(sprintf('Created %s permission', $permission['name']));
                }
            }

            $permissionNames = array_column($permissions, 'name');

            Permission::query()
                ->whereNotIn('name', $permissionNames)
                ->each(function (Permission $permission): void {
                    $this->info(sprintf('Deleted %s permission', $permission->name));
                    $permission->delete();
                });

            if ($role = Role::where('name', 'admin')->first()) {
                $role->syncPermissions($permissionNames);
            }
        });

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return Command::SUCCESS;
    }
}
