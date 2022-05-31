<?php

/*
 * This file is a part of the PHP Undected Webdriver project.
 *
 * Copyright (c) 2022-present Be Wilson <be.wilson@benjamin-wilson.co.uk>
 *
 * This file is subject to the MIT license that is bundled
 * with this source code in the LICENSE.md file.
 */

function rrmdir($dir)
{
    if (true == is_dir($dir)) {
        $files = scandir($dir);
        foreach ($files as $file) {
            if ('.' != $file && '..' != $file) {
                rrmdir($dir.DIRECTORY_SEPARATOR.$file);
            }
        }
        rmdir($dir);
    } elseif (true == file_exists($dir)) {
        unlink($dir);
    }
}

function rcopy($src, $dst)
{
    if (true == file_exists($dst)) {
        rrmdir($dst);
    }
    if (true == is_dir($src)) {
        mkdir($dst);
        $files = scandir($src);
        foreach ($files as $file) {
            if ('.' != $file && '..' != $file) {
                rcopy($src.DIRECTORY_SEPARATOR.$file, $dst.DIRECTORY_SEPARATOR.$file);
            }
        }
    } elseif (true == file_exists($src)) {
        copy($src, $dst);
    }
}

function generateRandomString($length = 10)
{
    $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; ++$i) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }

    return $randomString;
}

function generateRandomNumber($length = 4)
{
    $numbers = '123456789';
    $charactersLength = strlen($numbers);
    $randomString = '';
    for ($i = 0; $i < $length; ++$i) {
        $randomString .= $numbers[rand(0, $charactersLength - 1)];
    }

    return $randomString;
}
