<?

namespace Kushelbek\FilamentUsersPermissionsRoles\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class PublishCommand extends Command
{
    protected $signature = 'filament-users-permissions:publish 
                            {--tag=* : Tags to publish (config, migrations, translations, views, assets, all)}
                            {--force : Overwrite existing files}
                            {--only=* : Publish only specific types}
                            {--except=* : Publish all except specific types}';
    
    protected $description = 'Publish specific resources of the package';
    
    protected $availableTags = [
        'config',
        'migrations',
        'translations',
        'views',
        'assets',
        'stubs',
        'all',
    ];
    
    public function handle(): int
    {
        $tags = $this->option('tag');
        $force = $this->option('force');
        
        if (empty($tags)) {
            $tags = ['all'];
        }
        
        if (in_array('all', $tags)) {
            $tags = ['config', 'migrations', 'translations', 'views', 'assets', 'stubs'];
        }
        
        foreach ($tags as $tag) {
            if (!in_array($tag, $this->availableTags) && $tag !== 'all') {
                $this->error("Tag '{$tag}' is not available. Available tags: " . implode(', ', $this->availableTags));
                continue;
            }
            
            $this->publishTag($tag, $force);
        }
        
        $this->info('✅ Publishing completed successfully!');
        
        return self::SUCCESS;
    }
    
    protected function publishTag(string $tag, bool $force): void
    {
        $tagName = "filament-users-permissions-{$tag}";
        
        $this->info("📦 Publishing {$tag}...");
        
        if ($this->callSilently('vendor:publish', [
            '--tag' => $tagName,
            '--force' => $force,
        ]) === 0) {
            $this->info("  ✅ {$tag} published successfully");
            
            // Дополнительные действия после публикации
            $this->postPublishActions($tag);
        } else {
            $this->error("  ❌ Failed to publish {$tag}");
        }
    }
    
    protected function postPublishActions(string $tag): void
    {
        switch ($tag) {
            case 'config':
                $this->info("  📋 Configuration file: config/filament-users-permissions.php");
                break;
                
            case 'migrations':
                $migrations = glob(database_path('migrations/*_filament_users_permissions_*.php'));
                $count = count($migrations);
                $this->info("  📋 Published {$count} migration(s)");
                break;
                
            case 'translations':
                $enPath = lang_path('vendor/filament-users-permissions/en');
                $ruPath = lang_path('vendor/filament-users-permissions/ru');
                
                if (File::exists($enPath)) {
                    $this->info("  🌐 English translations: {$enPath}");
                }
                if (File::exists($ruPath)) {
                    $this->info("  🌐 Russian translations: {$ruPath}");
                }
                break;
                
            case 'views':
                $viewsPath = resource_path('views/vendor/filament-users-permissions');
                if (File::exists($viewsPath)) {
                    $viewCount = count(File::allFiles($viewsPath));
                    $this->info("  👁️ Published {$viewCount} view(s)");
                }
                break;
        }
    }
}