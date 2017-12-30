<?php

namespace Bolt\Extension\JKazimir\EditLocking;

use Bolt\Nut\BaseCommand;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SocketServeCommand extends BaseCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('socket:serve')
            ->setDescription('Run the socket server for edit locking')
            ->addArgument('address', InputArgument::OPTIONAL, 'Address:port', '0.0.0.0')
            ->addOption('port', 'p', InputOption::VALUE_REQUIRED, 'Address port number', '8080')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $address = $input->getArgument('address');
        if (strpos($address, ':') === false) {
            $address .= ':' . $input->getOption('port');
        }

        if ($this->isOtherServerProcessRunning($address)) {
            $this->io->error(sprintf('A process is already listening on %s', $address));

            return 1;
        }

        list($hostname, $port) = explode(':', $address);

        $server = IoServer::factory(
            new HttpServer(
                new WsServer(new LockingServer())
            ),
            $port,
            $hostname
        );

        $this->io->success(sprintf('Server running on ws://%s', $address));
        $this->io->comment('Quit the server with CONTROL-C.');

        $server->run();
    }

    /**
     * Determines if another process is bound to the given address and port.
     *
     * @param string $address An address/port tuple
     *
     * @return bool
     */
    protected function isOtherServerProcessRunning($address)
    {
        list($hostname, $port) = explode(':', $address);

        $fp = @fsockopen($hostname, $port, $errno, $errstr, 5);

        if ($fp !== false) {
            fclose($fp);

            return true;
        }

        return false;
    }
}
