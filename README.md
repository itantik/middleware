# PHP Middleware

General PHP middleware implementation. You can see many similarities, but it does not conform to any PSR. The main goal is to provide a generic middleware processor, not just HTTP request/response handler.

## Installation

```
composer require itantik/middleware
```

## Usage

Let's look at an example.

#### Request

Request implements `Itantik\Middleware\IRequest`. Otherwise it is a plain object, most often a data transfer object.

```php
class LoggableRequest implements IRequest
{
    /** @var string[] */
    private $logs = [];

    public function addLog(string $message): void
    {
        $this->logs[] = $message;
    }

    /**
     * @return string[]
     */
    public function getLogs(): array
    {
        return $this->logs;
    }
}
```

#### Response

Similar to the request, the response is a plain object that implements `Itantik\Middleware\IResponse` interface.

```php
class LoggableResponse implements IResponse
{
    /** @var string[] */
    private $logs = [];

    public function addLog(string $message): void
    {
        $this->logs[] = $message;
    }

    /**
     * @return string[]
     */
    public function getLogs(): array
    {
        return $this->logs;
    }
}
```

#### Middleware

Middleware implements `Itantik\Middleware\IMiddleware` interface.

```php
class FirstMiddleware implements IMiddleware
{
    public function handle(IRequest $request, ILayer $nextLayer): IResponse
    {
        // do something BEFORE next middleware
        if ($request instanceof LoggableRequest) {
            $request->addLog('FirstMiddleware begin');
        }

        // MUST invoke next middleware
        $resp = $nextLayer->handle($request);

        // do something AFTER next middleware
        if ($resp instanceof LoggableResponse) {
            $resp->addLog('FirstMiddleware end');
        }

        // MUST return response
        return $resp;
    }
}

class SecondMiddleware implements IMiddleware
{
    public function handle(IRequest $request, ILayer $nextLayer): IResponse
    {
        // here do nothing BEFORE next middleware

        // MUST invoke next middleware
        $resp = $nextLayer->handle($request);

        // do something AFTER next middleware
        if ($resp instanceof LoggableResponse) {
            $resp->addLog('SecondMiddleware end');
        }

        // MUST return response
        return $resp;
    }
}
```

An example of middleware that wraps each request handler into a database transaction.

```php
class TransactionalMiddleware implements IMiddleware
{
    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function handle(IRequest $request, ILayer $nextLayer): IResponse
    {
        $connection = $this->connection;

        // begin database transaction
        $connection->beginTransaction();

        try {
            // invoke next middleware
            $resp = $nextLayer->handle($request);

            // commit database transaction
            $connection->commit();
        } catch (Exception $e) {
            // rollback database transaction
            $connection->rollback();
            throw $e;
        }

        // return response
        return $resp;
    }
}
```

#### Core layer

Core layer is the last segment in the middleware chain. It processes the request and returns a response. Core layer implements `Itantik\Middleware\ILayer` interface.

```php
class CoreLayer implements ILayer
{
    public function handle(IRequest $request): IResponse
    {
        if ($request instanceof LoggableRequest) {
            $request->addLog('CoreLayer begin');
        }

        // create response
        $resp = new LoggableResponse();
        $resp->addLog('CoreLayer end');

        // return response
        return $resp;
    }
}
```

#### Middleware manager

Middleware manager registers all middlewares, processes a given request through them and returns a response.

```php
// create middleware manager
$manager = new \Itantik\Middleware\Manager();

// register middlewares
$manager->append(new FirstMiddleware());
$manager->append(new SecondMiddleware());

// create request
$request = new LoggableRequest();

// create core layer for this request
$coreLayer = new CoreLayer();

// run it
$response = $manager->process($request, $coreLayer);

// expected result
$requestLogs = $request->getLogs();
// [
//    'FirstMiddleware begin',
//    'CoreLayer begin',
// ]
$responseLogs = $response->getLogs();
// [
//    'CoreLayer end',
//    'SecondMiddleware end',
//    'FirstMiddleware end',
// ]

// now we can take another request with appropriate core layer that handles it
// and run middleware manager again
$response = $manager->process($anotherRequest, $anotherLayer);
// and again
```

#### Transport layer

The transport layer is a segment in the middleware chain. It holds the middleware instance, and the next layer instance. In the handle method, it invokes the middleware handler with the request and the next layer.

Manager uses a default transport layer, but you can create your own and add additional functionality to it. For example, if you use middlewares from untrusted sources, you can perform some checks.

```php
// transport layer object
class DataTransportLayer implements ILayer
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

    /**
     * @param IRequest $request
     * @return IResponse
     * @throws MiddlewareException
     */
    public function handle(IRequest $request): IResponse
    {
        $res = $this->middleware->handle($request, $this->nextLayer);

        // check a correct response type returned from each middleware handler
        if (!($res instanceof DataResponse)) {
            throw new MiddlewareException(
                sprintf("Middleware handler must return an instance of '%s'.", DataResponse::class)
            );
        }
        return $res;
    }
}

// transport layer factory
class DataTransportLayerFactory implements ITransportLayerFactory
{
    public function create(IMiddleware $middleware, ILayer $nextLayer): ILayer
    {
        return new DataTransportLayer($middleware, $nextLayer);
    }
}

// add transport layer factory to the manager
$manager = new \Itantik\Middleware\Manager(new DataTransportLayerFactory());
```

## Requirements

- PHP 7.2
