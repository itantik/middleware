<?php

declare(strict_types=1);

namespace Itantik\Middleware;

class Manager
{
    /** @var ITransportLayerFactory */
    private $transportLayerFactory;
    /** @var IMiddleware[] */
    private $stack = [];


    public function __construct(?ITransportLayerFactory $transportLayerFactory = null)
    {
        $this->transportLayerFactory = $transportLayerFactory ?: new TransportLayerFactory();
    }

    public function append(IMiddleware $middleware): void
    {
        $this->stack[] = $middleware;
    }

    public function prepend(IMiddleware $middleware): void
    {
        array_unshift($this->stack, $middleware);
    }

    public function process(IRequest $request, ILayer $coreLayer): IResponse
    {
        $layer = $this->createLayer(0, $coreLayer);
        return $layer->handle($request);
    }

    private function createLayer(int $index, ILayer $coreLayer): ILayer
    {
        $stack = $this->stack;
        if (count($stack) > $index) {
            $middleware = $stack[$index];
            $nextLayer = $this->createLayer($index + 1, $coreLayer);
            return $this->transportLayerFactory->create($middleware, $nextLayer);
        }
        return $coreLayer;
    }
}
