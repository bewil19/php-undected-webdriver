<?php

/*
 * This file is a part of the PHP Undected Webdriver project.
 *
 * Copyright (c) 2022-present Be Wilson <be.wilson@benjamin-wilson.co.uk>
 *
 * This file is subject to the MIT license that is bundled
 * with this source code in the LICENSE.md file.
 */

require_once __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'autoload.php';

$sites = [
    'https://nowsecure.nl',
    'https://bot.sannysoft.com/',
];

$browsers = [
    // 'UndectedWebdriver\Firefox',
    'UndectedWebdriver\Chrome',
];

foreach ($browsers as $browser) {
    $example = new $browser();

    foreach ($sites as $site) {
        $example->get($site);

        $a = 0;
        while ($a <= 10) {
            sleep(5);
            ++$a;
        }
    }

    $example->driver()->close();
}
