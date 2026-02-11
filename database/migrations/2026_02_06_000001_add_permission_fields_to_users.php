<?

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'is_super_admin')) {
                $table->boolean('is_super_admin')
                    ->default(false)
                    ->after('remember_token')
                    ->comment('Super administrator flag');
            }
            
            if (!Schema::hasColumn('users', 'permissions_override')) {
                $table->json('permissions_override')
                    ->nullable()
                    ->after('is_super_admin')
                    ->comment('Custom permissions override');
            }
            
            if (!Schema::hasColumn('users', 'last_permission_sync')) {
                $table->timestamp('last_permission_sync')
                    ->nullable()
                    ->after('permissions_override')
                    ->comment('Last time permissions were synced');
            }
            
            if (!Schema::hasColumn('users', 'access_level')) {
                $table->enum('access_level', ['basic', 'extended', 'full'])
                    ->default('basic')
                    ->after('last_permission_sync')
                    ->comment('User access level');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $columns = ['is_super_admin', 'permissions_override', 'last_permission_sync', 'access_level'];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};