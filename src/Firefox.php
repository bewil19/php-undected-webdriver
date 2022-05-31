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

use Facebook\WebDriver\Firefox\FirefoxDriver;
use Facebook\WebDriver\Firefox\FirefoxDriverService;
use Facebook\WebDriver\Firefox\FirefoxOptions;
use Facebook\WebDriver\Firefox\FirefoxProfile;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use UndectedWebdriver\Patchers\Firefox as PatchersFirefox;

class Firefox
{
    private $driver;
    private $devTools;
    private $dataPath;

    public function __construct($options = null)
    {
        $patcher = new PatchersFirefox();
        $patcher->auto();

        $this->dataPath = $patcher->dataPath();

        if (true == empty($options)) {
            $options = new FirefoxOptions();
        }

        $profile = new FirefoxProfile();
        $profile->setPreference('dom.webdriver.enabled', false);
        $profile->setPreference('useAutomationExtension', false);

        $profile->setPreference('browser.startup.homepage_orverride.mstone', 'ignore');
        $profile->setPreference('startup.homepage_welcome_url.additional', 'about:blank');

        $options->setPreference('profile', $profile->encode());

        /*$options->addArguments([
            '--no-service-autorun',
            '--disable-blink-features=AutomationControlled',
        ]);

        $options->setExperimentalOption('excludeSwitches', ['enable-automation']);
        $options->setExperimentalOption('useAutomationExtension', false);*/

        $capabilites = DesiredCapabilities::firefox();
        $capabilites->setCapability(FirefoxOptions::CAPABILITY, $options);

        $port = generateRandomNumber();
        $service = new FirefoxDriverService($patcher->dataPath().DIRECTORY_SEPARATOR.$patcher->exeName(), $port, ['--port='.$port]);

        $this->driver = FirefoxDriver::startUsingDriverService($service, $capabilites);

        $this->driver->executeScript("Object.defineProperty(navigator, 'webdriver', {get: () => undefined})");

        // $this->devTools = $this->driver->getDevTools();
    }

    public function get(string $url)
    {
        return $this->driver->get($url);
    }

    public function driver()
    {
        return $this->driver;
    }
}
