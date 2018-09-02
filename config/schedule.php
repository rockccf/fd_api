<?php
/**
 * @var \omnilight\scheduling\Schedule $schedule
 */

// Place here all of your cron jobs

//To check for 4d results (web scraping and populate the results)
$schedule->command('bet')->dailyAt('20:00');
$schedule->command('bet')->dailyAt('20:15');
$schedule->command('bet')->dailyAt('20:30');