<?php

namespace App\Database;

use App\Jobs\Ssh\CreateTunnelJob;
use Illuminate\Database\SqlServerConnection as BaseSqlServerConnection;

/**
 * Custom SQL Server database connection that extends the base SQL Server connection.
 */
final class SqlServerConnection extends BaseSqlServerConnection
{
    /**
     * Creates an SSH tunnel for the SQL Server connection synchronously.
     *
     * @return self The current SQL Server connection instance.
     */
    public function createTunnel(): self
    {
        CreateTunnelJob::dispatchSync();

        return $this;
    }
}
