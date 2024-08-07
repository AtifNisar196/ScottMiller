<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class ArtisanController extends Controller
{

    public function migrate_db()
    {
        // Execute migration command
        Artisan::call('migrate:fresh --seed');

        // Optionally, you can get the output of the migration command
        $output = Artisan::output();

        // Do something with the output if needed

        // Redirect or return a response
        return response()->json(['status' => true, 'message' => 'Migration completed successfully']);
    }

    public function migrate()
    {
        // Execute migration command
        Artisan::call('migrate');

        // Optionally, you can get the output of the migration command
        $output = Artisan::output();

        // Do something with the output if needed

        // Redirect or return a response
        return response()->json(['status' => true, 'message' => 'Migration completed successfully']);
    }
}