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

use UndectedWebdriver\Exceptions\PlatformUnsupported;
use UndectedWebdriver\Exceptions\UnableMakeFolder;
use ZipArchive;

class Chrome
{
    private $urlRepo = 'https://chromedriver.storage.googleapis.com';

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
                throw new PlatformUnsupported('Platform '.strtolower(php_uname('s')).' is not supported!', 1);

                break;
        }

        if (!file_exists($this->dataPath()) && !mkdir($this->dataPath(), 0777, true)) {
            throw new UnableMakeFolder('Unable to make dataPath at '.$this->dataPath());
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
        $default = 'chromedriver';

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
                throw new UnableMakeFolder('Can\'t make temp folder!', 1);
            }
            $this->unzipPackage($this->fetchPackage());
        }

        return $this->patch();
    }

    private function zipName()
    {
        $default = 'chromedriver_';

        switch ($this->platform) {
            case 'windows':
                return $default.'win32.zip';

                break;

            case 'linux':
                return $default.'linux64.zip';

                break;

            default:
                return '';

                break;
        }
    }

    private function unzipPackage($filepath)
    {
        $zip = new ZipArchive();
        $zip->open($filepath);
        $zip->extractTo($this->tempFolder);
        $zip->close();
        chmod($this->tempFolder.DIRECTORY_SEPARATOR.$this->exeName(), 0755);
        rrmdir($filepath);
    }

    private function fetchPackage()
    {
        $url = $this->urlRepo.'/'.$this->fetchReleaseVersion().'/'.$this->zipName();
        file_put_contents($this->tempFolder.DIRECTORY_SEPARATOR.$this->zipName(), fopen($url, 'r'));

        return $this->tempFolder.DIRECTORY_SEPARATOR.$this->zipName();
    }

    private function fetchReleaseVersion()
    {
        return file_get_contents($this->urlRepo.'/LATEST_RELEASE');
    }

    private function patch()
    {
        if (!$this->isBinaryPatched()) {
            $this->patchExe();
        }

        return $this->isBinaryPatched();
    }

    private function isBinaryPatched()
    {
        if (file_exists($this->dataPath().DIRECTORY_SEPARATOR.$this->exeName())) {
            $file = fopen($this->dataPath().DIRECTORY_SEPARATOR.$this->exeName(), 'rb');
            $line = fread($file, filesize($this->dataPath().DIRECTORY_SEPARATOR.$this->exeName()));
            if (preg_match('/cdc_.{22}/m', $line)) {
                return false;
            }

            return true;
        }

        return false;
    }

    private function patchExe()
    {
        $replacement = $this->genRandomCDC();
        $file = fopen($this->tempFolder.DIRECTORY_SEPARATOR.$this->exeName(), 'rb');
        $line = fread($file, filesize($this->tempFolder.DIRECTORY_SEPARATOR.$this->exeName()));
        $line = preg_replace('/cdc_.{22}/m', $replacement, $line);

        $file = fopen($this->dataPath().DIRECTORY_SEPARATOR.$this->exeName(), 'wb');
        fwrite($file, $line);
        fclose($file);
        chmod($this->dataPath().DIRECTORY_SEPARATOR.$this->exeName(), 0777);
        rrmdir($this->tempFolder);
    }

    private function genRandomCDC()
    {
        $line = generateRandomString(3).'_';
        $line .= generateRandomString(22);

        return $line;
    }
}
