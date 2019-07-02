<?php
namespace ShoppingFeed\Signal;

use Zend\ServiceManager;

return [
    'dependencies' => [
        'factories' =>  [
            GlobalHandler::class => ServiceManager\Factory\InvokableFactory::class
        ]
    ]
];