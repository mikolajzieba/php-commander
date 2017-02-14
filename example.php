<?php
include('AbstractCommand.php');
include('CommandRouter.php');
include('Listing.php');
include('Process.php');

(new CommandRouter($argv))
    ->register('list', new Listing())
    ->register('proccess', new Process())
    ->run();

