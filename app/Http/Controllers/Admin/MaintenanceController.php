<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Database updates from inside the admin panel.
 *
 * Code is deployed by pulling the repository, which cannot run `php artisan
 * migrate` on its own. This page lists the migrations that have not run yet
 * and applies them with one click, so a release that adds columns never has
 * to be finished from a terminal. Migrations in this project are additive
 * (new columns, new settings), so running them never alters existing rows.
 */
class MaintenanceController extends Controller
{
    public const PENDING_CACHE_KEY = 'admin.migrations.pending';

    public function index()
    {
        $pending = self::pendingMigrations();

        return view('admin.maintenance.index', compact('pending'));
    }

    public function migrate()
    {
        try {
            Artisan::call('migrate', ['--force' => true]);
            $output = trim(Artisan::output());
            Cache::forget(self::PENDING_CACHE_KEY);
            Cache::flush();

            return redirect()->route('admin.maintenance.index')
                ->with('success', 'Database updated.')
                ->with('migrate_output', $output);
        } catch (\Throwable $e) {
            Log::error('Admin migrate failed: ' . $e->getMessage());

            return redirect()->route('admin.maintenance.index')
                ->with('error', 'Database update failed: ' . $e->getMessage());
        }
    }

    /**
     * Migration files that have not been recorded as run. Cached briefly so
     * the admin layout's banner check is not one extra query on every page.
     *
     * @return string[]
     */
    public static function pendingMigrations(bool $fresh = false): array
    {
        if ($fresh) {
            Cache::forget(self::PENDING_CACHE_KEY);
        }

        return Cache::remember(self::PENDING_CACHE_KEY, 60, function () {
            try {
                $migrator = app('migrator');
                if (!$migrator->repositoryExists()) {
                    return [];
                }
                $files = $migrator->getMigrationFiles($migrator->paths() ?: [database_path('migrations')]);
                $ran = $migrator->getRepository()->getRan();

                return array_values(array_diff(array_keys($files), $ran));
            } catch (\Throwable $e) {
                Log::warning('Could not read migration status: ' . $e->getMessage());

                return [];
            }
        });
    }
}
