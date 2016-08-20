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

class SshCommand extends Command
{
    protected $inventory;

    public function configure()
    {
        $this->setName('ssh')
            ->setDescription('SSH into provided server')
            ->addArgument(
                'server',
                InputArgument::REQUIRED,
                'Name of the server to connect to'
            )
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $serverName = $input->getArgument('server');
        $loader = new JsonInfrastructureLoader();
        $infrastructure = $loader->autoload();
        $server = $infrastructure->getServer($serverName);
        
        $ip = $server->getPublicIp();
        $process = new Process('ssh root@' . $ip);
        $process->setTty(true);
        $process->run();
    }
}
