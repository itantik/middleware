<?php

declare(strict_types=1);

namespace Tests\Assets;

use Itantik\Middleware\ILayer;
use Itantik\Middleware\IRequest;
use Itantik\Middleware\IResponse;

class CoreLayer implements ILayer
{
    public function handle(IRequest $request): IResponse
    {
        if ($request instanceof LoggableRequest) {
            $request->addLog('CoreLayer: begin');
        }

        $resp = new LoggableResponse();
        $resp->addLog('CoreLayer: end');

        return $resp;
    }
}
