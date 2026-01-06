<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Exports\{TableExport};
use Maatwebsite\Excel\Facades\Excel;
 
class AdminTableschemaController extends Controller
{
    private $user_id;
    private $role_id;
    private $clientID;
    private $ws_id;

    public function __construct()
    {
        $this->user_id = session('user_id');
        $this->role_id = session('role_id');
        $this->clientID = session('client');
        $this->ws_id = session('workspace');
    }

    public function index(Request $request)
    {
        $sql = "
            SELECT
                c.TABLE_NAME AS table_name,
                c.COLUMN_NAME AS column_name,
                c.ORDINAL_POSITION,
                c.COLUMN_DEFAULT,
                c.IS_NULLABLE,
                c.COLUMN_TYPE,
                c.NUMERIC_PRECISION,
                c.NUMERIC_SCALE,
                c.DATETIME_PRECISION,
                c.CHARACTER_SET_NAME,
                c.COLLATION_NAME,
                c.COLUMN_KEY,
                c.EXTRA,
                c.COLUMN_COMMENT,
                c.GENERATION_EXPRESSION,
                t.TABLE_ROWS,
                t.CREATE_TIME,
                t.UPDATE_TIME,
                t.TABLE_COLLATION
            FROM
                INFORMATION_SCHEMA.COLUMNS AS c
            JOIN
                INFORMATION_SCHEMA.TABLES AS t
                ON c.TABLE_SCHEMA = t.TABLE_SCHEMA
                AND c.TABLE_NAME = t.TABLE_NAME
            WHERE
                c.TABLE_SCHEMA = 'prod_nutriflow'
            ORDER BY
                c.TABLE_NAME,
                c.ORDINAL_POSITION
        ";
        $schema_response = DB::select($sql);
        
        $first = $schema_response[0];  
                    // object
        $keys  = array_keys((array)$first);
        $tableNames = array_unique(array_map(fn($r) => $r->table_name, $schema_response));
        sort($tableNames);
        
        return view('backend.table_schema.manage', compact('tableNames','schema_response','keys'));
    } 

    /**
     * Download data as an Excel file based on the provided Table.
     */
    public function download_excel($table)
    {
        $schema_response = $this->get_download_fields($table);
        $first = $schema_response[0];  
        $custom_heading = array_map('strtoupper', array_keys((array)$first));
        return Excel::download(new TableExport($custom_heading,$schema_response), 'Tableschema_data.xlsx');


    }

    /**
     * Download data as an Excel file based on the provided Table.
     */
    public function download_csv($table)
    {
        $schema_response = $this->get_download_fields($table);
        $schema_response = collect($schema_response)->map(function ($item) {
            return (array) $item;
        });
        $first = $schema_response[0];  
        $custom_heading = array_map('strtoupper', array_keys((array)$first));
        
        ob_start();
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=Tableschema.csv');

        ob_end_clean();
        $output = fopen('php://output', 'w');
        fputcsv($output, $custom_heading);
        foreach ($schema_response as $data_item) {
            fputcsv($output, $data_item);
        }
        fclose($output);
        return;
    }

    public function get_download_fields($table)
    {
        $tables = explode(',', $table); // ["cache_locks", "client_roles"]

        // quote each table name
        $tables = array_map(fn($t) => "'" . trim($t) . "'", $tables);

        // join again → 'cache_locks','client_roles'
        $tableList = implode(',', $tables);

        $sql = "
            SELECT
                c.TABLE_NAME AS table_name,
                c.COLUMN_NAME AS column_name,
                c.ORDINAL_POSITION,
                c.COLUMN_DEFAULT,
                c.IS_NULLABLE,
                c.COLUMN_TYPE,
                c.NUMERIC_PRECISION,
                c.NUMERIC_SCALE,
                c.DATETIME_PRECISION,
                c.CHARACTER_SET_NAME,
                c.COLLATION_NAME,
                c.COLUMN_KEY,
                c.EXTRA,
                c.COLUMN_COMMENT,
                c.GENERATION_EXPRESSION,
                t.TABLE_ROWS,
                t.CREATE_TIME,
                t.UPDATE_TIME,
                t.TABLE_COLLATION
            FROM
                INFORMATION_SCHEMA.COLUMNS AS c
            JOIN
                INFORMATION_SCHEMA.TABLES AS t
                ON c.TABLE_SCHEMA = t.TABLE_SCHEMA
                AND c.TABLE_NAME = t.TABLE_NAME
            WHERE
                c.TABLE_SCHEMA = 'prod_nutriflow'
                AND c.TABLE_NAME IN ($tableList)
            ORDER BY
                c.TABLE_NAME,
                c.ORDINAL_POSITION
        ";
        return DB::select($sql);
    }

}
