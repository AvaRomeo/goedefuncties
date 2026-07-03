<?php

namespace App\Console\Commands;

use Illuminate\Foundation\Console\KeyGenerateCommand as BaseKeyGenerateCommand;

class KeyGenerateCommand extends BaseKeyGenerateCommand
{
    public function handle()
    {
        $this->components->error(
            'php artisan key:generate is geblokkeerd voor dit project. '
            .'Gevoelige budgetvelden (beginsaldo, bedrag, omschrijving) staan versleuteld '
            .'met de huidige APP_KEY in database.sqlite. Een nieuwe key maakt die data '
            .'onherstelbaar onleesbaar. Gebruik de APP_KEY uit het gecommitte .env-bestand '
            .'(git pull) in plaats van een nieuwe te genereren.'
        );

        return self::FAILURE;
    }
}
