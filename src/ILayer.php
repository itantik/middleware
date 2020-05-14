<?php

declare(strict_types=1);

namespace Itantik\Middleware;

interface ILayer
{
    public function handle(IRequest $request): IResponse;
}
