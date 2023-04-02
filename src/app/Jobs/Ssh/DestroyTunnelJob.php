<?php

namespace App\Jobs\Ssh;

class DestroyTunnelJob extends AbstractTunnelJob
{
    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->destroyTunnel();
    }

    /*
     * Use pkill to kill the SSH tunnel
     */
    public function destroyTunnel(): bool
    {
        $sshCommand = preg_replace('/[\s]{2}[\s]*/', ' ', $this->sshCommand);

        return $this->runCommand('pkill -f "' . $sshCommand . '"');
    }
}
