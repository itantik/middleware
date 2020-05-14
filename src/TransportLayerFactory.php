<?php

declare(strict_types=1);

namespace Itantik\Middleware;

final class TransportLayerFactory implements ITransportLayerFactory
{
    public function create(IMiddleware $middleware, ILayer $nextLayer): ILayer
    {
        return new TransportLayer($middleware, $nextLayer);
    }
}
