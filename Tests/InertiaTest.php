<?php

namespace Rompetomp\InertiaBundle\Tests;

use PHPUnit\Framework\TestCase;
use Rompetomp\InertiaBundle\Service\Inertia;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Twig\Environment;

class InertiaTest extends TestCase
{
    /** @var \Rompetomp\InertiaBundle\Service\Inertia */
    private $inertia;
    /** @var \Mockery\LegacyMockInterface|\Mockery\MockInterface|\Twig\Environment */
    private $environment;
    /** @var \Mockery\LegacyMockInterface|\Mockery\MockInterface|\Symfony\Component\HttpFoundation\RequestStack */
    private $requestStack;
    /** @var \Mockery\LegacyMockInterface|\Mockery\MockInterface|\Symfony\Component\Serializer\Serializer|null */
    private $serializer;

    public function setUp(): void
    {
        $this->serializer   = null;
        $this->environment  = \Mockery::mock(Environment::class);
        $this->requestStack = \Mockery::mock(RequestStack::class);

        $this->inertia = new Inertia('app.twig.html', $this->environment, $this->requestStack, $this->serializer);
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

    public function testSetRootView()
    {
        $this->inertia->setRootView('other-root.twig.html');
        $this->assertEquals('other-root.twig.html', $this->inertia->getRootView());
    }

    public function testRenderJSON()
    {
        $mockRequest = \Mockery::mock(Request::class);
        $mockRequest->shouldReceive('getRequestUri')->andSet('headers', new HeaderBag(['X-Inertia' => true]));
        $mockRequest->allows()->getRequestUri()->andReturns('https://example.test');
        $this->requestStack->allows()->getCurrentRequest()->andReturns($mockRequest);

        $this->inertia = new Inertia('app.twig.html', $this->environment, $this->requestStack, $this->serializer);

        $response = $this->inertia->render('Dashboard');
        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    public function testRenderProps()
    {
        $mockRequest = \Mockery::mock(Request::class);
        $mockRequest->shouldReceive('getRequestUri')->andSet('headers', new HeaderBag(['X-Inertia' => true]));
        $mockRequest->allows()->getRequestUri()->andReturns('https://example.test');
        $this->requestStack->allows()->getCurrentRequest()->andReturns($mockRequest);

        $this->inertia = new Inertia('app.twig.html', $this->environment, $this->requestStack, $this->serializer);

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

        $this->inertia = new Inertia('app.twig.html', $this->environment, $this->requestStack, $this->serializer);
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

        $this->inertia = new Inertia('app.twig.html', $this->environment, $this->requestStack, $this->serializer);

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

        $this->inertia = new Inertia('app.twig.html', $this->environment, $this->requestStack, $this->serializer);

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

    public function testContextSingle()
    {
        $this->inertia->context('groups', [ 'group1', 'group2' ]);
        $this->assertEquals([ 'group1', 'group2' ], $this->inertia->getContext('groups'));
    }

    public function testContextMultiple()
    {
        $this->inertia->context('groups', [ 'group1', 'group2' ]);
        $this->assertEquals(
            [
                'groups'    =>  [ 'group1', 'group2' ],
            ],
            $this->inertia->getContext()
        );
    }

    public function testTypesArePreservedUsingJsonEncode()
    {
        $mockRequest = \Mockery::mock(Request::class);
        $mockRequest->shouldReceive('getRequestUri')->andSet('headers', new HeaderBag(['X-Inertia' => true]));
        $mockRequest->allows()->getRequestUri()->andReturns('https://example.test');
        $this->requestStack->allows()->getCurrentRequest()->andReturns($mockRequest);

        $this->inertia = new Inertia('app.twig.html', $this->environment, $this->requestStack, $this->serializer);

        $this->innerTestTypesArePreserved(false);
    }

    public function testTypesArePreservedUsingSerializer()
    {
        $mockRequest = \Mockery::mock(Request::class);
        $mockRequest->shouldReceive('getRequestUri')->andSet('headers', new HeaderBag(['X-Inertia' => true]));
        $mockRequest->allows()->getRequestUri()->andReturns('https://example.test');
        $this->requestStack->allows()->getCurrentRequest()->andReturns($mockRequest);

        $this->serializer = new Serializer([new ObjectNormalizer()], [new JsonEncoder()]);
        $this->inertia = new Inertia('app.twig.html', $this->environment, $this->requestStack, $this->serializer);

        $this->innerTestTypesArePreserved(true);
    }

    private function innerTestTypesArePreserved($usingSerializer = false)
    {
        $props = [
            'integer'               => 123,
            'float'                 => 1.23,
            'string'                => 'test',
            'null'                  => null,
            'true'                  => true,
            'false'                 => false,
            'object'                => new \DateTime(),
            'empty_object'          => new \stdClass(),
            'iterable_object'       => new \ArrayObject([1, 2, 3]),
            'empty_iterable_object' => new \ArrayObject(),
            'array'                 => [1, 2, 3],
            'empty_array'           => [],
            'associative_array'     => ['test' => 'test']
        ];

        $response      = $this->inertia->render('Dashboard', $props);
        $data          = json_decode($response->getContent(), false);
        $responseProps = (array) $data->props;

        $this->assertIsInt($responseProps['integer']);
        $this->assertIsFloat($responseProps['float']);
        $this->assertIsString($responseProps['string']);
        $this->assertNull($responseProps['null']);
        $this->assertTrue($responseProps['true']);
        $this->assertFalse($responseProps['false']);
        $this->assertIsObject($responseProps['object']);
        $this->assertIsObject($responseProps['empty_object']);

        if (!$usingSerializer) {
            $this->assertIsObject($responseProps['iterable_object']);
        } else {
            $this->assertIsArray($responseProps['iterable_object']);
        }

        $this->assertIsObject($responseProps['empty_iterable_object']);
        $this->assertIsArray($responseProps['array']);
        $this->assertIsArray($responseProps['empty_array']);
        $this->assertIsObject($responseProps['associative_array']);
    }
}
