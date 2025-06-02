<?php

namespace Modules\CpanelAutomation\Http\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * CPanel Helper Class
 *
 * A helper class to interact with cPanel API for creating databases,
 * database users, and subdomains from a script admin panel.
 */
class CpanelHelper {
    protected $cpanelUrl;
    protected $cpanelToken;
    protected $cpanelUser;

    public function __construct($cpanelUrl,$cpanelToken,$cpanelUser)
    {
        $this->cpanelUrl   = $cpanelUrl;
        $this->cpanelToken = $cpanelToken;
        $this->cpanelUser  = $cpanelUser;
    }

    protected function request($module, $function, $params = [])
    {
        $response = Http::withHeaders([
            'Authorization' => 'cpanel ' . $this->cpanelUser . ':' . $this->cpanelToken,
        ])->get("{$this->cpanelUrl}/execute/{$module}/{$function}", $params);
        return $response->json();
    }

    public function createDatabaseUser($username, $password)
    {
        return $this->request('Mysql', 'create_user', [
            'name'     => $this->getDatabasePrefix().$username,
            'password' => $password,
        ]);
    }

    public function createDatabase($database)
    {
        return $this->request('Mysql', 'create_database', [
            'name' => Str::limit($this->getDatabasePrefix().$database, 64),
        ]);
    }
    protected function getDatabasePrefix()
    {
        return $this->cpanelUser.'_';
    }
    public function assignUserToDatabase($username, $database)
    {
        return $this->request('Mysql', 'set_privileges_on_database', [
            'user'       => $this->getDatabasePrefix().$username,
            'database'   => $this->getDatabasePrefix().$database,
            'privileges' => 'ALL PRIVILEGES',
        ]);
    }

    public function deleteDatabase($database)
    {
        return $this->request('Mysql', 'delete_database', [
            'name' => $this->getDatabasePrefix().$database,
        ]);
    }

    public function deleteDatabaseUser($username)
    {
        return $this->request('Mysql', 'delete_user', [
            'name' => $this->getDatabasePrefix().$username,
        ]);
    }

}
