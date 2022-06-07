<?php

/*
 * This file is a part of the PHP Undected Webdriver project.
 *
 * Copyright (c) 2022-present Be Wilson <be.wilson@benjamin-wilson.co.uk>
 *
 * This file is subject to the MIT license that is bundled
 * with this source code in the LICENSE.md file.
 */

namespace UndectedWebdriver;

use Facebook\WebDriver\Chrome\ChromeDriver;
use Facebook\WebDriver\Chrome\ChromeDriverService;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use UndectedWebdriver\Patchers\Chrome as PatchersChrome;

class Chrome
{
    private $driver;
    private $devTools;
    private $dataPath;

    public function __construct($options = null)
    {
        $patcher = new PatchersChrome();
        $patcher->auto();

        $this->dataPath = $patcher->dataPath();

        $this->fixExitFlag();

        if (empty($options)) {
            $options = new ChromeOptions();
        }

        $options->addArguments([
            '--user-data-dir='.$patcher->dataPath().DIRECTORY_SEPARATOR.'chrome_user-data',
            '--no-default-browser-check',
            '--no-first-run',
            '--no-service-autorun',
            '--disable-blink-features=AutomationControlled',
        ]);

        $options->setExperimentalOption('excludeSwitches', ['enable-automation']);
        $options->setExperimentalOption('useAutomationExtension', false);

        $capabilites = DesiredCapabilities::chrome();
        $capabilites->setCapability(ChromeOptions::CAPABILITY_W3C, $options);

        $port = generateRandomNumber();
        $service = new ChromeDriverService($patcher->dataPath().DIRECTORY_SEPARATOR.$patcher->exeName(), $port, ['--port='.$port]);

        $this->driver = ChromeDriver::startUsingDriverService($service, $capabilites);

        $this->driver->executeScript("Object.defineProperty(navigator, 'webdriver', {get: () => undefined})");

        $this->devTools = $this->driver->getDevTools();
    }

    public function get(string $url)
    {
        if (is_array($this->getCDCprops())) {
            $this->removeCDCprops();
        }

        return $this->driver->get($url);
    }

    public function driver()
    {
        return $this->driver;
    }

    private function getCDCprops()
    {
        return $this->driver->executeScript('
        let objectToInspect = window,
        result = [];
    while(objectToInspect !== null)
    { result = result.concat(Object.getOwnPropertyNames(objectToInspect));
      objectToInspect = Object.getPrototypeOf(objectToInspect); }
    return result.filter(i => i.match(/.+_.+_(Array|Promise|Symbol)/ig))');
    }

    private function removeCDCprops()
    {
        $this->devTools->execute('Page.addScriptToEvaluateOnNewDocument', [
            'source' => "
            let objectToInspect = window,
                        result = [];
                    while(objectToInspect !== null) 
                    { result = result.concat(Object.getOwnPropertyNames(objectToInspect));
                      objectToInspect = Object.getPrototypeOf(objectToInspect); }
                    result.forEach(p => p.match(/.+_.+_(Array|Promise|Symbol)/ig)
                                        &&delete window[p]&&console.log('removed',p))",
        ]);
    }

    private function fixExitFlag()
    {
        $file = $this->dataPath.DIRECTORY_SEPARATOR.'chrome_user-data'.DIRECTORY_SEPARATOR.'Default'.DIRECTORY_SEPARATOR.'Preferences';

        if (file_exists($file)) {
            $config = file_get_contents($file);
            $config = json_decode($config);
            if ('Normal' !== $config->profile->exit_type) {
                $config->profile->exit_type = 'Normal';
            }
            $config = json_encode($config);
            file_put_contents($file, $config);
        }
    }
}
