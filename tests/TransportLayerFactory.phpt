<?php
// phpcs:disable PSR1.Files.SideEffects

declare(strict_types=1);

namespace Tests;

use Tester\Assert;
use Tester\TestCase;
use Tests\Assets\CoreLayer;
use Tests\Assets\SomeMiddleware;

require_once __DIR__ . '/bootstrap.php';

/**
 * @testCase
 */
class TransportLayerFactory extends TestCase
{
    public function setUp()
    {
    }

    public function testCreate()
    {
        $factory = new \Itantik\Middleware\TransportLayerFactory();

        $mw = new SomeMiddleware('MW');
        $coreLayer = new CoreLayer();
        $layer = $factory->create($mw, $coreLayer);

        Assert::type(\Itantik\Middleware\TransportLayer::class, $layer);
    }
}

(new TransportLayerFactory())->run();
