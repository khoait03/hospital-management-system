<?php

use App\Http\Middleware\CheckLin;
use App\Http\Middleware\Admin\CheckLogin as CheckLoginAdmin;
use App\Http\Middleware\Client\CheckLogin as CheckLoginClient;

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
        using: function () {

            $shopRoutes = [
                'shop.php'
            ];

            $clientRoutes = [

                'page.php',
                'auth.php',
                'profile.php',
                'book.php',
                'profile-doctor.php'

            ];

            $systemRoutes = [
                'system.php',
                'auth.php',
                'medicalRecord.php',
                'appointmentSchedule.php',
                'medicine.php',
                'medicineType.php',
                'schedule.php',
                'account.php',
                'blog.php',
                'serviceType.php',
                'service.php',
                'order.php',
                'ordermedicine.php',
                'account.php',
                'doctor/medicalRecordDoctor.php',
                'doctor/checkupHealth.php',
                'doctor/schedulesDoctor.php',
                'specialty.php',
                'profile.php',
                'patient.php',
                'sclinic.php',
                'product.php',
                'saleProduct.php',
                'coupon.php',
                'category.php',
                'doctor.php',
                'orderproduct.php',
                'borderlineResult.php'
            ];


            foreach ($clientRoutes as $route) {
                Route::middleware(['web',  'license'])
                    ->prefix('')
                    ->name('client.')
                    ->group(base_path("routes/client/{$route}"));
            }

            foreach ($systemRoutes as $route) {
                Route::middleware(['web','license'])
                    ->prefix('admin')
                    ->name('system.')
                    ->group(base_path("routes/system/{$route}"));
            }

            foreach ($shopRoutes as $route) {
                Route::middleware(['web','license'])
                    ->prefix('')
                    ->name('shop.')
                    ->group(base_path("routes/shop/{$route}"));
            }


            Route::fallback(function () {
                return response()->view('error.404', [], 404);
            });


            Route::middleware('web')
                    ->prefix('')

                    ->group(base_path("routes/web.php"));
        }
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'check_login_admin' => CheckLoginAdmin::class,
            'check_login_client' => CheckLoginClient::class,
            'license' => CheckLin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();