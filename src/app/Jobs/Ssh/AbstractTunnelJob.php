<?php

namespace App\Jobs\Ssh;

use App\Jobs\AbstractJob;

abstract class AbstractTunnelJob extends AbstractJob
{
    /**
     * The Command for checking if the tunnel is open
     */
    protected string $ncCommand = 'nc -vz %s %d > /dev/null 2>&1';

    /**
     * The command for creating the tunnel
     */
    protected string $sshCommand = 'ssh -N -L %d:%s:%d -p %d %s@%s';

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        $localAddress = '127.0.0.1';
        $localPort = config('database.connections.sqlsrv.port');
        $sshConfig = config('ssh.1c');

        $this->ncCommand = sprintf($this->ncCommand, $localAddress, $localPort);

        $this->sshCommand = sprintf(
            $this->sshCommand,
            $localPort,
            $sshConfig['bind_address'],
            $sshConfig['bind_port'],
            $sshConfig['port'],
            $sshConfig['user'],
            $sshConfig['hostname']
        );
    }

    /**
     * Verifies whether the tunnel is active or not.
     */
    protected function verifyTunnel(): bool
    {
        return $this->runCommand($this->ncCommand);
    }

    /**
     * Runs a command and converts the exit code to a boolean
     */
    protected function runCommand(string $command): bool
    {
        $resultСode = 1;
        exec($command, $output, $resultСode);

        return $resultСode === 0;
    }
}
