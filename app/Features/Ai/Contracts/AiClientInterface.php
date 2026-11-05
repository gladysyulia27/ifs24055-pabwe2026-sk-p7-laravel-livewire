<?php
namespace App\Features\Ai\Contracts;
interface AiClientInterface {public function generate(string $prompt,int $count=5):array;}
