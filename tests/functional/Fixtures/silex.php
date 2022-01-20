<?php

require __DIR__.'/../../../vendor/autoload.php';

use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\EventListener\ErrorListener;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

$kernel = new class('test', true) extends Kernel {
    use MicroKernelTrait;
    public function registerBundles(): iterable { yield new FrameworkBundle(); }
    private function getConfigDir(): string { return $this->getProjectDir(); }

    private function configureRoutes(RoutingConfigurator $routes): void
    {
        $routes
            ->add('foo', '/foo')
            ->methods(['GET'])
            ->controller(['kernel', 'foo'])
        ;

        $routes
            ->add('baz', '/baz')
            ->methods(['POST'])
            ->controller(['kernel', 'baz'])
        ;

        $routes
            ->add('put-target', '/put-target')
            ->methods(['PUT'])
            ->controller(['kernel', 'putTarget'])
        ;

        $routes
            ->add('delete-target', '/delete-target')
            ->methods(['DELETE'])
            ->controller(['kernel', 'deleteTarget'])
        ;

        $routes
            ->add('script-name', '/script-name')
            ->methods(['GET'])
            ->controller(['kernel', 'scriptName'])
        ;
    }

    private function configureContainer(ContainerConfigurator $container, LoaderInterface $loader, ContainerBuilder $builder): void
    {
        $container->services()->remove(ErrorListener::class);
    }

    public function foo() { return new Response('bar'); }
    public function baz() { return new Response('qux'); }
    public function putTarget() { return new Response('putted'); }
    public function deleteTarget() { return new Response('deleted'); }
    public function scriptName(Request $request) { return new Response($request->server->get('SCRIPT_NAME')); }
};

$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
