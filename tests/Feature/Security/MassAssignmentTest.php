<?php

namespace Tests\Feature\Security;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class MassAssignmentTest extends TestCase
{
    /**
     * Audit that every Eloquent model has either $fillable or explicit $guarded.
     */
    public function test_all_models_have_explicit_mass_assignment_protection(): void
    {
        $modelFiles = File::files(app_path('Models'));

        $this->assertNotEmpty($modelFiles, 'Models directory should contain model classes.');

        foreach ($modelFiles as $file) {
            $className = 'App\\Models\\'.$file->getFilenameWithoutExtension();

            if (! class_exists($className)) {
                continue;
            }

            $reflection = new \ReflectionClass($className);
            if ($reflection->isAbstract() || ! $reflection->isSubclassOf(Model::class)) {
                continue;
            }

            /** @var Model $instance */
            $instance = new $className;
            $fillable = $instance->getFillable();
            $guarded = $instance->getGuarded();

            $hasFillable = ! empty($fillable);
            $isGuardedAll = $guarded === ['*'];
            $isExplicitlyGuarded = ! empty($guarded) && $guarded !== [];

            $this->assertTrue(
                $hasFillable || $isGuardedAll || $isExplicitlyGuarded,
                "Model [{$className}] lacks mass-assignment protection (neither fillable nor guarded configured)."
            );
        }
    }

    /**
     * Verify that sensitive fields are guarded or hidden on User.
     */
    public function test_user_model_hides_sensitive_credentials(): void
    {
        $user = new User([
            'name' => 'Test User',
            'email' => 'test@taallumbd.com',
            'password' => 'secret_hash',
            'two_factor_secret' => 'SECRETKEY123',
            'two_factor_recovery_codes' => ['CODE1', 'CODE2'],
        ]);

        $array = $user->toArray();

        $this->assertArrayNotHasKey('password', $array);
        $this->assertArrayNotHasKey('remember_token', $array);
        $this->assertArrayNotHasKey('two_factor_secret', $array);
        $this->assertArrayNotHasKey('two_factor_recovery_codes', $array);
    }
}
