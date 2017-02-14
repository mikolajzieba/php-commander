<?php

/**
 * Created by PhpStorm.
 * User: mzieba <admin@hadriel.net>
 * Date: 13.02.17
 * Time: 12:19
 */
class Listing extends AbstractCommand
{

    /**
     * list all your listings
     */
    public function run($directory1, $drectory2)
    {
        echo 'Hi!'.PHP_EOL;
    }

    /**
     * Print help
     */
    public function help()
    {
        // TODO: Implement help() method.
    }
}
