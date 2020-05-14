<?php

declare(strict_types=1);

namespace Itantik\Middleware;

interface IMiddleware
{
    public function handle(IRequest $request, ILayer $nextLayer): IResponse;
}
