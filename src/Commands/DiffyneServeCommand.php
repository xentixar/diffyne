<?php

namespace Diffyne\Commands;

use Illuminate\Console\Command;

class DiffyneServeCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'diffyne:serve 
                            {--host=0.0.0.0 : The host to bind to}
                            {--port=8080 : The port to bind to}';

    /**
     * The console command description.
     */
    protected $description = 'Start the Diffyne WebSocket server for real-time updates';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $host = $this->option('host');
        $port = $this->option('port');

        $this->info('Diffyne WebSocket Server');
        $this->newLine();
        $this->line("Starting WebSocket server on {$host}:{$port}...");
        $this->newLine();
        $this->warn('⚠️  WebSocket server is currently not implemented.');
        $this->warn('    This feature is planned for a future release.');
        $this->newLine();
        $this->line('For now, Diffyne uses AJAX transport mode by default.');
        $this->line('To use AJAX mode, ensure your config is set to:');
        $this->line("  <fg=cyan>'transport' => 'ajax'</> in config/diffyne.php");
        $this->newLine();

        return self::SUCCESS;
    }
}
