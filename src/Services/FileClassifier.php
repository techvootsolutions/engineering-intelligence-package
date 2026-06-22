<?php

namespace Dev\EipAgent\Services;

use SplFileInfo;

class FileClassifier
{
    public function classify(SplFileInfo $file): string
    {
        $path = $file->getRealPath();
        
        if (str_contains($path, '/Http/Controllers/')) return 'controllers';
        if (str_contains($path, '/Models/')) return 'models';
        if (str_contains($path, '/Http/Requests/')) return 'requests';
        if (str_contains($path, '/Http/Middleware/')) return 'middleware';
        if (str_contains($path, '/Policies/')) return 'policies';
        if (str_contains($path, '/Events/')) return 'events';
        if (str_contains($path, '/Listeners/')) return 'listeners';
        if (str_contains($path, '/Jobs/')) return 'jobs';
        if (str_contains($path, '/Notifications/')) return 'notifications';
        if (str_contains($path, '/Mail/')) return 'mail';
        if (str_contains($path, '/Providers/')) return 'providers';
        if (str_contains($path, '/Services/')) return 'services';
        if (str_contains($path, '/Traits/')) return 'traits';
        if (str_contains($path, '/Helpers/')) return 'helpers';
        if (str_contains($path, '/Console/Commands/')) return 'commands';
        if (str_contains($path, '/routes/')) return 'routes';
        if (str_contains($path, '/config/')) return 'configs';
        if (str_contains($path, '/bootstrap/')) return 'bootstrap';
        if (str_contains($path, '/Enums/')) return 'enums';
        if (str_contains($path, '/Observers/')) return 'observers';
        if (str_contains($path, '/database/factories/')) return 'factories';
        if (str_contains($path, '/database/seeders/')) return 'seeders';
        if (str_contains($path, '/database/migrations/')) return 'migrations';
        if (str_contains($path, '/Broadcasting/')) return 'channels';
        
        return 'other';
    }
}
