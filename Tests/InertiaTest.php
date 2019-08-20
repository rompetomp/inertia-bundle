<?php

namespace Rompetomp\InertiaBundle\Tests;

use PHPUnit\Framework\TestCase;
use Rompetomp\InertiaBundle\Service\Inertia;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

class InertiaTest extends TestCase
{
    /** @var \Rompetomp\InertiaBundle\Service\Inertia */
    private $inertia;
    /** @var \Mockery\LegacyMockInterface|\Mockery\MockInterface|\Twig\Environment */
    private $environment;
    /** @var \Mockery\LegacyMockInterface|\Mockery\MockInterface|\Symfony\Component\HttpFoundation\RequestStack */
    private $requestStack;

    public function setUp()
    {
        $this->environment  = \Mockery::mock(Environment::class);
        $this->requestStack = \Mockery::mock(RequestStack::class);

        $this->inertia = new Inertia('app.twig.html', $this->environment, $this->requestStack);
    }

    public function testSharedSingle()
    {
        $this->inertia->share('app_name', 'Testing App 1');
        $this->inertia->share('app_version', '1.0.0');
        $this->assertEquals('Testing App 1', $this->inertia->getShared('app_name'));
        $this->assertEquals('1.0.0', $this->inertia->getShared('app_version'));
    }

    public function testSharedMultiple()
    {
        $this->inertia->share('app_name', 'Testing App 2');
        $this->inertia->share('app_version', '2.0.0');
        $this->assertEquals(
            [
                'app_version' => '2.0.0',
                'app_name'    => 'Testing App 2',
            ],
            $this->inertia->getShared()
        );
    }

    public function testVersion()
    {
        $this->assertNull($this->inertia->getVersion());
        $this->inertia->version('1.2.3');
        $this->assertEquals($this->inertia->getVersion(), '1.2.3');
    }

    public function testRootView()
    {
        $this->assertEquals('app.twig.html', $this->inertia->getRootView());
    }

    public function testRenderJSON()
    {
        $mockRequest = \Mockery::mock(Request::class);
        $mockRequest->shouldReceive('getRequestUri')->andSet('headers', new HeaderBag(['X-Inertia' => true]));
        $mockRequest->allows()->getRequestUri()->andReturns('https://example.test');
        $this->requestStack->allows()->getCurrentRequest()->andReturns($mockRequest);

        $this->inertia = new Inertia('app.twig.html', $this->environment, $this->requestStack);

        $response = $this->inertia->render('Dashboard');
        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    public function testRenderProps()
    {
        $mockRequest = \Mockery::mock(Request::class);
        $mockRequest->shouldReceive('getRequestUri')->andSet('headers', new HeaderBag(['X-Inertia' => true]));
        $mockRequest->allows()->getRequestUri()->andReturns('https://example.test');
        $this->requestStack->allows()->getCurrentRequest()->andReturns($mockRequest);

        $this->inertia = new Inertia('app.twig.html', $this->environment, $this->requestStack);

        $response = $this->inertia->render('Dashboard', ['test' => 123]);
        $data     = json_decode($response->getContent(), true);
        $this->assertEquals(['test' => 123], $data['props']);
    }

    public function testRenderSharedProps()
    {
        $mockRequest = \Mockery::mock(Request::class);
        $mockRequest->shouldReceive('getRequestUri')->andSet('headers', new HeaderBag(['X-Inertia' => true]));
        $mockRequest->allows()->getRequestUri()->andReturns('https://example.test');
        $this->requestStack->allows()->getCurrentRequest()->andReturns($mockRequest);

        $this->inertia = new Inertia('app.twig.html', $this->environment, $this->requestStack);
        $this->inertia->share('app_name', 'Testing App 3');
        $this->inertia->share('app_version', '2.0.0');

        $response = $this->inertia->render('Dashboard', ['test' => 123]);
        $data     = json_decode($response->getContent(), true);
        $this->assertEquals(['test' => 123, 'app_name' => 'Testing App 3', 'app_version' => '2.0.0'], $data['props']);
    }

    public function testRenderClosureProps()
    {
        $mockRequest = \Mockery::mock(Request::class);
        $mockRequest->shouldReceive('getRequestUri')->andSet('headers', new HeaderBag(['X-Inertia' => true]));
        $mockRequest->allows()->getRequestUri()->andReturns('https://example.test');
        $this->requestStack->allows()->getCurrentRequest()->andReturns($mockRequest);

        $this->inertia = new Inertia('app.twig.html', $this->environment, $this->requestStack);

        /** @var JsonResponse $response */
        $response = $this->inertia->render('Dashboard', ['test' => function () {
            return 'test-value';
        }]);
        $this->assertEquals(
            'test-value',
            json_decode($response->getContent(), true)['props']['test']
        );
    }

    public function testRenderDoc()
    {
        $mockRequest = \Mockery::mock(Request::class);
        $mockRequest->shouldReceive('getRequestUri')->andSet('headers', new HeaderBag(['X-Inertia' => false]));
        $mockRequest->allows()->getRequestUri()->andReturns('https://example.test');
        $this->requestStack->allows()->getCurrentRequest()->andReturns($mockRequest);

        $this->environment->allows('render')->andReturn('<div>123</div>');

        $this->inertia = new Inertia('app.twig.html', $this->environment, $this->requestStack);

        $response = $this->inertia->render('Dashboard');
        $this->assertInstanceOf(Response::class, $response);
    }

    public function testViewDataSingle()
    {
        $this->inertia->viewData('app_name', 'Testing App 1');
        $this->inertia->viewData('app_version', '1.0.0');
        $this->assertEquals('Testing App 1', $this->inertia->getViewData('app_name'));
        $this->assertEquals('1.0.0', $this->inertia->getViewData('app_version'));
    }

    public function testViewDataMultiple()
    {
        $this->inertia->viewData('app_name', 'Testing App 2');
        $this->inertia->viewData('app_version', '2.0.0');
        $this->assertEquals(
            [
                'app_version' => '2.0.0',
                'app_name'    => 'Testing App 2',
            ],
            $this->inertia->getViewData()
        );
    }
}
