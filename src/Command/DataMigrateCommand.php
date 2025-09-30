<?php
namespace App\Command;
//Importaciones
use App\Service\DataMigrator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Output\OutputInterface;
// nombre y descripcion linea de comando en consola 
#[AsCommand(
    name: 'app:data-migrate',
    description: 'Migra datos de tiempos_importados a tiempos_exportados usando PDO'
)]
//clase principal con extends command de symfony 
class DataMigrateCommand extends Command
{
    public function __construct(private DataMigrator $dataMigrator)
    {
        parent::__construct();
    }
//Defincion de como se usa el comando
    protected function configure(): void
    {
        $this
            ->addArgument('limit',  InputArgument::OPTIONAL, 'Cantidad de registros a migrar', 10)
            ->addArgument('offset', InputArgument::OPTIONAL, 'Desde qué registro empezar (0 = primeros)', 0);
    }
//logica principal del comando se ejecuta cuando es llamado por consola
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io     = new SymfonyStyle($input, $output);
        $limit  = (int) $input->getArgument('limit');
        $offset = (int) $input->getArgument('offset');

        $migrados = $this->dataMigrator->migrate($limit, $offset);
        $io->success("Migración completada. Registros migrados: {$migrados}");
// llama a otro método del servicio para obtener los últimos registros que se insertaron
        $ultimos = $this->dataMigrator->getLastInsertedRecords(min($limit, 5));
        if ($ultimos) {
            $io->writeln('Últimos insertados (máx 5):');
            foreach ($ultimos as $row) {
                $io->writeln(json_encode($row, JSON_UNESCAPED_UNICODE));
            }
        }

        return Command::SUCCESS;
    }
}
