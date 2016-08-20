<?php

namespace Infraverse\Command;

use RuntimeException;

use Infraverse\Model\Infrastructure;
use Infraverse\Loader\JsonInfrastructureLoader;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class ConnectCommand extends Command
{
    protected $inventory;

    public function configure()
    {
        $this->setName('connect')
            ->setDescription('Connect to a service')
            ->addArgument(
                'name',
                InputArgument::REQUIRED,
                'Name of the service to connect to'
            )
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $name = $input->getArgument('name');
        $loader = new JsonInfrastructureLoader();
        $infrastructure = $loader->autoload();
        $service = $infrastructure->getService($name);
        $credential = $service->getCredential();
        $ip = $service->getServer()->getPublicIp();
        switch ($service->getType()) {
            case 'mysql':
                $cmd = "mysql -u " . $credential->getUsername() . ' -h ' . $ip . ' --password=' . $credential->getPassword();
                $process = new Process($cmd);
                $process->setTty(true);
                $process->run();

                break;
            default:
                throw new RuntimeException("Unsupported service type: " . $service->getType());
        }
    }
}
