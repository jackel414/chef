<?php

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class MigrateCommand extends Command
{
    use Migrator, EnvironmentVariables;

    protected function configure()
    {
        $this->setName('migrate')
             ->setDescription('Run the database migrations')
             ->addOption(
                    'database',
                    null,
                    InputOption::VALUE_OPTIONAL,
                    'The database connection to use.'
                );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $database = $input->getOption('database');
        $this->prepareDatabase($database);

        $path = $this->getMigrationPath();

        $this->runMigration($path);

        foreach ( $this->notes as $note ) {
            $output->writeln($note);
        }
    }

    /**
     * Prepare the migration database for running.
     *
     * @return void
     */
    protected function prepareDatabase($db_option = null)
    {
        $connection = $this->env('DB_CONNECTION', 'mysql');
        $host = $this->env('DB_HOST', 'localhost');
        $port = $this->env('DB_PORT', '3306');
        $database = $db_option ?: $this->env('DB_DATABASE', 'chef');
        $username = $this->env('DB_USERNAME', 'root');
        $password = $this->env('DB_PASSWORD', 'root');

        $db_connection = "$connection:dbname=$database;host=$host;port=$port";

        try {
            $this->dbh = new PDO($db_connection, $username, $password);
        } catch (PDOException $e) {
            echo 'Connection failed: ' . $e->getMessage();
        }
    }

    protected function getMigrationPath()
    {
        return __DIR__.'/migrations';
    }
}

