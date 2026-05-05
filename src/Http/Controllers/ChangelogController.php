<?php

namespace Appload\ProjectsHub\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\File;

class ChangelogController extends Controller
{
    public  function getChangelogJson(Request $request)
    {
        $path = resource_path('.changes/changelog.json');

        if (!File::exists($path)) {
            return response()->json([
                'message' => 'File not found'
            ], Response::HTTP_NOT_FOUND);
        }

        $json = File::get($path);

        return response($json)
            ->header('Content-Type', 'application/json');
    }
}
