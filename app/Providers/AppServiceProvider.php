<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Services\QuestionData\Contracts\QuestionDataRepository;
use App\Services\QuestionData\Entities\QuestionData;
use App\Services\QuestionData\Repositories\EloquentQuestionDataRepository;

use App\Services\Users\Contracts\UsersRepository;
use App\Services\Users\Entities\Users;
use App\Services\Users\Repositories\EloquentUsersRepository;

use App\Services\Configurations\Contracts\ConfigurationsRepository;
use App\Services\Configurations\Entities\Configurations;
use App\Services\Configurations\Repositories\EloquentConfigurationsRepository;

use App\Services\CMS\Contracts\CMSRepository;
use App\Services\CMS\Entities\CMS;
use App\Services\CMS\Repositories\EloquentCMSRepository;

use App\Services\EmailTemplate\Contracts\EmailTemplatesRepository;
use App\Services\EmailTemplate\Entities\EmailTemplates;
use App\Services\EmailTemplate\Repositories\EloquentEmailTemplatesRepository;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(QuestionDataRepository::class, function () {
        return new EloquentQuestionDataRepository(new QuestionData());
        });

        $this->app->bind(UsersRepository::class, function () {
        return new EloquentUsersRepository(new Users());
        });

        $this->app->bind(ConfigurationsRepository::class, function () {
        return new EloquentConfigurationsRepository(new Configurations());
        });

        $this->app->bind(CMSRepository::class, function () {
        return new EloquentCMSRepository(new CMS());
        });

        $this->app->bind(EmailTemplatesRepository::class, function () {
        return new EloquentEmailTemplatesRepository(new EmailTemplates());
        });
    }
}
