```shell
ssh -f -L local-port:remote-address:remote-port login-name >> logfile
```

* `ssh`: this is the call to the ssh command line program, which establishes the SSH connection.
* `-f`: this option “forks” the new SSH connection, sending it to the background and leaving control with the local shell. For port forwarding, this is necessary because we’ll be using local programs to communicate with the remote machine.
* `-N` describes we don't want to execute any remote commands, we just want the port forwarding.
* `-L local-port:remote-address:remote-port`: The -L option is for “local forwarding.” It means we want SSH to tunnel commands sent via a port on our local machine to a specific port on the remote machine; the colon-separated parameters that follow are the ports and address in question. (There is a -R option that does the opposite — “remote forwarding” — we don’t use it here but can be quite useful in other applications.)
* `login-name`: this is the login name used to establish an SSH connection to the remote host.
* `[command]`: this optional parameter is a command you would like to execute remotely as soon as the connection is established.
* `>> logfile`: As we will explain in a moment, we want this tunnel to close itself after we are done using it. Redirecting standard output to a log file prevents the program from “hanging” while stdout awaits an end-of-stream that won’t arrive unless the process is manually killed.

```shell
ssh -f -L 3307:127.0.0.1:3306 user@remote.example.com sleep 10 >> logfile
```
* **sleep 10**: When it comes to tunnel connections, we basically have two options: leave the connection open all the time or open it and close it as needed. We prefer the latter, and as such we don’t specify the “-N” option when establishing a tunnel, which would leave it open until the process is manually killed (bad for automation). Since `-N` is not specified, our tunnel will close itself as soon as its SSH session isn’t being used for anything. This is ideal behavior, except for the few seconds between when we create the tunnel and when we get a MySQL connection up and running via the tunnel. To buy us some time during this period, we issue the harmless “sleep 60” command when the tunnel is created, which basically buys us 60 seconds to get something else going through the tunnel before it closes itself. As long as a MySQL connection is established in that timeframe, we are all set.

* Use ssh public key instead of password
