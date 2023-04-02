<?php

namespace App\Jobs\Ssh;

class CreateTunnelJob extends AbstractTunnelJob
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
    const WAIT_AFTER_CONNECTION = 800_000;

    /**
     * Log messages for troubleshooting.
     */
    const NOHUP_LOG = '/dev/null';

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
}
