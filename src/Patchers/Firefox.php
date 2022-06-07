<?php

/*
 * This file is a part of the PHP Undected Webdriver project.
 *
 * Copyright (c) 2022-present Be Wilson <be.wilson@benjamin-wilson.co.uk>
 *
 * This file is subject to the MIT license that is bundled
 * with this source code in the LICENSE.md file.
 */

namespace UndectedWebdriver\Patchers;

use Exception;
use PharData;
use ZipArchive;

class Firefox
{
    private $urlRepo = 'https://github.com/mozilla/geckodriver';

    private $osName;

    private $platform;
    private $tempFolder;

    public function __construct()
    {
        $this->osName = strtolower(substr(php_uname('s'), 0, 3));

        switch ($this->osName) {
            case 'win':
                $this->platform = 'windows';

                break;

            case 'lin':
                $this->platform = 'linux';

                break;

            default:
                throw new Exception('Platform '.strtolower(php_uname('s')).' is not supported!', 1);

                break;
        }

        if (!file_exists($this->dataPath()) && !mkdir($this->dataPath(), 0777, true)) {
            throw new Exception('Unable to make dataPath at '.$this->dataPath());
        }
    }

    public function dataPath()
    {
        switch ($this->platform) {
            case 'windows':
                return $_SERVER['APPDATA'].DIRECTORY_SEPARATOR.'undetectedWebDriverPHP';

                break;

            case 'linux':
                return $_SERVER['HOME'].DIRECTORY_SEPARATOR.'.local'.DIRECTORY_SEPARATOR.'undetectedWebDriverPHP';

                break;

            default:
                return '';

                break;
        }
    }

    public function exeName()
    {
        $default = 'geckodriver';

        switch ($this->platform) {
            case 'windows':
                return $default.'.exe';

                break;

            case 'linux':
                return $default;

                break;

            default:
                return '';

                break;
        }
    }

    public function auto()
    {
        if (!$this->isBinaryPatched()) {
            $this->tempFolder = $this->dataPath().DIRECTORY_SEPARATOR.'tmpFiles';
            rrmdir($this->tempFolder);
            if (!mkdir($this->tempFolder, 0777, true)) {
                throw new Exception('Can\'t make temp folder!', 1);
            }
            $this->unzipPackage($this->fetchPackage());
        }

        return $this->patch();
    }

    private function getLatestVersion()
    {
        $url = $this->urlRepo.'/releases/latest';
        $page = file_get_contents($url);
        $re = '/Release \d+.\d+.\d+/m';
        preg_match($re, $page, $matches);

        return str_replace('Release ', '', $matches[0]);
    }

    private function zipName()
    {
        $default = 'geckodriver-v'.$this->getLatestVersion().'-';

        switch ($this->platform) {
            case 'windows':
                return $default.'win32.zip';

                break;

            case 'linux':
                return $default.'linux64.tar.gz';

                break;

            default:
                return '';

                break;
        }
    }

    private function unzipPackage($filepath)
    {
        if (strstr($filepath, '.tar.gz')) {
            $zip = new PharData($filepath);
            $zip->extractTo($this->tempFolder);
        } else {
            $zip = new ZipArchive();
            $zip->open($filepath);
            $zip->extractTo($this->tempFolder);
            $zip->close();
        }

        chmod($this->tempFolder.DIRECTORY_SEPARATOR.$this->exeName(), 0755);
        rrmdir($filepath);
    }

    private function fetchPackage()
    {
        $url = $this->urlRepo.'/releases/download/v'.$this->getLatestVersion().'/'.$this->zipName();
        file_put_contents($this->tempFolder.DIRECTORY_SEPARATOR.$this->zipName(), fopen($url, 'r'));

        return $this->tempFolder.DIRECTORY_SEPARATOR.$this->zipName();
    }

    private function isBinaryPatched()
    {
        // todo
        return true;
    }

    private function patch()
    {
        if (!$this->isBinaryPatched()) {
            $this->patchExe();
        }

        return true;
    }

    private function patchExe()
    {
        $file = fopen($this->tempFolder.DIRECTORY_SEPARATOR.$this->exeName(), 'rb');
        $line = fread($file, filesize($this->tempFolder.DIRECTORY_SEPARATOR.$this->exeName()));

        $file = fopen($this->dataPath().DIRECTORY_SEPARATOR.$this->exeName(), 'wb');
        fwrite($file, $line);
        fclose($file);
        chmod($this->dataPath().DIRECTORY_SEPARATOR.$this->exeName(), 0777);
        rrmdir($this->tempFolder);
    }
}
