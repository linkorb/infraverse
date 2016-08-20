<?php

namespace Infraverse\Command;

use RuntimeException;

use Infraverse\Model\Infrastructure;
use Infraverse\Loader\JsonInfrastructureLoader;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ConfigCommand extends Command
{
    protected $inventory;

    public function configure()
    {
        $this->setName('config')
            ->setDescription('Show configuration')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $loader = new JsonInfrastructureLoader();
        $infrastructure = $loader->autoload();
        
        //print_r($infrastructure);

        $output->writeLn("<comment>Servers:</comment>");
        foreach ($infrastructure->getServers() as $server) {
            $output->writeLn(
                "<info>" . $server->getName() . "</info> " .
                "(" . $server->getPublicIp() . '/' . $server->getPrivateIp(). ")"
            );
        }
        $output->writeLn("");
        $output->writeLn("<comment>Services:</comment>");
        foreach ($infrastructure->getServices() as $service) {
            $output->writeLn(
                "<info>" . $service->getName() . "</info> " .
                "(" . $service->getType() . ")"
            );
        }
        
        $output->writeLn("");
        $output->writeLn("<comment>Clusters:</comment>");
        foreach ($infrastructure->getClusters() as $cluster) {
            $output->writeLn(
                "<info>" . $cluster->getName() . "</info> " .
                "(" . $cluster->getType() . ")"
            );
        }
    }
}
