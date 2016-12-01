<?php

trait MigrationCreator
{
    /**
     * Create a new migration at the given path.
     *
     * @param  string  $name
     * @param  string  $path
     * @param  string  $table
     * @param  bool    $create
     * @return string
     */
    protected function create($name, $path, $table = null, $create = false)
    {
        $path = $this->getPath($name, $path);

        $stub = $this->getStub($table, $create);

        file_put_contents( $path, $this->populateStub($name, $stub, $table) );

        return $path;
    }

    /**
     * Get the migration stub file.
     *
     * @param  string  $table
     * @param  bool    $create
     * @return string
     */
    protected function getStub($table, $create)
    {
        if (is_null($table)) {
            return $this->fileGet($this->getStubPath().'/blank.stub');
        } else {
            $stub = $create ? 'create.stub' : 'update.stub';

            return $this->fileGet($this->getStubPath()."/{$stub}");
        }
    }

    /**
     * Populate the place-holders in the migration stub.
     *
     * @param  string  $name
     * @param  string  $stub
     * @param  string  $table
     * @return string
     */
    protected function populateStub($name, $stub, $table)
    {
        $name = ucwords(str_replace(['-', '_'], ' ', $name));
        $name = str_replace(' ', '', $name);

        $stub = str_replace('DummyClass', ucwords($name), $stub);

        if (! is_null($table)) {
            $stub = str_replace('DummyTable', $table, $stub);
        }

        return $stub;
    }

    /**
     * Get the full path name to the migration.
     *
     * @param  string  $name
     * @param  string  $path
     * @return string
     */
    protected function getPath($name, $path)
    {
        return $path.'/'.$this->getDatePrefix().'_'.$name.'.php';
    }

    /**
     * Get the date prefix for the migration.
     *
     * @return string
     */
    protected function getDatePrefix()
    {
        return date('Y_m_d_His');
    }

    /**
     * Get the path to the stubs.
     *
     * @return string
     */
    protected function getStubPath()
    {
        return __DIR__.'/stubs';
    }

    /**
     * Get the contents of a file.
     *
     * @param  string  $path
     * @param  bool  $lock
     * @return string
     */
    public function fileGet($path, $lock = false)
    {
        if ( is_file($path) ) {
            return $lock ? $this->sharedGet($path) : file_get_contents($path);
        }

        return false;
    }

    /**
     * Get contents of a file with shared access.
     *
     * @param  string  $path
     * @return string
     */
    public function sharedGet($path)
    {
        $contents = '';

        $handle = fopen($path, 'r');

        if ($handle) {
            try {
                if (flock($handle, LOCK_SH)) {
                    while (! feof($handle)) {
                        $contents .= fread($handle, 1048576);
                    }
                }
            } finally {
                fclose($handle);
            }
        }

        return $contents;
    }    
}