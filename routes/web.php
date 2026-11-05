<?php
use Illuminate\Support\Facades\Route;
use App\Features\Auth\Livewire\Login;
use App\Features\Auth\Livewire\Register;
use App\Features\Profile\Livewire\Profile;
use App\Features\Stock\Livewire\StockList;
use App\Features\Stock\Livewire\StockDetail;
use App\Features\Admin\Livewire\UserList;
use App\Features\Admin\Livewire\AdminUserStockList;
Route::get('/',fn()=>auth()->check()?redirect(auth()->user()->role==='admin'?'/admin/users':'/stocks'):redirect('/login'));
Route::middleware('guest')->group(function(){Route::get('/login',Login::class)->name('login');Route::get('/register',Register::class)->name('register');});
Route::post('/logout',function(){auth()->logout();request()->session()->invalidate();request()->session()->regenerateToken();return redirect('/login');})->middleware('auth')->name('logout');
Route::middleware(['auth','throttle:1000-per-second','role:member'])->group(function(){Route::get('/stocks',StockList::class)->name('stocks.index');Route::get('/stocks/{stock}',StockDetail::class)->middleware('owns.stock')->name('stocks.show');});
Route::middleware(['auth','throttle:1000-per-second'])->get('/profile',Profile::class)->name('profile');
Route::middleware(['auth','throttle:1000-per-second','role:admin'])->prefix('admin')->name('admin.')->group(function(){Route::get('/users',UserList::class)->name('users');Route::get('/users/{user}/stocks',AdminUserStockList::class)->name('users.stocks');});
