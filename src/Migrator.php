<?php

trait Migrator
{
    /**
     * Run the outstanding migrations at a given path.
     *
     * @param  string  $path
     * @param  array  $options
     * @return void
     */
    public function runMigration($path, array $options = [])
    {
        $this->notes = [];

        $files = $this->getMigrationFiles($path);

        //check if migrations have already been run.
        $ran = array();

        $migrations = array_diff($files, $ran);

        $this->requireFiles($path, $migrations);

        $this->runMigrationList($migrations);
    }

    /**
     * Run an array of migrations.
     *
     * @param  array  $migrations
     * @param  array  $options
     * @return void
     */
    public function runMigrationList($migrations, array $options = [])
    {
        if (count($migrations) == 0) {
            $this->note('<info>Nothing to migrate.</info>');

            return;
        }

        foreach ($migrations as $file) {
            $this->runUp($file);
        }
    }

    /**
     * Run "up" a migration instance.
     *
     * @param  string  $file
     * @param  int     $batch
     * @param  bool    $pretend
     * @return void
     */
    protected function runUp($file)
    {
        $migration = $this->resolve($file);

        $sql = $migration->up();
        
        $error = false;
        try {
            $this->dbh->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );//Error Handling
            $this->dbh->exec($sql);
        } catch (PDOException $e) {
            $error = $e->getMessage();
        }

        if ( $error ) {
            $this->note("<error>Error in file:</error> $file");
        } else {
            $this->note("<info>Migrated:</info> $file");
        }
    }

    /**
     * Get all of the migration files in a given path.
     *
     * @param  string  $path
     * @return array
     */
    public function getMigrationFiles($path)
    {
        $files = glob($path.'/*_*.php', 0);

        if ($files === false) {
            return [];
        }

        $files = array_map(function ($file) {
            return str_replace('.php', '', basename($file));

        }, $files);

        sort($files);

        return $files;
    }

    /**
     * Resolve a migration instance from a file.
     *
     * @param  string  $file
     * @return object
     */
    public function resolve($file)
    {
        $file = implode('_', array_slice(explode('_', $file), 4));

        $class = ucwords(str_replace(['-', '_'], ' ', $file));
        $class = str_replace(' ', '', $class);

        return new $class;
    }

    /**
     * Require in all the migration files in a given path.
     *
     * @param  string  $path
     * @param  array   $files
     * @return void
     */
    public function requireFiles($path, array $files)
    {
        foreach ($files as $file) {
            $this->requireOnce($path.'/'.$file.'.php');
        }
    }

    /**
     * Require the given file once.
     *
     * @param  string  $file
     * @return mixed
     */
    public function requireOnce($file)
    {
        require_once $file;
    }

    /**
     * Raise a note event for the migrator.
     *
     * @param  string  $message
     * @return void
     */
    protected function note($message)
    {
        $this->notes[] = $message;
    }
}