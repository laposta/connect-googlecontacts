<?php

namespace Path;

class Resolver
{
    /**
     * @var string
     */
    protected $applicationRoot;

    /**
     * string
     */
    protected $documentRoot;

    /**
     * @var string
     */
    protected $systemRoot;

    /**
     * @var string
     */
    protected $tmpRoot;

    /**
     * Default constructor
     *
     * @param string $applicationRoot
     * @param string $documentRoot
     * @param string $systemRoot
     * @param string $tmpRoot
     */
    function __construct($applicationRoot, $documentRoot, $systemRoot = '/', $tmpRoot = '/tmp')
    {
        $this->applicationRoot = realpath($applicationRoot);
        $this->documentRoot    = realpath($documentRoot);
        $this->systemRoot      = @realpath($systemRoot);
        $this->tmpRoot         = @realpath($tmpRoot);

        if (false === $this->systemRoot) {
            $this->systemRoot = '/';
        }

        if (false === $this->tmpRoot) {
            $this->tmpRoot = '/tmp';
        }
    }

    /**
     * Resolve a path relative to application root
     *
     * @param string $path
     *
     * @return string
     */
    public function application($path = '')
    {
        return $this->resolvePath($this->applicationRoot, $path);
    }

    /**
     * Resolve a path relative to document root
     *
     * @param string $path
     *
     * @return string
     */
    public function document($path = '')
    {
        return $this->resolvePath($this->documentRoot, $path);
    }

    /**
     * Resolve a path relative to system root
     *
     * @param string $path
     *
     * @return string
     */
    public function system($path = '')
    {
        return $this->resolvePath($this->systemRoot, $path);
    }

    /**
     * Resolve a path relative to tmp root
     *
     * @param string $path
     *
     * @return string
     */
    public function tmp($path = '')
    {
        return $this->resolvePath($this->tmpRoot, $path);
    }

    /**
     * Resolve a path relative to the given base.
     *
     * @param string $base
     * @param string $path
     *
     * @return string
     */
    protected function resolvePath($base, $path = '')
    {
        if (empty($path)) {
            return $base;
        }

        return $base . DIRECTORY_SEPARATOR . trim($path, '\\/');
    }
}
