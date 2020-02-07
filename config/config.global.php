<?php
namespace ShoppingFeed\Signal;

use Laminas\ServiceManager;

return [
    'dependencies' => [
        'factories' =>  [
            GlobalHandler::class => ServiceManager\Factory\InvokableFactory::class
        ]
    ]
];