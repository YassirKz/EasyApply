<?php

use App\Http\Controllers\EntrepriseController;
use App\Http\Controllers\LettreCvController;
use App\Http\Controllers\EnvoiController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('entreprises.index');
});

Route::middleware(['auth'])->group(function () {
    // Dashboard redirect
    Route::get('/dashboard', function () {
        return redirect()->route('entreprises.index');
    })->name('dashboard');

    // Entreprises CRUD & Import & AI & Batch Deletion
    Route::get('/entreprises', [EntrepriseController::class, 'index'])->name('entreprises.index');
    Route::post('/entreprises', [EntrepriseController::class, 'store'])->name('entreprises.store');
    Route::get('/entreprises/{entreprise}/json', [EntrepriseController::class, 'showJson'])->name('entreprises.showJson');
    Route::put('/entreprises/{entreprise}', [EntrepriseController::class, 'update'])->name('entreprises.update');
    Route::delete('/entreprises/batch', [EntrepriseController::class, 'destroyBatch'])->name('entreprises.destroyBatch');
    Route::delete('/entreprises/all', [EntrepriseController::class, 'destroyAll'])->name('entreprises.destroyAll');
    Route::delete('/entreprises/{entreprise}', [EntrepriseController::class, 'destroy'])->name('entreprises.destroy');
    Route::post('/entreprises/import', [EntrepriseController::class, 'import'])->name('entreprises.import');
    Route::post('/entreprises/gemini-all', [EntrepriseController::class, 'generateAiAll'])->name('entreprises.geminiAll');
    Route::post('/entreprises/{entreprise}/gemini', [EntrepriseController::class, 'generateAi'])->name('entreprises.gemini');



    // Lettre & CV management
    Route::get('/lettre', [LettreCvController::class, 'editLettre'])->name('lettre.edit');
    Route::post('/lettre', [LettreCvController::class, 'updateLettre'])->name('lettre.update');
    Route::get('/cv', [LettreCvController::class, 'editCv'])->name('cv.edit');
    Route::post('/cv', [LettreCvController::class, 'updateCv'])->name('cv.update');
    Route::get('/cv/pdf', [LettreCvController::class, 'previewPdf'])->name('cv.pdf');
    Route::post('/cv/documents', [LettreCvController::class, 'uploadDocuments'])->name('cv.documents.upload');
    Route::get('/cv/documents/download', [LettreCvController::class, 'downloadDocuments'])->name('cv.documents.download');
    Route::delete('/cv/documents', [LettreCvController::class, 'deleteDocuments'])->name('cv.documents.delete');

    // Email Sending & Scheduling
    Route::post('/envoi-masse', [EnvoiController::class, 'envoyerMasse'])->name('envoi.masse');
    Route::post('/envoi-test', [EnvoiController::class, 'envoyerTest'])->name('envoi.test');
    Route::post('/envoi-programmer', [EnvoiController::class, 'programmerMasse'])->name('envoi.programmer');
    Route::delete('/entreprises/{entreprise}/programmer', [EnvoiController::class, 'annulerProgrammation'])->name('envoi.annulerProgrammation');

    // Profile Management
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

