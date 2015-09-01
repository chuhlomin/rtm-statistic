<?php

namespace app\models\service;


use Pimple\Container;

class Factory
{
    /** @var array */
    private $serviceClassMap;

    /** @var Container*/
    private $container;

    public function __construct(Container $container)
    {
        $this->container = $container;

        $this->serviceClassMap = [
            'rtm' => ['RtmService', 'client_rtm']
        ];
    }

    /**
     * @param string $alias
     * @return ServiceInterface
     */
    public function createTaskService($alias)
    {
        if (!array_key_exists($alias, $this->serviceClassMap)) {
            throw new \InvalidArgumentException("$alias is not valid task service alias");
        }

        $serviceDefinition = $this->serviceClassMap[$alias];

        $className = __NAMESPACE__ . '\\' . $serviceDefinition[0];
        $clientAlias = $serviceDefinition[1];

        return new $className($this->container[$clientAlias]);
    }
}
