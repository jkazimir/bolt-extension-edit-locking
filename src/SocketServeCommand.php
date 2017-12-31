<?php

namespace Bolt\Extension\JKazimir\EditLocking;

use Bolt\Nut\BaseCommand;
use Hoa\Socket\Server as SocketServer;
use Hoa\Websocket\Server as WSServer;
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
            ->addOption('secure', 's', InputOption::VALUE_REQUIRED, 'Use TLS', false)
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $protocol = $input->getOption('secure') ? 'wss:' : 'ws:';
        $address = $input->getArgument('address');
        if (strpos($address, ':') === false) {
            $address .= ':' . $input->getOption('port');
        }

        if ($this->isOtherServerProcessRunning($address)) {
            $this->io->error(sprintf('A process is already listening on %s', $address));

            return 1;
        }

        $server = new WSServer(new SocketServer("$protocol//$address"));

        $app = new LockingServerApp();
        $app->registerToServer($server);

        $this->io->success(sprintf('Server running on %s//%s', $protocol, $address));
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
