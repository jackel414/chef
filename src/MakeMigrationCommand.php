<?php

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class MakeMigrationCommand extends Command
{
    use MigrationCreator;

    protected function configure()
    {
        $this->setName('make:migration')
             ->setDescription('Create a new migration file')
             ->addArgument(
                    'name', InputArgument::REQUIRED, 'The name of the migration.'
                )
             ->addOption(
                    'create',
                    null,
                    InputOption::VALUE_OPTIONAL,
                    'The table to be created.'
                )
             ->addOption(
                    'table',
                    null,
                    InputOption::VALUE_OPTIONAL,
                    'The table to migrate.'
                );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $path = $this->getMigrationPath();
        
        $name = $input->getArgument('name');
        $table = $input->getOption('table');
        $create = $input->getOption('create') ?: false;

        if (! $table && is_string($create)) {
            $table = $create;

            $create = true;
        }

        $file = pathinfo($this->create($name, $path, $table, $create), PATHINFO_FILENAME);

        $output->writeln("<info>Created Migration:</info> $file");
    }

    /**
     * Get migration path.
     *
     * @return string
     */
    protected function getMigrationPath()
    {
        return __DIR__.'/migrations';
    }
}
