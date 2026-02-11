<?

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Проверяем, включена ли поддержка команд в конфиге
        if (!config('filament-users-permissions.features.teams', false)) {
            return;
        }

        // Создаем таблицу команд
        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique()->nullable();
            $table->text('description')->nullable();
            $table->json('settings')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('owner_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });

        // Добавляем team_id в таблицы ролей и разрешений
        Schema::table('roles', function (Blueprint $table) {
            $table->foreignId('team_id')
                ->nullable()
                ->after('guard_name')
                ->constrained('teams')
                ->onDelete('cascade');
            
            $table->unique(['name', 'team_id', 'guard_name']);
        });

        Schema::table('permissions', function (Blueprint $table) {
            $table->foreignId('team_id')
                ->nullable()
                ->after('guard_name')
                ->constrained('teams')
                ->onDelete('cascade');
            
            $table->unique(['name', 'team_id', 'guard_name']);
        });

        // Создаем таблицу для связи пользователей с командами
        Schema::create('team_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('role')->default('member');
            $table->json('permissions')->nullable();
            $table->timestamps();
            
            $table->unique(['team_id', 'user_id']);
        });

        // Создаем таблицу для ролей команд
        Schema::create('team_roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->json('permissions')->nullable();
            $table->foreignId('team_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        if (!config('filament-users-permissions.features.teams', false)) {
            return;
        }

        Schema::dropIfExists('team_roles');
        Schema::dropIfExists('team_user');
        
        Schema::table('permissions', function (Blueprint $table) {
            $table->dropForeign(['team_id']);
            $table->dropUnique(['name', 'team_id', 'guard_name']);
            $table->dropColumn('team_id');
        });
        
        Schema::table('roles', function (Blueprint $table) {
            $table->dropForeign(['team_id']);
            $table->dropUnique(['name', 'team_id', 'guard_name']);
            $table->dropColumn('team_id');
        });
        
        Schema::dropIfExists('teams');
    }
};