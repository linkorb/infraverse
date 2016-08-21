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
use PDO;

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
        
        $output->writeln("Cluster: <info>" . $name . "</info>");
        $output->writeln("Replication user: " . $cluster->getCredentialReplication()->getUsername());
        foreach ($cluster->getMembers() as $member) {
            $service = $member->getService();
            $output->writeLn(
                " * <info>" . $service->getName() . "</info> <comment>Public</comment>: " .
                $service->getServer()->getPublicIp() . ':' . $service->getPort() .
                ' <comment>Private</comment>: ' .
                $service->getServer()->getPrivateIp() . ':' . $service->getPort()
                
            );

            $pdo = $service->getPdo();

            // Get MASTER STATUS
            $statement = $pdo->prepare("SHOW MASTER STATUS;");
            $statement->execute();
            $row = $statement->fetch(PDO::FETCH_ASSOC);
            if ($output->getVerbosity() >= OutputInterface::VERBOSITY_DEBUG) {
                print_r($row);
            }
            $output->writeln(
                "   <comment>Position</comment>: " .
                $row['File'] . ":" . $row['Position'] .
                " <comment>Do</comment>: " . $row['Binlog_Do_DB'] . ' <comment>Ignore</comment>:' . $row['Binlog_Ignore_DB']
            );

            // Get SLAVE STATUS
            $statement = $pdo->prepare("SHOW SLAVE STATUS;");
            $statement->execute();
            $row = $statement->fetch(PDO::FETCH_ASSOC);
            if ($output->getVerbosity() >= OutputInterface::VERBOSITY_DEBUG) {
                print_r($row);
            }
            if ($row) {
                $output->writeln(
                    "   <comment>Master</comment>: " .
                    $row['Master_User'] . '@' . $row['Master_Host'] . ':' . $row['Master_Port']
                );
                
                $output->writeln(
                    "   <comment>Slave IO Status</comment>: " .
                    $row['Slave_IO_State'] .
                    " <comment>Slave IO Running</comment>: " . $row['Slave_IO_Running'] .
                    " <comment>Slave SQL Running</comment>: " . $row['Slave_SQL_Running']
                );
                
                $output->writeln(
                    "   <comment>Seconds behind master</comment>: " . $row['Seconds_Behind_Master']
                );

                if ($row['Last_IO_Errno']) {
                    $output->writeln("   <error>IO Error #" . $row['Last_IO_Errno'] . '</error>: ' . $row['Last_IO_Error']);
                }
                if ($row['Last_SQL_Errno']) {
                    $output->writeln("   <error>SQL Error #" . $row['Last_SQL_Errno'] . '</error>: ' . $row['Last_SQL_Error']);
                }

            }
            /*
            $output->writeln(
                "   <comment>Position</comment>: " .
                $row['File'] . ":" . $row['Position'] .
                " <comment>Do/Ignore</comment>: " . $row['Binlog_Do_DB'] . '/' . $row['Binlog_Ignore_DB']
            );
            */
            $output->writeln("");

        }
    }
}
