<?php

namespace Seferov\Typhp;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

class Configuration
{
    /**
     * @var string|null
     */
    private $configFilePath;
    /**
     * @var string|null
     */
    private $path;

    public function __construct(?string $configFilePath, ?string $path)
    {
        $this->configFilePath = $configFilePath;
        $this->path = $path;
    }

    /**
     * @throws ParseException
     * @return \Iterator|\Symfony\Component\Finder\SplFileInfo[]
     */
    public function getFiles(): \Traversable
    {
        $finder = new Finder();

        if ($this->path) {
            if (is_dir($this->path)) {
                $finder->in($this->path);
            } else {
                $finder->in(getcwd());
                $finder->path($this->path);
            }
        } elseif ($this->configFilePath && file_exists($this->configFilePath)) {
            $config = Yaml::parseFile($this->configFilePath);

            if (isset($config['project']['directories'])) {
                $finder->in($config['project']['directories']);
            }
            if (isset($config['project']['exclude']['directories'])) {
                $finder->exclude($config['project']['exclude']['directories']);
            }
        } else {
            $finder->in(getcwd());
        }

        $finder->files()->name('*.php');

        return $finder->files()->getIterator();
    }
}
