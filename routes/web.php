<?php

use App\Http\Controllers\Admin\AuditController;
use App\Http\Controllers\Admin\DarkmodeController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\DashboardController;
use App\Http\Livewire\Bibliotheque\Etiquette\EtiquetteIndexComponent;
use App\Http\Livewire\Finance;
use App\Http\Livewire\Notification\NoficationIndexComponent;
use App\Http\Livewire\Profile\UserEditComponent;
use App\Http\Livewire\Roles;
use App\Http\Livewire\SchoolSettingsComponent;
use App\Http\Livewire\Scolarite\Eleve\PassageClasseSuperieureComponent;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', DashboardController::class)->name('home')->middleware('auth');
Route::get('perceptions/{perception}/verify', function ($perception) {
    return view('livewire.finance.cards.recu', ['perception' => $perception]);
})->name('perceptions.verify');


Route::get('darkmode/toggle', [DarkmodeController::class, 'toggle'])
    ->name('darkmode.toggle');

// parametres

//Users

Route::resource('users', UserController::class)->except(['show', 'edit']);
Route::get('users/{user}/edit', UserEditComponent::class)->name('users.edit');
Route::get('users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.password.autoreset');
// auth routes except register
Auth::routes([
    'register' => false,
    'reset' => false,
    'confirm' => false,
    'verify' => false
]);

Route::middleware('auth:web')->group(function () {
    Route::get('notifications/get', [NotificationController::class, 'getNotificationsData'])->name('notifications.get');
    Route::get('notifications', NoficationIndexComponent::class)->name('notifications.index');
    Route::get('/scolarite/eleves/passer-classe-superieure', PassageClasseSuperieureComponent::class)
        ->name('eleves.passer-classe-superieure');

    Route::get('/scolarite/eleves/passer-classe-superieure/{id}', PassageClasseSuperieureComponent::class)
        ->name('eleves.passer-classe-superieure');

    Route::get('roles', Roles\RolesIndexComponent::class)->name('roles.index');
    Route::get('roles/create', Roles\RoleModal::class)->name('roles.create');
    Route::get('roles/{role}', Roles\RoleModal::class)->name('roles.show');

    Route::get("audits", [AuditController::class, 'index'])->name("audits.index");
    Route::get("audits/{audit}", [AuditController::class, 'show'])->name("audits.show");

    Route::get('school-settings', SchoolSettingsComponent::class)->name('school-settings.index');

});



Route::view('test-miabi','test.miabi');


//Scolarite
require __DIR__ . '/modules/scolarite.php';
# Finance
require __DIR__ . '/modules/finance.php';
# Logistique
require __DIR__ . '/modules/logistique.php';
# Bibliothèque
require __DIR__ . '/modules/bibliotheque.php';

Route::match(['get', 'post'], 'git-deploy', function () {
    Artisan::call('git:deploy');
    return 'Deployed!';
});

