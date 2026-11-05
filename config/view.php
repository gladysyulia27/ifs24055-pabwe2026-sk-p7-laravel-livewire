<?php
return ['paths'=>[resource_path('views')],'compiled'=>env('VIEW_COMPILED_PATH',env('APP_ENV')==='vercel'?'/tmp/laravel-views':storage_path('framework/views'))];
