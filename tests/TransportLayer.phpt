<?php
// phpcs:disable PSR1.Files.SideEffects

declare(strict_types=1);

namespace Tests;

use Tester\Assert;
use Tester\TestCase;
use Tests\Assets\CoreLayer;
use Tests\Assets\SomeMiddleware;
use Tests\Assets\LoggableRequest;
use Tests\Assets\LoggableResponse;

require_once __DIR__ . '/bootstrap.php';

/**
 * @testCase
 */
class TransportLayer extends TestCase
{
    public function setUp()
    {
    }

    public function testHandler()
    {
        $mw = new SomeMiddleware('MW');
        $coreLayer = new CoreLayer();
        $layer = new \Itantik\Middleware\TransportLayer($mw, $coreLayer);

        $request = new LoggableRequest();
        $response = $layer->handle($request);

        Assert::type(LoggableResponse::class, $response);

        $requestLogs = $request->getLogs();
        Assert::count(2, $requestLogs);
        Assert::same('MyMiddleware MW: begin', $requestLogs[0]);
        Assert::same('CoreLayer: begin', $requestLogs[1]);

        /** @var LoggableResponse $response */
        $responseLogs = $response->getLogs();
        Assert::count(2, $responseLogs);
        Assert::same('CoreLayer: end', $responseLogs[0]);
        Assert::same('MyMiddleware MW: end', $responseLogs[1]);
    }
}

(new TransportLayer())->run();
