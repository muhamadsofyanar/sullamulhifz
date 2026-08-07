<?php

namespace App\Providers;

use App\Models\Announcement;
use App\Models\AssignmentSubmission;
use App\Models\FridayDevelopmentSession;
use App\Models\LiaisonMessage;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Paginator::useBootstrapFive();

        Relation::enforceMorphMap([
            'announcement' => Announcement::class,
            'assignment_submission' => AssignmentSubmission::class,
            'friday_session' => FridayDevelopmentSession::class,
            'liaison_message' => LiaisonMessage::class,
            'student' => Student::class,
            'teacher' => Teacher::class,
        ]);

        ResetPassword::createUrlUsing(static function (object $notifiable, string $token): string {
            return rtrim((string) config('sullam.portal_base_url'), '/')
                .'/atur-ulang-kata-sandi/'.$token
                .'?email='.rawurlencode((string) $notifiable->getEmailForPasswordReset());
        });

        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
