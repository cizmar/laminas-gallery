<?php

namespace Gallery\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

use Gallery\Controller\ShowroomController;
use Gallery\Form\ImageForm;
use Application\Service\ImageManager;

class ShowroomControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $formManager = $container->get('FormElementManager');
        $imageManager = $container->get(ImageManager::class);
        return new ShowroomController(
            $formManager->get(ImageForm::class),
            $imageManager
        );
    }
}
