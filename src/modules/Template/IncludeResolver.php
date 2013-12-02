<?php

namespace Template;

use RuntimeException;

class IncludeResolver
{
    /**
     * @var bool
     */
    protected $printComments;

    /**
     * @var string
     */
    protected $docRoot;

    /**
     * @param bool $printComments
     */
    function __construct($printComments = false)
    {
        $this->printComments = $printComments;
    }

    /**
     * @param string $file
     *
     * @throws RuntimeException
     * @return string
     */
    public function resolve($file)
    {
        $list = array($file);

        if (substr_count($file, '*')) {
            $list = $this->resolveList($file);
        }



        $result = '';

        foreach ($list as $listFile) {
            $result .= $this->recursiveResolve($listFile);
        }

        return $result;
    }

    /**
     * @param $file
     *
     * @throws RuntimeException
     * @return string
     */
    protected function recursiveResolve($file)
    {
        if (!is_readable($file)) {
            throw new RuntimeException("Unable to resolve server side includes for '$file'. File is not readable.");
        }

        if (empty($this->docRoot)) {
            $this->docRoot = dirname(filter_input(INPUT_SERVER, 'SCRIPT_FILENAME'));
        }

        $relativeRoot = dirname($file);
        $self         = $this;

        return preg_replace_callback(
            '/[ \t]*<!--\s*#include\s+(file|virtual)=["\']([^"\']+)["\']\s*-->[ \t]*\r?\n/mi',
            function ($matches) use ($relativeRoot, $file, $self) {
                $type = $matches[1];
                $path = $matches[2];

                return $self->resolveFile(
                            $path,
                            $type === 'virtual' ? $this->docRoot : $relativeRoot,
                            $file
                );
            },
            file_get_contents($file)
        );
    }

    /**
     * @param $path
     * @param $root
     *
     * @throws RuntimeException
     * @return string
     */
    public function resolveFile($path, $root)
    {
        $fullPath = $root . DIRECTORY_SEPARATOR . ltrim($path, '/\\');

        $startComment = $endComment = '';

        if ($this->printComments) {
            $startComment = '<!-- start:' . $path . ' -->' . PHP_EOL;
            $endComment   = '<!-- end:' . $path . ' -->' . PHP_EOL;
        }

        return $startComment . $this->resolve($fullPath) . $endComment;
    }

    /**
     * @param string $docRoot
     *
     * @return IncludeResolver
     */
    public function setDocRoot($docRoot)
    {
        $this->docRoot = $docRoot;

        return $this;
    }

    /**
     * @return string
     */
    public function getDocRoot()
    {
        return $this->docRoot;
    }

    /**
     * @param $path
     *
     * @throws RuntimeException
     * @return array
     */
    protected function resolveList($path)
    {
        $list = glob($path);

        if ($list === false) {
            throw new RuntimeException("Unable to resolve include list for '$path'.");
        }

        return $list;
    }
}
