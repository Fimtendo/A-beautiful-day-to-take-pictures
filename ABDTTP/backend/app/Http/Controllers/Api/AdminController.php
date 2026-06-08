<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function tables()
    {
        $default = config('database.default');
        $driver = config("database.connections.{$default}.driver");

        $tables = [];

        if ($driver === 'sqlite') {
            $rows = DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%';");
            foreach ($rows as $r) {
                $tables[] = $r->name;
            }
        } else {
            // MySQL / MariaDB
            $dbName = DB::getDatabaseName();
            $rows = DB::select("SELECT table_name as name FROM information_schema.tables WHERE table_schema = ?", [$dbName]);
            foreach ($rows as $r) {
                $tables[] = $r->name;
            }
        }

        // attach counts
        $result = [];
        foreach ($tables as $t) {
            try {
                $count = DB::table($t)->count();
            } catch (\Throwable $e) {
                $count = null;
            }
            $result[] = ['table' => $t, 'count' => $count];
        }

        return response()->json($result);
    }

    public function table(Request $request, $name)
    {
        $limit = (int) $request->query('limit', 100);

        try {
            $rows = DB::table($name)->limit($limit)->get();
        } catch (\Throwable $e) {
            return response()->json(['error' => 'Table not found or error reading table', 'details' => $e->getMessage()], 400);
        }

        return response()->json($rows);
    }
}
