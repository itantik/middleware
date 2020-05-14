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
class Manager extends TestCase
{
    /** @var \Itantik\Middleware\Manager */
    private $manager;

    public function setUp()
    {
        $this->manager = new \Itantik\Middleware\Manager();
    }

    public function testConstructor()
    {
        Assert::type(\Itantik\Middleware\Manager::class, $this->manager);
    }

    public function testWithCoreLayer()
    {
        $manager = $this->manager;

        $coreLayer = new CoreLayer();
        $request = new LoggableRequest();
        $response = $manager->process($request, $coreLayer);

        Assert::type(LoggableResponse::class, $response);

        $requestLogs = $request->getLogs();
        Assert::count(1, $requestLogs);
        Assert::same('CoreLayer: begin', $requestLogs[0]);

        /** @var LoggableResponse $response */
        $responseLogs = $response->getLogs();
        Assert::count(1, $responseLogs);
        Assert::same('CoreLayer: end', $responseLogs[0]);
    }

    public function testWithMiddlewares()
    {
        $manager = $this->manager;
        $manager->append(new SomeMiddleware('MW1'));
        $manager->append(new SomeMiddleware('MW2'));
        $manager->prepend(new SomeMiddleware('MW0'));

        $coreLayer = new CoreLayer();
        $request = new LoggableRequest();
        $response = $manager->process($request, $coreLayer);

        Assert::type(LoggableResponse::class, $response);

        $requestLogs = $request->getLogs();
        Assert::count(4, $requestLogs);
        Assert::same('MyMiddleware MW0: begin', $requestLogs[0]);
        Assert::same('MyMiddleware MW1: begin', $requestLogs[1]);
        Assert::same('MyMiddleware MW2: begin', $requestLogs[2]);
        Assert::same('CoreLayer: begin', $requestLogs[3]);

        /** @var LoggableResponse $response */
        $responseLogs = $response->getLogs();
        Assert::count(4, $responseLogs);
        Assert::same('CoreLayer: end', $responseLogs[0]);
        Assert::same('MyMiddleware MW2: end', $responseLogs[1]);
        Assert::same('MyMiddleware MW1: end', $responseLogs[2]);
        Assert::same('MyMiddleware MW0: end', $responseLogs[3]);
    }
}

(new Manager())->run();
