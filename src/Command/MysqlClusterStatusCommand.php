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

class MysqlClusterStatusCommand extends Command
{
    protected $inventory;

    public function configure()
    {
        $this->setName('mysql-cluster:status')
            ->setDescription('Show mysql cluster status')
            ->addArgument(
                'name',
                InputArgument::REQUIRED,
                'Name of the cluster'
            )
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $name = $input->getArgument('name');
        $loader = new JsonInfrastructureLoader();
        $infrastructure = $loader->autoload();
        $cluster = $infrastructure->getCluster($name);
        
        $output->writeLn("Cluster: <comment>" . $name . "</comment>");
        foreach ($cluster->getMembers() as $member) {
            $service = $member->getService();
            $output->writeLn(" * <info>" . $service->getName() . "</info>");
            $pdo = $service->getPdo();
            
        }
    }
}
