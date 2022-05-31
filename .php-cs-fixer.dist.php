<?php

$header = <<<'EOF'
This file is a part of the PHP Undected Webdriver project.

Copyright (c) 2022-present Be Wilson <be.wilson@benjamin-wilson.co.uk>

This file is subject to the MIT license that is bundled
with this source code in the LICENSE.md file.
EOF;

$fixers = [
    '@PhpCsFixer'
];

$rules = [
    'header_comment' => ['header' => $header]
];

foreach ($fixers as $fix) {
    $rules[$fix] = true;
}

$config = new PhpCsFixer\Config();

return $config
    ->setRules($rules)
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->in(__DIR__)
    );
