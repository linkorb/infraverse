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

class MysqlClusterSetupCommand extends Command
{
    protected $inventory;

    public function configure()
    {
        $this->setName('mysql-cluster:setup')
            ->setDescription('Setyp mysql cluster for replication')
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
        $replicationUser = $cluster->getCredentialReplication()->getUsername();
        if (!$replicationUser) {
            throw new RuntimeException("Replication user undefined");
        }
        if ($replicationUser=='root') {
            throw new RuntimeException("Replication user can't be root");
        }
        $replicationPass = $cluster->getCredentialReplication()->getPassword();
        if (!$replicationPass) {
            throw new RuntimeException("Replication password undefined");
        }
        if (strlen($replicationPass)>32) {
            throw new RuntimeException("Replication password too long (max 32 chars)");
        }
        
        $output->writeLn("Cluster: <comment>" . $name . "</comment>");
        foreach ($cluster->getMembers() as $member) {
            $service = $member->getService();
            if ($service->getRole()=='master') {
                $output->writeLn(
                    " * <info>" . $service->getName() . "</info>"
                );

                $pdo = $service->getPdo();

                // Remove existing replication users
                $statement = $pdo->prepare("SELECT user, host FROM mysql.user WHERE user=:user");
                $statement->execute(['user' => $replicationUser]);
                $rows = $statement->fetchAll(PDO::FETCH_ASSOC);
                if ($output->getVerbosity() >= OutputInterface::VERBOSITY_DEBUG) {
                    print_r($rows);
                }
                foreach ($rows as $row) {
                    $output->writeln("Removing " . $row['user'] . '@' . $row['host']);
                    $statement = $pdo->prepare("DELETE FROM mysql.user WHERE user=:user AND host=:host");
                    $statement->execute(['user' => $row['user'], 'host' => $row['host']]);
                }
                
                // Adding new replication user
                $output->writeln("Adding " . $replicationUser . '@%');
                $statement = $pdo->prepare("GRANT REPLICATION SLAVE ON *.* TO :user@'%' IDENTIFIED BY :password;");
                $statement->execute(['user' => $replicationUser, 'password' => $replicationPass]);
                if ($statement->errorCode()>0) {
                    $info = $statement->errorInfo();
                    throw new RuntimeException("Adding replication user failed: " . $info[2]);
                }
                $statement = $pdo->prepare("FLUSH PRIVILEGES;");
                $statement->execute();
                
                
                $statement = $pdo->prepare("FLUSH TABLES WITH READ LOCK;");
                $statement->execute();
                
                $statement = $pdo->prepare("SHOW MASTER STATUS;");
                $statement->execute();
                $row = $statement->fetch(PDO::FETCH_ASSOC);
                print_r($row);
                $file = $row['File'];
                $position = $row['Position'];
                
    
                
                foreach ($cluster->getMembers() as $member) {
                    $slaveService = $member->getService();
                    if ($slaveService->getRole()=='slave') {
                        $output->writeln("Connecting to service " . $slaveService->getName());
                        
                        $slavePdo = $slaveService->getPdo();
                        $statement = $slavePdo->prepare("STOP SLAVE;");
                        $statement->execute();
                        
                        sleep(3);
                        
                        $rootCredential = $service->getCredential();
                        $cmd = '';
                        $cmd .= "mysqldump -u " . $rootCredential->getUsername() . ' -h ' . $service->getServer()->getPublicIp();
                        $cmd .= ' --password=' .  $rootCredential->getPassword();
                        $cmd .= ' --all-databases';
                        $cmd .= ' --master-data';
                        $cmd .= ' --flush-privileges'; // because all-databases implies system tables
                        $cmd .= ' --verbose';
                        $cmd .= ' | ';
                        $cmd .= "mysql -u " . $rootCredential->getUsername() . ' -h ' . $slaveService->getServer()->getPublicIp();
                        $cmd .= ' --password=' .  $rootCredential->getPassword();
                        
                        $process = new Process($cmd);
                        $process->setTty(true);
                        $process->run();
                        
                        sleep(3);
                        $output->writeln("   Changing master");
                        $statement = $slavePdo->prepare(
                            "CHANGE MASTER TO
                            MASTER_HOST=:host, MASTER_USER=:user, MASTER_PASSWORD=:password,
                            MASTER_LOG_FILE=:file, MASTER_LOG_POS=" . (int)$position
                        );
                        $statement->execute(
                            [
                                "host" => $service->getServer()->getPrivateIp(),
                                "user" => $replicationUser,
                                "password" => $replicationPass,
                                "file" => $file
                            ]
                        );
                        sleep(5);
                        
                        $output->writeln("   Starting slave");
                        $slavePdo = $slaveService->getPdo();
                        $statement = $slavePdo->prepare("START SLAVE;");
                        $statement->execute();
                    }
                }

                $statement = $pdo->prepare("UNLOCK TABLES;");
                $statement->execute();
                
                $output->writeln("Done");
            }

        }
    }
}
