<?php
$path = $argv[1] ?? 'coverage.xml';
if (!is_file($path)) { fwrite(STDERR, "Coverage report not found: {$path}\n"); exit(1); }
$xml = simplexml_load_file($path);
if (!$xml || !isset($xml->project->metrics)) { fwrite(STDERR, "Invalid Clover coverage report.\n"); exit(1); }
$metrics = $xml->project->metrics;
$total = (int) $metrics['statements'];
$covered = (int) $metrics['coveredstatements'];
$coverage = $total > 0 ? ($covered / $total) * 100 : 0;
printf("Statement coverage: %.2f%% (%d/%d)\n", $coverage, $covered, $total);
if ($coverage < 100) { fwrite(STDERR, "Coverage gate failed: app/ must reach 100%.\n"); exit(1); }
