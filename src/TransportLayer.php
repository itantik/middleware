<?php

declare(strict_types=1);

namespace Itantik\Middleware;

final class TransportLayer implements ILayer
{
    /** @var IMiddleware */
    private $middleware;
    /** @var ILayer */
    private $nextLayer;

    public function __construct(IMiddleware $middleware, ILayer $nextLayer)
    {
        $this->middleware = $middleware;
        $this->nextLayer = $nextLayer;
    }

    public function handle(IRequest $request): IResponse
    {
        return $this->middleware->handle($request, $this->nextLayer);
    }
}
