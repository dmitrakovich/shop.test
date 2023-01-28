<?php

namespace App\Jobs\Ssh;

use App\Jobs\AbstractJob;

class CreateTunnelJob extends AbstractJob
{
    /**
     * How often it is checked if the tunnel is created.
     */
    const CONNECTION_TRIES = 1;

    /**
     * Wait a bit until next iteration (ms).
     */
    const WAIT_BEFORE_NEXT_ITERATION = 1_000_000;

    /**
     * Ensure we wait long enough for it to actually connect (ms).
     */
    const WAIT_AFTER_CONNECTION = 250_000;

    /**
     * Log messages for troubleshooting.
     */
    const NOHUP_LOG = '/dev/null';

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
     * Execute the job.
     */
    public function handle(): int
    {
        if ($this->verifyTunnel()) {
            return 1;
        }

        $this->createTunnel();

        for ($i = 0; $i < self::CONNECTION_TRIES; $i++) {
            if ($this->verifyTunnel()) {
                return 2;
            }
            usleep(self::WAIT_BEFORE_NEXT_ITERATION);
        }

        throw new \Exception(sprintf(
            "Could Not Create SSH Tunnel with command:\n\t%s\nCheck your configuration.",
            $this->sshCommand
        ));
    }

    /**
     * Creates the SSH Tunnel for us.
     */
    protected function createTunnel(): void
    {
        $this->runCommand(sprintf(
            'nohup %s >> %s 2>&1 &',
            $this->sshCommand,
            self::NOHUP_LOG
        ));

        usleep(self::WAIT_AFTER_CONNECTION);
    }

    /**
     * Verifies whether the tunnel is active or not.
     */
    protected function verifyTunnel(): bool
    {
        return $this->runCommand($this->ncCommand);
    }

    /*
     * Use pkill to kill the SSH tunnel
     */
    public function destroyTunnel(): bool
    {
        $sshCommand = preg_replace('/[\s]{2}[\s]*/', ' ', $this->sshCommand);

        return $this->runCommand('pkill -f "' . $sshCommand . '"');
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
