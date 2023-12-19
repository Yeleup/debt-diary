<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use function Symfony\Component\DependencyInjection\Loader\Configurator\env;

#[AsCommand(
    name: 'app:backup',
    description: 'Database Backup',
)]
class DatabaseBackupCommand extends Command
{
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct();

        $this->container = $container;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $backupDir = $this->container->getParameter('kernel.project_dir') . '/docker/cron/backup/';
        $filename = (new \DateTime())->format('Y-m-d') . '.sql';

        if (!file_exists($backupDir)) {
            mkdir($backupDir, 0777, true);
        }

        // Build the mysqldump command
        $command = [
            'mysqldump',
            '--user=' . $_ENV['SYMFONY_DATABASE_USER'],
            '--password=' . $_ENV['SYMFONY_DATABASE_PASSWORD'],
            '--host=' . $_ENV['SYMFONY_DATABASE_HOST'],
            $_ENV['SYMFONY_DATABASE_NAME'],
            '--result-file=' . $backupDir . $filename,
        ];

        // Create a new Process instance
        $process = new Process($command);

        // Run the command
        $process->run();

        // Check if the command was successful
        if (!$process->isSuccessful()) {
            $output->writeln('Error Output: ' . $process->getErrorOutput());
            throw new ProcessFailedException($process);
        }

        $io->success('Backup created successfully: ' . $filename);

        return Command::SUCCESS;
    }
}
