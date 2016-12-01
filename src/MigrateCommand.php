<?php

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class MigrateCommand extends Command
{
    use Migrator;

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
        $this->prepareDatabase();

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
    protected function prepareDatabase()
    {
		$dsn = 'mysql:dbname=chef_test;host=127.0.0.1;port=8889';
		$user = 'root';
		$password = 'root';

		try {
		    $this->dbh = new PDO($dsn, $user, $password);
		} catch (PDOException $e) {
		    echo 'Connection failed: ' . $e->getMessage();
		}
    }

    protected function getMigrationPath()
    {
        return __DIR__.'/migrations';
    }
}

