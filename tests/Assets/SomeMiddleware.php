<?php

declare(strict_types=1);

namespace Tests\Assets;

use Itantik\Middleware\ILayer;
use Itantik\Middleware\IMiddleware;
use Itantik\Middleware\IRequest;
use Itantik\Middleware\IResponse;

class SomeMiddleware implements IMiddleware
{
    /** @var string */
    private $id;

    public function __construct(string $id)
    {
        $this->id = $id;
    }

    public function handle(IRequest $request, ILayer $nextLayer): IResponse
    {
        if ($request instanceof LoggableRequest) {
            $request->addLog(sprintf('MyMiddleware %s: begin', $this->id));
        }

        $resp = $nextLayer->handle($request);

        if ($resp instanceof LoggableResponse) {
            $resp->addLog(sprintf('MyMiddleware %s: end', $this->id));
        }

        return $resp;
    }
}
