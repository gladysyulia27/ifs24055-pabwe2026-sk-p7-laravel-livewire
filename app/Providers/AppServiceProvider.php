<?php
namespace App\Providers;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use App\Features\Stock\Contracts\StockRepositoryInterface;
use App\Features\Stock\Repositories\StockRepository;
use App\Features\Ai\Contracts\AiClientInterface;
use App\Features\Ai\Services\OpenAiStockGeneratorService;
use App\Features\Stock\Models\Stock;
use App\Features\Stock\Policies\StockPolicy;
class AppServiceProvider extends ServiceProvider {
 public function register(): void {$this->app->bind(StockRepositoryInterface::class,StockRepository::class);$this->app->bind(AiClientInterface::class,OpenAiStockGeneratorService::class);}
 public function boot(): void {Gate::policy(Stock::class,StockPolicy::class);RateLimiter::for('1000-per-second',fn($request)=>Limit::perSecond(1000)->by($request->user()?->id?:$request->ip()));if(config('app.force_https')){\URL::forceScheme('https');}}
}
