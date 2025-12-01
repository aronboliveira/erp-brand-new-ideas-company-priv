<?php

use Illuminate\Support\Facades\Schema;
use App\Config\Constants\DatabaseConstants;
use Illuminate\Database\{Migrations\Migration, Schema\Blueprint};

class CreatePermissionTables extends Migration
{
    private const GUARD = 'guard_name';
    private const ENTITY = 'permission';
    private const C = '.cache';
    private const S = '.store';
    private const TABLE = self::ENTITY . 's';
    private const TABLE_ROLES = 'roles';
    private const MD = 'model';
    private const COL_PERM = self::ENTITY . '_id';
    private const COL_ROLE = 'role_id';
    private const MODEL_ID = 'model_id';
    private const MODEL_TYPE = 'model_type';
    private const COL_NAME = 'name';
    private const COL_MODEL_MORPH_KEY = self::MD . '_' . 'morph_key';
    private const TABLE_MODEL_HAS_PERMISSIONS = self::MD . '_has_permissions';
    private const TABLE_MODEL_HAS_ROLES = self::MD . '_has_roles';
    private const TABLE_ROLE_HAS_PERMISSIONS = 'role_has_permissions';
    public function up(): void
    {
        $tableNames = config(self::ENTITY . '.table_names');
        $columnNames = config(self::ENTITY . '.column_names');
        if (empty($tableNames))
            throw new \Exception('Error: config/permission.php not loaded. Run [php artisan config:clear] and try again.');
        Schema::create($tableNames[self::TABLE], function (Blueprint $table) {
            $table->bigIncrements('id'); // ! CHANGED
            $table->string(self::COL_NAME);
            $table->string(self::GUARD);
            $table->timestamps();
        });
        Schema::create($tableNames[self::TABLE_ROLES], function (Blueprint $table) {
            $table->uuid('id')->primary(); // ! CHANGED
            $table->string(self::COL_NAME);
            $table->string(self::GUARD);
            $table->uuid(DatabaseConstants::COL_TABLE_CREATOR)
                ->default(DatabaseConstants::DEFAULT_UUID);
            $table->timestamps();
        });
        Schema::create($tableNames[self::TABLE_MODEL_HAS_PERMISSIONS], function (Blueprint $table) use ($tableNames, $columnNames) {
            $table->unsignedBigInteger(self::COL_PERM); // ! CHANGED
            $table->string(self::MODEL_TYPE);
            $table->uuid($columnNames[self::COL_MODEL_MORPH_KEY]); // ! CHANGED
            $table->index(
                [$columnNames[self::COL_MODEL_MORPH_KEY], self::MODEL_TYPE],
                self::TABLE_MODEL_HAS_PERMISSIONS . '_' . self::MODEL_ID . '_' . self::MODEL_TYPE . '_index'
            );
            $table->foreign(self::COL_PERM)
                ->references('id')
                ->on($tableNames[self::TABLE])
                ->onDelete('cascade');
            $table->primary(
                [self::COL_PERM, $columnNames[self::COL_MODEL_MORPH_KEY], self::MODEL_TYPE],
                self::TABLE_MODEL_HAS_PERMISSIONS . '_' . self::ENTITY . '_' . self::MODEL_TYPE . '_primary'
            );
        });
        Schema::create($tableNames[self::TABLE_MODEL_HAS_ROLES], function (Blueprint $table) use ($tableNames, $columnNames) {
            $table->uuid(self::COL_ROLE); // ! CHANGED
            $table->string(self::MODEL_TYPE);
            $table->uuid($columnNames[self::COL_MODEL_MORPH_KEY]); // ! CHANGED
            $table->index(
                [$columnNames[self::COL_MODEL_MORPH_KEY], self::MODEL_TYPE],
                self::TABLE_MODEL_HAS_ROLES . '_' . self::MODEL_ID . '_' . self::MODEL_TYPE . '_index'
            );
            $table->foreign(self::COL_ROLE)
                ->references('id')
                ->on($tableNames[self::TABLE_ROLES])
                ->onDelete('cascade');
            $table->primary(
                [self::COL_ROLE, $columnNames[self::COL_MODEL_MORPH_KEY], self::MODEL_TYPE],
                self::TABLE_MODEL_HAS_PERMISSIONS . '_role_' . self::MODEL_TYPE . '_primary'
            );
        });
        Schema::create($tableNames[self::TABLE_ROLE_HAS_PERMISSIONS], function (Blueprint $table) use ($tableNames) {
            $table->unsignedBigInteger(self::COL_PERM); // ! CHANGED
            $table->uuid(self::COL_ROLE); // ! CHANGED
            $table->foreign(self::COL_PERM)
                ->references('id')
                ->on($tableNames[self::TABLE])
                ->onDelete('cascade');
            $table->foreign(self::COL_ROLE)
                ->references('id')
                ->on($tableNames[self::TABLE_ROLES])
                ->onDelete('cascade');
            $table->primary(
                [self::COL_PERM, self::COL_ROLE],
                self::TABLE_ROLE_HAS_PERMISSIONS . '_' . self::COL_PERM . '_' . self::COL_ROLE . '_primary'
            );
        });
        app('cache')
            ->store(config(self::ENTITY . self::C . self::S) != 'default' ?
                config(self::ENTITY . self::C . self::S) : null)
            ->forget(config(self::ENTITY . self::C . '.key'));
    }

    public function down(): void
    {
        $tableNames = config(self::ENTITY . '.table_names');
        if (empty($tableNames))
            throw new \Exception('Error: config/permission.php not found and defaults could not be merged. Please publish the package configuration before proceeding, or drop the tables manually.');
        Schema::drop($tableNames[self::TABLE_ROLE_HAS_PERMISSIONS]);
        Schema::drop($tableNames[self::TABLE_MODEL_HAS_ROLES]);
        Schema::drop($tableNames[self::TABLE_MODEL_HAS_PERMISSIONS]);
        Schema::drop($tableNames[self::TABLE_ROLES]);
        Schema::drop($tableNames[self::TABLE]);
    }
}
