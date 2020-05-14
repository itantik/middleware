<?php

declare(strict_types=1);

namespace Itantik\Middleware;

interface ITransportLayerFactory
{
    public function create(IMiddleware $middleware, ILayer $nextLayer): ILayer;
}
