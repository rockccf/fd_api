<?php
/**
 * @var \omnilight\scheduling\Schedule $schedule
 */

// Place here all of your cron jobs

//To check for 4d results (web scraping and populate the results)
$schedule->command('bet')->dailyAt('20:05');
$schedule->command('bet')->dailyAt('20:15');
$schedule->command('bet')->dailyAt('20:30');
$schedule->command('bet')->dailyAt('20:45');
$schedule->command('bet')->dailyAt('21:00');

// Schedule weekly job for specific day (0-6) and time...
$schedule->command('admin/reset-user-balance')->weeklyOn(1, '00:01');